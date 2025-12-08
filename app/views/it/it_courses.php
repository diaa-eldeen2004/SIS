<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - IT Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
// Load courses data server-side
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Logger.php';
$db = Database::getInstance()->getConnection();

// Initialize message variables (will be set from URL params after form submissions)
$message = isset($_GET['message']) ? $_GET['message'] : '';
$messageType = isset($_GET['type']) ? $_GET['type'] : 'info';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'assign-doctor' && isset($_POST['course_id']) && isset($_POST['doctor_id'])) {
        try {
            // Check if assignment already exists
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM doctor_courses WHERE doctor_id = ? AND course_id = ?");
            $checkStmt->execute([$_POST['doctor_id'], $_POST['course_id']]);
            
            if ($checkStmt->fetchColumn() == 0) {
                // Check if assigned_at column exists
                try {
                    $colCheck = $db->query("SHOW COLUMNS FROM doctor_courses LIKE 'assigned_at'")->fetchAll();
                    $hasAssignedAtColumn = !empty($colCheck);
                } catch (Exception $e) {
                    $hasAssignedAtColumn = false;
                }
                
                if ($hasAssignedAtColumn) {
                    $stmt = $db->prepare("INSERT INTO doctor_courses (doctor_id, course_id, assigned_at) VALUES (?, ?, NOW())");
                    $stmt->execute([$_POST['doctor_id'], $_POST['course_id']]);
                } else {
                    // Fallback to just doctor_id and course_id if assigned_at doesn't exist
                    $stmt = $db->prepare("INSERT INTO doctor_courses (doctor_id, course_id) VALUES (?, ?)");
                    $stmt->execute([$_POST['doctor_id'], $_POST['course_id']]);
                }
                
                // Get info for logging
                $infoStmt = $db->prepare("
                    SELECT c.course_code, c.course_name, CONCAT(d.first_name, ' ', d.last_name) as doctor_name
                    FROM courses c, doctors d
                    WHERE c.id = ? AND d.id = ?
                ");
                $infoStmt->execute([$_POST['course_id'], $_POST['doctor_id']]);
                $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
                
                Logger::success("Doctor assigned to course", [
                    'doctor_id' => $_POST['doctor_id'],
                    'doctor_name' => $info['doctor_name'] ?? 'N/A',
                    'course_id' => $_POST['course_id'],
                    'course_code' => $info['course_code'] ?? 'N/A',
                    'course_name' => $info['course_name'] ?? 'N/A'
                ], 'course_management');
                
                header('Location: it_courses.php?message=' . urlencode('Doctor assigned successfully') . '&type=success');
                exit;
            } else {
                header('Location: it_courses.php?message=' . urlencode('Doctor is already assigned to this course') . '&type=warning');
                exit;
            }
        } catch (Exception $e) {
            header('Location: it_courses.php?message=' . urlencode('Error assigning doctor: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'remove-doctor' && isset($_POST['course_id']) && isset($_POST['doctor_id'])) {
        try {
            // Get info for logging before deletion
            $infoStmt = $db->prepare("
                SELECT c.course_code, c.course_name, CONCAT(d.first_name, ' ', d.last_name) as doctor_name
                FROM courses c, doctors d
                WHERE c.id = ? AND d.id = ?
            ");
            $infoStmt->execute([$_POST['course_id'], $_POST['doctor_id']]);
            $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("DELETE FROM doctor_courses WHERE doctor_id = ? AND course_id = ?");
            $stmt->execute([$_POST['doctor_id'], $_POST['course_id']]);
            
            Logger::warning("Doctor removed from course", [
                'doctor_id' => $_POST['doctor_id'],
                'doctor_name' => $info['doctor_name'] ?? 'N/A',
                'course_id' => $_POST['course_id'],
                'course_code' => $info['course_code'] ?? 'N/A',
                'course_name' => $info['course_name'] ?? 'N/A'
            ], 'course_management');
            
            header('Location: it_courses.php?message=' . urlencode('Doctor removed successfully') . '&type=success');
            exit;
        } catch (Exception $e) {
            header('Location: it_courses.php?message=' . urlencode('Error removing doctor: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'enroll-student' && isset($_POST['course_id']) && isset($_POST['student_id'])) {
        try {
            // Check if status column exists in the table
            $columns = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
            $hasStatusColumn = !empty($columns);
            
            $status = $_POST['enrollment_status'] ?? 'taking';
            $courseId = (int)$_POST['course_id'];
            $studentIds = [];
            
            // Handle both single student and multiple students
            if (is_array($_POST['student_id'])) {
                $studentIds = array_map('intval', $_POST['student_id']);
            } else {
                $studentIds = [(int)$_POST['student_id']];
            }
            
            $enrolled = 0;
            $skipped = 0;
            $errors = [];
            
            foreach ($studentIds as $studentId) {
                // Check if enrollment already exists
                $checkStmt = $db->prepare("SELECT COUNT(*) FROM student_courses WHERE student_id = ? AND course_id = ?");
                $checkStmt->execute([$studentId, $courseId]);
                
                if ($checkStmt->fetchColumn() == 0) {
                    try {
                        if ($hasStatusColumn) {
                            $stmt = $db->prepare("INSERT INTO student_courses (student_id, course_id, status, enrolled_at) VALUES (?, ?, ?, NOW())");
                            $stmt->execute([$studentId, $courseId, $status]);
                        } else {
                            // Use enrolled_at column if it exists, otherwise use created_at
                            try {
                                $stmt = $db->prepare("INSERT INTO student_courses (student_id, course_id, enrolled_at) VALUES (?, ?, NOW())");
                                $stmt->execute([$studentId, $courseId]);
                            } catch (Exception $e2) {
                                // Fallback to created_at if enrolled_at doesn't exist
                                $stmt = $db->prepare("INSERT INTO student_courses (student_id, course_id, created_at) VALUES (?, ?, NOW())");
                                $stmt->execute([$studentId, $courseId]);
                            }
                        }
                        $enrolled++;
                        
                        // Get info for logging
                        $infoStmt = $db->prepare("
                            SELECT c.course_code, c.course_name, CONCAT(st.first_name, ' ', st.last_name) as student_name
                            FROM courses c, students st
                            WHERE c.id = ? AND st.id = ?
                        ");
                        $infoStmt->execute([$courseId, $studentId]);
                        $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
                        
                        Logger::success("Student enrolled in course (IT direct enrollment)", [
                            'student_id' => $studentId,
                            'student_name' => $info['student_name'] ?? 'N/A',
                            'course_id' => $courseId,
                            'course_code' => $info['course_code'] ?? 'N/A',
                            'course_name' => $info['course_name'] ?? 'N/A',
                            'status' => $status
                        ], 'course_management');
                    } catch (Exception $e) {
                        $errors[] = "Error enrolling student ID $studentId: " . $e->getMessage();
                        Logger::error("Error enrolling student in course", [
                            'student_id' => $studentId,
                            'course_id' => $courseId,
                            'error' => $e->getMessage()
                        ], 'course_management');
                    }
                } else {
                    $skipped++;
                }
            }
            
            if (count($studentIds) === 1) {
                // Single student enrollment
                if ($enrolled > 0) {
                    header('Location: it_courses.php?message=' . urlencode('Student enrolled successfully') . '&type=success');
                } elseif ($skipped > 0) {
                    header('Location: it_courses.php?message=' . urlencode('Student is already enrolled in this course') . '&type=warning');
                } else {
                    header('Location: it_courses.php?message=' . urlencode(implode(', ', $errors)) . '&type=error');
                }
            } else {
                // Bulk enrollment
                $message = "$enrolled student(s) enrolled successfully";
                if ($skipped > 0) {
                    $message .= ", $skipped already enrolled";
                }
                if (!empty($errors)) {
                    $message .= ". Errors: " . implode(', ', $errors);
                }
                $messageType = !empty($errors) ? 'warning' : 'success';
                header('Location: it_courses.php?message=' . urlencode($message) . '&type=' . $messageType);
            }
            exit;
        } catch (Exception $e) {
            header('Location: it_courses.php?message=' . urlencode('Error enrolling student: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'remove-student' && isset($_POST['course_id']) && isset($_POST['student_id'])) {
        try {
            // Get info for logging before deletion
            $infoStmt = $db->prepare("
                SELECT c.course_code, c.course_name, CONCAT(st.first_name, ' ', st.last_name) as student_name
                FROM courses c, students st
                WHERE c.id = ? AND st.id = ?
            ");
            $infoStmt->execute([$_POST['course_id'], $_POST['student_id']]);
            $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("DELETE FROM student_courses WHERE student_id = ? AND course_id = ?");
            $stmt->execute([$_POST['student_id'], $_POST['course_id']]);
            
            Logger::warning("Student removed from course", [
                'student_id' => $_POST['student_id'],
                'student_name' => $info['student_name'] ?? 'N/A',
                'course_id' => $_POST['course_id'],
                'course_code' => $info['course_code'] ?? 'N/A',
                'course_name' => $info['course_name'] ?? 'N/A'
            ], 'course_management');
            
            header('Location: it_courses.php?message=' . urlencode('Student removed successfully') . '&type=success');
            exit;
        } catch (Exception $e) {
            header('Location: it_courses.php?message=' . urlencode('Error removing student: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    }
}

// Get message from URL parameters (set above if form was submitted)
if (!$message && isset($_GET['message'])) {
    $message = $_GET['message'];
    $messageType = isset($_GET['type']) ? $_GET['type'] : 'info';
}

// Read filters from query params
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$departmentFilter = isset($_GET['department']) ? trim($_GET['department']) : '';
$levelFilter = isset($_GET['level']) ? trim($_GET['level']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build WHERE clause
$where = "WHERE 1=1";
$params = [];

if ($search !== '') {
    $where .= " AND (c.course_code LIKE ? OR c.course_name LIKE ? OR c.description LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($departmentFilter !== '') {
    $where .= " AND c.department = ?";
    $params[] = $departmentFilter;
}

if ($levelFilter !== '') {
    $where .= " AND c.level = ?";
    $params[] = (int)$levelFilter;
}

if ($statusFilter !== '') {
    $where .= " AND c.status = ?";
    $params[] = $statusFilter;
}

// Fetch courses
$courses = [];
try {
    $stmt = $db->prepare("
        SELECT c.id, c.course_code, c.course_name, c.description, c.department, 
               c.level, c.credits, c.status, c.max_students, c.created_at
        FROM courses c
        $where
        ORDER BY c.created_at DESC
        LIMIT 100
    ");
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Load assignments for each course
    foreach ($courses as &$course) {
        // Get assigned doctors
        $doctorStmt = $db->prepare("
            SELECT d.id, d.first_name, d.last_name, d.email, d.department
            FROM doctors d
            INNER JOIN doctor_courses dc ON d.id = dc.doctor_id
            WHERE dc.course_id = ?
        ");
        $doctorStmt->execute([$course['id']]);
        $course['assigned_doctors'] = $doctorStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get enrolled students
        // Check if status column exists in student_courses table
        try {
            $colCheck = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
            $hasStatusColumn = !empty($colCheck);
        } catch (Exception $e) {
            $hasStatusColumn = false;
        }
        
        if ($hasStatusColumn) {
            $studentStmt = $db->prepare("
                SELECT s.id, s.first_name, s.last_name, s.student_number, s.email, sc.status
                FROM students s
                INNER JOIN student_courses sc ON s.id = sc.student_id
                WHERE sc.course_id = ?
            ");
        } else {
            $studentStmt = $db->prepare("
                SELECT s.id, s.first_name, s.last_name, s.student_number, s.email, 'taking' as status
                FROM students s
                INNER JOIN student_courses sc ON s.id = sc.student_id
                WHERE sc.course_id = ?
            ");
        }
        $studentStmt->execute([$course['id']]);
        $course['enrolled_students'] = $studentStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($course);
    
} catch (PDOException $e) {
    error_log("Error loading courses: " . $e->getMessage());
}

// Load doctors and students for dropdowns
$doctors = [];
$students = [];
try {
    $doctorStmt = $db->query("SELECT id, first_name, last_name, email, department FROM doctors ORDER BY last_name, first_name");
    $doctors = $doctorStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $studentStmt = $db->query("SELECT id, first_name, last_name, student_number, email FROM students ORDER BY last_name, first_name");
    $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error loading dropdowns: " . $e->getMessage());
}

// Get unique departments for filter
$departments = [];
try {
    $deptStmt = $db->query("SELECT DISTINCT department FROM courses WHERE department IS NOT NULL AND department != '' ORDER BY department");
    $departments = $deptStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Ignore
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
            <h2><i class="fas fa-laptop-code"></i> IT Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="it_dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="it_schedule.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Semester Schedule
            </a>
            <a href="it_courses.php" class="nav-item active">
                <i class="fas fa-book"></i> Course Management
            </a>
            <a href="it_enrollments.php" class="nav-item">
                <i class="fas fa-user-check"></i> Enrollment Requests
            </a>
            <a href="it_logs.php" class="nav-item">
                <i class="fas fa-file-alt"></i> System Logs
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
                    <h1 style="margin: 0; color: var(--text-primary);">Course Management</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Manage course assignments, assign doctors and enroll students to courses.</p>
                </div>
                <a href="it_courses.php" class="btn btn-primary">
                    <i class="fas fa-sync"></i> Refresh
                </a>
            </div>
        </header>

        <!-- Messages will be shown via Toastify notifications -->

        <!-- Content Body -->
        <div class="content-body">
            <!-- Filters -->
            <section class="course-filters" style="margin-bottom: 2rem;">
                <div class="card">
                    <form method="GET" action="it_courses.php" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-input" placeholder="Search by course code, name..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Department</label>
                            <select name="department" class="form-input" onchange="this.form.submit()">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo $departmentFilter === $dept ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="it_courses.php" class="btn btn-outline">Clear</a>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Courses List -->
            <section class="courses-list">
                <?php if (empty($courses)): ?>
                    <div class="card" style="padding: 3rem; text-align: center;">
                        <i class="fas fa-book" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                        <p style="color: var(--text-secondary);">No courses found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <div class="card" style="margin-bottom: 2rem;">
                            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h2 style="margin: 0; color: var(--text-primary);">
                                        <?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_name']); ?>
                                    </h2>
                                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary); font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($course['department'] ?? 'N/A'); ?> • <?php echo htmlspecialchars($course['credits'] ?? 0); ?> Credits
                                        <span class="badge" style="background-color: <?php echo $course['status'] === 'active' ? 'var(--success-color)' : ($course['status'] === 'inactive' ? 'var(--error-color)' : 'var(--warning-color)'); ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; margin-left: 0.5rem;">
                                            <?php echo ucfirst($course['status'] ?? 'active'); ?>
                                        </span>
                                    </p>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn btn-primary" onclick="showAssignDoctorModal(<?php echo $course['id']; ?>)">
                                        <i class="fas fa-user-md"></i> Assign Doctor
                                    </button>
                                    <button class="btn btn-success" onclick="showEnrollStudentModal(<?php echo $course['id']; ?>)">
                                        <i class="fas fa-user-plus"></i> Enroll Student(s)
                                    </button>
                                </div>
                            </div>
                            
                            <div class="card-body" style="padding: 1.5rem;">
                                <?php if (!empty($course['description'])): ?>
                                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;"><?php echo htmlspecialchars($course['description']); ?></p>
                                <?php endif; ?>
                                
                                <div class="grid grid-2" style="gap: 2rem;">
                                    <!-- Assigned Doctors -->
                                    <div>
                                        <h3 style="margin-bottom: 1rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                                            <i class="fas fa-user-md" style="color: var(--primary-color);"></i>
                                            Assigned Doctors
                                            <span class="badge" style="background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; margin-left: 0.5rem;">
                                                <?php echo count($course['assigned_doctors'] ?? []); ?>
                                            </span>
                                        </h3>
                                        <div style="min-height: 100px; max-height: 300px; overflow-y: auto;">
                                            <?php if (!empty($course['assigned_doctors'])): ?>
                                                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                                    <?php foreach ($course['assigned_doctors'] as $doctor): ?>
                                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background-color: var(--surface-color); border-radius: 8px;">
                                                            <div>
                                                                <div style="font-weight: 500; color: var(--text-primary);">Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?></div>
                                                                <div style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars($doctor['department'] ?? 'N/A'); ?> • <?php echo htmlspecialchars($doctor['email'] ?? ''); ?></div>
                                                            </div>
                                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this doctor?');">
                                                                <input type="hidden" name="action" value="remove-doctor">
                                                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                                <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                                                <button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                                                    <i class="fas fa-times"></i> Remove
                                                                </button>
                                                            </form>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div style="padding: 2rem; text-align: center; background-color: var(--surface-color); border-radius: 8px; border: 2px dashed var(--border-color);">
                                                    <i class="fas fa-user-md" style="font-size: 2rem; color: var(--text-secondary); margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                                    <p style="color: var(--text-secondary); margin: 0;">No doctors assigned</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Enrolled Students -->
                                    <div>
                                        <h3 style="margin-bottom: 1rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                                            <i class="fas fa-user-graduate" style="color: var(--success-color);"></i>
                                            Enrolled Students
                                            <span class="badge" style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; margin-left: 0.5rem;">
                                                <?php echo count($course['enrolled_students'] ?? []); ?>
                                            </span>
                                        </h3>
                                        <div style="min-height: 100px; max-height: 300px; overflow-y: auto;">
                                            <?php if (!empty($course['enrolled_students'])): ?>
                                                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                                    <?php foreach ($course['enrolled_students'] as $student): ?>
                                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background-color: var(--surface-color); border-radius: 8px;">
                                                            <div>
                                                                <div style="font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                                                <div style="font-size: 0.9rem; color: var(--text-secondary);">
                                                                    <?php echo htmlspecialchars($student['student_number'] ?? 'N/A'); ?> • <?php echo htmlspecialchars($student['email'] ?? ''); ?>
                                                                    <?php if (!empty($student['status'])): ?>
                                                                        • <span class="badge" style="background-color: var(--primary-color); color: white; padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.75rem;"><?php echo htmlspecialchars($student['status']); ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this student?');">
                                                                <input type="hidden" name="action" value="remove-student">
                                                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                                <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                                                <button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                                                    <i class="fas fa-times"></i> Remove
                                                                </button>
                                                            </form>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div style="padding: 2rem; text-align: center; background-color: var(--surface-color); border-radius: 8px; border: 2px dashed var(--border-color);">
                                                    <i class="fas fa-user-graduate" style="font-size: 2rem; color: var(--text-secondary); margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                                    <p style="color: var(--text-secondary); margin: 0;">No students enrolled</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>University Portal</h3>
                <p>Comprehensive campus management system designed to streamline academic operations and enhance the learning experience.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="../home.php">Home</a>
                <a href="../about.php">About Us</a>
                <a href="../contact.php">Contact</a>
                <a href="../help_center.php">Help Center</a>
            </div>
            <div class="footer-section">
                <h3>User Access</h3>
                <a href="../auth/auth_login.php">Student Login</a>
                <a href="../auth/auth_login.php">Doctor Login</a>
                <a href="../auth/auth_login.php">Admin Login</a>
                <a href="../auth/auth_sign.php">Register</a>
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

    <!-- Assign Doctor Modal -->
    <div id="assignDoctorModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Assign Doctor to Course</h2>
                <button class="modal-close" onclick="closeAssignDoctorModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignDoctorForm" method="POST" action="it_courses.php">
                    <input type="hidden" name="action" value="assign-doctor">
                    <input type="hidden" id="assignDoctorCourseId" name="course_id">
                    <div class="form-group">
                        <label class="form-label">Select Doctor <span style="color: var(--error-color);">*</span></label>
                        <select name="doctor_id" class="form-input" required>
                            <option value="">Select a doctor...</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?php echo $doctor['id']; ?>">
                                    Dr. <?php echo htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']); ?> - <?php echo htmlspecialchars($doctor['department']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                        <button type="button" class="btn btn-outline" onclick="closeAssignDoctorModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Doctor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enroll Student Modal -->
    <div id="enrollStudentModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Enroll Student(s) to Course</h2>
                <button class="modal-close" onclick="closeEnrollStudentModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="enrollStudentForm" method="POST" action="it_courses.php">
                    <input type="hidden" name="action" value="enroll-student">
                    <input type="hidden" id="enrollStudentCourseId" name="course_id">
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label class="form-label" style="margin: 0;">Select Student(s) <span style="color: var(--error-color);">*</span></label>
                            <button type="button" class="btn btn-outline" onclick="toggleSelectAllStudents()" style="padding: 0.25rem 0.75rem; font-size: 0.85rem;">
                                <i class="fas fa-check-square" id="selectAllIcon"></i> <span id="selectAllText">Select All</span>
                            </button>
                        </div>
                        <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; max-height: 400px; overflow-y: auto; background-color: var(--surface-color);">
                            <?php if (empty($students)): ?>
                                <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">No students available</p>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                    <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background-color 0.2s;" 
                                           onmouseover="this.style.backgroundColor='rgba(var(--primary-color-rgb), 0.1)'"
                                           onmouseout="this.style.backgroundColor='transparent'">
                                        <input type="checkbox" name="student_id[]" value="<?php echo $student['id']; ?>" class="student-checkbox" onchange="updateSelectedCount()" style="width: 18px; height: 18px; cursor: pointer;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 500; color: var(--text-primary);">
                                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                            </div>
                                            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                                ID: <?php echo htmlspecialchars($student['student_number'] ?? 'N/A'); ?> 
                                                • <?php echo htmlspecialchars($student['email'] ?? ''); ?>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <small style="display: block; color: var(--text-secondary); margin-top: 0.5rem;">
                            <i class="fas fa-info-circle"></i> Selected: <strong id="selectedCount" style="color: var(--primary-color);">0</strong> student(s)
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="enrollment_status" class="form-input">
                            <option value="taking" selected>Taking (Currently Enrolled)</option>
                            <option value="taken">Taken (Completed)</option>
                        </select>
                    </div>
                    <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                        <button type="button" class="btn btn-outline" onclick="closeEnrollStudentModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="enrollSubmitBtn" disabled>
                            <i class="fas fa-user-plus"></i> Enroll Student(s)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="../js/main.js"></script>
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
                gravity: "top", // `top` or `bottom`
                position: "right", // `left`, `center` or `right`
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

        function showAssignDoctorModal(courseId) {
            document.getElementById('assignDoctorCourseId').value = courseId;
            document.getElementById('assignDoctorModal').classList.add('active');
        }

        function closeAssignDoctorModal() {
            document.getElementById('assignDoctorModal').classList.remove('active');
            document.getElementById('assignDoctorForm').reset();
        }

        function showEnrollStudentModal(courseId) {
            document.getElementById('enrollStudentCourseId').value = courseId;
            document.getElementById('enrollStudentModal').classList.add('active');
        }

        function closeEnrollStudentModal() {
            document.getElementById('enrollStudentModal').classList.remove('active');
            document.getElementById('enrollStudentForm').reset();
            document.getElementById('selectedCount').textContent = '0';
            allStudentsSelected = false;
            updateSelectAllButton();
        }

        let allStudentsSelected = false;

        function toggleSelectAllStudents() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            allStudentsSelected = !allStudentsSelected;
            
            checkboxes.forEach(cb => {
                cb.checked = allStudentsSelected;
            });
            
            updateSelectedCount();
            updateSelectAllButton();
        }

        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.student-checkbox:checked');
            const count = checkboxes.length;
            document.getElementById('selectedCount').textContent = count;
            
            // Enable/disable submit button
            const submitBtn = document.getElementById('enrollSubmitBtn');
            if (submitBtn) {
                submitBtn.disabled = count === 0;
            }
            
            // Update select all button state
            const allCheckboxes = document.querySelectorAll('.student-checkbox');
            if (allCheckboxes.length > 0) {
                allStudentsSelected = count === allCheckboxes.length;
                updateSelectAllButton();
            }
        }

        function updateSelectAllButton() {
            const icon = document.getElementById('selectAllIcon');
            const text = document.getElementById('selectAllText');
            if (icon && text) {
                if (allStudentsSelected) {
                    icon.className = 'fas fa-square';
                    text.textContent = 'Deselect All';
                } else {
                    icon.className = 'fas fa-check-square';
                    text.textContent = 'Select All';
                }
            }
        }

        // Initialize count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSelectedCount();
            
            // Add form validation
            const enrollForm = document.getElementById('enrollStudentForm');
            if (enrollForm) {
                enrollForm.addEventListener('submit', function(e) {
                    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
                    if (checkboxes.length === 0) {
                        e.preventDefault();
                        Toastify({
                            text: 'Please select at least one student to enroll',
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: '#f59e0b',
                            },
                            close: true,
                        }).showToast();
                        return false;
                    }
                });
            }
        });

        // Close modals on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                closeAssignDoctorModal();
                closeEnrollStudentModal();
            }
        });
    </script>
</body>
</html>
