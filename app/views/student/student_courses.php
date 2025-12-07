<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Student Portal</title>
    <link rel="stylesheet" href="css/styles.css">
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

// Handle enrollment request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request-enrollment') {
    if (!$studentId) {
        header('Location: student_courses.php?message=' . urlencode('Please log in to request enrollment') . '&type=error');
        exit;
    }
    
    if (isset($_POST['course_id'])) {
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
        $stmt = $db->prepare("
            SELECT sc.*, c.course_code, c.course_name, c.description,
                   GROUP_CONCAT(CONCAT(d.first_name, ' ', d.last_name) SEPARATOR ', ') as instructors
            FROM student_courses sc
            LEFT JOIN courses c ON sc.course_id = c.id
            LEFT JOIN doctor_courses dc ON c.id = dc.course_id
            LEFT JOIN doctors d ON dc.doctor_id = d.id
            WHERE sc.student_id = ?
            GROUP BY sc.id
            ORDER BY sc.enrolled_at DESC
        ");
        $stmt->execute([$studentId]);
        $enrolledCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        Logger::error("Error loading enrolled courses", ['error' => $e->getMessage()], 'student');
    }
}

// Load available courses (courses not yet enrolled)
$availableCourses = [];
if ($studentId) {
    try {
        $enrolledCourseIds = array_map(function($c) { return $c['course_id']; }, $enrolledCourses);
        $placeholders = $enrolledCourseIds ? implode(',', array_fill(0, count($enrolledCourseIds), '?')) : '0';
        
        $sql = "SELECT c.* FROM courses c";
        if ($enrolledCourseIds) {
            $sql .= " WHERE c.id NOT IN ($placeholders)";
        }
        $sql .= " ORDER BY c.course_code";
        
        $stmt = $db->prepare($sql);
        if ($enrolledCourseIds) {
            $stmt->execute($enrolledCourseIds);
        } else {
            $stmt->execute();
        }
        $availableCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        Logger::error("Error loading available courses", ['error' => $e->getMessage()], 'student');
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
            <a href="student_dashboard.html" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="student_courses.html" class="nav-item active">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="student_assignments.html" class="nav-item">
                <i class="fas fa-tasks"></i> Assignments
            </a>
            <a href="student_attendance.html" class="nav-item">
                <i class="fas fa-calendar-check"></i> Attendance
            </a>
            <a href="student_calendar.html" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Calendar
            </a>
            <a href="student_notifications.html" class="nav-item">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a href="student_profile.html" class="nav-item">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="../app/settings.html" class="nav-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="../app/logout.html" class="nav-item">
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
                        <!-- Course Card 1 -->
                        <div class="course-card" data-course="calculus" data-semester="Fall 2024" data-status="active">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 class="course-title">Calculus I</h3>
                                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.25rem 0;">MATH 101</p>
                            </div>
                                <div style="text-align: right;">
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span>
                        </div>
                                </div>
                            <p class="course-description">Introduction to differential and integral calculus with applications to science and engineering.</p>
                            <div class="course-meta">
                                <div>
                                    <i class="fas fa-user" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Dr. Sarah Smith</span>
                                </div>
                                <div>
                                    <i class="fas fa-calendar" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Fall 2024</span>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Progress</span>
                                    <span style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">75%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 75%; background-color: var(--primary-color);"></div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <button class="btn btn-primary" style="flex: 1;" onclick="viewCourse('calculus')">
                                    <i class="fas fa-eye"></i> View Course
                                </button>
                                <button class="btn btn-outline" onclick="dropCourse('calculus')">
                                    <i class="fas fa-times"></i>
                                </button>
                        </div>
                    </div>

                        <!-- Course Card 2 -->
                        <div class="course-card" data-course="cs101" data-semester="Fall 2024" data-status="active">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 class="course-title">Computer Science 101</h3>
                                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.25rem 0;">CS 101</p>
                            </div>
                                <div style="text-align: right;">
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span>
                        </div>
                                </div>
                            <p class="course-description">Introduction to programming concepts using Python. Learn variables, functions, and basic algorithms.</p>
                            <div class="course-meta">
                                <div>
                                    <i class="fas fa-user" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Dr. Michael Johnson</span>
                                </div>
                                <div>
                                    <i class="fas fa-calendar" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Fall 2024</span>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Progress</span>
                                    <span style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">60%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 60%; background-color: var(--accent-color);"></div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <button class="btn btn-primary" style="flex: 1;" onclick="viewCourse('cs101')">
                                    <i class="fas fa-eye"></i> View Course
                                </button>
                                <button class="btn btn-outline" onclick="dropCourse('cs101')">
                                    <i class="fas fa-times"></i>
                                </button>
                        </div>
                    </div>

                        <!-- Course Card 3 -->
                        <div class="course-card" data-course="physics" data-semester="Fall 2024" data-status="active">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 class="course-title">Physics I</h3>
                                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.25rem 0;">PHYS 101</p>
                            </div>
                                <div style="text-align: right;">
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span>
                        </div>
                                </div>
                            <p class="course-description">Mechanics, thermodynamics, and wave motion. Introduction to classical physics principles.</p>
                            <div class="course-meta">
                                <div>
                                    <i class="fas fa-user" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Dr. Emily Brown</span>
                                </div>
                                <div>
                                    <i class="fas fa-calendar" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Fall 2024</span>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Progress</span>
                                    <span style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">45%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 45%; background-color: var(--success-color);"></div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <button class="btn btn-primary" style="flex: 1;" onclick="viewCourse('physics')">
                                    <i class="fas fa-eye"></i> View Course
                                </button>
                                <button class="btn btn-outline" onclick="dropCourse('physics')">
                                    <i class="fas fa-times"></i>
                                </button>
                        </div>
                    </div>

                        <!-- Course Card 4 -->
                        <div class="course-card" data-course="english" data-semester="Fall 2024" data-status="active">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 class="course-title">English Literature</h3>
                                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.25rem 0;">ENG 201</p>
                            </div>
                                <div style="text-align: right;">
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span>
                        </div>
                                </div>
                            <p class="course-description">Survey of English literature from medieval to modern periods. Critical analysis and interpretation.</p>
                            <div class="course-meta">
                                <div>
                                    <i class="fas fa-user" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Dr. Robert Wilson</span>
                                </div>
                                <div>
                                    <i class="fas fa-calendar" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Fall 2024</span>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Progress</span>
                                    <span style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">30%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 30%; background-color: var(--error-color);"></div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <button class="btn btn-primary" style="flex: 1;" onclick="viewCourse('english')">
                                    <i class="fas fa-eye"></i> View Course
                                </button>
                                <button class="btn btn-outline" onclick="dropCourse('english')">
                                    <i class="fas fa-times"></i>
                                </button>
                        </div>
                    </div>

                        <!-- Course Card 5 -->
                        <div class="course-card" data-course="chemistry" data-semester="Spring 2024" data-status="completed">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 class="course-title">General Chemistry</h3>
                                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.25rem 0;">CHEM 101</p>
                            </div>
                                <div style="text-align: right;">
                                    <span style="background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Completed</span>
                        </div>
                                </div>
                            <p class="course-description">Fundamental principles of chemistry including atomic structure, bonding, and reactions.</p>
                            <div class="course-meta">
                                <div>
                                    <i class="fas fa-user" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Dr. Lisa Davis</span>
                                </div>
                                <div>
                                    <i class="fas fa-calendar" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Spring 2024</span>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Final Grade</span>
                                    <span style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">A-</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%; background-color: var(--success-color);"></div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <button class="btn btn-primary" style="flex: 1;" onclick="viewCourse('chemistry')">
                                    <i class="fas fa-eye"></i> View Course
                                </button>
                                <button class="btn btn-outline" onclick="viewTranscript('chemistry')">
                                    <i class="fas fa-file-alt"></i>
                                </button>
                            </div>
                        </div>
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
    <div id="availableCoursesModal" class="modal">
        <div class="modal-content" style="max-width: 900px; max-height: 80vh;">
            <div class="modal-header">
                <h2>Available Courses</h2>
                <button class="modal-close" onclick="closeAvailableCoursesModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: calc(80vh - 120px);">
                <?php if (empty($availableCourses)): ?>
                    <div style="padding: 2rem; text-align: center;">
                        <i class="fas fa-book" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                        <p style="color: var(--text-secondary);">No available courses found. You may already be enrolled in all courses.</p>
                    </div>
                <?php else: ?>
                    <div style="display: grid; gap: 1rem;">
                        <?php foreach ($availableCourses as $course): ?>
                            <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background-color: var(--surface-color);">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <div>
                                        <h3 style="margin: 0; color: var(--text-primary);"><?php echo htmlspecialchars($course['course_name'] ?? 'N/A'); ?></h3>
                                        <p style="margin: 0.25rem 0; color: var(--text-secondary); font-size: 0.9rem;">
                                            <strong><?php echo htmlspecialchars($course['course_code'] ?? 'N/A'); ?></strong>
                                        </p>
                                    </div>
                                    <button class="btn btn-primary" onclick="requestEnrollment(<?php echo $course['id']; ?>)">
                                        <i class="fas fa-user-plus"></i> Request Enrollment
                                    </button>
                                </div>
                                <?php if (!empty($course['description'])): ?>
                                    <p style="color: var(--text-secondary); margin: 0.5rem 0; font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($course['description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
    <script src="../../js/main.js"></script>
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

        // Show available courses modal
        function showAvailableCourses() {
            document.getElementById('availableCoursesModal').classList.add('active');
        }
        
        function closeAvailableCoursesModal() {
            document.getElementById('availableCoursesModal').classList.remove('active');
        }
        
        function requestEnrollment(courseId) {
            if (confirm('Request enrollment in this course? Your request will be sent to IT for approval.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'student_courses.php';
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'request-enrollment';
                const courseInput = document.createElement('input');
                courseInput.type = 'hidden';
                courseInput.name = 'course_id';
                courseInput.value = courseId;
                form.appendChild(actionInput);
                form.appendChild(courseInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Close modal on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                closeAvailableCoursesModal();
            }
        });
    </script>
</body>
</html>