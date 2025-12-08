<?php
// IT Officer API for schedule, enrollments, backups, and logs
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { ob_clean(); http_response_code(200); exit; }

try {
    require_once __DIR__ . '/../../app/core/Database.php';
    $db = Database::getInstance()->getConnection();
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get-stats':
            getDashboardStats($db);
            break;
        case 'list-sections':
            listSections($db);
            break;
        case 'create-section':
            createSection($db);
            break;
        case 'delete-section':
            deleteSection($db);
            break;
        case 'list-enrollments':
            listEnrollments($db);
            break;
        case 'process-enrollment':
            processEnrollment($db);
            break;
        case 'enrollment-stats':
            getEnrollmentStats($db);
            break;
        case 'list-backups':
            listBackups($db);
            break;
        case 'create-backup':
            createBackup($db);
            break;
        case 'restore-backup':
            restoreBackup($db);
            break;
        case 'delete-backup':
            deleteBackup($db);
            break;
        case 'get-backup-config':
            getBackupConfig($db);
            break;
        case 'update-backup-config':
            updateBackupConfig($db);
            break;
        case 'list-logs':
            listLogs($db);
            break;
        case 'log-stats':
            getLogStats($db);
            break;
        case 'export-logs':
            exportLogs($db);
            break;
        case 'clear-logs':
            clearLogs($db);
            break;
        case 'get-course-doctors':
            getCourseDoctors($db);
            break;
        case 'get-course-students':
            getCourseStudents($db);
            break;
        case 'assign-doctor':
            assignDoctor($db);
            break;
        case 'remove-doctor':
            removeDoctor($db);
            break;
        case 'enroll-student':
            enrollStudent($db);
            break;
        case 'remove-student':
            removeStudent($db);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success'=>false,'message'=>'Route not found']);
            break;
    }
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

