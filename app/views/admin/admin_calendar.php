<?php
// Dynamic calendar page powered by DB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../core/Database.php';

$db = Database::getInstance()->getConnection();

// Read filters from query params
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$eventTypeFilter = isset($_GET['eventType']) ? trim($_GET['eventType']) : '';
$departmentFilter = isset($_GET['department']) ? trim($_GET['department']) : '';
$monthFilter = isset($_GET['month']) ? trim($_GET['month']) : '';

// Get current month/year for calendar display
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Initialize default values
$eventsThisMonth = 0;
$examsScheduled = 0;
$conflicts = 0;
$peopleAffected = 0;
$events = [];
$upcomingEvents = [];
$tableError = false;

// Check if calendar_events table exists, handle gracefully if it doesn't
try {
    // Count events this month
    $monthStmt = $db->prepare("SELECT COUNT(*) as cnt FROM calendar_events WHERE MONTH(start_date) = ? AND YEAR(start_date) = ?");
    $monthStmt->execute([$currentMonth, $currentYear]);
    $eventsThisMonth = (int)$monthStmt->fetchColumn();

    // Count exams scheduled
    $examsStmt = $db->query("SELECT COUNT(*) as cnt FROM calendar_events WHERE event_type = 'exam' AND status = 'active'");
    $examsScheduled = (int)$examsStmt->fetchColumn();

    // Count conflicts (simplified - events on same day/time)
    $conflictsStmt = $db->query("
        SELECT COUNT(*) as cnt FROM (
            SELECT DATE(start_date) as event_date, COUNT(*) as cnt 
            FROM calendar_events 
            WHERE status = 'active' 
            GROUP BY DATE(start_date) 
            HAVING cnt > 1
        ) as conflicts
    ");
    $conflicts = (int)$conflictsStmt->fetchColumn();

    // Approximate people affected (simplified - count unique course enrollments)
    $peopleStmt = $db->query("
        SELECT COUNT(DISTINCT course_id) as cnt 
        FROM calendar_events 
        WHERE course_id IS NOT NULL AND status = 'active'
    ");
    $peopleAffected = (int)$peopleStmt->fetchColumn() * 30; // Rough estimate

    // Build WHERE clause for filtering
    $where = "WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $where .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
        $like = "%$search%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if ($eventTypeFilter !== '') {
        $where .= " AND event_type = ?";
        $params[] = $eventTypeFilter;
    }

    if ($departmentFilter !== '') {
        $where .= " AND department = ?";
        $params[] = $departmentFilter;
    }

    if ($monthFilter !== '') {
        $where .= " AND MONTH(start_date) = ? AND YEAR(start_date) = YEAR(CURRENT_DATE())";
        $params[] = (int)$monthFilter;
    }

    // Fetch events (limit 100 for performance)
    $dataStmt = $db->prepare("SELECT * FROM calendar_events $where ORDER BY start_date ASC LIMIT 100");
    $dataStmt->execute($params);
    $events = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get upcoming events (next 7 days)
    $upcomingStmt = $db->prepare("
        SELECT * FROM calendar_events 
        WHERE start_date >= CURRENT_DATE() 
        AND start_date <= DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)
        AND status = 'active'
        ORDER BY start_date ASC 
        LIMIT 10
    ");
    $upcomingStmt->execute();
    $upcomingEvents = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get events for current month for calendar grid
    $calendarStmt = $db->prepare("
        SELECT * FROM calendar_events 
        WHERE MONTH(start_date) = ? AND YEAR(start_date) = ? AND status = 'active'
        ORDER BY start_date ASC
    ");
    $calendarStmt->execute([$currentMonth, $currentYear]);
    $calendarEvents = $calendarStmt->fetchAll(PDO::FETCH_ASSOC);

    // Group events by day for calendar display
    $eventsByDay = [];
    foreach ($calendarEvents as $event) {
        $day = (int)date('j', strtotime($event['start_date']));
        if (!isset($eventsByDay[$day])) {
            $eventsByDay[$day] = [];
        }
        $eventsByDay[$day][] = $event;
    }
} catch (PDOException $e) {
    $eventsByDay = [];
    // If calendar_events table doesn't exist, show friendly message
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $tableError = true;
        $errorMessage = "The calendar_events table doesn't exist yet. Please run the database migrations first.";
    } else {
        error_log('Calendar page database error: ' . $e->getMessage());
        $tableError = true;
        $errorMessage = "Database error: " . $e->getMessage();
    }
}

// Get unique departments for filter
$departments = [];
if (!$tableError) {
    try {
        $deptStmt = $db->query("SELECT DISTINCT department FROM calendar_events WHERE department IS NOT NULL ORDER BY department");
        $departments = $deptStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        // Ignore errors
    }
}

// Generate calendar grid
$firstDay = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
$daysInMonth = date('t', $firstDay);
$dayOfWeek = date('w', $firstDay); // 0 = Sunday, 6 = Saturday
$monthName = date('F', $firstDay);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Admin Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php if ($tableError): ?>
    <section class="error-message" style="margin: 2rem; padding: 2rem; background-color: var(--error-color); color: white; border-radius: 8px;">
        <h2 style="margin-top: 0;"><i class="fas fa-exclamation-triangle"></i> Database Table Not Found</h2>
        <p><?php echo htmlspecialchars($errorMessage); ?></p>
        <p>To fix this issue, please run the database migrations:</p>
        <a href="../../../run-migrations.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">
            <i class="fas fa-database"></i> Run Migrations
        </a>
    </section>
<?php endif; ?>
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
            <h2><i class="fas fa-graduation-cap"></i> Admin Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="admin_manage_students.php" class="nav-item">
                <i class="fas fa-user-graduate"></i> Manage Students
            </a>
            <a href="admin_manage_doctors.php" class="nav-item">
                <i class="fas fa-chalkboard-teacher"></i> Manage Doctors
            </a>
            <a href="admin_manage_courses.php" class="nav-item">
                <i class="fas fa-book"></i> Manage Courses
            </a>
            <a href="admin_manage_advisor.php" class="nav-item">
                <i class="fas fa-user-tie"></i> Manage Advisors
            </a>
            <a href="admin_manage_it.php" class="nav-item">
                <i class="fas fa-laptop-code"></i> Manage IT Officers
            </a>
            <a href="admin_manage_user.php" class="nav-item">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="admin_reports.php" class="nav-item">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="admin_calendar.php" class="nav-item active">
                <i class="fas fa-calendar-alt"></i> Calendar
            </a>
            <a href="admin_profile.php" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Calendar Management</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Manage university events, exams, and academic schedules.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshCalendar()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="createEvent()">
                        <i class="fas fa-plus"></i> Create Event
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Calendar Overview -->
            <section class="calendar-overview" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($eventsThisMonth); ?></div>
                        <div style="color: var(--text-secondary);">This Month</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($examsScheduled); ?></div>
                        <div style="color: var(--text-secondary);">Exams Scheduled</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($conflicts); ?></div>
                        <div style="color: var(--text-secondary);">Conflicts</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($peopleAffected); ?></div>
                        <div style="color: var(--text-secondary);">People Affected</div>
                    </div>
                </div>
            </section>

            <!-- Calendar Filter -->
            <section class="calendar-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <select class="form-input" id="eventTypeFilter" onchange="filterCalendar()">
                                <option value="">All Event Types</option>
                                <option value="exam">Exam</option>
                                <option value="assignment">Assignment Due</option>
                                <option value="holiday">Holiday</option>
                                <option value="meeting">Meeting</option>
                                <option value="event">University Event</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="departmentFilter" onchange="filterCalendar()">
                                <option value="">All Departments</option>
                                <option value="computer-science">Computer Science</option>
                                <option value="mathematics">Mathematics</option>
                                <option value="physics">Physics</option>
                                <option value="engineering">Engineering</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="monthFilter" onchange="filterCalendar()">
                                <option value="">All Months</option>
                                <option value="september">September 2024</option>
                                <option value="october">October 2024</option>
                                <option value="november">November 2024</option>
                                <option value="december">December 2024</option>
                            </select>
                        </div>
                        <div>
                            <button class="btn btn-outline" onclick="resetCalendarFilters()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Calendar View -->
            <section class="calendar-view">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-calendar-alt" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            <?php echo htmlspecialchars($monthName . ' ' . $currentYear); ?> Calendar
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="previousMonth()">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-outline" onclick="nextMonth()">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <button class="btn btn-primary" onclick="goToToday()">
                                <i class="fas fa-calendar-day"></i> Today
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="calendar-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background-color: var(--border-color);">
                        <!-- Calendar Headers -->
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Sun</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Mon</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Tue</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Wed</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Thu</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Fri</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Sat</div>

                        <!-- Calendar Days -->
                        <?php
                        // Print empty cells for days before the first day of the month
                        for ($i = 0; $i < $dayOfWeek; $i++) {
                            echo '<div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);"></div>';
                        }
                        
                        // Print days of the month
                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $isToday = ($day == date('j') && $currentMonth == date('n') && $currentYear == date('Y'));
                            $dayEvents = isset($eventsByDay[$day]) ? $eventsByDay[$day] : [];
                            
                            // Event type colors
                            $eventColors = [
                                'exam' => 'var(--warning-color)',
                                'assignment' => 'var(--error-color)',
                                'holiday' => 'var(--success-color)',
                                'meeting' => 'var(--accent-color)',
                                'university_event' => 'var(--primary-color)',
                                'other' => 'var(--text-secondary)'
                            ];
                            
                            echo '<div style="background-color: ' . ($isToday ? 'var(--primary-color)' : 'var(--surface-color)') . '; padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">';
                            echo '<div style="font-weight: 600; margin-bottom: 0.5rem; color: ' . ($isToday ? 'white' : 'var(--text-primary)') . ';">' . $day . '</div>';
                            
                            // Display events for this day (limit to 3 visible)
                            $displayed = 0;
                            foreach (array_slice($dayEvents, 0, 3) as $event) {
                                $eventType = $event['event_type'] ?? 'other';
                                $color = $eventColors[$eventType] ?? 'var(--text-secondary)';
                                $title = htmlspecialchars(substr($event['title'] ?? 'Event', 0, 20));
                                echo '<div style="background-color: ' . $color . '; color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.25rem; cursor: pointer;" onclick="editEvent(' . $event['id'] . ')" title="' . htmlspecialchars($event['title'] ?? '') . '">' . $title . '</div>';
                                $displayed++;
                            }
                            
                            if (count($dayEvents) > 3) {
                                echo '<div style="font-size: 0.7rem; color: var(--text-secondary);">+' . (count($dayEvents) - 3) . ' more</div>';
                            }
                            
                            echo '</div>';
                            
                            // Start new row if it's Saturday
                            if (($dayOfWeek + $day) % 7 == 0 && $day < $daysInMonth) {
                                // This will be handled by the grid
                            }
                        }
                        
                        // Print empty cells for days after the last day of the month
                        $lastDayOfWeek = ($dayOfWeek + $daysInMonth) % 7;
                        if ($lastDayOfWeek > 0) {
                            for ($i = $lastDayOfWeek; $i < 7; $i++) {
                                echo '<div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);"></div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- Upcoming Events -->
            <section class="upcoming-events" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-clock" style="color: var(--warning-color); margin-right: 0.5rem;"></i>
                            Upcoming Events
                        </h2>
                        <button class="btn btn-outline" onclick="viewAllEvents()">
                            <i class="fas fa-list"></i> View All
                        </button>
                    </div>
                    <div class="events-list">
                        <?php if (empty($upcomingEvents)): ?>
                            <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                                <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>No upcoming events in the next 7 days.</p>
                            </div>
                        <?php else: ?>
                            <?php 
                            $eventIcons = [
                                'exam' => 'fa-file-alt',
                                'assignment' => 'fa-tasks',
                                'holiday' => 'fa-calendar-check',
                                'meeting' => 'fa-users',
                                'university_event' => 'fa-star',
                                'other' => 'fa-calendar'
                            ];
                            $eventColors = [
                                'exam' => 'var(--warning-color)',
                                'assignment' => 'var(--error-color)',
                                'holiday' => 'var(--success-color)',
                                'meeting' => 'var(--accent-color)',
                                'university_event' => 'var(--primary-color)',
                                'other' => 'var(--text-secondary)'
                            ];
                            
                            foreach ($upcomingEvents as $event): 
                                $eventType = $event['event_type'] ?? 'other';
                                $icon = $eventIcons[$eventType] ?? 'fa-calendar';
                                $color = $eventColors[$eventType] ?? 'var(--text-secondary)';
                                $startDate = new DateTime($event['start_date']);
                                $isToday = $startDate->format('Y-m-d') == date('Y-m-d');
                                $isTomorrow = $startDate->format('Y-m-d') == date('Y-m-d', strtotime('+1 day'));
                                $dateLabel = $isToday ? 'Today' : ($isTomorrow ? 'Tomorrow' : $startDate->format('M j, Y'));
                            ?>
                                <div class="event-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                    <div style="width: 40px; height: 40px; background-color: <?php echo $color; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="fas <?php echo $icon; ?>"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);"><?php echo htmlspecialchars($event['title'] ?? 'Untitled Event'); ?></h4>
                                        <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;"><?php echo htmlspecialchars($event['description'] ?? 'No description'); ?></p>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo $dateLabel; ?></div>
                                        <div style="font-size: 0.9rem; color: <?php echo $color; ?>; font-weight: 500;"><?php echo $startDate->format('g:i A'); ?></div>
                                    </div>
                                    <div style="display: flex; gap: 0.25rem;">
                                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editEvent(<?php echo $event['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="deleteEvent(<?php echo $event['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="quick-actions" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-bolt" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Quick Actions
                        </h2>
                    </div>
                    <div class="grid grid-4">
                        <button class="btn btn-primary" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="createEvent()">
                            <i class="fas fa-plus" style="font-size: 2rem;"></i>
                            <span>Create Event</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="scheduleExam()">
                            <i class="fas fa-file-alt" style="font-size: 2rem;"></i>
                            <span>Schedule Exam</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="manageConflicts()">
                            <i class="fas fa-exclamation-triangle" style="font-size: 2rem;"></i>
                            <span>Manage Conflicts</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="exportCalendar()">
                            <i class="fas fa-download" style="font-size: 2rem;"></i>
                            <span>Export Calendar</span>
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Modal Overlay (shared for all modals) -->
    <div id="modalOverlay" class="modal-overlay" onclick="closeAllModals()" hidden></div>

    <!-- Add/Edit Event Modal -->
    <div id="eventFormModal" class="modal" data-header-style="primary" hidden>
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2 id="eventModalTitle">Create Event</h2>
                <button class="modal-close" onclick="closeEventFormModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="eventForm" onsubmit="handleEventFormSubmit(event)">
                <input type="hidden" id="eventId" name="id">
                <div class="form-group">
                    <label class="form-label">Event Title *</label>
                    <input type="text" name="title" id="eventTitle" class="form-input" placeholder="e.g., Midterm Exam - CS101" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="eventDescription" class="form-input" rows="3" placeholder="Event description..."></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Event Type *</label>
                        <select name="event_type" id="eventType" class="form-input" required>
                            <option value="">Select Type</option>
                            <option value="exam">Exam</option>
                            <option value="assignment">Assignment Due</option>
                            <option value="holiday">Holiday</option>
                            <option value="meeting">Meeting</option>
                            <option value="university_event">University Event</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="eventStatus" class="form-input">
                            <option value="active">Active</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="postponed">Postponed</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Start Date & Time *</label>
                        <input type="datetime-local" name="start_date" id="eventStartDate" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date & Time</label>
                        <input type="datetime-local" name="end_date" id="eventEndDate" class="form-input">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <select name="department" id="eventDepartment" class="form-input">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                            <?php endforeach; ?>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Physics">Physics</option>
                            <option value="Engineering">Engineering</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" id="eventLocation" class="form-input" placeholder="e.g., Room 101, Building A">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Course (optional)</label>
                    <input type="number" name="course_id" id="eventCourseId" class="form-input" placeholder="Course ID">
                    <small style="color: var(--text-secondary);">Leave blank if not course-specific</small>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Save Event
                    </button>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeEventFormModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Chat Widget -->
    <div class="chat-widget">
        <button class="chat-toggle" onclick="toggleChat()">
            <i class="fas fa-comments"></i>
        </button>
        <div class="chat-box">
            <div class="chat-header">
                <h3>Calendar Chat</h3>
                <button class="chat-close" onclick="closeChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chat-body">
                <form class="chat-form">
                    <div class="form-group">
                        <label class="form-label">From</label>
                        <input type="email" name="from" class="form-input" placeholder="admin@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="email" name="to" class="form-input" placeholder="user@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Calendar update" required>
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
                <a href="../index.php">Home</a>
                <a href="../app/about.php">About Us</a>
                <a href="../app/contact.php">Contact</a>
                <a href="../app/help_center.php">Help Center</a>
            </div>
            <div class="footer-section">
                <h3>User Access</h3>
                <a href="../auth/auth_login.php">Student Login</a>
                <a href="../auth/auth_login.php">Doctor Login</a>
                <a href="../auth/auth_login.php">Admin Login</a>
                <a href="../auth/auth_signup.php">Register</a>
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
        // Helper function to get API base path
        function getApiPath(endpoint) {
            const currentUrl = window.location.href;
            const url = new URL(currentUrl);
            const pathParts = url.pathname.split('/').filter(Boolean);
            let rootIndex = pathParts.indexOf('sis');
            if (rootIndex === -1) rootIndex = 0;
            const projectRoot = '/' + pathParts.slice(0, rootIndex + 1).join('/');
            return projectRoot + '/public/api/' + endpoint;
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
        });

        // Filter calendar
        function filterCalendar() {
            const eventTypeFilter = document.getElementById('eventTypeFilter').value;
            const departmentFilter = document.getElementById('departmentFilter').value;
            const monthFilter = document.getElementById('monthFilter').value;

            const params = new URLSearchParams();
            if (eventTypeFilter) params.append('eventType', eventTypeFilter);
            if (departmentFilter) params.append('department', departmentFilter);
            if (monthFilter) params.append('month', monthFilter);

            window.location.href = 'admin_calendar.php?' + params.toString();
        }

        // Reset calendar filters
        function resetCalendarFilters() {
            window.location.href = 'admin_calendar.php';
        }

        // Calendar navigation
        function previousMonth() {
            const urlParams = new URLSearchParams(window.location.search);
            let month = parseInt(urlParams.get('month')) || <?php echo $currentMonth; ?>;
            let year = parseInt(urlParams.get('year')) || <?php echo $currentYear; ?>;
            month--;
            if (month < 1) {
                month = 12;
                year--;
            }
            urlParams.set('month', month);
            urlParams.set('year', year);
            window.location.href = 'admin_calendar.php?' + urlParams.toString();
        }

        function nextMonth() {
            const urlParams = new URLSearchParams(window.location.search);
            let month = parseInt(urlParams.get('month')) || <?php echo $currentMonth; ?>;
            let year = parseInt(urlParams.get('year')) || <?php echo $currentYear; ?>;
            month++;
            if (month > 12) {
                month = 1;
                year++;
            }
            urlParams.set('month', month);
            urlParams.set('year', year);
            window.location.href = 'admin_calendar.php?' + urlParams.toString();
        }

        function goToToday() {
            window.location.href = 'admin_calendar.php';
        }

        // Event management
        function createEvent() {
            document.getElementById('eventForm').reset();
            document.getElementById('eventId').value = '';
            document.getElementById('eventModalTitle').textContent = 'Create Event';
            // Set default start date to today
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('eventStartDate').value = now.toISOString().slice(0, 16);
            openModal('eventFormModal');
        }

        async function viewEvent(eventId) {
            try {
                const apiPath = getApiPath(`calendar.php?action=get&id=${eventId}`);
                const response = await fetch(apiPath);
                const result = await response.json();
                if (result.success) {
                    editEvent(eventId, result.data);
                } else {
                    showNotification(result.message || 'Failed to load event', 'error');
                }
            } catch (error) {
                console.error('Error viewing event:', error);
                showNotification('An error occurred', 'error');
            }
        }

        function editEvent(eventId, eventData = null) {
            if (eventData) {
                // Populate form with existing data
                document.getElementById('eventId').value = eventData.id;
                document.getElementById('eventTitle').value = eventData.title || '';
                document.getElementById('eventDescription').value = eventData.description || '';
                document.getElementById('eventType').value = eventData.event_type || '';
                document.getElementById('eventStatus').value = eventData.status || 'active';
                document.getElementById('eventDepartment').value = eventData.department || '';
                document.getElementById('eventLocation').value = eventData.location || '';
                document.getElementById('eventCourseId').value = eventData.course_id || '';
                
                // Format dates for datetime-local input (combine date and time if separate)
                if (eventData.start_date) {
                    let startDateTimeStr = eventData.start_date;
                    // If we have separate time, combine them
                    if (eventData.start_time) {
                        startDateTimeStr = eventData.start_date + 'T' + eventData.start_time;
                    }
                    const startDate = new Date(startDateTimeStr);
                    startDate.setMinutes(startDate.getMinutes() - startDate.getTimezoneOffset());
                    document.getElementById('eventStartDate').value = startDate.toISOString().slice(0, 16);
                }
                if (eventData.end_date) {
                    let endDateTimeStr = eventData.end_date;
                    // If we have separate time, combine them
                    if (eventData.end_time) {
                        endDateTimeStr = eventData.end_date + 'T' + eventData.end_time;
                    }
                    const endDate = new Date(endDateTimeStr);
                    endDate.setMinutes(endDate.getMinutes() - endDate.getTimezoneOffset());
                    document.getElementById('eventEndDate').value = endDate.toISOString().slice(0, 16);
                }
                
                document.getElementById('eventModalTitle').textContent = 'Edit Event';
            } else {
                // Fetch event data
                const apiPath = getApiPath(`calendar.php?action=get&id=${eventId}`);
                fetch(apiPath)
                    .then(r => r.json())
                    .then(result => {
                        if (result.success && result.data) {
                            editEvent(eventId, result.data);
                        } else {
                            showNotification('Failed to load event', 'error');
                        }
                    })
                    .catch(e => {
                        console.error(e);
                        showNotification('Error loading event', 'error');
                    });
                return;
            }
            openModal('eventFormModal');
        }

        // Modal functions
        function openModal(modalId) {
            document.querySelectorAll('.modal.active').forEach(m => {
                if (m.id !== modalId) {
                    m.classList.remove('active');
                    m.setAttribute('hidden', '');
                }
            });

            const modal = document.getElementById(modalId);
            const overlay = document.getElementById('modalOverlay');
            if (!modal) return;

            overlay.classList.add('active');
            overlay.removeAttribute('hidden');

            modal.classList.add('active');
            modal.removeAttribute('hidden');

            const header = modal.querySelector('.modal-header');
            if (header) {
                header.classList.remove('modal-header--primary','modal-header--secondary','modal-header--accent');
                const style = modal.dataset.headerStyle || 'primary';
                header.classList.add('modal-header--' + style);
            }

            const firstInput = modal.querySelector('input, select, textarea, button');
            if (firstInput) firstInput.focus();
        }

        function closeAllModals() {
            document.querySelectorAll('.modal').forEach(m => {
                m.classList.remove('active');
                m.setAttribute('hidden', '');
            });
            const overlay = document.getElementById('modalOverlay');
            overlay.classList.remove('active');
            overlay.setAttribute('hidden', '');
        }

        function closeEventFormModal() {
            const modal = document.getElementById('eventFormModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('hidden', '');
            const overlay = document.getElementById('modalOverlay');
            overlay.classList.remove('active');
            overlay.setAttribute('hidden', '');
        }

        // Form submission handler
        async function handleEventFormSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('eventForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            const eventId = data.id;
            delete data.id;

            // Validate required fields
            if (!data.title || data.title.trim() === '') {
                showNotification('Event Title is required', 'error');
                document.getElementById('eventTitle').focus();
                return;
            }
            if (!data.start_date || data.start_date.trim() === '') {
                showNotification('Start Date & Time is required', 'error');
                document.getElementById('eventStartDate').focus();
                return;
            }
            if (!data.event_type || data.event_type.trim() === '') {
                showNotification('Event Type is required', 'error');
                document.getElementById('eventType').focus();
                return;
            }

            // Convert course_id to integer if present
            if (data.course_id && data.course_id.trim() !== '') {
                data.course_id = parseInt(data.course_id) || null;
            } else {
                delete data.course_id;
            }

            // Parse datetime-local fields and split into date and time
            if (data.start_date) {
                const startDateTime = new Date(data.start_date);
                data.start_date = startDateTime.toISOString().split('T')[0]; // YYYY-MM-DD
                data.start_time = startDateTime.toTimeString().split(' ')[0].substring(0, 5); // HH:MM
            }
            
            if (data.end_date) {
                const endDateTime = new Date(data.end_date);
                data.end_date = endDateTime.toISOString().split('T')[0]; // YYYY-MM-DD
                data.end_time = endDateTime.toTimeString().split(' ')[0].substring(0, 5); // HH:MM
            }

            // Trim string fields
            if (data.title) data.title = data.title.trim();
            if (data.description) data.description = data.description.trim();
            if (data.location) data.location = data.location.trim();
            if (data.department) data.department = data.department.trim();
            if (data.event_type) data.event_type = data.event_type.trim();
            if (data.status) data.status = data.status.trim();

            // Remove empty fields
            Object.keys(data).forEach(k => {
                if (data[k] === '' || data[k] === null || data[k] === undefined) {
                    delete data[k];
                }
            });

            try {
                const action = eventId ? 'update' : 'create';
                if (eventId) data.id = eventId;

                // Get API path using helper function
                const apiPath = getApiPath(`calendar.php?action=${action}`);
                console.log('API Path:', apiPath); // Debug log
                console.log('Sending data:', data); // Debug log

                const response = await fetch(apiPath, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                // Check if response is OK
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    console.error('Request body was:', JSON.stringify(data));
                    try {
                        const errorJson = JSON.parse(errorText);
                        showNotification(errorJson.message || 'Failed to save event', 'error');
                    } catch {
                        showNotification('Server error: ' + response.status + ' - ' + errorText.substring(0, 100), 'error');
                    }
                    return;
                }

                const result = await response.json();
                if (result.success) {
                    showNotification(result.message || 'Event saved successfully', 'success');
                    closeEventFormModal();
                    setTimeout(() => location.reload(), 500);
                } else {
                    showNotification(result.message || 'Failed to save event', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                console.error('Error stack:', error.stack);
                showNotification('An error occurred: ' + error.message, 'error');
            }
        }

        function scheduleExam() {
            document.getElementById('eventForm').reset();
            document.getElementById('eventId').value = '';
            document.getElementById('eventModalTitle').textContent = 'Schedule Exam';
            document.getElementById('eventType').value = 'exam';
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('eventStartDate').value = now.toISOString().slice(0, 16);
            openModal('eventFormModal');
        }

        async function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                try {
                    const apiPath = getApiPath('calendar.php?action=delete');
                    const response = await fetch(apiPath, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: eventId })
                    });
                    const result = await response.json();
                    if (result.success) {
                        showNotification('Event deleted successfully', 'success');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showNotification(result.message || 'Failed to delete event', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                }
            }
        }

        function manageConflicts() {
            showNotification('Opening conflict management...', 'info');
            // TODO: Implement conflict management
        }

        function exportCalendar() {
            showNotification('Exporting calendar data...', 'info');
            // TODO: Implement calendar export
        }

        function viewAllEvents() {
            window.location.href = 'admin_calendar.php';
        }

        function refreshCalendar() {
            window.location.reload();
        }
    </script>
</body>
</html>
