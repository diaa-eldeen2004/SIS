<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - Student Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
// Load assignments from database
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

// Get enrolled course IDs for this student
$enrolledCourseIds = [];
if ($studentId) {
    try {
        $courseStmt = $db->prepare("SELECT course_id FROM student_courses WHERE student_id = ? AND (status = 'taking' OR status = 'approved' OR status IS NULL)");
        $courseStmt->execute([$studentId]);
        $enrolledCourses = $courseStmt->fetchAll(PDO::FETCH_COLUMN);
        $enrolledCourseIds = array_filter($enrolledCourses);
    } catch (Exception $e) {
        error_log("Error getting enrolled courses: " . $e->getMessage());
    }
}

// Fetch assignments from calendar_events table
$assignments = [];
$stats = [
    'pending' => 0,
    'submitted' => 0,
    'graded' => 0,
    'overdue' => 0
];

if (!empty($enrolledCourseIds)) {
    try {
        $placeholders = implode(',', array_fill(0, count($enrolledCourseIds), '?'));
        $sql = "
            SELECT ce.*, c.course_code, c.course_name
            FROM calendar_events ce
            LEFT JOIN courses c ON ce.course_id = c.id
            WHERE ce.event_type = 'assignment'
            AND ce.status = 'active'
            AND ce.course_id IN ($placeholders)
            ORDER BY ce.end_date ASC, ce.start_date ASC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($enrolledCourseIds);
        $allAssignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $today = date('Y-m-d');
        
        foreach ($allAssignments as $assignment) {
            // Determine assignment status based on due date
            $dueDate = $assignment['end_date'] ?? $assignment['start_date'] ?? null;
            $status = 'pending'; // default
            $dueDateCategory = 'month'; // default
            
            if ($dueDate) {
                $dueTimestamp = strtotime($dueDate);
                $todayTimestamp = strtotime($today);
                $daysUntilDue = floor(($dueTimestamp - $todayTimestamp) / (60 * 60 * 24));
                
                // Determine status
                if ($daysUntilDue < 0) {
                    $status = 'overdue';
                } else {
                    $status = 'pending'; // For now, we'll assume pending if not submitted/graded
                }
                
                // Determine due date category for filtering
                if ($daysUntilDue == 0) {
                    $dueDateCategory = 'today';
                } elseif ($daysUntilDue > 0 && $daysUntilDue <= 7) {
                    $dueDateCategory = 'week';
                } elseif ($daysUntilDue > 7 && $daysUntilDue <= 30) {
                    $dueDateCategory = 'month';
                }
            }
            
            $assignment['computed_status'] = $status;
            $assignment['due_date_category'] = $dueDateCategory;
            $assignments[] = $assignment;
            
            // Update statistics
            if ($status === 'pending') {
                $stats['pending']++;
            } elseif ($status === 'submitted') {
                $stats['submitted']++;
            } elseif ($status === 'graded') {
                $stats['graded']++;
            } elseif ($status === 'overdue') {
                $stats['overdue']++;
            }
        }
    } catch (Exception $e) {
        error_log("Error loading assignments: " . $e->getMessage());
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
            <a href="student_assignments.php" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Assignments</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">View and submit your course assignments.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshAssignments()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="showSubmissionHistory()">
                        <i class="fas fa-history"></i> Submission History
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Assignment Filter -->
            <section class="assignment-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search assignments..." id="assignmentSearch" onkeyup="filterAssignments()">
                        </div>
                        <div>
                            <select class="form-input" id="courseFilter" onchange="filterAssignments()">
                                <option value="">All Courses</option>
                                <?php foreach ($enrolledCoursesList as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['course_code']); ?>">
                                        <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterAssignments()">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="submitted">Submitted</option>
                                <option value="graded">Graded</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="dueDateFilter" onchange="filterAssignments()">
                                <option value="">All Due Dates</option>
                                <option value="today">Due Today</option>
                                <option value="week">Due This Week</option>
                                <option value="month">Due This Month</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Assignment Statistics -->
            <section class="assignment-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $stats['pending']; ?></div>
                        <div style="color: var(--text-secondary);">Pending</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $stats['submitted']; ?></div>
                        <div style="color: var(--text-secondary);">Submitted</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $stats['graded']; ?></div>
                        <div style="color: var(--text-secondary);">Graded</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo $stats['overdue']; ?></div>
                        <div style="color: var(--text-secondary);">Overdue</div>
                    </div>
                </div>
            </section>

            <!-- Assignments List -->
            <section class="assignments-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-tasks" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            All Assignments
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="toggleView('list')" id="listViewBtn">
                                <i class="fas fa-list"></i>
                            </button>
                            <button class="btn btn-outline" onclick="toggleView('cards')" id="cardsViewBtn">
                                <i class="fas fa-th"></i>
                            </button>
                        </div>
                    </div>

                    <!-- List View -->
                    <div id="listView" class="assignment-list-view">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Course</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Grade</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($assignments)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                            <p>No assignments found. Assignments will appear here once they are created for your enrolled courses.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    foreach ($assignments as $assignment): 
                                        $status = $assignment['computed_status'];
                                        $dueDate = $assignment['end_date'] ?? $assignment['start_date'];
                                        $courseCode = $assignment['course_code'] ?? 'N/A';
                                        $courseName = $assignment['course_name'] ?? '';
                                        
                                        // Format due date
                                        $dueDateFormatted = $dueDate ? date('M j, Y', strtotime($dueDate)) : 'TBD';
                                        
                                        // Calculate days until due
                                        $daysUntilDue = null;
                                        if ($dueDate) {
                                            $daysUntilDue = floor((strtotime($dueDate) - strtotime(date('Y-m-d'))) / (60 * 60 * 24));
                                        }
                                        
                                        // Determine status colors and text
                                        $statusColors = [
                                            'pending' => 'var(--warning-color)',
                                            'submitted' => 'var(--primary-color)',
                                            'graded' => 'var(--success-color)',
                                            'overdue' => 'var(--error-color)'
                                        ];
                                        $statusText = ucfirst($status);
                                        $statusColor = $statusColors[$status] ?? 'var(--secondary-color)';
                                        
                                        // Determine due date text
                                        $dueDateText = '';
                                        $dueDateColor = 'var(--text-secondary)';
                                        if ($daysUntilDue !== null) {
                                            if ($daysUntilDue < 0) {
                                                $dueDateText = 'Overdue';
                                                $dueDateColor = 'var(--error-color)';
                                            } elseif ($daysUntilDue == 0) {
                                                $dueDateText = 'Due Today';
                                                $dueDateColor = 'var(--warning-color)';
                                            } elseif ($daysUntilDue == 1) {
                                                $dueDateText = 'Due Tomorrow';
                                                $dueDateColor = 'var(--warning-color)';
                                            } elseif ($daysUntilDue <= 7) {
                                                $dueDateText = "Due in {$daysUntilDue} days";
                                                $dueDateColor = 'var(--primary-color)';
                                            } else {
                                                $dueDateText = "Due in {$daysUntilDue} days";
                                                $dueDateColor = 'var(--text-secondary)';
                                            }
                                        }
                                    ?>
                                    <tr data-course="<?php echo htmlspecialchars($courseCode); ?>" data-status="<?php echo htmlspecialchars($status); ?>" data-due="<?php echo htmlspecialchars($assignment['due_date_category']); ?>">
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>
                                                <?php if (!empty($assignment['description'])): ?>
                                                    <br><small style="color: var(--text-secondary);"><?php echo htmlspecialchars(substr($assignment['description'], 0, 80)) . (strlen($assignment['description']) > 80 ? '...' : ''); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($courseCode . ($courseName ? ' - ' . $courseName : '')); ?></td>
                                        <td>
                                            <div style="color: <?php echo $dueDateColor; ?>; font-weight: 500;"><?php echo htmlspecialchars($dueDateFormatted); ?></div>
                                            <?php if ($dueDateText): ?>
                                                <small style="color: <?php echo $dueDateColor; ?>;"><?php echo htmlspecialchars($dueDateText); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><span style="background-color: <?php echo $statusColor; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;"><?php echo htmlspecialchars($statusText); ?></span></td>
                                        <td>-</td>
                                        <td>
                                            <div style="display: flex; gap: 0.25rem;">
                                                <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewAssignment(<?php echo $assignment['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($status === 'pending' || $status === 'overdue'): ?>
                                                    <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="submitAssignment(<?php echo $assignment['id']; ?>)">
                                                        <i class="fas fa-upload"></i>
                                                    </button>
                                                <?php elseif ($status === 'submitted'): ?>
                                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewSubmission(<?php echo $assignment['id']; ?>)">
                                                        <i class="fas fa-file-alt"></i>
                                                    </button>
                                                <?php elseif ($status === 'graded'): ?>
                                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewFeedback(<?php echo $assignment['id']; ?>)">
                                                        <i class="fas fa-comments"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cards View -->
                    <div id="cardsView" class="assignment-cards-view" style="display: none;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
                            <?php if (empty($assignments)): ?>
                                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-secondary);">
                                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                    <p>No assignments found. Assignments will appear here once they are created for your enrolled courses.</p>
                                </div>
                            <?php else: ?>
                                <?php 
                                foreach ($assignments as $assignment): 
                                    $status = $assignment['computed_status'];
                                    $dueDate = $assignment['end_date'] ?? $assignment['start_date'];
                                    $courseCode = $assignment['course_code'] ?? 'N/A';
                                    $courseName = $assignment['course_name'] ?? '';
                                    
                                    // Format due date
                                    $dueDateFormatted = $dueDate ? date('M j, Y', strtotime($dueDate)) : 'TBD';
                                    
                                    // Calculate days until due
                                    $daysUntilDue = null;
                                    if ($dueDate) {
                                        $daysUntilDue = floor((strtotime($dueDate) - strtotime(date('Y-m-d'))) / (60 * 60 * 24));
                                    }
                                    
                                    // Determine status colors and text
                                    $statusColors = [
                                        'pending' => 'var(--warning-color)',
                                        'submitted' => 'var(--primary-color)',
                                        'graded' => 'var(--success-color)',
                                        'overdue' => 'var(--error-color)'
                                    ];
                                    $statusText = ucfirst($status);
                                    $statusColor = $statusColors[$status] ?? 'var(--secondary-color)';
                                    
                                    // Determine due date text
                                    $dueDateText = '';
                                    $dueDateColor = 'var(--text-secondary)';
                                    if ($daysUntilDue !== null) {
                                        if ($daysUntilDue < 0) {
                                            $dueDateText = 'Overdue';
                                            $dueDateColor = 'var(--error-color)';
                                        } elseif ($daysUntilDue == 0) {
                                            $dueDateText = 'Due Today';
                                            $dueDateColor = 'var(--warning-color)';
                                        } elseif ($daysUntilDue == 1) {
                                            $dueDateText = 'Due Tomorrow';
                                            $dueDateColor = 'var(--warning-color)';
                                        } elseif ($daysUntilDue <= 7) {
                                            $dueDateText = "Due in {$daysUntilDue} days";
                                            $dueDateColor = 'var(--primary-color)';
                                        } else {
                                            $dueDateText = "Due in {$daysUntilDue} days";
                                            $dueDateColor = 'var(--text-secondary)';
                                        }
                                    }
                                ?>
                                <div class="assignment-card" data-course="<?php echo htmlspecialchars($courseCode); ?>" data-status="<?php echo htmlspecialchars($status); ?>" data-due="<?php echo htmlspecialchars($assignment['due_date_category']); ?>" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background-color: var(--surface-color);">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                        <div>
                                            <h3 style="margin: 0; color: var(--text-primary);"><?php echo htmlspecialchars($assignment['title']); ?></h3>
                                            <p style="margin: 0.25rem 0; color: var(--text-secondary); font-size: 0.9rem;"><?php echo htmlspecialchars($courseCode . ($courseName ? ' - ' . $courseName : '')); ?></p>
                                        </div>
                                        <span style="background-color: <?php echo $statusColor; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;"><?php echo htmlspecialchars($statusText); ?></span>
                                    </div>
                                    <?php if (!empty($assignment['description'])): ?>
                                        <p style="color: var(--text-secondary); margin-bottom: 1rem;"><?php echo htmlspecialchars(substr($assignment['description'], 0, 100)) . (strlen($assignment['description']) > 100 ? '...' : ''); ?></p>
                                    <?php endif; ?>
                                    <div style="margin-bottom: 1rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Due Date</span>
                                            <span style="font-size: 0.9rem; color: <?php echo $dueDateColor; ?>; font-weight: 500;"><?php echo htmlspecialchars($dueDateFormatted); ?></span>
                                        </div>
                                        <?php if ($dueDateText): ?>
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <span style="font-size: 0.9rem; color: var(--text-secondary);">Status</span>
                                                <span style="font-size: 0.9rem; color: <?php echo $dueDateColor; ?>;"><?php echo htmlspecialchars($dueDateText); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn btn-primary" style="flex: 1;" onclick="viewAssignment(<?php echo $assignment['id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <?php if ($status === 'pending' || $status === 'overdue'): ?>
                                            <button class="btn btn-success" onclick="submitAssignment(<?php echo $assignment['id']; ?>)">
                                                <i class="fas fa-upload"></i> Submit
                                            </button>
                                        <?php elseif ($status === 'submitted'): ?>
                                            <button class="btn btn-outline" onclick="viewSubmission(<?php echo $assignment['id']; ?>)">
                                                <i class="fas fa-file-alt"></i> Submission
                                            </button>
                                        <?php elseif ($status === 'graded'): ?>
                                            <button class="btn btn-outline" onclick="viewFeedback(<?php echo $assignment['id']; ?>)">
                                                <i class="fas fa-comments"></i> Feedback
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
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
                <h3>Assignment Chat</h3>
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
                        <input type="text" name="subject" class="form-input" placeholder="Assignment question" required>
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
        let currentView = 'list';

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

        // Filter assignments
        function filterAssignments() {
            const searchTerm = document.getElementById('assignmentSearch').value.toLowerCase();
            const courseFilter = document.getElementById('courseFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const dueDateFilter = document.getElementById('dueDateFilter').value;

            const assignmentRows = document.querySelectorAll('tbody tr');
            const assignmentCards = document.querySelectorAll('.assignment-card');

            assignmentRows.forEach(row => {
                const assignmentName = row.querySelector('td').textContent.toLowerCase();
                const course = row.getAttribute('data-course');
                const status = row.getAttribute('data-status');
                const dueDate = row.getAttribute('data-due');

                const matchesSearch = assignmentName.includes(searchTerm);
                const matchesCourse = !courseFilter || course === courseFilter;
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesDueDate = !dueDateFilter || dueDate === dueDateFilter;

                if (matchesSearch && matchesCourse && matchesStatus && matchesDueDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            assignmentCards.forEach(card => {
                const assignmentName = card.querySelector('h3').textContent.toLowerCase();
                const course = card.getAttribute('data-course');
                const status = card.getAttribute('data-status');
                const dueDate = card.getAttribute('data-due');

                const matchesSearch = assignmentName.includes(searchTerm);
                const matchesCourse = !courseFilter || course === courseFilter;
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesDueDate = !dueDateFilter || dueDate === dueDateFilter;

                if (matchesSearch && matchesCourse && matchesStatus && matchesDueDate) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Toggle view between list and cards
        function toggleView(view) {
            currentView = view;
            const listView = document.getElementById('listView');
            const cardsView = document.getElementById('cardsView');

            if (view === 'list') {
                listView.style.display = 'block';
                cardsView.style.display = 'none';
            } else {
                listView.style.display = 'none';
                cardsView.style.display = 'block';
            }

            updateViewButtons();
        }

        // Update view button states
        function updateViewButtons() {
            const listBtn = document.getElementById('listViewBtn');
            const cardsBtn = document.getElementById('cardsViewBtn');

            if (currentView === 'list') {
                listBtn.classList.add('btn-primary');
                listBtn.classList.remove('btn-outline');
                cardsBtn.classList.add('btn-outline');
                cardsBtn.classList.remove('btn-primary');
            } else {
                cardsBtn.classList.add('btn-primary');
                cardsBtn.classList.remove('btn-outline');
                listBtn.classList.add('btn-outline');
                listBtn.classList.remove('btn-primary');
            }
        }

        // View assignment details
        function viewAssignment(assignmentId) {
            showNotification(`Opening assignment details for ${assignmentId}...`, 'info');
            // In a real implementation, this would open a modal or navigate to assignment details
        }

        // Submit assignment
        function submitAssignment(assignmentId) {
            showNotification(`Opening submission form for ${assignmentId}...`, 'info');
            // In a real implementation, this would open a file upload modal
        }

        // View submission
        function viewSubmission(assignmentId) {
            showNotification(`Viewing submission for ${assignmentId}...`, 'info');
            // In a real implementation, this would show the submitted files
        }

        // View feedback
        function viewFeedback(assignmentId) {
            showNotification(`Viewing feedback for ${assignmentId}...`, 'info');
            // In a real implementation, this would show instructor feedback
        }

        // Refresh assignments
        function refreshAssignments() {
            showNotification('Refreshing assignments...', 'info');
            // In a real implementation, this would reload assignment data from the server
            setTimeout(() => {
                showNotification('Assignments refreshed successfully', 'success');
            }, 1000);
        }

        // Show submission history
        function showSubmissionHistory() {
            showNotification('Opening submission history...', 'info');
            // In a real implementation, this would open a modal or navigate to submission history
        }
    </script>
</body>
</html>
