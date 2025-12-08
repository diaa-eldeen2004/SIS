<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Schedule - Student Portal</title>
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
if (isset($_SESSION['user']['id'])) {
    try {
        $userStmt = $db->prepare("SELECT id FROM students WHERE user_id = ?");
        $userStmt->execute([$_SESSION['user']['id']]);
        $student = $userStmt->fetch(PDO::FETCH_ASSOC);
        if ($student) {
            $studentId = $student['id'];
        }
    } catch (Exception $e) {
        error_log("Error getting student_id: " . $e->getMessage());
    }
}

// Check if sections table exists
$sectionsTableExists = false;
try {
    $tableCheck = $db->query("SHOW TABLES LIKE 'sections'");
    $sectionsTableExists = $tableCheck->rowCount() > 0;
} catch (Exception $e) {
    $sectionsTableExists = false;
}

// Load official schedule (active sections only)
$schedule = [];
$scheduleByDay = [
    'Monday' => [],
    'Tuesday' => [],
    'Wednesday' => [],
    'Thursday' => [],
    'Friday' => [],
    'Saturday' => [],
    'Sunday' => []
];

if ($sectionsTableExists) {
    try {
        $stmt = $db->query("
            SELECT s.*, c.course_code, c.course_name,
                   CONCAT(d.first_name, ' ', d.last_name) as doctor_name
            FROM sections s
            LEFT JOIN courses c ON s.course_id = c.id
            LEFT JOIN doctors d ON s.doctor_id = d.id
            WHERE s.status = 'active'
            ORDER BY 
                FIELD(s.days, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                s.time
        ");
        $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by day
        foreach ($schedule as $section) {
            $day = $section['days'] ?? 'Monday';
            if (isset($scheduleByDay[$day])) {
                $scheduleByDay[$day][] = $section;
            }
        }
    } catch (Exception $e) {
        error_log("Error loading schedule: " . $e->getMessage());
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
            <a href="student_courses.php" class="nav-item">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="student_schedule.php" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Official Semester Schedule</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">View the official schedule published by IT.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <?php if (!$sectionsTableExists): ?>
                <div class="card" style="padding: 3rem; text-align: center;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--warning-color); margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-secondary); font-weight: 500; margin-bottom: 0.5rem; font-size: 1.1rem;">Schedule Not Available</p>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">The official schedule has not been published yet. Please check back later.</p>
                </div>
            <?php elseif (empty($schedule)): ?>
                <div class="card" style="padding: 3rem; text-align: center;">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-secondary); font-weight: 500; margin-bottom: 0.5rem; font-size: 1.1rem;">No Schedule Available</p>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">There are no active courses scheduled at this time.</p>
                </div>
            <?php else: ?>
                <!-- Weekly Schedule View -->
                <section class="schedule-view" style="margin-bottom: 2rem;">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-calendar-week" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                                Weekly Schedule
                            </h2>
                        </div>
                        <div style="overflow-x: auto;">
                            <table class="table" style="width: 100%; min-width: 800px;">
                                <thead>
                                    <tr>
                                        <th style="width: 120px;">Day</th>
                                        <th>Course</th>
                                        <th>Time</th>
                                        <th>Room</th>
                                        <th>Instructor</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                    foreach ($weekDays as $day): 
                                        $daySections = $scheduleByDay[$day] ?? [];
                                        if (empty($daySections)):
                                    ?>
                                        <tr>
                                            <td style="font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($day); ?></td>
                                            <td colspan="5" style="color: var(--text-secondary); text-align: center; font-style: italic;">No classes scheduled</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($daySections as $index => $section): ?>
                                        <tr>
                                            <?php if ($index === 0): ?>
                                                <td rowspan="<?php echo count($daySections); ?>" style="font-weight: 500; color: var(--text-primary); vertical-align: middle;">
                                                    <?php echo htmlspecialchars($day); ?>
                                                </td>
                                            <?php endif; ?>
                                            <td>
                                                <div style="font-weight: 500; color: var(--text-primary);">
                                                    <?php echo htmlspecialchars($section['course_code'] ?? 'N/A'); ?>
                                                </div>
                                                <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                                    <?php echo htmlspecialchars($section['course_name'] ?? ''); ?>
                                                </div>
                                            </td>
                                            <td style="color: var(--text-primary);"><?php echo htmlspecialchars($section['time'] ?? 'TBD'); ?></td>
                                            <td style="color: var(--text-primary);"><?php echo htmlspecialchars($section['room'] ?? 'TBD'); ?></td>
                                            <td style="color: var(--text-primary);"><?php echo htmlspecialchars($section['doctor_name'] ?? 'TBD'); ?></td>
                                            <td>
                                                <span style="background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; text-transform: capitalize;">
                                                    <?php echo htmlspecialchars($section['type'] ?? 'lecture'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- Schedule Statistics -->
                <section class="schedule-stats">
                    <div class="grid grid-4">
                        <div class="card" style="text-align: center; padding: 1.5rem;">
                            <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                                <i class="fas fa-book"></i>
                            </div>
                            <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo count($schedule); ?></div>
                            <div style="color: var(--text-secondary);">Total Classes</div>
                        </div>
                        <div class="card" style="text-align: center; padding: 1.5rem;">
                            <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">
                                <?php 
                                $daysWithClasses = 0;
                                foreach ($scheduleByDay as $daySections) {
                                    if (!empty($daySections)) $daysWithClasses++;
                                }
                                echo $daysWithClasses;
                                ?>
                            </div>
                            <div style="color: var(--text-secondary);">Days with Classes</div>
                        </div>
                        <div class="card" style="text-align: center; padding: 1.5rem;">
                            <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">
                                <?php 
                                $uniqueCourses = [];
                                foreach ($schedule as $section) {
                                    if (!empty($section['course_id'])) {
                                        $uniqueCourses[$section['course_id']] = true;
                                    }
                                }
                                echo count($uniqueCourses);
                                ?>
                            </div>
                            <div style="color: var(--text-secondary);">Unique Courses</div>
                        </div>
                        <div class="card" style="text-align: center; padding: 1.5rem;">
                            <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                                <i class="fas fa-building"></i>
                            </div>
                            <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">
                                <?php 
                                $uniqueRooms = [];
                                foreach ($schedule as $section) {
                                    if (!empty($section['room'])) {
                                        $uniqueRooms[$section['room']] = true;
                                    }
                                }
                                echo count($uniqueRooms);
                                ?>
                            </div>
                            <div style="color: var(--text-secondary);">Rooms Used</div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
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

    <!-- Scripts -->
    <script src="../js/main.js"></script>
</body>
</html>

