<?php
/**
 * Diagnostic script to check enrollment requests and why they might not be displaying
 */
require_once __DIR__ . '/app/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h1>Enrollment Requests Diagnostic</h1>";
    
    // 1. Check total enrollments
    $totalStmt = $db->query("SELECT COUNT(*) as total FROM student_courses");
    $total = $totalStmt->fetch(PDO::FETCH_ASSOC);
    echo "<h2>Total Enrollments in Database: " . ($total['total'] ?? 0) . "</h2>";
    
    // 2. Show all enrollments with their IDs
    echo "<h3>All Enrollment Records:</h3>";
    $allStmt = $db->query("SELECT id, student_id, course_id, status, enrolled_at FROM student_courses ORDER BY id DESC");
    $allRecords = $allStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($allRecords)) {
        echo "<p>No enrollment records found in student_courses table.</p>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Student ID</th><th>Course ID</th><th>Status</th><th>Enrolled At</th><th>Student Exists?</th><th>Course Exists?</th></tr>";
        
        foreach ($allRecords as $record) {
            $studentId = $record['student_id'] ?? null;
            $courseId = $record['course_id'] ?? null;
            
            // Check if student exists
            $studentExists = 'N/A';
            if ($studentId) {
                $studentCheck = $db->prepare("SELECT id, first_name, last_name FROM students WHERE id = ?");
                $studentCheck->execute([$studentId]);
                $student = $studentCheck->fetch(PDO::FETCH_ASSOC);
                $studentExists = $student ? "YES - " . ($student['first_name'] ?? '') . " " . ($student['last_name'] ?? '') : "NO";
            }
            
            // Check if course exists
            $courseExists = 'N/A';
            if ($courseId) {
                $courseCheck = $db->prepare("SELECT id, course_code, course_name FROM courses WHERE id = ?");
                $courseCheck->execute([$courseId]);
                $course = $courseCheck->fetch(PDO::FETCH_ASSOC);
                $courseExists = $course ? "YES - " . ($course['course_code'] ?? '') : "NO";
            }
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($record['id'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($studentId ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($courseId ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($record['status'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($record['enrolled_at'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($studentExists) . "</td>";
            echo "<td>" . htmlspecialchars($courseExists) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Test the actual query used in it_enrollments.php
    echo "<h3>Test Query (same as IT page):</h3>";
    try {
        $testSql = "
            SELECT sc.id, sc.student_id, sc.course_id, sc.status,
                   CONCAT(st.first_name, ' ', st.last_name) as student_name,
                   st.student_number,
                   c.course_code,
                   c.course_name,
                   sc.enrolled_at as requested_at
            FROM student_courses sc
            LEFT JOIN students st ON sc.student_id = st.id
            LEFT JOIN courses c ON sc.course_id = c.id
            ORDER BY CASE WHEN sc.status = 'pending' OR sc.status IS NULL THEN 0 ELSE 1 END, sc.enrolled_at DESC
            LIMIT 100
        ";
        
        $testStmt = $db->query($testSql);
        $testResults = $testStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Query returned: " . count($testResults) . " results</p>";
        
        if (empty($testResults)) {
            echo "<p style='color: red;'><strong>WARNING: Query returned 0 results even though there are " . ($total['total'] ?? 0) . " enrollments!</strong></p>";
        } else {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Student Name</th><th>Course Code</th><th>Status</th><th>Requested At</th></tr>";
            foreach ($testResults as $result) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($result['id'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($result['student_name'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($result['course_code'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($result['status'] ?? 'NULL') . "</td>";
                echo "<td>" . htmlspecialchars($result['requested_at'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error running test query: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // 4. Check status column
    echo "<h3>Status Column Info:</h3>";
    try {
        $statusCheck = $db->query("SHOW COLUMNS FROM student_courses LIKE 'status'");
        $statusInfo = $statusCheck->fetch(PDO::FETCH_ASSOC);
        if ($statusInfo) {
            echo "<p>Status column exists. Type: " . htmlspecialchars($statusInfo['Type'] ?? 'N/A') . "</p>";
        } else {
            echo "<p style='color: orange;'>Status column does not exist!</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error checking status column: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