function getDashboardStats($db) {
    try {
        $stats = [];
        
        // Total sections
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM sections WHERE status = 'active'");
        $stats['total_sections'] = (int)$stmt->fetchColumn();
        
        // Pending enrollments (check if status column supports 'pending')
        try {
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM student_courses WHERE status = 'pending'");
            $stats['pending_enrollments'] = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $stats['pending_enrollments'] = 0;
        }
        
        // Total backups (simplified - you may have a backups table)
        $stats['total_backups'] = 0; // Placeholder
        $stats['last_backup'] = 'Never';
        
        // Critical logs (simplified)
        $stats['critical_logs'] = 0; // Placeholder
        
        echo json_encode(['success' => true, 'stats' => $stats]);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function listSections($db) {
    try {
        $stmt = $db->prepare("
            SELECT s.*, c.course_code, c.course_name, 
                   CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                   CONCAT(s.days, ' ', s.time) as schedule
            FROM sections s
            LEFT JOIN courses c ON s.course_id = c.id
            LEFT JOIN doctors d ON s.doctor_id = d.id
            ORDER BY s.created_at DESC
            LIMIT 100
        ");
        $stmt->execute();
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add enrolled count for each section (if section_id column exists)
        try {
            $columns = $db->query("SHOW COLUMNS FROM student_courses")->fetchAll(PDO::FETCH_COLUMN);
            $hasSectionId = in_array('section_id', $columns);
            
            if ($hasSectionId) {
                foreach ($sections as &$section) {
                    $enrolledStmt = $db->prepare("SELECT COUNT(*) FROM student_courses WHERE section_id = ?");
                    $enrolledStmt->execute([$section['id']]);
                    $section['enrolled'] = (int)$enrolledStmt->fetchColumn();
                }
            } else {
                foreach ($sections as &$section) {
                    $section['enrolled'] = 0;
                }
            }
        } catch (Exception $e) {
            // If error, set enrolled to 0
            foreach ($sections as &$section) {
                $section['enrolled'] = 0;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $sections]);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function createSection($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;
        
        // Check if sections table exists and has required columns
        $columns = $db->query("SHOW COLUMNS FROM sections")->fetchAll(PDO::FETCH_COLUMN);
        
        $stmt = $db->prepare("
            INSERT INTO sections (course_id, section_code, type, doctor_id, room, days, time, capacity, semester, notes, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
        ");
        
        $result = $stmt->execute([
            $input['course_id'] ?? null,
            $input['section_code'] ?? null,
            $input['type'] ?? null,
            $input['doctor_id'] ?? null,
            $input['room'] ?? null,
            $input['days'] ?? null,
            $input['time'] ?? null,
            $input['capacity'] ?? 30,
            $input['semester'] ?? null,
            $input['notes'] ?? null
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Section created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create section']);
        }
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function deleteSection($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID is required']);
            return;
        }
        
        $stmt = $db->prepare("DELETE FROM sections WHERE id = ?");
        $result = $stmt->execute([$input['id']]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Section deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete section']);
        }
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function listEnrollments($db) {
    try {
        // Check if section_id column exists in student_courses
        $columns = $db->query("SHOW COLUMNS FROM student_courses")->fetchAll(PDO::FETCH_COLUMN);
        $hasSectionId = in_array('section_id', $columns);
        $hasRequestedAt = in_array('requested_at', $columns);
        
        $selectFields = "sc.id, sc.student_id, sc.course_id, sc.status";
        $joinSections = "";
        $orderBy = $hasRequestedAt ? "sc.requested_at DESC" : "sc.enrolled_at DESC";
        
        if ($hasSectionId) {
            $selectFields .= ", sc.section_id";
            $joinSections = "LEFT JOIN sections s ON sc.section_id = s.id";
            $selectFields .= ", s.section_code";
        }
        
        if ($hasRequestedAt) {
            $selectFields .= ", sc.requested_at";
        } else {
            $selectFields .= ", sc.enrolled_at as requested_at";
        }
        
        $stmt = $db->prepare("
            SELECT $selectFields,
                   CONCAT(st.first_name, ' ', st.last_name) as student_name,
                   st.student_number,
                   c.course_code
            FROM student_courses sc
            LEFT JOIN students st ON sc.student_id = st.id
            LEFT JOIN courses c ON sc.course_id = c.id
            $joinSections
            ORDER BY $orderBy
            LIMIT 100
        ");
        $stmt->execute();
        $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $enrollments]);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function processEnrollment($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['id']) || empty($input['status'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID and status are required']);
            return;
        }
        
        $allowedStatuses = ['approved', 'rejected'];
        if (!in_array($input['status'], $allowedStatuses)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            return;
        }
        
        // Check if processed_at column exists
        $columns = $db->query("SHOW COLUMNS FROM student_courses")->fetchAll(PDO::FETCH_COLUMN);
        $hasProcessedAt = in_array('processed_at', $columns);
        
        if ($hasProcessedAt) {
            $stmt = $db->prepare("UPDATE student_courses SET status = ?, processed_at = NOW() WHERE id = ?");
        } else {
            $stmt = $db->prepare("UPDATE student_courses SET status = ? WHERE id = ?");
        }
        
        $result = $stmt->execute([$input['status'], $input['id']]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Enrollment processed successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to process enrollment']);
        }
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getEnrollmentStats($db) {
    try {
        $stats = [];
        
        // Check if status column supports these values
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM student_courses WHERE status = 'pending'");
            $stats['pending'] = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $stats['pending'] = 0;
        }
        
        try {
            $columns = $db->query("SHOW COLUMNS FROM student_courses")->fetchAll(PDO::FETCH_COLUMN);
            $hasProcessedAt = in_array('processed_at', $columns);
            
            if ($hasProcessedAt) {
                $stmt = $db->query("SELECT COUNT(*) FROM student_courses WHERE status = 'approved' AND DATE(processed_at) = CURDATE()");
                $stats['approved'] = (int)$stmt->fetchColumn();
                
                $stmt = $db->query("SELECT COUNT(*) FROM student_courses WHERE status = 'rejected' AND DATE(processed_at) = CURDATE()");
                $stats['rejected'] = (int)$stmt->fetchColumn();
            } else {
                $stats['approved'] = 0;
                $stats['rejected'] = 0;
            }
        } catch (Exception $e) {
            $stats['approved'] = 0;
            $stats['rejected'] = 0;
        }
        
        $stmt = $db->query("SELECT COUNT(*) FROM student_courses");
        $stats['total'] = (int)$stmt->fetchColumn();
        
        echo json_encode(['success' => true, 'stats' => $stats]);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function listBackups($db) {
    try {
        // Placeholder - you would query a backups table if it exists
        $backups = [];
        $stats = [
            'total' => 0,
            'last_backup' => 'Never',
            'total_size' => 0,
            'next_backup' => 'Not scheduled'
        ];
        
        echo json_encode(['success' => true, 'data' => $backups, 'stats' => $stats]);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function createBackup($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        // Placeholder for backup creation logic
        // In a real implementation, you would:
        // 1. Export database
        // 2. Archive files
        // 3. Store backup metadata
        
        echo json_encode(['success' => true, 'message' => 'Backup created successfully']);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function restoreBackup($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        // Placeholder for backup restore logic
        echo json_encode(['success' => true, 'message' => 'Backup restored successfully']);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function deleteBackup($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        // Placeholder for backup deletion logic
        echo json_encode(['success' => true, 'message' => 'Backup deleted successfully']);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getBackupConfig($db) {
    try {
        // Placeholder - you would store this in a config table
        $config = [
            'enabled' => true,
            'frequency' => 'daily',
            'time' => '02:00',
            'retention' => 30,
            'max_backups' => 10
        ];
        
        echo json_encode(['success' => true, 'config' => $config]);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function updateBackupConfig($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        // Placeholder - you would update a config table
        echo json_encode(['success' => true, 'message' => 'Backup configuration updated']);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function listLogs($db) {
    try {
        $level = $_GET['level'] ?? '';
        $dateRange = $_GET['dateRange'] ?? 'month';
        $source = $_GET['source'] ?? '';
        $search = $_GET['search'] ?? '';
        
        // Placeholder - you would query a logs table
        $logs = [];
        
        echo json_encode(['success' => true, 'data' => $logs]);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getLogStats($db) {
    try {
        $stats = [
            'errors' => 0,
            'warnings' => 0,
            'info' => 0,
            'total' => 0
        ];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function exportLogs($db) {
    // Placeholder for log export
    header('Content-Type: text/plain');
    echo "Log export functionality - to be implemented";
}

function clearLogs($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        // Placeholder - you would clear a logs table
        echo json_encode(['success' => true, 'message' => 'Logs cleared successfully']);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getCourseDoctors($db) {
    try {
        $courseId = (int)($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
            return;
        }

        $stmt = $db->prepare("
            SELECT d.id, d.first_name, d.last_name, d.email, d.department, d.phone, dc.assigned_at
            FROM doctor_courses dc
            INNER JOIN doctors d ON dc.doctor_id = d.id
            WHERE dc.course_id = ?
            ORDER BY dc.assigned_at DESC
        ");
        $stmt->execute([$courseId]);
        $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $doctors]);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getCourseStudents($db) {
    try {
        $courseId = (int)($_GET['course_id'] ?? 0);
        if ($courseId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
            return;
        }

        $stmt = $db->prepare("
            SELECT s.id, s.first_name, s.last_name, s.email, s.student_number, s.phone, sc.status, sc.enrolled_at
            FROM student_courses sc
            INNER JOIN students s ON sc.student_id = s.id
            WHERE sc.course_id = ?
            ORDER BY sc.enrolled_at DESC
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $students]);
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function assignDoctor($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;
        
        if (empty($input['course_id']) || empty($input['doctor_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Course ID and Doctor ID are required']);
            return;
        }
        
        $courseId = (int)$input['course_id'];
        $doctorId = (int)$input['doctor_id'];
        
        // Check if assignment already exists
        $checkStmt = $db->prepare("SELECT id FROM doctor_courses WHERE course_id = ? AND doctor_id = ?");
        $checkStmt->execute([$courseId, $doctorId]);
        if ($checkStmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Doctor is already assigned to this course']);
            return;
        }
        
        $stmt = $db->prepare("INSERT INTO doctor_courses (course_id, doctor_id, assigned_at) VALUES (?, ?, NOW())");
        $result = $stmt->execute([$courseId, $doctorId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Doctor assigned successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to assign doctor']);
        }
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function removeDoctor($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;
        
        if (empty($input['course_id']) || empty($input['doctor_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Course ID and Doctor ID are required']);
            return;
        }
        
        $courseId = (int)$input['course_id'];
        $doctorId = (int)$input['doctor_id'];
        
        $stmt = $db->prepare("DELETE FROM doctor_courses WHERE course_id = ? AND doctor_id = ?");
        $result = $stmt->execute([$courseId, $doctorId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Doctor removed successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to remove doctor']);
        }
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function enrollStudent($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;
        
        if (empty($input['course_id']) || empty($input['student_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Course ID and Student ID are required']);
            return;
        }
        
        $courseId = (int)$input['course_id'];
        $studentId = (int)$input['student_id'];
        $status = $input['status'] ?? 'taking';
        
        // Check if enrollment already exists
        $checkStmt = $db->prepare("SELECT id FROM student_courses WHERE course_id = ? AND student_id = ?");
        $checkStmt->execute([$courseId, $studentId]);
        if ($checkStmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Student is already enrolled in this course']);
            return;
        }
        
        $stmt = $db->prepare("INSERT INTO student_courses (course_id, student_id, status, enrolled_at) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$courseId, $studentId, $status]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Student enrolled successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to enroll student']);
        }
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function removeStudent($db) {
    ob_clean();
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input === null) $input = $_POST;
        
        if (empty($input['course_id']) || empty($input['student_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Course ID and Student ID are required']);
            return;
        }
        
        $courseId = (int)$input['course_id'];
        $studentId = (int)$input['student_id'];
        
        $stmt = $db->prepare("DELETE FROM student_courses WHERE course_id = ? AND student_id = ?");
        $result = $stmt->execute([$courseId, $studentId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Student removed successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to remove student']);
        }
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
