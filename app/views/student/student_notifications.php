<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Student Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
// Load notifications from database
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'send-message' && $studentId) {
        try {
            $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : null;
            $recipientType = isset($_POST['recipient_type']) ? $_POST['recipient_type'] : 'doctor';
            $courseId = isset($_POST['course_id']) && !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;
            $subject = trim($_POST['subject'] ?? '');
            $messageText = trim($_POST['message'] ?? '');
            
            // Validation
            if (empty($subject)) {
                throw new Exception('Subject is required.');
            }
            
            if (empty($messageText)) {
                throw new Exception('Message is required.');
            }
            
            if (!$recipientId) {
                throw new Exception('Please select a recipient.');
            }
            
            // Verify recipient exists
            if ($recipientType === 'doctor') {
                $recipientCheck = $db->prepare("SELECT id, first_name, last_name, email FROM doctors WHERE id = ?");
                $recipientCheck->execute([$recipientId]);
                $recipient = $recipientCheck->fetch(PDO::FETCH_ASSOC);
                if (!$recipient) {
                    throw new Exception('Selected recipient not found.');
                }
            } else {
                throw new Exception('Invalid recipient type.');
            }
            
            // Get student info
            $studentStmt = $db->prepare("SELECT first_name, last_name, email FROM students WHERE id = ?");
            $studentStmt->execute([$studentId]);
            $studentInfo = $studentStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$studentInfo) {
                throw new Exception('Student information not found.');
            }
            
            // Check if notifications table exists
            $notificationsTableExists = false;
            try {
                $tableCheck = $db->query("SHOW TABLES LIKE 'notifications'");
                $notificationsTableExists = $tableCheck->rowCount() > 0;
            } catch (Exception $e) {
                $notificationsTableExists = false;
            }
            
            if ($notificationsTableExists) {
                // Create a notification for the doctor (recipient)
                // Format: "Message from [Student Name]: [Subject]"
                $notificationTitle = "Message from " . ($studentInfo['first_name'] ?? '') . " " . ($studentInfo['last_name'] ?? '') . ": " . $subject;
                
                // Build the full message with student info
                $fullMessage = "From: " . ($studentInfo['first_name'] ?? '') . " " . ($studentInfo['last_name'] ?? '') . "\n";
                $fullMessage .= "Email: " . ($studentInfo['email'] ?? '') . "\n";
                if ($courseId) {
                    $courseStmt = $db->prepare("SELECT course_code, course_name FROM courses WHERE id = ?");
                    $courseStmt->execute([$courseId]);
                    $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
                    if ($course) {
                        $fullMessage .= "Course: " . ($course['course_code'] ?? '') . " - " . ($course['course_name'] ?? '') . "\n";
                    }
                }
                $fullMessage .= "\n" . $messageText;
                
                // Check table columns
                $columns = $db->query("SHOW COLUMNS FROM notifications")->fetchAll(PDO::FETCH_COLUMN);
                
                // Build INSERT statement
                $insertFields = ['doctor_id', 'title', 'message', 'notification_type', 'priority', 'student_id', 'course_id', 'created_by', 'created_by_role'];
                $insertValues = [];
                $placeholders = [];
                
                // Add fields that exist
                $fieldsToInsert = [];
                $valuesToInsert = [];
                
                if (in_array('doctor_id', $columns)) {
                    $fieldsToInsert[] = 'doctor_id';
                    $valuesToInsert[] = $recipientId;
                    $placeholders[] = '?';
                }
                
                if (in_array('title', $columns)) {
                    $fieldsToInsert[] = 'title';
                    $valuesToInsert[] = $notificationTitle;
                    $placeholders[] = '?';
                }
                
                if (in_array('message', $columns)) {
                    $fieldsToInsert[] = 'message';
                    $valuesToInsert[] = $fullMessage;
                    $placeholders[] = '?';
                }
                
                if (in_array('notification_type', $columns)) {
                    $fieldsToInsert[] = 'notification_type';
                    $valuesToInsert[] = 'announcement'; // Student messages are treated as announcements
                    $placeholders[] = '?';
                }
                
                if (in_array('priority', $columns)) {
                    $fieldsToInsert[] = 'priority';
                    $valuesToInsert[] = 'normal';
                    $placeholders[] = '?';
                }
                
                if (in_array('student_id', $columns) && $studentId) {
                    $fieldsToInsert[] = 'student_id';
                    $valuesToInsert[] = $studentId; // Reference to sender
                    $placeholders[] = '?';
                }
                
                if (in_array('course_id', $columns) && $courseId) {
                    $fieldsToInsert[] = 'course_id';
                    $valuesToInsert[] = $courseId;
                    $placeholders[] = '?';
                }
                
                if (in_array('created_by', $columns)) {
                    $fieldsToInsert[] = 'created_by';
                    $valuesToInsert[] = $studentId;
                    $placeholders[] = '?';
                }
                
                if (in_array('created_by_role', $columns)) {
                    $fieldsToInsert[] = 'created_by_role';
                    $valuesToInsert[] = 'student';
                    $placeholders[] = '?';
                }
                
                // Add timestamps
                $hasCreatedAt = in_array('created_at', $columns);
                $hasUpdatedAt = in_array('updated_at', $columns);
                
                if ($hasCreatedAt) {
                    $fieldsToInsert[] = 'created_at';
                    $placeholders[] = 'NOW()';
                }
                
                if ($hasUpdatedAt) {
                    $fieldsToInsert[] = 'updated_at';
                    $placeholders[] = 'NOW()';
                }
                
                // Build and execute INSERT
                $sql = "INSERT INTO notifications (" . implode(', ', $fieldsToInsert) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $db->prepare($sql);
                
                // Execute with only ? placeholder values (excluding NOW() which is in SQL)
                $stmt->execute($valuesToInsert);
                
                // Log the action
                Logger::info("Student sent message to doctor", [
                    'student_id' => $studentId,
                    'student_name' => ($studentInfo['first_name'] ?? '') . ' ' . ($studentInfo['last_name'] ?? ''),
                    'doctor_id' => $recipientId,
                    'doctor_name' => ($recipient['first_name'] ?? '') . ' ' . ($recipient['last_name'] ?? ''),
                    'course_id' => $courseId,
                    'subject' => $subject
                ], 'notification');
                
                header('Location: student_notifications.php?message=' . urlencode('Your message has been sent successfully to Dr. ' . ($recipient['first_name'] ?? '') . ' ' . ($recipient['last_name'] ?? '') . '!') . '&type=success');
                exit;
            } else {
                // If notifications table doesn't exist, just log it
                Logger::info("Student sent message (notifications table not available)", [
                    'student_id' => $studentId,
                    'student_name' => ($studentInfo['first_name'] ?? '') . ' ' . ($studentInfo['last_name'] ?? ''),
                    'recipient_type' => $recipientType,
                    'recipient_id' => $recipientId,
                    'recipient_name' => ($recipient['first_name'] ?? '') . ' ' . ($recipient['last_name'] ?? ''),
                    'course_id' => $courseId,
                    'subject' => $subject
                ], 'notification');
                
                header('Location: student_notifications.php?message=' . urlencode('Your message has been logged! Note: Notifications system is being set up. Please contact IT to enable message delivery.') . '&type=warning');
                exit;
            }
        } catch (Exception $e) {
            Logger::error("Error sending message", [
                'error' => $e->getMessage(),
                'student_id' => $studentId,
                'recipient_id' => $_POST['recipient_id'] ?? null
            ], 'notification');
            
            header('Location: student_notifications.php?message=' . urlencode('Error sending message: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'mark-read' && isset($_POST['notification_id'])) {
        try {
            $notificationId = (int)$_POST['notification_id'];
            $stmt = $db->prepare("UPDATE notifications SET is_read = TRUE, read_at = NOW() WHERE id = ? AND student_id = ?");
            $stmt->execute([$notificationId, $studentId]);
            
            Logger::info("Notification marked as read", [
                'notification_id' => $notificationId,
                'student_id' => $studentId
            ], 'notification');
            
            header('Location: student_notifications.php?message=' . urlencode('Notification marked as read') . '&type=success');
            exit;
        } catch (Exception $e) {
            header('Location: student_notifications.php?message=' . urlencode('Error: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'mark-unread' && isset($_POST['notification_id'])) {
        try {
            $notificationId = (int)$_POST['notification_id'];
            $stmt = $db->prepare("UPDATE notifications SET is_read = FALSE, read_at = NULL WHERE id = ? AND student_id = ?");
            $stmt->execute([$notificationId, $studentId]);
            
            header('Location: student_notifications.php?message=' . urlencode('Notification marked as unread') . '&type=success');
            exit;
        } catch (Exception $e) {
            header('Location: student_notifications.php?message=' . urlencode('Error: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'mark-all-read' && $studentId) {
        try {
            $stmt = $db->prepare("UPDATE notifications SET is_read = TRUE, read_at = NOW() WHERE student_id = ? AND is_read = FALSE");
            $stmt->execute([$studentId]);
            $affected = $stmt->rowCount();
            
            Logger::info("All notifications marked as read", [
                'student_id' => $studentId,
                'notifications_marked' => $affected
            ], 'notification');
            
            header('Location: student_notifications.php?message=' . urlencode("Marked {$affected} notification(s) as read") . '&type=success');
            exit;
        } catch (Exception $e) {
            header('Location: student_notifications.php?message=' . urlencode('Error: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    }
}

// Check if notifications table exists
$notificationsTableExists = false;
try {
    $tableCheck = $db->query("SHOW TABLES LIKE 'notifications'");
    $notificationsTableExists = $tableCheck->rowCount() > 0;
} catch (Exception $e) {
    $notificationsTableExists = false;
}

// Get filters
$typeFilter = isset($_GET['type']) ? trim($_GET['type']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$courseFilter = isset($_GET['course']) ? trim($_GET['course']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch notifications from database
$notifications = [];
$stats = [
    'unread' => 0,
    'read' => 0,
    'urgent' => 0,
    'total' => 0
];

if ($notificationsTableExists && $studentId) {
    try {
        // Build WHERE clause
        $where = "WHERE n.student_id = ?";
        $params = [$studentId];
        
        if ($typeFilter !== '') {
            $where .= " AND n.notification_type = ?";
            $params[] = $typeFilter;
        }
        
        if ($statusFilter === 'read') {
            $where .= " AND n.is_read = TRUE";
        } elseif ($statusFilter === 'unread') {
            $where .= " AND (n.is_read = FALSE OR n.is_read IS NULL)";
        } elseif ($statusFilter === 'urgent') {
            $where .= " AND n.priority = 'urgent'";
        }
        
        if ($courseFilter !== '') {
            $where .= " AND c.course_code = ?";
            $params[] = $courseFilter;
        }
        
        if ($search !== '') {
            $where .= " AND (n.title LIKE ? OR n.message LIKE ?)";
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
        }
        
        // Get statistics
        $statsStmt = $db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_read = FALSE OR is_read IS NULL THEN 1 ELSE 0 END) as unread,
                SUM(CASE WHEN is_read = TRUE THEN 1 ELSE 0 END) as read,
                SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent
            FROM notifications
            WHERE student_id = ?
        ");
        $statsStmt->execute([$studentId]);
        $statsRow = $statsStmt->fetch(PDO::FETCH_ASSOC);
        if ($statsRow) {
            $stats['total'] = (int)$statsRow['total'];
            $stats['unread'] = (int)$statsRow['unread'];
            $stats['read'] = (int)$statsRow['read'];
            $stats['urgent'] = (int)$statsRow['urgent'];
        }
        
        // Fetch notifications
        $sql = "
            SELECT n.*, 
                   c.course_code, c.course_name,
                   CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                   CASE 
                       WHEN n.created_by_role = 'system' THEN 'System Administrator'
                       WHEN n.created_by_role = 'admin' THEN 'Administrator'
                       WHEN d.first_name IS NOT NULL THEN CONCAT('Dr. ', d.first_name, ' ', d.last_name)
                       ELSE 'Unknown'
                   END as sender_name
            FROM notifications n
            LEFT JOIN courses c ON n.course_id = c.id
            LEFT JOIN doctors d ON n.doctor_id = d.id
            $where
            ORDER BY 
                CASE WHEN n.is_read = FALSE OR n.is_read IS NULL THEN 0 ELSE 1 END,
                CASE WHEN n.priority = 'urgent' THEN 0 ELSE 1 END,
                n.created_at DESC
            LIMIT 100
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Error loading notifications: " . $e->getMessage());
    }
}

// Get enrolled courses for filter dropdown
$enrolledCoursesList = [];
if ($studentId) {
    try {
        $coursesStmt = $db->prepare("
            SELECT DISTINCT c.id, c.course_code, c.course_name
            FROM courses c
            INNER JOIN student_courses sc ON c.id = sc.course_id
            WHERE sc.student_id = ? AND (sc.status = 'taking' OR sc.status = 'approved' OR sc.status IS NULL)
            ORDER BY c.course_code
        ");
        $coursesStmt->execute([$studentId]);
        $enrolledCoursesList = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting enrolled courses list: " . $e->getMessage());
    }
}

// Get available recipients (doctors from enrolled courses) for send message dropdown
$availableRecipients = [];
if ($studentId) {
    try {
        // Get doctors assigned to courses the student is enrolled in
        $recipientsStmt = $db->prepare("
            SELECT DISTINCT 
                d.id as doctor_id,
                CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                d.email as doctor_email,
                c.id as course_id,
                c.course_code,
                c.course_name
            FROM doctors d
            INNER JOIN doctor_courses dc ON d.id = dc.doctor_id
            INNER JOIN courses c ON dc.course_id = c.id
            INNER JOIN student_courses sc ON c.id = sc.course_id
            WHERE sc.student_id = ? 
            AND (sc.status = 'taking' OR sc.status = 'approved' OR sc.status IS NULL)
            ORDER BY d.last_name, d.first_name, c.course_code
        ");
        $recipientsStmt->execute([$studentId]);
        $availableRecipients = $recipientsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Also get all doctors (for general inquiries)
        $allDoctorsStmt = $db->query("
            SELECT DISTINCT 
                d.id as doctor_id,
                CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                d.email as doctor_email,
                NULL as course_id,
                NULL as course_code,
                NULL as course_name
            FROM doctors d
            ORDER BY d.last_name, d.first_name
            LIMIT 50
        ");
        $allDoctors = $allDoctorsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Merge and deduplicate by doctor_id
        $recipientsMap = [];
        foreach ($availableRecipients as $recipient) {
            $key = $recipient['doctor_id'];
            if (!isset($recipientsMap[$key])) {
                $recipientsMap[$key] = $recipient;
            }
        }
        foreach ($allDoctors as $doctor) {
            $key = $doctor['doctor_id'];
            if (!isset($recipientsMap[$key])) {
                $recipientsMap[$key] = $doctor;
            }
        }
        $availableRecipients = array_values($recipientsMap);
        
    } catch (Exception $e) {
        error_log("Error getting available recipients: " . $e->getMessage());
    }
}

// Get student info for send message form
$studentInfo = null;
if ($studentId) {
    try {
        $studentInfoStmt = $db->prepare("SELECT first_name, last_name, email FROM students WHERE id = ?");
        $studentInfoStmt->execute([$studentId]);
        $studentInfo = $studentInfoStmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting student info: " . $e->getMessage());
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
            <a href="student_notifications.php" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Notifications</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Stay updated with important announcements and messages.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-primary" onclick="showSendMessageModal()">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                    <button class="btn btn-outline" onclick="refreshNotifications()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <?php if ($notificationsTableExists && $studentId): ?>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Mark all notifications as read?');">
                        <input type="hidden" name="action" value="mark-all-read">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-double"></i> Mark All Read
                        </button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-primary" onclick="markAllAsRead()" disabled>
                        <i class="fas fa-check-double"></i> Mark All Read
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Notification Statistics -->
            <section class="notification-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['unread']); ?></div>
                        <div style="color: var(--text-secondary);">Unread</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['read']); ?></div>
                        <div style="color: var(--text-secondary);">Read</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['urgent']); ?></div>
                        <div style="color: var(--text-secondary);">Urgent</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['total']); ?></div>
                        <div style="color: var(--text-secondary);">Total</div>
                    </div>
                </div>
            </section>

            <!-- Notification Filter -->
            <section class="notification-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <form method="GET" action="student_notifications.php" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" name="search" class="form-input" placeholder="Search notifications..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div>
                            <select class="form-input" name="type" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="assignment" <?php echo $typeFilter === 'assignment' ? 'selected' : ''; ?>>Assignments</option>
                                <option value="exam" <?php echo $typeFilter === 'exam' ? 'selected' : ''; ?>>Exams</option>
                                <option value="announcement" <?php echo $typeFilter === 'announcement' ? 'selected' : ''; ?>>Announcements</option>
                                <option value="grade" <?php echo $typeFilter === 'grade' ? 'selected' : ''; ?>>Grades</option>
                                <option value="system" <?php echo $typeFilter === 'system' ? 'selected' : ''; ?>>System</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" name="status" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="unread" <?php echo $statusFilter === 'unread' ? 'selected' : ''; ?>>Unread</option>
                                <option value="read" <?php echo $statusFilter === 'read' ? 'selected' : ''; ?>>Read</option>
                                <option value="urgent" <?php echo $statusFilter === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" name="course" onchange="this.form.submit()">
                                <option value="">All Courses</option>
                                <?php foreach ($enrolledCoursesList as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['course_code']); ?>" <?php echo $courseFilter === $course['course_code'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="student_notifications.php" class="btn btn-outline">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Notifications List -->
            <section class="notifications-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-bell" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            All Notifications
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="toggleView('all')" id="allViewBtn">
                                <i class="fas fa-list"></i> All
                            </button>
                            <button class="btn btn-outline" onclick="toggleView('unread')" id="unreadViewBtn">
                                <i class="fas fa-envelope"></i> Unread
                            </button>
                        </div>
                    </div>

                    <div class="notifications-container" id="notificationsContainer">
                        <?php if (!$notificationsTableExists): ?>
                            <div style="padding: 3rem; text-align: center;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--warning-color); margin-bottom: 1rem;"></i>
                                <p style="color: var(--text-secondary); font-weight: 500; margin-bottom: 0.5rem; font-size: 1.1rem;">Notifications system is not available</p>
                                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">The notifications system is currently being set up. Please contact the IT department if you need assistance.</p>
                                <p style="color: var(--text-secondary); font-size: 0.85rem; font-style: italic;">You can still send messages to your instructors using the chat widget on this page.</p>
                            </div>
                        <?php elseif (empty($notifications)): ?>
                            <div style="padding: 3rem; text-align: center;">
                                <i class="fas fa-bell-slash" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                                <p style="color: var(--text-secondary); font-weight: 500; margin-bottom: 0.5rem;">No notifications found</p>
                                <p style="color: var(--text-secondary); font-size: 0.9rem;">You don't have any notifications at this time.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <?php
                                $isRead = !empty($notification['is_read']);
                                $priority = $notification['priority'] ?? 'normal';
                                $type = $notification['notification_type'] ?? 'announcement';
                                $courseCode = $notification['course_code'] ?? '';
                                
                                // Determine border color and icon based on type
                                $borderColor = 'var(--primary-color)';
                                $iconClass = 'fas fa-bullhorn';
                                $badgeColor = 'var(--primary-color)';
                                
                                switch ($type) {
                                    case 'assignment':
                                        $borderColor = 'var(--warning-color)';
                                        $iconClass = 'fas fa-tasks';
                                        $badgeColor = 'var(--warning-color)';
                                        break;
                                    case 'exam':
                                        $borderColor = 'var(--error-color)';
                                        $iconClass = 'fas fa-graduation-cap';
                                        $badgeColor = 'var(--error-color)';
                                        break;
                                    case 'grade':
                                        $borderColor = 'var(--success-color)';
                                        $iconClass = 'fas fa-chart-line';
                                        $badgeColor = 'var(--success-color)';
                                        break;
                                    case 'system':
                                        $borderColor = 'var(--accent-color)';
                                        $iconClass = 'fas fa-cog';
                                        $badgeColor = 'var(--accent-color)';
                                        break;
                                }
                                
                                if ($priority === 'urgent') {
                                    $borderColor = 'var(--error-color)';
                                }
                                
                                $senderName = $notification['sender_name'] ?? 'System';
                                $courseName = $notification['course_name'] ?? '';
                                $createdAt = $notification['created_at'] ?? '';
                                $dateFormatted = $createdAt ? date('M j, Y g:i A', strtotime($createdAt)) : 'N/A';
                                ?>
                                <div class="notification-item <?php echo $isRead ? 'read' : 'unread'; ?> <?php echo $priority === 'urgent' ? 'urgent' : ''; ?>" 
                                     data-type="<?php echo htmlspecialchars($type); ?>" 
                                     data-course="<?php echo htmlspecialchars(strtolower($courseCode)); ?>" 
                                     data-status="<?php echo $priority === 'urgent' ? 'urgent' : ($isRead ? 'read' : 'unread'); ?>"
                                     style="border-left: 4px solid <?php echo $borderColor; ?>; background-color: <?php echo $isRead ? 'var(--background-color)' : 'var(--surface-color)'; ?>; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; <?php echo $isRead ? 'opacity: 0.7;' : ''; ?>">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                                <i class="<?php echo $iconClass; ?>" style="color: <?php echo $borderColor; ?>;"></i>
                                                <?php if ($priority === 'urgent'): ?>
                                                    <span style="background-color: var(--error-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500;">URGENT</span>
                                                <?php endif; ?>
                                                <span style="background-color: <?php echo $badgeColor; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;"><?php echo htmlspecialchars(ucfirst($type)); ?></span>
                                                <?php if ($isRead): ?>
                                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Read</span>
                                                <?php endif; ?>
                                            </div>
                                            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);"><?php echo htmlspecialchars($notification['title'] ?? 'No Title'); ?></h3>
                                            <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);"><?php echo htmlspecialchars($notification['message'] ?? ''); ?></p>
                                            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($senderName); ?></span>
                                                <?php if (!empty($courseCode)): ?>
                                                    <span><i class="fas fa-book"></i> <?php echo htmlspecialchars($courseCode . ($courseName ? ' - ' . $courseName : '')); ?></span>
                                                <?php endif; ?>
                                                <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($dateFormatted); ?></span>
                                            </div>
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                            <button class="btn btn-primary" style="padding: 0.5rem;" onclick="viewNotification(<?php echo $notification['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($isRead): ?>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Mark this notification as unread?');">
                                                    <input type="hidden" name="action" value="mark-unread">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                    <button type="submit" class="btn btn-outline" style="padding: 0.5rem;">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="mark-read">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                    <button type="submit" class="btn btn-outline" style="padding: 0.5rem;">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Send Message Modal -->
    <div id="sendMessageModalOverlay" class="modal-overlay" onclick="closeSendMessageModal()"></div>
    <div id="sendMessageModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Send Message</h2>
                <button class="modal-close" onclick="closeSendMessageModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="sendMessageForm" method="POST" action="student_notifications.php">
                    <input type="hidden" name="action" value="send-message">
                    
                    <div class="form-group">
                        <label class="form-label">From</label>
                        <input type="text" class="form-input" value="<?php echo htmlspecialchars(($studentInfo['first_name'] ?? '') . ' ' . ($studentInfo['last_name'] ?? '') . ' (' . ($studentInfo['email'] ?? '') . ')'); ?>" readonly style="background-color: var(--background-color);">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">To <span style="color: var(--error-color);">*</span></label>
                        <select name="recipient_id" class="form-input" required id="recipientSelect" onchange="updateRecipientInfo()">
                            <option value="">Select Recipient</option>
                            <?php if (!empty($availableRecipients)): ?>
                                <?php 
                                // Group by doctor
                                $groupedRecipients = [];
                                foreach ($availableRecipients as $recipient) {
                                    $key = $recipient['doctor_id'];
                                    if (!isset($groupedRecipients[$key])) {
                                        $groupedRecipients[$key] = $recipient;
                                    }
                                }
                                
                                // Separate course instructors from all doctors
                                $courseInstructors = array_filter($availableRecipients, function($r) { return !empty($r['course_code']); });
                                ?>
                                <?php if (!empty($courseInstructors)): ?>
                                <optgroup label="Your Course Instructors">
                                    <?php foreach ($courseInstructors as $recipient): ?>
                                        <option value="<?php echo $recipient['doctor_id']; ?>" 
                                                data-type="doctor" 
                                                data-course-id="<?php echo $recipient['course_id']; ?>"
                                                data-course-code="<?php echo htmlspecialchars($recipient['course_code']); ?>"
                                                data-email="<?php echo htmlspecialchars($recipient['doctor_email'] ?? ''); ?>">
                                            Dr. <?php echo htmlspecialchars($recipient['doctor_name']); ?> - <?php echo htmlspecialchars($recipient['course_code'] . ' (' . $recipient['course_name'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                                <?php if (count($groupedRecipients) > 0): ?>
                                <optgroup label="All Instructors">
                                    <?php foreach ($groupedRecipients as $recipient): ?>
                                        <option value="<?php echo $recipient['doctor_id']; ?>" 
                                                data-type="doctor" 
                                                data-email="<?php echo htmlspecialchars($recipient['doctor_email'] ?? ''); ?>">
                                            Dr. <?php echo htmlspecialchars($recipient['doctor_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                            <?php else: ?>
                                <option value="" disabled>No recipients available</option>
                            <?php endif; ?>
                        </select>
                        <input type="hidden" name="recipient_type" id="recipientType" value="doctor">
                        <input type="hidden" name="course_id" id="courseId" value="">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Subject <span style="color: var(--error-color);">*</span></label>
                        <input type="text" name="subject" class="form-input" placeholder="Enter message subject" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Message <span style="color: var(--error-color);">*</span></label>
                        <textarea name="message" class="form-input" rows="6" placeholder="Type your message here..." required></textarea>
                    </div>
                    
                    <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                        <button type="button" class="btn btn-outline" onclick="closeSendMessageModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </div>
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
        
        let currentView = 'all';
        
        // Send Message Modal Functions - Define globally
        function showSendMessageModal() {
            console.log('showSendMessageModal called');
            const modal = document.getElementById('sendMessageModal');
            const overlay = document.getElementById('sendMessageModalOverlay');
            console.log('Modal elements:', {modal, overlay});
            if (modal && overlay) {
                overlay.classList.add('active');
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                console.log('Modal opened successfully');
            } else {
                console.error('Modal elements not found:', {modal, overlay});
                alert('Error: Modal elements not found. Please refresh the page.');
            }
        }
        
        function closeSendMessageModal() {
            const modal = document.getElementById('sendMessageModal');
            const overlay = document.getElementById('sendMessageModalOverlay');
            if (modal && overlay) {
                modal.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
                const form = document.getElementById('sendMessageForm');
                if (form) {
                    form.reset();
                    const recipientTypeInput = document.getElementById('recipientType');
                    const courseIdInput = document.getElementById('courseId');
                    if (recipientTypeInput) recipientTypeInput.value = 'doctor';
                    if (courseIdInput) courseIdInput.value = '';
                }
            }
        }
        
        function updateRecipientInfo() {
            const select = document.getElementById('recipientSelect');
            if (!select) return;
            
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const recipientType = selectedOption.getAttribute('data-type') || 'doctor';
                const courseId = selectedOption.getAttribute('data-course-id') || '';
                
                const recipientTypeInput = document.getElementById('recipientType');
                const courseIdInput = document.getElementById('courseId');
                
                if (recipientTypeInput) {
                    recipientTypeInput.value = recipientType;
                }
                if (courseIdInput) {
                    courseIdInput.value = courseId;
                }
            }
        }

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
            
            // Set up modal overlay click handler
            const overlay = document.getElementById('sendMessageModalOverlay');
            const modal = document.getElementById('sendMessageModal');
            
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        closeSendMessageModal();
                    }
                });
            }
            
            // Prevent modal content clicks from closing the modal
            if (modal) {
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                }
            }
        });

        // Filter notifications
        function filterNotifications() {
            const searchTerm = document.getElementById('notificationSearch').value.toLowerCase();
            const typeFilter = document.getElementById('typeFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const courseFilter = document.getElementById('courseFilter').value;

            const notificationItems = document.querySelectorAll('.notification-item');

            notificationItems.forEach(item => {
                const title = item.querySelector('h3').textContent.toLowerCase();
                const content = item.querySelector('p').textContent.toLowerCase();
                const type = item.getAttribute('data-type');
                const course = item.getAttribute('data-course');
                const status = item.getAttribute('data-status');

                const matchesSearch = title.includes(searchTerm) || content.includes(searchTerm);
                const matchesType = !typeFilter || type === typeFilter;
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesCourse = !courseFilter || course === courseFilter;

                if (matchesSearch && matchesType && matchesStatus && matchesCourse) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Toggle view
        function toggleView(view) {
            currentView = view;
            const notificationItems = document.querySelectorAll('.notification-item');

            notificationItems.forEach(item => {
                if (view === 'all') {
                    item.style.display = 'block';
                } else if (view === 'unread') {
                    if (item.classList.contains('unread')) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });

            updateViewButtons();
        }

        // Update view buttons
        function updateViewButtons() {
            const allBtn = document.getElementById('allViewBtn');
            const unreadBtn = document.getElementById('unreadViewBtn');

            if (currentView === 'all') {
                allBtn.classList.add('btn-primary');
                allBtn.classList.remove('btn-outline');
                unreadBtn.classList.add('btn-outline');
                unreadBtn.classList.remove('btn-primary');
            } else {
                unreadBtn.classList.add('btn-primary');
                unreadBtn.classList.remove('btn-outline');
                allBtn.classList.add('btn-outline');
                allBtn.classList.remove('btn-primary');
            }
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('notificationSearch').value = '';
            document.getElementById('typeFilter').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('courseFilter').value = '';

            const notificationItems = document.querySelectorAll('.notification-item');
            notificationItems.forEach(item => {
                item.style.display = 'block';
            });

            showNotification('Filters reset successfully', 'success');
        }

        // View notification
        function viewNotification(notificationId) {
            console.log('View notification:', notificationId);
            // Could open a modal with full details in the future
        }
        

        // Refresh notifications
        function refreshNotifications() {
            window.location.reload();
        }
        
        // Mark all as read (if using JavaScript fallback)
        function markAllAsRead() {
            if (confirm('Mark all notifications as read?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="mark-all-read">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
