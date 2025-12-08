<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Schedule - IT Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
// Load sections and handle form submissions server-side
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Logger.php';
$db = Database::getInstance()->getConnection();

// Initialize message variables
$message = isset($_GET['message']) ? $_GET['message'] : '';
$messageType = isset($_GET['type']) ? $_GET['type'] : 'info';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create-table') {
        try {
            // SQL to create the sections table
            $sql = "CREATE TABLE IF NOT EXISTS `sections` (
                `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `course_id` INT(11) UNSIGNED NOT NULL,
                `section_code` VARCHAR(50) NOT NULL,
                `type` ENUM('lecture', 'lab', 'tutorial') NOT NULL DEFAULT 'lecture',
                `doctor_id` INT(11) UNSIGNED NULL,
                `room` VARCHAR(100) NULL,
                `days` VARCHAR(50) NULL,
                `time` VARCHAR(100) NULL,
                `capacity` INT(4) DEFAULT 30,
                `semester` VARCHAR(50) NULL,
                `notes` TEXT NULL,
                `status` ENUM('active', 'pending', 'closed') DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_course_id` (`course_id`),
                INDEX `idx_doctor_id` (`doctor_id`),
                INDEX `idx_semester` (`semester`),
                INDEX `idx_status` (`status`),
                INDEX `idx_type` (`type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $db->exec($sql);
            
            Logger::success("Sections table created", [], 'schedule');
            
            header('Location: it_schedule.php?message=' . urlencode('Sections table created successfully!') . '&type=success');
            exit;
        } catch (Exception $e) {
            Logger::error("Error creating sections table", ['error' => $e->getMessage()], 'schedule');
            header('Location: it_schedule.php?message=' . urlencode('Error creating table: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'create-section') {
        try {
            // Generate unique schedule group ID for this week
            $scheduleGroupId = 'SCHEDULE_' . date('YmdHis') . '_' . uniqid();
            
            // Get days marked as "off"
            $daysOff = $_POST['days_off'] ?? [];
            if (!is_array($daysOff)) {
                $daysOff = [];
            }
            
            // Validate required fields - new structure: courses organized by day
            $coursesByDay = $_POST['courses'] ?? [];
            
            if (empty($coursesByDay) || !is_array($coursesByDay)) {
                throw new Exception('Please add at least one course for at least one day.');
            }
            
            // Collect all valid courses with their day
            $allValidCourses = [];
            $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            foreach ($weekDays as $day) {
                // Skip days marked as "off"
                if (in_array($day, $daysOff)) {
                    continue;
                }
                
                if (!isset($coursesByDay[$day]) || !is_array($coursesByDay[$day])) {
                    continue;
                }
                
                $dayCourses = [];
                foreach ($coursesByDay[$day] as $index => $course) {
                    if (!empty($course['course_id']) && !empty($course['time']) && !empty($course['room'])) {
                        $dayCourses[] = [
                            'course_id' => $course['course_id'],
                            'time' => $course['time'],
                            'room' => $course['room'],
                            'days' => $day
                        ];
                    }
                }
                
                // Check maximum 3 courses per day
                if (count($dayCourses) > 3) {
                    throw new Exception("Maximum 3 courses allowed per day. {$day} has " . count($dayCourses) . " courses.");
                }
                
                // Check lecture count per day (minimum 1, maximum 3 lectures per day)
                $lectureCountStmt = $db->prepare("
                    SELECT COUNT(*) as count
                    FROM sections
                    WHERE days = ?
                    AND status = 'active'
                ");
                $lectureCountStmt->execute([$day]);
                $lectureCountResult = $lectureCountStmt->fetch(PDO::FETCH_ASSOC);
                $currentLectureCount = (int)$lectureCountResult['count'];
                
                // Check maximum: if adding these courses would exceed 3, reject
                $coursesToAdd = count($dayCourses);
                if (($currentLectureCount + $coursesToAdd) > 3) {
                    throw new Exception("Maximum limit reached! Adding {$coursesToAdd} course(s) would exceed the maximum of 3 lectures per day on {$day}. Currently there are {$currentLectureCount} lecture(s) scheduled.");
                }
                
                $allValidCourses = array_merge($allValidCourses, $dayCourses);
            }
            
            if (empty($allValidCourses)) {
                throw new Exception('Please add at least one course with all required fields (Course, Time, Room) for at least one day.');
            }
            
            // Check for duplicate room+time within the same weekly schedule being created
            // No two courses in the same schedule can have the same room at the same time
            $roomTimeCombinations = [];
            $duplicateConflicts = [];
            
            foreach ($allValidCourses as $index => $courseData) {
                $room = $courseData['room'];
                $time = $courseData['time'];
                $days = $courseData['days'];
                $courseId = $courseData['course_id'];
                
                // Get course name for better error message
                $courseStmt = $db->prepare("SELECT course_code, course_name FROM courses WHERE id = ?");
                $courseStmt->execute([$courseId]);
                $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
                $courseName = $course ? ($course['course_code'] . ' - ' . $course['course_name']) : 'Course #' . $courseId;
                
                $key = $room . '|' . $time . '|' . $days;
                
                if (isset($roomTimeCombinations[$key])) {
                    // Found duplicate room+time+day combination
                    $existingCourse = $roomTimeCombinations[$key];
                    // Get existing course name
                    $existingCourseStmt = $db->prepare("SELECT course_code, course_name FROM courses WHERE id = ?");
                    $existingCourseStmt->execute([$existingCourse['course_id']]);
                    $existingCourseInfo = $existingCourseStmt->fetch(PDO::FETCH_ASSOC);
                    $existingCourseName = $existingCourseInfo ? ($existingCourseInfo['course_code'] . ' - ' . $existingCourseInfo['course_name']) : 'Course #' . $existingCourse['course_id'];
                    
                    $duplicateConflicts[] = [
                        'room' => $room,
                        'time' => $time,
                        'day' => $days,
                        'conflicting_course' => $courseName,
                        'existing_course' => $existingCourseName
                    ];
                } else {
                    $roomTimeCombinations[$key] = $courseData;
                }
            }
            
            if (!empty($duplicateConflicts)) {
                // Format error message nicely (plain text for Toastify)
                $errorLines = [];
                $errorLines[] = "❌ SCHEDULE CONFLICT DETECTED!";
                $errorLines[] = "";
                $errorLines[] = "The following conflicts were found in your schedule:";
                $errorLines[] = "";
                
                foreach ($duplicateConflicts as $conflict) {
                    $errorLines[] = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
                    $errorLines[] = "📍 Room: {$conflict['room']}";
                    $errorLines[] = "⏰ Time: {$conflict['time']}";
                    $errorLines[] = "📅 Day: {$conflict['day']}";
                    $errorLines[] = "";
                    $errorLines[] = "Already booked by: {$conflict['existing_course']}";
                    $errorLines[] = "You tried to add: {$conflict['conflicting_course']}";
                    $errorLines[] = "";
                }
                
                $errorLines[] = "💡 SOLUTION: Choose a different room, time, or day for one of the conflicting courses.";
                
                $formattedError = implode("\n", $errorLines);
                
                throw new Exception($formattedError);
            }
            
            // Check table structure
            $columns = $db->query("SHOW COLUMNS FROM sections")->fetchAll(PDO::FETCH_COLUMN);
            
            // Prepare INSERT statement structure
            $fields = ['course_id', 'section_code', 'type', 'doctor_id', 'room', 'days', 'time', 'capacity', 'semester', 'notes', 'status'];
            $insertFields = [];
            $insertPlaceholders = [];
            
            foreach ($fields as $field) {
                if (in_array($field, $columns)) {
                    $insertFields[] = $field;
                    $insertPlaceholders[] = '?';
                }
            }
            
            // Add timestamps if columns exist
            $hasCreatedAt = in_array('created_at', $columns);
            $hasUpdatedAt = in_array('updated_at', $columns);
            
            if ($hasCreatedAt) {
                $insertFields[] = 'created_at';
            }
            if ($hasUpdatedAt) {
                $insertFields[] = 'updated_at';
            }
            
            // Build placeholders - use ? for all regular fields, NOW() for timestamps
            $finalPlaceholders = $insertPlaceholders;
            if ($hasCreatedAt) {
                $finalPlaceholders[] = 'NOW()';
            }
            if ($hasUpdatedAt) {
                $finalPlaceholders[] = 'NOW()';
            }
            
            // Build SQL - NOW() is literal SQL, not a placeholder
            $sql = "INSERT INTO sections (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $finalPlaceholders) . ")";
            $stmt = $db->prepare($sql);
            
            $createdCourses = [];
            $conflicts = [];
            
            $createdCourses = [];
            $conflicts = [];
            
            // Process each course with its own day, time and room
            foreach ($allValidCourses as $courseData) {
                $courseId = $courseData['course_id'];
                $time = $courseData['time'];
                $room = $courseData['room'];
                $days = $courseData['days'];
                
                // Check for room/time/day conflict - no two courses can have same day+time+room
                $conflictCheck = $db->prepare("
                    SELECT s.id, s.room, s.time, s.days, c.course_code, c.course_name
                    FROM sections s
                    LEFT JOIN courses c ON s.course_id = c.id
                    WHERE s.room = ? 
                    AND s.time = ? 
                    AND s.days = ?
                    AND s.status = 'active'
                ");
                $conflictCheck->execute([$room, $time, $days]);
                $conflict = $conflictCheck->fetch(PDO::FETCH_ASSOC);
                
                if ($conflict) {
                    $conflicts[] = "{$days}: Room {$room} at {$time} - " . ($conflict['course_code'] . ' - ' . $conflict['course_name']);
                    continue; // Skip this course - room/time/day conflict
                }
                
                // Get course info for logging
                $courseStmt = $db->prepare("SELECT course_code, course_name FROM courses WHERE id = ?");
                $courseStmt->execute([$courseId]);
                $course = $courseStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$course) {
                    continue; // Skip invalid course
                }
                
                // Build values array for this course
                // Only include values for ? placeholders (not NOW() which is SQL)
                $values = [];
                foreach ($fields as $field) {
                    if (in_array($field, $columns)) {
                        if ($field === 'status') {
                            $values[] = 'active';
                        } elseif ($field === 'course_id') {
                            $values[] = $courseId;
                        } elseif ($field === 'time') {
                            $values[] = $time;
                        } elseif ($field === 'room') {
                            $values[] = $room;
                        } elseif ($field === 'days') {
                            $values[] = $days;
                        } else {
                            $value = $_POST[$field] ?? null;
                            // Convert empty string to null for nullable fields
                            if (($field === 'doctor_id') && $value === '') {
                                $value = null;
                            }
                            $values[] = $value;
                        }
                    }
                }
                
                // Verify we have the correct number of values (should match number of ? in placeholders)
                $questionMarkCount = substr_count(implode(', ', $insertPlaceholders), '?');
                if (count($values) !== $questionMarkCount) {
                    throw new Exception("Value count mismatch. Expected {$questionMarkCount} values, got " . count($values));
                }
                
                // Execute insert - PDO will only bind to ? placeholders, NOW() stays as SQL
                $stmt->execute($values);
                $createdCourses[] = "{$days}: " . $course['course_code'] . ' - ' . $course['course_name'] . " ({$time}, Room {$room})";
                
                // Log the section creation
                Logger::success("Section created", [
                    'section_code' => $_POST['section_code'] ?? 'A',
                    'course_id' => $courseId,
                    'course_code' => $course['course_code'] ?? 'N/A',
                    'course_name' => $course['course_name'] ?? 'N/A',
                    'room' => $room,
                    'days' => $days,
                    'time' => $time,
                    'semester' => $_POST['semester'] ?? 'Fall 2024'
                ], 'schedule');
            }
            
            // Build result message
            $message = '';
            if (!empty($createdCourses)) {
                $message = 'Successfully created schedule for: ' . implode(', ', $createdCourses);
            }
            if (!empty($conflicts)) {
                if (!empty($message)) {
                    $message .= '. ';
                }
                $message .= 'Skipped (already scheduled): ' . implode(', ', $conflicts);
            }
            
            if (empty($createdCourses) && !empty($conflicts)) {
                throw new Exception('All selected courses are already scheduled at this time, room, and day.');
            }
            
            if (empty($createdCourses)) {
                throw new Exception('No courses were scheduled. Please try again.');
            }
            
            header('Location: it_schedule.php?message=' . urlencode($message) . '&type=success');
            exit;
        } catch (Exception $e) {
            Logger::error("Error creating section", ['error' => $e->getMessage()], 'schedule');
            header('Location: it_schedule.php?message=' . urlencode('Error creating section: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'delete-schedule-group' && isset($_POST['schedule_group_id'])) {
        try {
            $scheduleGroupId = $_POST['schedule_group_id'];
            
            // Check if schedule_group_id column exists
            $columns = $db->query("SHOW COLUMNS FROM sections")->fetchAll(PDO::FETCH_COLUMN);
            $hasScheduleGroupId = in_array('schedule_group_id', $columns);
            
            $sectionCount = 0;
            $groupInfo = null;
            
            // Handle ungrouped schedules (old format)
            if (strpos($scheduleGroupId, 'ungrouped_') === 0) {
                // Extract the section ID
                $sectionId = str_replace('ungrouped_', '', $scheduleGroupId);
                $infoStmt = $db->prepare("
                    SELECT COUNT(*) as count, 
                           MIN(created_at) as created_at
                    FROM sections
                    WHERE id = ?
                ");
                $infoStmt->execute([$sectionId]);
                $groupInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);
                $sectionCount = (int)$groupInfo['count'];
                
                if ($sectionCount == 0) {
                    throw new Exception('Schedule not found');
                }
                
                // Delete the single section
                $stmt = $db->prepare("DELETE FROM sections WHERE id = ?");
                $stmt->execute([$sectionId]);
            } else {
                // Handle grouped schedules
                if (!$hasScheduleGroupId) {
                    throw new Exception('Schedule grouping not available. Please add schedule_group_id column to database.');
                }
                
                // Get info for logging before deletion
                $infoStmt = $db->prepare("
                    SELECT COUNT(*) as count, 
                           MIN(created_at) as created_at
                    FROM sections
                    WHERE schedule_group_id = ?
                ");
                $infoStmt->execute([$scheduleGroupId]);
                $groupInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);
                $sectionCount = (int)$groupInfo['count'];
                
                if ($sectionCount == 0) {
                    throw new Exception('Schedule group not found or already deleted');
                }
                
                // Delete all sections in this group
                $stmt = $db->prepare("DELETE FROM sections WHERE schedule_group_id = ?");
                $stmt->execute([$scheduleGroupId]);
            }
            
            // Log the deletion
            Logger::warning("Weekly schedule group deleted", [
                'schedule_group_id' => $scheduleGroupId,
                'sections_deleted' => $sectionCount,
                'created_at' => $groupInfo['created_at'] ?? 'N/A'
            ], 'schedule');
            
            header('Location: it_schedule.php?message=' . urlencode("Weekly schedule deleted successfully. Removed {$sectionCount} course(s).") . '&type=success');
            exit;
        } catch (Exception $e) {
            Logger::error("Error deleting schedule group", ['error' => $e->getMessage()], 'schedule');
            header('Location: it_schedule.php?message=' . urlencode('Error deleting schedule: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'delete-section' && isset($_POST['section_id'])) {
        try {
            // Get section info for logging before deletion
            $infoStmt = $db->prepare("
                SELECT s.*, c.course_code, c.course_name
                FROM sections s
                LEFT JOIN courses c ON s.course_id = c.id
                WHERE s.id = ?
            ");
            $infoStmt->execute([$_POST['section_id']]);
            $sectionInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$sectionInfo) {
                throw new Exception('Section not found');
            }
            
            // Check minimum lecture requirement (at least 1 lecture per day)
            // Count lectures for this day
            $dayLectureCountStmt = $db->prepare("
                SELECT COUNT(*) as count
                FROM sections
                WHERE days = ?
                AND status = 'active'
            ");
            $dayLectureCountStmt->execute([$sectionInfo['days']]);
            $dayLectureCountResult = $dayLectureCountStmt->fetch(PDO::FETCH_ASSOC);
            $currentDayLectureCount = (int)$dayLectureCountResult['count'];
            
            // Prevent deletion if this is the last lecture on this day
            if ($currentDayLectureCount <= 1) {
                throw new Exception("Cannot delete the last lecture on {$sectionInfo['days']}. The schedule must have a minimum of 1 lecture per day.");
            }
            
            $stmt = $db->prepare("DELETE FROM sections WHERE id = ?");
            $stmt->execute([$_POST['section_id']]);
            
            // Log the deletion
            Logger::warning("Section deleted", [
                'section_id' => $_POST['section_id'],
                'section_code' => $sectionInfo['section_code'] ?? 'N/A',
                'course_code' => $sectionInfo['course_code'] ?? 'N/A',
                'course_name' => $sectionInfo['course_name'] ?? 'N/A'
            ], 'schedule');
            
            header('Location: it_schedule.php?message=' . urlencode('Section deleted successfully') . '&type=success');
            exit;
        } catch (Exception $e) {
            Logger::error("Error deleting section", ['error' => $e->getMessage()], 'schedule');
            header('Location: it_schedule.php?message=' . urlencode('Error deleting section: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    }
}

// Get filters
$searchFilter = isset($_GET['search']) ? trim($_GET['search']) : '';
$semesterFilter = isset($_GET['semester']) ? trim($_GET['semester']) : '';
$typeFilter = isset($_GET['type']) ? trim($_GET['type']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build WHERE clause
$where = "WHERE 1=1";
$params = [];

if ($searchFilter !== '') {
    $where .= " AND (c.course_code LIKE ? OR c.course_name LIKE ? OR s.section_code LIKE ? OR s.room LIKE ?)";
    $like = "%$searchFilter%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($semesterFilter !== '') {
    $where .= " AND s.semester = ?";
    $params[] = $semesterFilter;
}

if ($typeFilter !== '') {
    $where .= " AND s.type = ?";
    $params[] = $typeFilter;
}

if ($statusFilter !== '') {
    $where .= " AND s.status = ?";
    $params[] = $statusFilter;
}

// Check if sections table exists
$sectionsTableExists = false;
try {
    $tableCheck = $db->query("SHOW TABLES LIKE 'sections'");
    $sectionsTableExists = $tableCheck->rowCount() > 0;
} catch (Exception $e) {
    $sectionsTableExists = false;
}

// Check if schedule_group_id column exists
$hasScheduleGroupId = false;
if ($sectionsTableExists) {
    try {
        $columns = $db->query("SHOW COLUMNS FROM sections LIKE 'schedule_group_id'")->fetchAll(PDO::FETCH_COLUMN);
        $hasScheduleGroupId = !empty($columns);
        
        // If column doesn't exist, add it
        if (!$hasScheduleGroupId) {
            try {
                $db->exec("ALTER TABLE `sections` ADD COLUMN `schedule_group_id` VARCHAR(100) NULL AFTER `id`, ADD INDEX `idx_schedule_group_id` (`schedule_group_id`)");
                $hasScheduleGroupId = true;
            } catch (Exception $e) {
                // Column might already exist or error adding it
                $hasScheduleGroupId = false;
            }
        }
    } catch (Exception $e) {
        $hasScheduleGroupId = false;
    }
}

// Load sections grouped by schedule_group_id
$scheduleGroups = [];
if ($sectionsTableExists) {
    try {
        // Build ORDER BY clause based on whether schedule_group_id exists
        $orderBy = "ORDER BY s.created_at DESC";
        if ($hasScheduleGroupId) {
            $orderBy .= ", s.schedule_group_id DESC";
        }
        
        $sql = "
            SELECT s.*, c.course_code, c.course_name, 
                   CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                   CONCAT(s.days, ' ', s.time) as schedule
            FROM sections s
            LEFT JOIN courses c ON s.course_id = c.id
            LEFT JOIN doctors d ON s.doctor_id = d.id
            $where
            $orderBy
            LIMIT 500
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $allSections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group sections by schedule_group_id
        foreach ($allSections as $section) {
            $groupId = $hasScheduleGroupId && isset($section['schedule_group_id']) && !empty($section['schedule_group_id']) 
                ? $section['schedule_group_id'] 
                : 'ungrouped_' . $section['id'];
            if (!isset($scheduleGroups[$groupId])) {
                $scheduleGroups[$groupId] = [
                    'group_id' => $groupId,
                    'created_at' => $section['created_at'],
                    'sections' => []
                ];
            }
            $scheduleGroups[$groupId]['sections'][] = $section;
        }
    } catch (Exception $e) {
        Logger::error("Error loading sections", ['error' => $e->getMessage()], 'schedule');
        $message = 'Error loading sections: ' . $e->getMessage();
        $messageType = 'error';
    }
} else {
    $message = 'Sections table does not exist. Please run the migration to create it.';
    $messageType = 'warning';
}

// Load courses for dropdown
$courses = [];
try {
    $courseStmt = $db->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code");
    $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Ignore
}

// Load doctors for dropdown
$doctors = [];
try {
    $doctorStmt = $db->query("SELECT id, first_name, last_name FROM doctors ORDER BY last_name, first_name");
    $doctors = $doctorStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Ignore
}

// Get unique semesters for filter
$semesters = [];
if ($sectionsTableExists) {
    try {
        $semesterStmt = $db->query("SELECT DISTINCT semester FROM sections WHERE semester IS NOT NULL AND semester != '' ORDER BY semester DESC");
        $semesters = $semesterStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        // Ignore
    }
}
// Always add default semesters if empty
if (empty($semesters)) {
    $semesters = ['Fall 2024', 'Spring 2025', 'Summer 2025'];
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
            <a href="it_schedule.php" class="nav-item active">
                <i class="fas fa-calendar-alt"></i> Semester Schedule
            </a>
            <a href="it_courses.php" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Semester Schedule</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Build and manage official semester schedule sections.</p>
                </div>
                <button class="btn btn-primary" onclick="showCreateSectionModal()">
                    <i class="fas fa-plus"></i> Create Schedule
                </button>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Filters -->
            <section class="schedule-filters" style="margin-bottom: 2rem;">
                <div class="card">
                    <form method="GET" action="it_schedule.php" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-input" placeholder="Search by course code, section..." value="<?php echo htmlspecialchars($searchFilter); ?>">
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-input" onchange="this.form.submit()">
                                <option value="">All Semesters</option>
                                <?php foreach ($semesters as $sem): ?>
                                    <option value="<?php echo htmlspecialchars($sem); ?>" <?php echo $semesterFilter === $sem ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sem); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-input" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="lecture" <?php echo $typeFilter === 'lecture' ? 'selected' : ''; ?>>Lecture</option>
                                <option value="lab" <?php echo $typeFilter === 'lab' ? 'selected' : ''; ?>>Lab</option>
                                <option value="tutorial" <?php echo $typeFilter === 'tutorial' ? 'selected' : ''; ?>>Tutorial</option>
                            </select>
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="closed" <?php echo $statusFilter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="it_schedule.php" class="btn btn-outline">Clear</a>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Sections List -->
            <section class="sections-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-list" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Course Sections
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="it_schedule.php" class="btn btn-outline">
                                <i class="fas fa-sync"></i> Refresh
                            </a>
                        </div>
                    </div>
                    <div id="sectionsTable">
                        <?php if (!$sectionsTableExists): ?>
                            <div id="noSections" style="padding: 3rem; text-align: center;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--warning-color); margin-bottom: 1rem;"></i>
                                <p style="color: var(--text-secondary); font-weight: 500; margin-bottom: 0.5rem; font-size: 1.1rem;">Sections table does not exist</p>
                                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">Click the button below to create the table and enable schedule management:</p>
                                
                                <form method="POST" action="it_schedule.php" style="display: inline-block;">
                                    <input type="hidden" name="action" value="create-table">
                                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">
                                        <i class="fas fa-database"></i> Create Sections Table
                                    </button>
                                </form>
                                
                                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.5rem;">Or run migrations:</p>
                                    <p style="color: var(--text-secondary); font-size: 0.85rem;">
                                        <a href="../../run-migrations.php" style="color: var(--primary-color); text-decoration: underline;">Run All Migrations</a>
                                    </p>
                                </div>
                            </div>
                        <?php elseif (empty($scheduleGroups)): ?>
                            <div id="noSections" style="padding: 3rem; text-align: center;">
                                <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                                <p style="color: var(--text-secondary);">No weekly schedules found. Create your first weekly schedule to get started.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($scheduleGroups as $groupId => $group): ?>
                                <div class="schedule-group" style="margin-bottom: 2rem; border: 2px solid var(--border-color); border-radius: 8px; overflow: hidden;">
                                    <div style="background: var(--primary-color); color: white; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <h3 style="margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
                                                <i class="fas fa-calendar-week"></i> Weekly Schedule
                                                <span style="font-size: 0.85rem; font-weight: normal; opacity: 0.9;">
                                                    (<?php echo count($group['sections']); ?> course<?php echo count($group['sections']) != 1 ? 's' : ''; ?>)
                                                </span>
                                            </h3>
                                            <small style="opacity: 0.9; font-size: 0.85rem;">
                                                Created: <?php echo date('M d, Y H:i', strtotime($group['created_at'])); ?>
                                            </small>
                                        </div>
                                        <form method="POST" action="it_schedule.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this entire weekly schedule? This will delete all <?php echo count($group['sections']); ?> courses.');">
                                            <input type="hidden" name="action" value="delete-schedule-group">
                                            <input type="hidden" name="schedule_group_id" value="<?php echo htmlspecialchars($groupId); ?>">
                                            <button type="submit" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 0.5rem 1rem;">
                                                <i class="fas fa-trash"></i> Delete Week
                                            </button>
                                        </form>
                                    </div>
                                    <div style="padding: 1rem;">
                                        <table class="table" style="width: 100%; border-collapse: collapse;">
                                            <thead>
                                                <tr style="border-bottom: 1px solid var(--border-color); background: var(--background-color);">
                                                    <th style="padding: 0.75rem; text-align: left; color: var(--text-primary); font-size: 0.9rem;">Day</th>
                                                    <th style="padding: 0.75rem; text-align: left; color: var(--text-primary); font-size: 0.9rem;">Course</th>
                                                    <th style="padding: 0.75rem; text-align: left; color: var(--text-primary); font-size: 0.9rem;">Time</th>
                                                    <th style="padding: 0.75rem; text-align: left; color: var(--text-primary); font-size: 0.9rem;">Room</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($group['sections'] as $section): ?>
                                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                                        <td style="padding: 0.75rem; color: var(--text-primary); font-weight: 500;">
                                                            <?php echo htmlspecialchars($section['days'] ?? 'N/A'); ?>
                                                        </td>
                                                        <td style="padding: 0.75rem;">
                                                            <div style="font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($section['course_code'] ?? 'N/A'); ?></div>
                                                            <div style="font-size: 0.85rem; color: var(--text-secondary);"><?php echo htmlspecialchars($section['course_name'] ?? ''); ?></div>
                                                        </td>
                                                        <td style="padding: 0.75rem; color: var(--text-primary);"><?php echo htmlspecialchars($section['time'] ?? 'TBD'); ?></td>
                                                        <td style="padding: 0.75rem; color: var(--text-primary);"><?php echo htmlspecialchars($section['room'] ?? 'TBD'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
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

    <!-- Create Schedule Modal -->
    <div id="createSectionModal" class="modal">
        <div class="modal-content" style="max-width: 600px; margin: auto;">
            <div class="modal-header">
                <h2>Create Schedule Entry</h2>
                <button class="modal-close" onclick="closeCreateSectionModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="createSectionForm" method="POST" action="it_schedule.php">
                    <input type="hidden" name="action" value="create-section">
                    
                    <!-- Week Schedule - All days at once -->
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label class="form-label">Schedule for Entire Week <span style="color: var(--error-color);">*</span></label>
                        <small style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 1rem; display: block;">
                            Mark days as "Day Off" to skip them. Add courses for active days. Maximum 3 courses per day. No two courses can have the same day, time, and room.
                        </small>
                    </div>
                    
                    <!-- Days of the week -->
                    <?php 
                    $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    foreach ($weekDays as $day): 
                    ?>
                    <div class="day-section" id="daySection_<?php echo $day; ?>" style="margin-bottom: 2rem; padding: 1.5rem; background: var(--background-color); border-radius: 8px; border: 2px solid var(--border-color);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin: 0;">
                                    <input type="checkbox" 
                                           name="days_off[]" 
                                           value="<?php echo $day; ?>" 
                                           class="day-off-checkbox"
                                           onchange="toggleDayOff('<?php echo $day; ?>', this.checked)"
                                           style="width: 18px; height: 18px; cursor: pointer;">
                                    <span style="font-weight: 500; color: var(--text-secondary);">Day Off</span>
                                </label>
                                <h3 style="margin: 0; color: var(--text-primary); font-size: 1.1rem;" id="dayTitle_<?php echo $day; ?>">
                                    <i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars($day); ?>
                                </h3>
                            </div>
                            <button type="button" 
                                    class="btn btn-outline add-course-btn" 
                                    id="addCourseBtn_<?php echo $day; ?>"
                                    onclick="addCourseRowForDay('<?php echo $day; ?>')" 
                                    style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                <i class="fas fa-plus"></i> Add Course
                            </button>
                        </div>
                        
                        <div id="courseEntries_<?php echo $day; ?>" class="day-course-entries">
                            <!-- Course rows will be added here -->
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Hidden fields for backward compatibility -->
                    <input type="hidden" name="section_code" value="A">
                    <input type="hidden" name="type" value="lecture">
                    <input type="hidden" name="doctor_id" value="">
                    <input type="hidden" name="capacity" value="30">
                    <input type="hidden" name="semester" value="Fall 2024">
                    <input type="hidden" name="status" value="active">
                    <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                        <button type="button" class="btn btn-outline" onclick="closeCreateSectionModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Schedule</button>
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
            let duration = 5000;
            
            if (messageType === 'success') {
                backgroundColor = '#10b981'; // green
            } else if (messageType === 'error') {
                backgroundColor = '#ef4444'; // red
                // Show error messages longer if they contain detailed conflict information
                if (message.includes('SCHEDULE CONFLICT') || message.includes('\n') || message.length > 100) {
                    duration = 12000; // 12 seconds for detailed conflict errors
                }
            } else if (messageType === 'warning') {
                backgroundColor = '#f59e0b'; // orange
            }
            
            // Format message for better display (replace newlines with HTML line breaks)
            let displayMessage = message.replace(/\n/g, '<br>');
            
            Toastify({
                text: displayMessage,
                duration: duration,
                gravity: "top",
                position: "right",
                style: {
                    background: backgroundColor,
                    color: 'white',
                    padding: '1rem 1.25rem',
                    borderRadius: '8px',
                    maxWidth: '550px',
                    fontSize: '14px',
                    lineHeight: '1.6',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.3)',
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

        let courseRowIndex = 0;
        const roomOptions = <?php
            $letters = range('A', 'Z');
            $rooms = [];
            for ($num = 1; $num <= 30; $num++) {
                foreach ($letters as $letter) {
                    $roomCode = $num . $letter;
                    $rooms[] = '<option value="' . htmlspecialchars($roomCode, ENT_QUOTES) . '">Room ' . htmlspecialchars($roomCode) . '</option>';
                }
            }
            echo json_encode(implode('', $rooms), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        ?>;
        
        function addCourseRowForDay(day) {
            const courseEntries = document.getElementById('courseEntries_' + day);
            const rowCount = courseEntries.children.length;
            
            // Check maximum limit
            if (rowCount >= 3) {
                alert('Maximum 3 courses per day allowed.');
                return;
            }
            
            const row = document.createElement('div');
            row.className = 'course-entry-row';
            row.style.cssText = 'display: grid; grid-template-columns: 2fr 1.5fr 1.5fr auto; gap: 1rem; align-items: end; margin-bottom: 1rem; padding: 1rem; background: var(--surface-color); border-radius: 8px; border: 1px solid var(--border-color);';
            
            // Course dropdown
            const courseGroup = document.createElement('div');
            courseGroup.className = 'form-group';
            courseGroup.style.margin = '0';
            courseGroup.innerHTML = `
                <label class="form-label" style="font-size: 0.85rem;">Course <span style="color: var(--error-color);">*</span></label>
                <select name="courses[${day}][${rowCount}][course_id]" class="form-input" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            `;
            
            // Time dropdown
            const timeGroup = document.createElement('div');
            timeGroup.className = 'form-group';
            timeGroup.style.margin = '0';
            timeGroup.innerHTML = `
                <label class="form-label" style="font-size: 0.85rem;">Time <span style="color: var(--error-color);">*</span></label>
                <select name="courses[${day}][${rowCount}][time]" class="form-input" required>
                    <option value="">Select Time</option>
                    <option value="8:30 AM - 10:30 AM">8:30 AM - 10:30 AM</option>
                    <option value="10:30 AM - 12:30 PM">10:30 AM - 12:30 PM</option>
                    <option value="12:30 PM - 2:30 PM">12:30 PM - 2:30 PM</option>
                    <option value="2:30 PM - 4:30 PM">2:30 PM - 4:30 PM</option>
                    <option value="4:30 PM - 6:30 PM">4:30 PM - 6:30 PM</option>
                </select>
            `;
            
            // Room dropdown
            const roomGroup = document.createElement('div');
            roomGroup.className = 'form-group';
            roomGroup.style.margin = '0';
            roomGroup.innerHTML = `
                <label class="form-label" style="font-size: 0.85rem;">Room <span style="color: var(--error-color);">*</span></label>
                <select name="courses[${day}][${rowCount}][room]" class="form-input" required>
                    <option value="">Select Room</option>
                    ${roomOptions}
                </select>
            `;
            
            // Remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-outline';
            removeBtn.style.cssText = 'padding: 0.5rem; height: fit-content; margin-bottom: 0;';
            removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
            removeBtn.onclick = function() {
                row.remove();
                updateDayRowNumbers(day);
            };
            
            row.appendChild(courseGroup);
            row.appendChild(timeGroup);
            row.appendChild(roomGroup);
            row.appendChild(removeBtn);
            
            courseEntries.appendChild(row);
        }
        
        function updateDayRowNumbers(day) {
            const courseEntries = document.getElementById('courseEntries_' + day);
            const rows = courseEntries.querySelectorAll('.course-entry-row');
            rows.forEach((row, index) => {
                const selects = row.querySelectorAll('select');
                selects.forEach(select => {
                    const name = select.getAttribute('name');
                    if (name) {
                        select.setAttribute('name', name.replace(/courses\[.+\]\[\d+\]/, `courses[${day}][${index}]`));
                    }
                });
            });
        }
        
        function showCreateSectionModal() {
            const modal = document.getElementById('createSectionModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Reset form
            document.getElementById('createSectionForm').reset();
            const weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            weekDays.forEach(day => {
                const entries = document.getElementById('courseEntries_' + day);
                if (entries) entries.innerHTML = '';
            });
        }

        function closeCreateSectionModal() {
            const modal = document.getElementById('createSectionModal');
            modal.classList.remove('active');
            document.getElementById('createSectionForm').reset();
            const weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            weekDays.forEach(day => {
                const entries = document.getElementById('courseEntries_' + day);
                if (entries) entries.innerHTML = '';
            });
            document.body.style.overflow = '';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('createSectionModal');
            if (event.target == modal || event.target.classList.contains('modal')) {
                closeCreateSectionModal();
            }
        }
    </script>
</body>
</html>
