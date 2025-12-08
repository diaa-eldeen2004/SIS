<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - University Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
session_start();
require_once __DIR__ . '/../../core/Database.php';
$db = Database::getInstance()->getConnection();

// Get current student ID from session
$studentId = null;
$studentInfo = null;
if (isset($_SESSION['user']['id'])) {
    try {
        $userStmt = $db->prepare("SELECT id, first_name, last_name, student_number, gpa, major, minor FROM students WHERE user_id = ?");
        $userStmt->execute([$_SESSION['user']['id']]);
        $studentInfo = $userStmt->fetch(PDO::FETCH_ASSOC);
        if ($studentInfo) {
            $studentId = $studentInfo['id'];
        }
    } catch (Exception $e) {
        error_log("Error getting student_id: " . $e->getMessage());
    }
}

// Initialize stats
$stats = [
    'enrolled_courses' => 0,
    'pending_assignments' => 0,
    'attendance_rate' => 0,
    'new_notifications' => 0,
    'gpa' => $studentInfo['gpa'] ?? '0.00',
    'credits' => 0
];

$upcomingAssignments = [];
$recentGrades = [];
$courseProgress = [];
$todaySchedule = [];

if ($studentId) {
    try {
        // Get enrolled courses count
        $columns = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'")->fetchAll();
        $hasStatusColumn = !empty($columns);
        
        $enrolledSql = "SELECT COUNT(*) as count FROM student_courses WHERE student_id = ?";
        if ($hasStatusColumn) {
            $enrolledSql .= " AND (status = 'taking' OR status = 'taken')";
        }
        $stmt = $db->prepare($enrolledSql);
        $stmt->execute([$studentId]);
        $stats['enrolled_courses'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get credits (approximate: 3 credits per course)
        $stats['credits'] = $stats['enrolled_courses'] * 3;
        
        // Get pending assignments from calendar_events
        try {
            $assignmentsStmt = $db->prepare("
                SELECT ce.*, c.course_code, c.course_name
                FROM calendar_events ce
                LEFT JOIN courses c ON ce.course_id = c.id
                WHERE ce.event_type = 'assignment'
                AND ce.status = 'active'
                AND ce.start_date >= CURDATE()
                AND (ce.affected_users LIKE ? OR ce.affected_users LIKE ? OR ce.affected_users IS NULL)
                ORDER BY ce.start_date ASC
                LIMIT 5
            ");
            $studentRole = '%student%';
            $allUsers = '%all%';
            $assignmentsStmt->execute([$studentRole, $allUsers]);
            $upcomingAssignments = $assignmentsStmt->fetchAll(PDO::FETCH_ASSOC);
            $stats['pending_assignments'] = count($upcomingAssignments);
        } catch (Exception $e) {
            // Calendar events table might not exist
            error_log("Error loading assignments: " . $e->getMessage());
        }
        
        // Get attendance rate
        try {
            $attendanceStmt = $db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present
                FROM student_attendance
                WHERE student_id = ?
            ");
            $attendanceStmt->execute([$studentId]);
            $attendanceData = $attendanceStmt->fetch(PDO::FETCH_ASSOC);
            if ($attendanceData && $attendanceData['total'] > 0) {
                $stats['attendance_rate'] = round(($attendanceData['present'] / $attendanceData['total']) * 100);
            }
        } catch (Exception $e) {
            // Attendance table might not exist
            error_log("Error loading attendance: " . $e->getMessage());
        }
        
        // Get recent grades from student_courses
        $gradesStmt = $db->prepare("
            SELECT sc.*, c.course_code, c.course_name
            FROM student_courses sc
            LEFT JOIN courses c ON sc.course_id = c.id
            WHERE sc.student_id = ? AND sc.grade IS NOT NULL AND sc.grade != ''
            ORDER BY sc.enrolled_at DESC
            LIMIT 5
        ");
        $gradesStmt->execute([$studentId]);
        $recentGrades = $gradesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get course progress (enrolled courses)
        $progressStmt = $db->prepare("
            SELECT sc.*, c.course_code, c.course_name
            FROM student_courses sc
            LEFT JOIN courses c ON sc.course_id = c.id
            WHERE sc.student_id = ?
        ");
        if ($hasStatusColumn) {
            $progressStmt = $db->prepare("
                SELECT sc.*, c.course_code, c.course_name
                FROM student_courses sc
                LEFT JOIN courses c ON sc.course_id = c.id
                WHERE sc.student_id = ? AND (sc.status = 'taking' OR sc.status = 'taken')
            ");
        }
        $progressStmt->execute([$studentId]);
        $courseProgress = $progressStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get today's schedule from sections
        $today = date('l'); // e.g., "Monday"
        try {
            $scheduleStmt = $db->prepare("
                SELECT s.*, c.course_code, c.course_name,
                       CONCAT(d.first_name, ' ', d.last_name) as doctor_name
                FROM sections s
                LEFT JOIN courses c ON s.course_id = c.id
                LEFT JOIN doctors d ON s.doctor_id = d.id
                WHERE s.days = ? AND s.status = 'active'
                ORDER BY s.time ASC
                LIMIT 5
            ");
            $scheduleStmt->execute([$today]);
            $todaySchedule = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Sections table might not exist
            error_log("Error loading schedule: " . $e->getMessage());
        }
        
        // Get new notifications count (if notifications table exists)
        try {
            $notificationsStmt = $db->query("SHOW TABLES LIKE 'notifications'");
            if ($notificationsStmt->rowCount() > 0) {
                $notifCountStmt = $db->prepare("
                    SELECT COUNT(*) as count FROM notifications
                    WHERE (user_id = ? OR user_role = 'student' OR user_role = 'all')
                    AND is_read = 0
                ");
                $notifCountStmt->execute([$studentId]);
                $stats['new_notifications'] = (int)$notifCountStmt->fetch(PDO::FETCH_ASSOC)['count'];
            }
        } catch (Exception $e) {
            // Notifications table might not exist
        }
        
    } catch (Exception $e) {
        error_log("Error loading dashboard data: " . $e->getMessage());
    }
}

$studentName = $studentInfo ? ($studentInfo['first_name'] . ' ' . $studentInfo['last_name']) : 'Student';
$displayGPA = $stats['gpa'] ? number_format((float)$stats['gpa'], 2) : '0.00';
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
            <a href="student_dashboard.php" class="nav-item active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="student_courses.php" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Welcome back, <?php echo htmlspecialchars($studentName); ?>!</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Here's what's happening with your courses today.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <div style="text-align: right;">
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">Current GPA</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);"><?php echo htmlspecialchars($displayGPA); ?></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">Credits</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-color);"><?php echo $stats['credits']; ?></div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Quick Stats -->
            <section class="quick-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $stats['enrolled_courses']; ?></div>
                        <div style="color: var(--text-secondary);">Enrolled Courses</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $stats['pending_assignments']; ?></div>
                        <div style="color: var(--text-secondary);">Pending Assignments</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $stats['attendance_rate']; ?>%</div>
                        <div style="color: var(--text-secondary);">Attendance Rate</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $stats['new_notifications']; ?></div>
                        <div style="color: var(--text-secondary);">New Notifications</div>
                    </div>
                </div>
            </section>

            <!-- Main Content Grid -->
            <div class="grid grid-2" style="gap: 2rem;">
                <!-- Upcoming Assignments -->
                <section class="upcoming-assignments">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-clock" style="color: var(--warning-color); margin-right: 0.5rem;"></i>
                                Upcoming Assignments
                            </h2>
                            <a href="student_assignments.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                View All
                            </a>
                        </div>
                        <div class="assignments-list">
                            <?php if (empty($upcomingAssignments)): ?>
                                <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                                    <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                    <p style="margin: 0;">No upcoming assignments</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($upcomingAssignments as $index => $assignment): 
                                    $dueDate = new DateTime($assignment['start_date']);
                                    $today = new DateTime();
                                    $diff = $today->diff($dueDate);
                                    $daysDiff = (int)$diff->format('%r%a');
                                    
                                    if ($daysDiff < 0) {
                                        $dueText = 'Overdue';
                                        $dueColor = 'var(--error-color)';
                                    } elseif ($daysDiff == 0) {
                                        $dueText = 'Due Today';
                                        $dueColor = 'var(--warning-color)';
                                    } elseif ($daysDiff == 1) {
                                        $dueText = 'Due Tomorrow';
                                        $dueColor = 'var(--warning-color)';
                                    } elseif ($daysDiff <= 7) {
                                        $dueText = "Due in {$daysDiff} days";
                                        $dueColor = 'var(--primary-color)';
                                    } else {
                                        $dueText = "Due in {$daysDiff} days";
                                        $dueColor = 'var(--success-color)';
                                    }
                                ?>
                                <div class="assignment-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; <?php echo $index < count($upcomingAssignments) - 1 ? 'border-bottom: 1px solid var(--border-color);' : ''; ?>">
                                    <div>
                                        <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);"><?php echo htmlspecialchars($assignment['title'] ?? 'Assignment'); ?></h4>
                                        <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($assignment['course_code'] ?? 'N/A'); ?>
                                            <?php if (!empty($assignment['course_name'])): ?>
                                                - <?php echo htmlspecialchars($assignment['course_name']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 0.9rem; color: <?php echo $dueColor; ?>; font-weight: 500;"><?php echo $dueText; ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);"><?php echo date('M d, Y', strtotime($assignment['start_date'])); ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Recent Grades -->
                <section class="recent-grades">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-chart-line" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                Recent Grades
                            </h2>
                            <a href="student_profile.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                View All
                            </a>
                        </div>
                        <div class="grades-list">
                            <?php if (empty($recentGrades)): ?>
                                <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                                    <i class="fas fa-chart-line" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                    <p style="margin: 0;">No grades available yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentGrades as $index => $grade): 
                                    $gradeValue = $grade['grade'] ?? 'N/A';
                                    // Determine color based on grade
                                    $gradeColor = 'var(--text-secondary)';
                                    if (in_array(strtoupper($gradeValue), ['A', 'A+', 'A-'])) {
                                        $gradeColor = 'var(--success-color)';
                                    } elseif (in_array(strtoupper($gradeValue), ['B', 'B+', 'B-'])) {
                                        $gradeColor = 'var(--accent-color)';
                                    } elseif (in_array(strtoupper($gradeValue), ['C', 'C+', 'C-'])) {
                                        $gradeColor = 'var(--warning-color)';
                                    } else {
                                        $gradeColor = 'var(--error-color)';
                                    }
                                ?>
                                <div class="grade-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; <?php echo $index < count($recentGrades) - 1 ? 'border-bottom: 1px solid var(--border-color);' : ''; ?>">
                                    <div>
                                        <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);"><?php echo htmlspecialchars($grade['course_code'] ?? 'Course'); ?> - Final Grade</h4>
                                        <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;"><?php echo htmlspecialchars($grade['course_name'] ?? ''); ?></p>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 1.2rem; font-weight: 700; color: <?php echo $gradeColor; ?>;"><?php echo htmlspecialchars($gradeValue); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);"><?php echo date('M Y', strtotime($grade['enrolled_at'])); ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Course Progress -->
            <section class="course-progress" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-pie" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Course Progress
                        </h2>
                    </div>
                    <div class="progress-courses">
                        <?php if (empty($courseProgress)): ?>
                            <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                                <i class="fas fa-book" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                <p style="margin: 0;">No enrolled courses</p>
                            </div>
                        <?php else: ?>
                            <?php 
                            $colors = ['var(--primary-color)', 'var(--accent-color)', 'var(--success-color)', 'var(--warning-color)', 'var(--error-color)'];
                            foreach ($courseProgress as $index => $course): 
                                // Calculate progress (simplified: based on enrollment date)
                                $enrolledDate = new DateTime($course['enrolled_at']);
                                $now = new DateTime();
                                $daysSinceEnrollment = $now->diff($enrolledDate)->days;
                                // Approximate: 15 weeks semester = 105 days, cap at 100%
                                $progress = min(100, round(($daysSinceEnrollment / 105) * 100));
                                $colorIndex = $index % count($colors);
                            ?>
                            <div class="course-progress-item" style="margin-bottom: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <h4 style="margin: 0; color: var(--text-primary);"><?php echo htmlspecialchars($course['course_code'] ?? 'Course'); ?> - <?php echo htmlspecialchars($course['course_name'] ?? ''); ?></h4>
                                    <span style="color: var(--text-secondary); font-size: 0.9rem;"><?php echo $progress; ?>% Complete</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo $progress; ?>%; background-color: <?php echo $colors[$colorIndex]; ?>;"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Today's Schedule -->
            <section class="todays-schedule" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-calendar-day" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Today's Schedule
                        </h2>
                        <a href="student_calendar.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                            View Calendar
                        </a>
                    </div>
                    <div class="schedule-list">
                        <?php if (empty($todaySchedule)): ?>
                            <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                                <i class="fas fa-calendar-times" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                                <p style="margin: 0;">No classes scheduled for today</p>
                            </div>
                        <?php else: ?>
                            <?php 
                            $scheduleColors = ['var(--primary-color)', 'var(--accent-color)', 'var(--success-color)', 'var(--warning-color)', 'var(--error-color)'];
                            foreach ($todaySchedule as $index => $schedule): 
                                $colorIndex = $index % count($scheduleColors);
                            ?>
                            <div class="schedule-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; <?php echo $index < count($todaySchedule) - 1 ? 'border-bottom: 1px solid var(--border-color);' : ''; ?>">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="width: 4px; height: 40px; background-color: <?php echo $scheduleColors[$colorIndex]; ?>; border-radius: 2px;"></div>
                                    <div>
                                        <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);"><?php echo htmlspecialchars($schedule['course_code'] ?? 'Course'); ?> - <?php echo htmlspecialchars(ucfirst($schedule['type'] ?? 'lecture')); ?></h4>
                                        <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($schedule['room'] ?? 'TBD'); ?>
                                            <?php if (!empty($schedule['doctor_name'])): ?>
                                                - <?php echo htmlspecialchars($schedule['doctor_name']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($schedule['time'] ?? 'TBD'); ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);"><?php echo htmlspecialchars($schedule['type'] ?? 'lecture'); ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                <h3>Student Chat</h3>
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

    <!-- Scripts -->
    <script src="../js/main.js"></script>
    <script>
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
        });
    </script>
</body>
</html>
