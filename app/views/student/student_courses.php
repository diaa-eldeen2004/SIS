<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Student Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
// Handle enrollment requests and load course data
session_start();
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Logger.php';
$db = Database::getInstance()->getConnection();

// Initialize message variables
$message = isset($_GET['message']) ? $_GET['message'] : '';
$messageType = isset($_GET['type']) ? $_GET['type'] : 'info';

// Get current student ID from session
$studentId = null;
if (isset($_SESSION['user']['id'])) {
    // Look up student_id from students table using user_id
    try {
        $userStmt = $db->prepare("SELECT id FROM students WHERE user_id = ?");
        $userStmt->execute([$_SESSION['user']['id']]);
        $student = $userStmt->fetch(PDO::FETCH_ASSOC);
        if ($student) {
            $studentId = $student['id'];
        }
    } catch (Exception $e) {
        // If students table doesn't exist or error, studentId remains null
        error_log("Error getting student_id: " . $e->getMessage());
    }
}

// Handle enrollment request submission (single or bulk)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request-enrollment') {
    if (!$studentId) {
        header('Location: student_courses.php?message=' . urlencode('Please log in to request enrollment') . '&type=error');
        exit;
    }
    
    // Handle bulk enrollment requests
    if (isset($_POST['course_ids']) && is_array($_POST['course_ids'])) {
        $courseIds = array_map('intval', $_POST['course_ids']);
        $courseIds = array_filter($courseIds); // Remove empty values
        
        if (empty($courseIds)) {
            header('Location: student_courses.php?message=' . urlencode('Please select at least one course') . '&type=warning');
            exit;
        }
        
        // Enforce maximum of 5 courses
        if (count($courseIds) > 5) {
            header('Location: student_courses.php?message=' . urlencode('You can only select a maximum of 5 courses at a time.') . '&type=warning');
            exit;
        }
        
        try {
            $columns = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
            $hasStatusColumn = !empty($columns);
            
            $successCount = 0;
            $skippedCount = 0;
            $skippedCourses = [];
            
            foreach ($courseIds as $courseId) {
                // Check if already enrolled or has pending request
                $checkStmt = $db->prepare("SELECT id, status FROM student_courses WHERE student_id = ? AND course_id = ?");
                $checkStmt->execute([$studentId, $courseId]);
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    $skippedCount++;
                    // Get course name for message
                    $courseStmt = $db->prepare("SELECT course_code FROM courses WHERE id = ?");
                    $courseStmt->execute([$courseId]);
                    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
                    $skippedCourses[] = $course['course_code'] ?? 'Course #' . $courseId;
                    continue;
                }
                
                // Ensure status column supports 'pending'
                try {
                    $db->exec("ALTER TABLE `student_courses` MODIFY COLUMN `status` ENUM('pending','taking','taken','approved','rejected') DEFAULT 'pending'");
                } catch (Exception $e) {
                    // Ignore if already updated
                }
                
                // Insert enrollment request with pending status
                try {
                    // Try with status column first
                    $stmt = $db->prepare("INSERT INTO student_courses (student_id, course_id, status, enrolled_at) VALUES (?, ?, 'pending', NOW())");
                    $stmt->execute([$studentId, $courseId]);
                    $successCount++;
                    error_log("Enrollment request created: Student $studentId, Course $courseId, Status: pending");
                } catch (Exception $e) {
                    // If status column doesn't exist or 'pending' not in ENUM, insert without status
                    try {
                        $stmt = $db->prepare("INSERT INTO student_courses (student_id, course_id, enrolled_at) VALUES (?, ?, NOW())");
                        $stmt->execute([$studentId, $courseId]);
                        $successCount++;
                        error_log("Enrollment request created (no status): Student $studentId, Course $courseId");
                    } catch (Exception $e2) {
                        error_log("ERROR creating enrollment: " . $e2->getMessage());
                        $skippedCount++;
                        $skippedCourses[] = 'Course #' . $courseId;
                    }
                }
                
                // Get course info for logging
                $courseStmt = $db->prepare("SELECT course_code, course_name FROM courses WHERE id = ?");
                $courseStmt->execute([$courseId]);
                $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
                
                // Log the enrollment request
                Logger::info("Student requested enrollment in course", [
                    'student_id' => $studentId,
                    'course_id' => $courseId,
                    'course_code' => $course['course_code'] ?? 'N/A',
                    'course_name' => $course['course_name'] ?? 'N/A'
                ], 'enrollment');
            }
            
            $message = '';
            if ($successCount > 0) {
                $message = "✅ {$successCount} enrollment request(s) submitted successfully!\n\n";
                $message .= "📋 Status: Pending Approval\n";
                $message .= "⏳ Your request(s) are now waiting for IT Officer approval.\n";
                $message .= "📚 You can see your pending enrollments in the 'Enrolled Courses' section below.\n\n";
                if ($skippedCount > 0) {
                    $message .= "⚠️ {$skippedCount} course(s) skipped (already enrolled or pending): " . implode(', ', $skippedCourses);
                }
            } else {
                $message = "⚠️ {$skippedCount} course(s) skipped (already enrolled or pending): " . implode(', ', $skippedCourses);
            }
            
            header('Location: student_courses.php?message=' . urlencode($message) . '&type=' . ($successCount > 0 ? 'success' : 'warning'));
            exit;
        } catch (Exception $e) {
            Logger::error("Error submitting enrollment requests", ['error' => $e->getMessage()], 'enrollment');
            header('Location: student_courses.php?message=' . urlencode('Error submitting enrollment requests: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    }
    // Handle single course enrollment (backward compatibility)
    elseif (isset($_POST['course_id'])) {
        try {
            $courseId = (int)$_POST['course_id'];
            
            // Check if already enrolled or has pending request
            $checkStmt = $db->prepare("SELECT id, status FROM student_courses WHERE student_id = ? AND course_id = ?");
            $checkStmt->execute([$studentId, $courseId]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                if ($existing['status'] === 'pending') {
                    header('Location: student_courses.php?message=' . urlencode('You already have a pending enrollment request for this course') . '&type=warning');
                } else {
                    header('Location: student_courses.php?message=' . urlencode('You are already enrolled in this course') . '&type=warning');
                }
                exit;
            }
            
            // Get course info for logging
            $courseStmt = $db->prepare("SELECT course_code, course_name FROM courses WHERE id = ?");
            $courseStmt->execute([$courseId]);
            $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if status column exists
            $columns = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
            $hasStatusColumn = !empty($columns);
            
            // Insert enrollment request with pending status
            if ($hasStatusColumn) {
                $stmt = $db->prepare("INSERT INTO student_courses (student_id, course_id, status, enrolled_at) VALUES (?, ?, 'pending', NOW())");
            } else {
                // Try enrolled_at, fallback to created_at
                try {
                    $stmt = $db->prepare("INSERT INTO student_courses (student_id, course_id, enrolled_at) VALUES (?, ?, NOW())");
                } catch (Exception $e2) {
                    $stmt = $db->prepare("INSERT INTO student_courses (student_id, course_id, created_at) VALUES (?, ?, NOW())");
                }
            }
            $stmt->execute([$studentId, $courseId]);
            
            // Log the enrollment request
            Logger::info("Student requested enrollment in course", [
                'student_id' => $studentId,
                'course_id' => $courseId,
                'course_code' => $course['course_code'] ?? 'N/A',
                'course_name' => $course['course_name'] ?? 'N/A'
            ], 'enrollment');
            
            header('Location: student_courses.php?message=' . urlencode('Enrollment request submitted successfully. Waiting for IT approval.') . '&type=success');
            exit;
        } catch (Exception $e) {
            Logger::error("Error submitting enrollment request", ['error' => $e->getMessage()], 'enrollment');
            header('Location: student_courses.php?message=' . urlencode('Error submitting enrollment request: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    }
}

// Load enrolled courses
$enrolledCourses = [];
if ($studentId) {
    try {
        // Check if status column exists
        $columns = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
        $hasStatusColumn = !empty($columns);
        
        $sql = "
            SELECT sc.*, c.course_code, c.course_name, c.description,
                   GROUP_CONCAT(DISTINCT CONCAT(d.first_name, ' ', d.last_name) SEPARATOR ', ') as instructors
            FROM student_courses sc
            LEFT JOIN courses c ON sc.course_id = c.id
            LEFT JOIN doctor_courses dc ON c.id = dc.course_id
            LEFT JOIN doctors d ON dc.doctor_id = d.id
            WHERE sc.student_id = ?
        ";
        
        if ($hasStatusColumn) {
            $sql .= " AND (sc.status = 'taking' OR sc.status = 'taken' OR sc.status = 'pending' OR sc.status IS NULL)";
        }
        
        $sql .= " GROUP BY sc.id, sc.course_id, sc.status, sc.enrolled_at, c.course_code, c.course_name, c.description ORDER BY sc.enrolled_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$studentId]);
        $enrolledCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        Logger::error("Error loading enrolled courses", ['error' => $e->getMessage()], 'student');
    }
}

// Load available courses (courses not yet enrolled or pending)
$availableCourses = [];
try {
    // Get all course IDs that the student is enrolled in (including pending)
    $enrolledCourseIds = [];
    if ($studentId) {
        try {
            $enrolledStmt = $db->prepare("SELECT DISTINCT course_id FROM student_courses WHERE student_id = ? AND course_id IS NOT NULL");
            $enrolledStmt->execute([$studentId]);
            $enrolledCourseIds = $enrolledStmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            // If query fails, just use empty array
            $enrolledCourseIds = [];
        }
    }
    
    // First, ensure status column supports 'pending' for student_courses
    try {
        $db->exec("ALTER TABLE `student_courses` MODIFY COLUMN `status` ENUM('pending','taking','taken','approved','rejected') DEFAULT 'pending'");
    } catch (Exception $e) {
        // Ignore if already updated
    }
    
    // SIMPLIFIED: Get ALL courses from database (removed status filter to show all courses)
    $sql = "SELECT c.id, c.course_code, c.course_name, c.description, c.department, c.credits, c.status
            FROM courses c";
    
    // Build WHERE clause - ONLY exclude enrolled courses
    $whereConditions = [];
    $params = [];
    
    // Exclude enrolled courses (including pending)
    if (!empty($enrolledCourseIds)) {
        $placeholders = implode(',', array_fill(0, count($enrolledCourseIds), '?'));
        $whereConditions[] = "c.id NOT IN ($placeholders)";
        $params = $enrolledCourseIds;
    }
    
    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(" AND ", $whereConditions);
    }
    
    $sql .= " ORDER BY c.course_code ASC";
    
    // Debug
    error_log("=== AVAILABLE COURSES DEBUG ===");
    error_log("SQL: " . $sql);
    error_log("Params: " . json_encode($params));
    error_log("Enrolled Course IDs: " . json_encode($enrolledCourseIds));
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $availableCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Found " . count($availableCourses) . " available courses");
    
    // Remove duplicates by ID
    $uniqueCourses = [];
    foreach ($availableCourses as $course) {
        if (isset($course['id']) && !isset($uniqueCourses[$course['id']])) {
            $uniqueCourses[$course['id']] = $course;
        }
    }
    $availableCourses = array_values($uniqueCourses);
    
    error_log("After deduplication: " . count($availableCourses) . " courses");
    
} catch (Exception $e) {
    Logger::error("Error loading available courses", ['error' => $e->getMessage()], 'student');
    error_log("Error loading available courses: " . $e->getMessage());
}

// Load recent enrollment requests for this student
$recentEnrollmentRequests = [];
if ($studentId) {
    try {
        // Ensure status column supports 'pending'
        try {
            $db->exec("ALTER TABLE `student_courses` MODIFY COLUMN `status` ENUM('pending','taking','taken','approved','rejected') DEFAULT 'pending'");
        } catch (Exception $e) {
            // Ignore if already updated
        }
        
        // Check if status column exists
        $columns = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
        $hasStatusColumn = !empty($columns);
        
        // Check for requested_at column
        $requestedAtColumns = $db->query("SHOW COLUMNS FROM student_courses LIKE 'requested_at'")->fetchAll();
        $hasRequestedAt = !empty($requestedAtColumns);
        
        // SIMPLIFIED: Get ALL enrollment records for this student (don't filter by status)
        $sql = "
            SELECT sc.*, c.course_code, c.course_name, c.description, c.department, c.credits
            FROM student_courses sc
            LEFT JOIN courses c ON sc.course_id = c.id
            WHERE sc.student_id = ?
        ";
        
        // Order by most recent first
        if ($hasRequestedAt) {
            $sql .= " ORDER BY sc.requested_at DESC";
        } else {
            $sql .= " ORDER BY sc.enrolled_at DESC, sc.id DESC";
        }
        
        $sql .= " LIMIT 10";
        
        // Debug
        error_log("=== RECENT ENROLLMENT REQUESTS QUERY ===");
        error_log("SQL: " . $sql);
        error_log("Student ID: " . $studentId);
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$studentId]);
        $recentEnrollmentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Found " . count($recentEnrollmentRequests) . " recent enrollment requests");
        
        // Debug: Log each request
        foreach ($recentEnrollmentRequests as $idx => $req) {
            error_log("Request #" . ($idx + 1) . ": Course ID=" . ($req['course_id'] ?? 'N/A') . ", Status=" . ($req['status'] ?? 'NULL') . ", Course=" . ($req['course_code'] ?? 'N/A'));
        }
        
    } catch (Exception $e) {
        Logger::error("Error loading recent enrollment requests", ['error' => $e->getMessage()], 'student');
        error_log("ERROR loading recent enrollment requests: " . $e->getMessage());
    }
}
?>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" title="Toggle Sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" title="Switch to dark mode">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-graduation-cap"></i> Student Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="student_dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="student_courses.php" class="nav-item active">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="student_schedule.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Official Schedule
            </a>
            <a href="student_assignments.php" class="nav-item">
                <i class="fas fa-tasks"></i> Assignments
            </a>
            <a href="student_attendance.php" class="nav-item">
                <i class="fas fa-calendar-check"></i> Attendance
            </a>
            <a href="student_calendar.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Calendar
            </a>
            <a href="student_notifications.php" class="nav-item">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a href="student_profile.php" class="nav-item">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="../settings.php" class="nav-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="../auth/logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Content Header -->
        <header class="content-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="margin: 0; color: var(--text-primary);">My Courses</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Manage your enrolled courses and access course materials.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshCourses()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="showAvailableCourses()">
                        <i class="fas fa-plus"></i> Browse Courses
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Course Filter -->
            <section class="course-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search courses..." id="courseSearch" onkeyup="filterCourses()">
                        </div>
                        <div>
                            <select class="form-input" id="semesterFilter" onchange="filterCourses()">
                                <option value="">All Semesters</option>
                                <option value="Fall 2024">Fall 2024</option>
                                <option value="Spring 2024">Spring 2024</option>
                                <option value="Summer 2024">Summer 2024</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterCourses()">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="dropped">Dropped</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Recent Enrollment Requests -->
            <section class="recent-enrollment-requests" style="margin-bottom: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-clock" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Recent Enrollment Requests
                            <?php if (!empty($recentEnrollmentRequests)): ?>
                                <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-secondary); margin-left: 0.5rem;">
                                    (<?php echo count($recentEnrollmentRequests); ?>)
                                </span>
                            <?php endif; ?>
                        </h2>
                    </div>
                    <div style="padding: 1rem;">
                        <?php if (empty($recentEnrollmentRequests)): ?>
                            <div style="padding: 2rem; text-align: center;">
                                <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p style="color: var(--text-secondary); font-weight: 500; margin-bottom: 0.5rem;">No Recent Enrollment Requests</p>
                                <p style="color: var(--text-secondary); font-size: 0.9rem;">You haven't submitted any enrollment requests yet. Use the "Browse Courses" button above to request enrollment in courses.</p>
                                <?php if (!$studentId): ?>
                                    <p style="color: var(--error-color); font-size: 0.85rem; margin-top: 0.5rem;">Please log in to view your enrollment requests.</p>
                                <?php else: ?>
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 1rem; font-style: italic;">
                                        Debug: Student ID = <?php echo $studentId; ?> | Requests found = <?php echo count($recentEnrollmentRequests); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                        <div style="display: grid; gap: 1rem;">
                            <?php foreach ($recentEnrollmentRequests as $request): 
                                $requestStatus = $request['status'] ?? 'pending';
                                $requestDate = $request['requested_at'] ?? $request['enrolled_at'] ?? $request['created_at'] ?? 'N/A';
                                
                                // Determine status color and text
                                if ($requestStatus === 'pending' || empty($requestStatus)) {
                                    $statusColor = 'var(--warning-color)';
                                    $statusText = 'Pending Approval';
                                    $statusIcon = 'fa-clock';
                                } elseif ($requestStatus === 'approved' || $requestStatus === 'taking') {
                                    $statusColor = 'var(--success-color)';
                                    $statusText = 'Approved';
                                    $statusIcon = 'fa-check-circle';
                                } elseif ($requestStatus === 'rejected') {
                                    $statusColor = 'var(--error-color)';
                                    $statusText = 'Rejected';
                                    $statusIcon = 'fa-times-circle';
                                } else {
                                    $statusColor = 'var(--text-secondary)';
                                    $statusText = ucfirst($requestStatus);
                                    $statusIcon = 'fa-info-circle';
                                }
                                
                                // Format date
                                if ($requestDate !== 'N/A') {
                                    try {
                                        $dateObj = new DateTime($requestDate);
                                        $formattedDate = $dateObj->format('M j, Y g:i A');
                                    } catch (Exception $e) {
                                        $formattedDate = $requestDate;
                                    }
                                } else {
                                    $formattedDate = 'N/A';
                                }
                            ?>
                                <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background-color: var(--surface-color); display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                            <h3 style="margin: 0; color: var(--text-primary); font-size: 1.1rem;">
                                                <?php echo htmlspecialchars($request['course_name'] ?? 'Course Name Not Available'); ?>
                                            </h3>
                                            <span style="background-color: <?php echo $statusColor; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.85rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem;">
                                                <i class="fas <?php echo $statusIcon; ?>"></i>
                                                <?php echo htmlspecialchars($statusText); ?>
                                            </span>
                                        </div>
                                        <div style="display: flex; gap: 2rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                                            <div>
                                                <span style="color: var(--text-secondary); font-size: 0.9rem;">Course Code:</span>
                                                <strong style="color: var(--text-primary); margin-left: 0.5rem;"><?php echo htmlspecialchars($request['course_code'] ?? 'N/A'); ?></strong>
                                            </div>
                                            <?php if (!empty($request['department'])): ?>
                                                <div>
                                                    <span style="color: var(--text-secondary); font-size: 0.9rem;">Department:</span>
                                                    <span style="color: var(--text-primary); margin-left: 0.5rem;"><?php echo htmlspecialchars($request['department']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($request['credits'])): ?>
                                                <div>
                                                    <span style="color: var(--text-secondary); font-size: 0.9rem;">Credits:</span>
                                                    <span style="color: var(--text-primary); margin-left: 0.5rem;"><?php echo (int)$request['credits']; ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-secondary); font-size: 0.85rem;">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>Requested: <?php echo htmlspecialchars($formattedDate); ?></span>
                                        </div>
                                        <?php if (!empty($request['description'])): ?>
                                            <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.5rem 0 0 0; line-height: 1.5;">
                                                <?php echo htmlspecialchars($request['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($requestStatus === 'pending' || empty($requestStatus)): ?>
                                        <div style="text-align: right;">
                                            <div style="padding: 0.75rem; background-color: rgba(245, 158, 11, 0.1); border-left: 3px solid var(--warning-color); border-radius: 4px; margin-bottom: 0.5rem;">
                                                <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">
                                                    <i class="fas fa-info-circle" style="margin-right: 0.25rem;"></i>
                                                    Waiting for IT Officer approval
                                                </p>
                                            </div>
                                        </div>
                                    <?php elseif ($requestStatus === 'rejected'): ?>
                                        <div style="text-align: right;">
                                            <div style="padding: 0.75rem; background-color: rgba(239, 68, 68, 0.1); border-left: 3px solid var(--error-color); border-radius: 4px;">
                                                <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">
                                                    <i class="fas fa-exclamation-triangle" style="margin-right: 0.25rem;"></i>
                                                    Request was rejected
                                                </p>
                                            </div>
                                        </div>
                                    <?php elseif ($requestStatus === 'approved' || $requestStatus === 'taking'): ?>
                                        <div style="text-align: right;">
                                            <div style="padding: 0.75rem; background-color: rgba(16, 185, 129, 0.1); border-left: 3px solid var(--success-color); border-radius: 4px;">
                                                <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">
                                                    <i class="fas fa-check-circle" style="margin-right: 0.25rem;"></i>
                                                    Request approved! You are now enrolled.
                                                </p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Enrolled Courses -->
            <section class="enrolled-courses" style="margin-bottom: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-book-open" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Enrolled Courses
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="toggleView('grid')" id="gridViewBtn">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="btn btn-outline" onclick="toggleView('list')" id="listViewBtn">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Course Grid View -->
                    <div id="courseGridView" class="course-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
                        <?php if (empty($enrolledCourses)): ?>
                            <div style="grid-column: 1 / -1; padding: 3rem; text-align: center;">
                                <i class="fas fa-book-open" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p style="color: var(--text-secondary); font-weight: 500; margin-bottom: 0.5rem; font-size: 1.1rem;">No Enrolled Courses</p>
                                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">Browse available courses and request enrollment to get started.</p>
                                <button class="btn btn-primary" onclick="showAvailableCourses()">
                                    <i class="fas fa-plus"></i> Browse Courses
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($enrolledCourses as $course): 
                                $status = $course['status'] ?? 'taking';
                                if ($status === 'pending') {
                                    $statusText = 'Pending Approval';
                                    $statusColor = 'var(--warning-color)';
                                } elseif ($status === 'taking') {
                                    $statusText = 'Active';
                                    $statusColor = 'var(--success-color)';
                                } elseif ($status === 'taken') {
                                    $statusText = 'Completed';
                                    $statusColor = 'var(--primary-color)';
                                } else {
                                    $statusText = ucfirst($status);
                                    $statusColor = 'var(--primary-color)';
                                }
                                $semester = 'Fall 2024'; // Could be fetched from sections table
                            ?>
                            <div class="course-card" data-course="<?php echo htmlspecialchars($course['course_code'] ?? ''); ?>" data-semester="<?php echo $semester; ?>" data-status="<?php echo $status; ?>">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <div>
                                        <h3 class="course-title"><?php echo htmlspecialchars($course['course_name'] ?? 'Course'); ?></h3>
                                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.25rem 0;"><?php echo htmlspecialchars($course['course_code'] ?? 'N/A'); ?></p>
                                    </div>
                                    <div style="text-align: right;">
                                        <span style="background-color: <?php echo $statusColor; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500;">
                                            <?php echo htmlspecialchars($statusText); ?>
                                        </span>
                                        <?php if ($status === 'pending'): ?>
                                            <div style="margin-top: 0.5rem; padding: 0.75rem; background-color: rgba(245, 158, 11, 0.1); border-left: 3px solid var(--warning-color); border-radius: 4px;">
                                                <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">
                                                    <i class="fas fa-clock" style="margin-right: 0.25rem;"></i>
                                                    Waiting for IT Officer approval
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="course-description"><?php echo htmlspecialchars($course['description'] ?? 'No description available.'); ?></p>
                                <div class="course-meta">
                                    <div>
                                        <i class="fas fa-user" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                        <span style="font-size: 0.9rem;"><?php echo htmlspecialchars($course['instructors'] ?? 'TBD'); ?></span>
                                    </div>
                                    <div>
                                        <i class="fas fa-calendar" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                        <span style="font-size: 0.9rem;"><?php echo $semester; ?></span>
                                    </div>
                                </div>
                                <div style="margin-top: 1rem;">
                                    <?php if ($status === 'taken' && !empty($course['grade'])): ?>
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Final Grade</span>
                                            <span style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;"><?php echo htmlspecialchars($course['grade']); ?></span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: 100%; background-color: var(--success-color);"></div>
                                        </div>
                                    <?php else: ?>
                                        <?php 
                                        // Calculate progress (simplified: based on enrollment date)
                                        $enrolledDate = new DateTime($course['enrolled_at'] ?? 'now');
                                        $now = new DateTime();
                                        $daysSinceEnrollment = $now->diff($enrolledDate)->days;
                                        $progress = min(100, round(($daysSinceEnrollment / 105) * 100));
                                        ?>
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Progress</span>
                                            <span style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;"><?php echo $progress; ?>%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?php echo $progress; ?>%; background-color: var(--primary-color);"></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                    <button class="btn btn-primary" style="flex: 1;" onclick="viewCourse('<?php echo htmlspecialchars($course['course_code'] ?? ''); ?>')">
                                        <i class="fas fa-eye"></i> View Course
                                    </button>
                                    <?php if ($status === 'pending'): ?>
                                        <button class="btn btn-outline" onclick="cancelEnrollmentRequest(<?php echo $course['id']; ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php elseif ($status === 'taking'): ?>
                                        <button class="btn btn-outline" onclick="dropCourse(<?php echo $course['id']; ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline" onclick="viewTranscript('<?php echo htmlspecialchars($course['course_code'] ?? ''); ?>')">
                                            <i class="fas fa-file-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Course List View -->
                    <div id="courseListView" class="course-list" style="display: none;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Instructor</th>
                                    <th>Semester</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-course="calculus" data-semester="Fall 2024" data-status="active">
                                    <td>
                                        <div>
                                            <strong>Calculus I (MATH 101)</strong>
                                            <br><small style="color: var(--text-secondary);">Introduction to differential and integral calculus</small>
                                        </div>
                                    </td>
                                    <td>Dr. Sarah Smith</td>
                                    <td>Fall 2024</td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 100px;">
                                                <div class="progress-bar" style="width: 75%; background-color: var(--primary-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem;">75%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('calculus')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="dropCourse('calculus')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-course="cs101" data-semester="Fall 2024" data-status="active">
                                    <td>
                                        <div>
                                            <strong>Computer Science 101 (CS 101)</strong>
                                            <br><small style="color: var(--text-secondary);">Introduction to programming concepts using Python</small>
                                        </div>
                                    </td>
                                    <td>Dr. Michael Johnson</td>
                                    <td>Fall 2024</td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 100px;">
                                                <div class="progress-bar" style="width: 60%; background-color: var(--accent-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem;">60%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('cs101')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="dropCourse('cs101')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-course="physics" data-semester="Fall 2024" data-status="active">
                                    <td>
                                        <div>
                                            <strong>Physics I (PHYS 101)</strong>
                                            <br><small style="color: var(--text-secondary);">Mechanics, thermodynamics, and wave motion</small>
                                        </div>
                                    </td>
                                    <td>Dr. Emily Brown</td>
                                    <td>Fall 2024</td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 100px;">
                                                <div class="progress-bar" style="width: 45%; background-color: var(--success-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem;">45%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('physics')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="dropCourse('physics')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-course="english" data-semester="Fall 2024" data-status="active">
                                    <td>
                                        <div>
                                            <strong>English Literature (ENG 201)</strong>
                                            <br><small style="color: var(--text-secondary);">Survey of English literature from medieval to modern periods</small>
                                        </div>
                                    </td>
                                    <td>Dr. Robert Wilson</td>
                                    <td>Fall 2024</td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 100px;">
                                                <div class="progress-bar" style="width: 30%; background-color: var(--error-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem;">30%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('english')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="dropCourse('english')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-course="chemistry" data-semester="Spring 2024" data-status="completed">
                                    <td>
                                        <div>
                                            <strong>General Chemistry (CHEM 101)</strong>
                                            <br><small style="color: var(--text-secondary);">Fundamental principles of chemistry</small>
                                        </div>
                                    </td>
                                    <td>Dr. Lisa Davis</td>
                                    <td>Spring 2024</td>
                                    <td><span style="background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Completed</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 100px;">
                                                <div class="progress-bar" style="width: 100%; background-color: var(--success-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">A-</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('chemistry')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewTranscript('chemistry')">
                                                <i class="fas fa-file-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Course Statistics -->
            <section class="course-stats">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">5</div>
                        <div style="color: var(--text-secondary);">Total Courses</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">4</div>
                        <div style="color: var(--text-secondary);">Active Courses</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">1</div>
                        <div style="color: var(--text-secondary);">Completed</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">3.75</div>
                        <div style="color: var(--text-secondary);">Average GPA</div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Chat Widget -->
    <div class="chat-widget">
        <button class="chat-toggle" onclick="toggleChat()">
            <i class="fas fa-comments"></i>
        </button>
        <div class="chat-box">
            <div class="chat-header">
                <h3>Course Chat</h3>
                <button class="chat-close" onclick="closeChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chat-body">
                <form class="chat-form">
                    <div class="form-group">
                        <label class="form-label">From</label>
                        <input type="email" name="from" class="form-input" placeholder="john.doe@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="email" name="to" class="form-input" placeholder="instructor@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Course question" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-input" rows="4" placeholder="Type your message here..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Available Courses Modal -->
    <div id="availableCoursesModalOverlay" class="modal-overlay" onclick="closeAvailableCoursesModal()"></div>
    <div id="availableCoursesModal" class="modal">
        <div class="modal-content" style="max-width: 900px; max-height: 80vh;">
            <div class="modal-header">
                <h2>Browse Available Courses</h2>
                <button class="modal-close" onclick="closeAvailableCoursesModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: calc(80vh - 200px); padding: 1.5rem;">
                <?php 
                $courseCount = count($availableCourses);
                if (empty($availableCourses)): 
                ?>
                    <div style="padding: 2rem; text-align: center;">
                        <i class="fas fa-book" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                        <p style="color: var(--text-secondary); font-weight: 500;">No available courses found.</p>
                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">
                            <?php 
                            if (!$studentId) {
                                echo "Please log in to view available courses.";
                            } else {
                                echo "You may already be enrolled in all courses, or there are no courses in the database.";
                            }
                            ?>
                        </p>
                        <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 1rem; font-style: italic;">
                            Debug: Found <?php echo $courseCount; ?> courses | Student ID: <?php echo $studentId ?? 'Not logged in'; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem; padding: 0.5rem; background: rgba(37, 99, 235, 0.1); border-radius: 4px;">
                        <i class="fas fa-info-circle"></i> Showing <?php echo count($availableCourses); ?> available course(s)
                    </p>
                    <form id="bulkEnrollmentForm" method="POST" action="student_courses.php">
                        <input type="hidden" name="action" value="request-enrollment">
                        <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                    <input type="checkbox" id="selectAllCourses" onchange="toggleAllCourses()" style="width: 18px; height: 18px; cursor: pointer;">
                                    <strong style="color: var(--text-primary);">Select All</strong>
                                </label>
                            </div>
                            <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                <span id="selectedCount">0</span> course(s) selected (Maximum: 5)
                            </div>
                        </div>
                        <div style="display: grid; gap: 1rem;">
                            <?php 
                            foreach ($availableCourses as $course): 
                                if (!isset($course['id']) || empty($course['id'])) {
                                    continue; // Skip invalid courses
                                }
                            ?>
                                <div style="border: 2px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background-color: var(--surface-color); transition: all 0.2s;" class="course-option" data-course-id="<?php echo (int)$course['id']; ?>">
                                    <label style="display: flex; align-items: start; gap: 1rem; cursor: pointer; margin: 0;">
                                        <input type="checkbox" 
                                               name="course_ids[]" 
                                               value="<?php echo (int)$course['id']; ?>" 
                                               class="course-checkbox"
                                               onchange="handleCourseSelection(this)"
                                               style="width: 20px; height: 20px; margin-top: 0.25rem; cursor: pointer; flex-shrink: 0;">
                                        <div style="flex: 1;">
                                            <h3 style="margin: 0 0 0.25rem 0; color: var(--text-primary); font-size: 1.1rem;">
                                                <?php echo htmlspecialchars($course['course_name'] ?? 'Course Name Not Available'); ?>
                                            </h3>
                                            <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary); font-size: 0.9rem;">
                                                <strong style="color: var(--primary-color);"><?php echo htmlspecialchars($course['course_code'] ?? 'N/A'); ?></strong>
                                                <?php if (!empty($course['department'])): ?>
                                                    <span style="color: var(--text-secondary);"> • <?php echo htmlspecialchars($course['department']); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($course['credits'])): ?>
                                                    <span style="color: var(--text-secondary);"> • <?php echo (int)$course['credits']; ?> Credits</span>
                                                <?php endif; ?>
                                            </p>
                                            <?php if (!empty($course['description'])): ?>
                                                <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem; line-height: 1.5;">
                                                    <?php echo htmlspecialchars($course['description']); ?>
                                                </p>
                                            <?php else: ?>
                                                <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem; font-style: italic;">
                                                    No description available.
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                            <button type="button" class="btn btn-outline" onclick="closeAvailableCoursesModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="submitEnrollmentBtn" disabled>
                                <i class="fas fa-paper-plane"></i> Submit Enrollment Request(s)
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>University Portal</h3>
                <p>Comprehensive campus management system designed to streamline academic operations and enhance the learning experience.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="../index.html">Home</a>
                <a href="../app/about.html">About Us</a>
                <a href="../app/contact.html">Contact</a>
                <a href="../app/help_center.html">Help Center</a>
            </div>
            <div class="footer-section">
                <h3>User Access</h3>
                <a href="../auth/auth_login.html">Student Login</a>
                <a href="../auth/auth_login.html">Doctor Login</a>
                <a href="../auth/auth_login.html">Admin Login</a>
                <a href="../auth/auth_signup.html">Register</a>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p><i class="fas fa-envelope"></i> info@university.edu</p>
                <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                <p><i class="fas fa-map-marker-alt"></i> 123 University Ave, Campus City</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 University Portal. All rights reserved. | Privacy Policy | Terms of Service</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="../js/main.js"></script>
    <style>
        .course-option {
            transition: all 0.2s;
        }
        .course-option:hover {
            border-color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.05);
        }
        .course-option input[type="checkbox"]:checked ~ div,
        .course-option:has(input[type="checkbox"]:checked) {
            border-color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.1);
        }
        .modal.active {
            display: flex !important;
        }
        .modal-overlay.active {
            display: block !important;
        }
        #submitEnrollmentBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        #availableCoursesModal {
            z-index: 2000;
        }
        #availableCoursesModalOverlay {
            z-index: 1999;
        }
        .course-checkbox:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
    <script>
        // Define functions immediately (before DOMContentLoaded) so they're available for onclick handlers
        function showAvailableCourses() {
            try {
                console.log('showAvailableCourses called'); // Debug
                const modal = document.getElementById('availableCoursesModal');
                const overlay = document.getElementById('availableCoursesModalOverlay');
                
                if (!modal) {
                    console.error('Modal not found!');
                    alert('Modal not found. Please refresh the page.');
                    return;
                }
                
                console.log('Modal found, showing...'); // Debug
                
                // Show overlay
                if (overlay) {
                    overlay.classList.add('active');
                    overlay.style.display = 'block';
                    overlay.style.zIndex = '1999';
                }
                
                // Show modal
                modal.classList.add('active');
                modal.style.display = 'flex';
                modal.style.flexDirection = 'column';
                modal.style.zIndex = '2000';
                document.body.style.overflow = 'hidden';
                
                // Reset form
                const form = document.getElementById('bulkEnrollmentForm');
                if (form) {
                    form.reset();
                    // Wait a bit for form to reset, then update count
                    setTimeout(function() {
                        if (typeof updateSelectedCount === 'function') {
                            updateSelectedCount();
                        }
                    }, 10);
                }
            } catch (error) {
                console.error('Error in showAvailableCourses:', error);
                alert('Error opening modal: ' + error.message);
            }
        }
        
        function closeAvailableCoursesModal() {
            const modal = document.getElementById('availableCoursesModal');
            const overlay = document.getElementById('availableCoursesModalOverlay');
            
            if (overlay) {
                overlay.classList.remove('active');
                overlay.style.display = 'none';
            }
            if (modal) {
                modal.classList.remove('active');
                modal.style.display = 'none';
            }
            document.body.style.overflow = '';
            
            // Reset form
            const form = document.getElementById('bulkEnrollmentForm');
            if (form) {
                form.reset();
                updateSelectedCount();
            }
        }
        
        // Make functions globally accessible
        window.showAvailableCourses = showAvailableCourses;
        window.closeAvailableCoursesModal = closeAvailableCoursesModal;
        
        // Handle individual course checkbox selection (enforce 5 course max)
        function handleCourseSelection(checkbox) {
            const checkedCount = document.querySelectorAll('.course-checkbox:checked').length;
            const maxCourses = 5;
            
            if (checkbox.checked && checkedCount > maxCourses) {
                checkbox.checked = false;
                alert(`You can only select a maximum of ${maxCourses} courses at a time.`);
                return;
            }
            
            updateSelectedCount();
        }
        
        // Toggle all courses selection (respects 5 course limit)
        function toggleAllCourses() {
            const selectAll = document.getElementById('selectAllCourses');
            const checkboxes = document.querySelectorAll('.course-checkbox');
            const maxCourses = 5;
            
            if (selectAll && selectAll.checked) {
                // Select up to 5 courses
                let selected = 0;
                checkboxes.forEach(checkbox => {
                    if (selected < maxCourses) {
                        checkbox.checked = true;
                        checkbox.disabled = false;
                        selected++;
                    } else {
                        checkbox.checked = false;
                    }
                });
            } else {
                // Unselect all
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.disabled = false;
                });
            }
            updateSelectedCount();
        }
        
        // Update selected count and enable/disable submit button (with 5 course max)
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.course-checkbox:checked');
            const allCheckboxes = document.querySelectorAll('.course-checkbox');
            const count = checkboxes.length;
            const maxCourses = 5;
            const countSpan = document.getElementById('selectedCount');
            const submitBtn = document.getElementById('submitEnrollmentBtn');
            
            if (countSpan) {
                if (count >= maxCourses) {
                    countSpan.style.color = 'var(--warning-color)';
                    countSpan.textContent = count + ' (Maximum reached)';
                } else {
                    countSpan.style.color = 'var(--text-secondary)';
                    countSpan.textContent = count;
                }
            }
            
            if (submitBtn) {
                submitBtn.disabled = count === 0;
                if (count === 0) {
                    submitBtn.style.opacity = '0.5';
                    submitBtn.style.cursor = 'not-allowed';
                } else {
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                }
            }
            
            // Disable unchecked checkboxes if max reached
            allCheckboxes.forEach(checkbox => {
                if (!checkbox.checked && count >= maxCourses) {
                    checkbox.disabled = true;
                    checkbox.style.opacity = '0.5';
                    checkbox.style.cursor = 'not-allowed';
                } else {
                    checkbox.disabled = false;
                    checkbox.style.opacity = '1';
                    checkbox.style.cursor = 'pointer';
                }
            });
            
            // Update select all checkbox state
            const selectAll = document.getElementById('selectAllCourses');
            if (selectAll && allCheckboxes.length > 0) {
                const maxSelectable = Math.min(maxCourses, allCheckboxes.length);
                selectAll.checked = count === maxSelectable;
                selectAll.indeterminate = count > 0 && count < maxSelectable;
            }
        }
        
        // Make functions globally accessible
        window.handleCourseSelection = handleCourseSelection;
        window.toggleAllCourses = toggleAllCourses;
        window.updateSelectedCount = updateSelectedCount;
    </script>
    <script>
        // Show toast notification on page load if there's a message
        <?php if (!empty($message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const messageType = '<?php echo htmlspecialchars($messageType, ENT_QUOTES); ?>';
            const message = <?php echo json_encode($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            
            let backgroundColor = '#2563eb'; // default blue
            if (messageType === 'success') {
                backgroundColor = '#10b981'; // green
            } else if (messageType === 'error') {
                backgroundColor = '#ef4444'; // red
            } else if (messageType === 'warning') {
                backgroundColor = '#f59e0b'; // orange
            }
            
            Toastify({
                text: message,
                duration: 5000,
                gravity: "top",
                position: "right",
                style: {
                    background: backgroundColor,
                },
                close: true,
            }).showToast();
            
            // Clean URL by removing message parameters
            if (window.location.search.includes('message=')) {
                const url = new URL(window.location);
                url.searchParams.delete('message');
                url.searchParams.delete('type');
                window.history.replaceState({}, '', url);
            }
        });
        <?php endif; ?>
        
        let currentView = 'grid';

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Set up chat form submission
            const chatForm = document.querySelector('.chat-form');
            if (chatForm) {
                chatForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    showNotification('Message sent successfully!', 'success');
                    this.reset();
                    closeChat();
                });
            }

            // Initialize view buttons
            updateViewButtons();
        });

        // Filter courses
        function filterCourses() {
            const searchTerm = document.getElementById('courseSearch').value.toLowerCase();
            const semesterFilter = document.getElementById('semesterFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const courseCards = document.querySelectorAll('.course-card');
            const courseRows = document.querySelectorAll('tbody tr');

            courseCards.forEach(card => {
                const courseName = card.querySelector('.course-title').textContent.toLowerCase();
                const semester = card.getAttribute('data-semester');
                const status = card.getAttribute('data-status');

                const matchesSearch = courseName.includes(searchTerm);
                const matchesSemester = !semesterFilter || semester === semesterFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesSemester && matchesStatus) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            courseRows.forEach(row => {
                const courseName = row.querySelector('td').textContent.toLowerCase();
                const semester = row.getAttribute('data-semester');
                const status = row.getAttribute('data-status');

                const matchesSearch = courseName.includes(searchTerm);
                const matchesSemester = !semesterFilter || semester === semesterFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesSemester && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Toggle view between grid and list
        function toggleView(view) {
            currentView = view;
            const gridView = document.getElementById('courseGridView');
            const listView = document.getElementById('courseListView');

            if (view === 'grid') {
                gridView.style.display = 'grid';
                listView.style.display = 'none';
            } else {
                gridView.style.display = 'none';
                listView.style.display = 'block';
            }

            updateViewButtons();
        }

        // Update view button states
        function updateViewButtons() {
            const gridBtn = document.getElementById('gridViewBtn');
            const listBtn = document.getElementById('listViewBtn');

            if (currentView === 'grid') {
                gridBtn.classList.add('btn-primary');
                gridBtn.classList.remove('btn-outline');
                listBtn.classList.add('btn-outline');
                listBtn.classList.remove('btn-primary');
            } else {
                listBtn.classList.add('btn-primary');
                listBtn.classList.remove('btn-outline');
                gridBtn.classList.add('btn-outline');
                gridBtn.classList.remove('btn-primary');
            }
        }

        // View course details
        function viewCourse(courseId) {
            showNotification(`Opening course details for ${courseId}...`, 'info');
            // In a real implementation, this would navigate to course-info.html with courseId parameter
            setTimeout(() => {
                window.location.href = '../course-info.html';
            }, 1000);
        }

        // Drop course
        function dropCourse(courseId) {
            if (confirm('Are you sure you want to drop this course? This action cannot be undone.')) {
                showNotification(`Course ${courseId} dropped successfully`, 'success');
                // In a real implementation, this would make an API call to drop the course
            }
        }

        // View transcript
        function viewTranscript(courseId) {
            showNotification(`Opening transcript for ${courseId}...`, 'info');
            // In a real implementation, this would open a transcript modal or page
        }

        // Refresh courses
        function refreshCourses() {
            showNotification('Refreshing courses...', 'info');
            // In a real implementation, this would reload course data from the server
            setTimeout(() => {
                showNotification('Courses refreshed successfully', 'success');
            }, 1000);
        }

        
        // Handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('bulkEnrollmentForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const checkboxes = document.querySelectorAll('.course-checkbox:checked');
                    const count = checkboxes.length;
                    
                    if (count === 0) {
                        e.preventDefault();
                        alert('Please select at least one course to request enrollment.');
                        return false;
                    }
                    
                    if (count > 5) {
                        e.preventDefault();
                        alert('You can only select a maximum of 5 courses at a time.');
                        return false;
                    }
                    
                    if (!confirm(`Submit enrollment request for ${count} course(s)? Your request(s) will be sent to IT for approval.`)) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        });
        
        // Close modal on overlay click (but not on modal content click)
        document.addEventListener('click', function(e) {
            if (e.target.id === 'availableCoursesModalOverlay') {
                closeAvailableCoursesModal();
            }
        });
        
        // Prevent modal content clicks from closing the modal
        const modalContent = document.querySelector('#availableCoursesModal .modal-content');
        if (modalContent) {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    </script>
</body>
</html>