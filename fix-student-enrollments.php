<?php
/**
 * Fix Student Enrollments - Run this script to:
 * 1. Add 'pending' status to student_courses table
 * 2. Test course loading
 * 3. Test enrollment requests
 */

require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Logger.php';

$db = Database::getInstance()->getConnection();

echo "<h1>Fix Student Enrollments</h1>";

// Step 1: Check and fix status column
echo "<h2>Step 1: Fixing status column...</h2>";
try {
    // Check current status column
    $stmt = $db->query("SHOW COLUMNS FROM student_courses WHERE Field = 'status'");
    $statusInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($statusInfo) {
        echo "<p>Current status column: " . htmlspecialchars($statusInfo['Type']) . "</p>";
        
        // Try to modify it to include 'pending'
        try {
            $db->exec("ALTER TABLE `student_courses` MODIFY COLUMN `status` ENUM('pending','taking','taken','approved','rejected') DEFAULT 'pending'");
            echo "<p style='color: green;'>✓ Status column updated successfully!</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠ Could not update status column: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>You may need to run this SQL manually:</p>";
            echo "<pre>ALTER TABLE `student_courses` MODIFY COLUMN `status` ENUM('pending','taking','taken','approved','rejected') DEFAULT 'pending';</pre>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Status column does not exist. Adding it...</p>";
        try {
            $db->exec("ALTER TABLE `student_courses` ADD COLUMN `status` ENUM('pending','taking','taken','approved','rejected') DEFAULT 'pending' AFTER `course_id`");
            echo "<p style='color: green;'>✓ Status column added successfully!</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error adding status column: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error checking status column: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 2: Test course loading
echo "<h2>Step 2: Testing course loading...</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM courses");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total courses in database: <strong>" . $result['count'] . "</strong></p>";
    
    $stmt2 = $db->query("SELECT id, course_code, course_name FROM courses LIMIT 10");
    $courses = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Sample courses:</p><ul>";
    foreach ($courses as $course) {
        echo "<li>" . htmlspecialchars($course['course_code']) . " - " . htmlspecialchars($course['course_name']) . "</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error loading courses: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 3: Test enrollment requests
echo "<h2>Step 3: Testing enrollment requests...</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) as count FROM student_courses");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Total enrollment records: <strong>" . $result['count'] . "</strong></p>";
    
    // Check pending enrollments
    $stmt2 = $db->query("SELECT COUNT(*) as count FROM student_courses WHERE status = 'pending' OR status IS NULL");
    $pendingResult = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "<p>Pending enrollment requests: <strong>" . $pendingResult['count'] . "</strong></p>";
    
    // Show sample enrollments
    $stmt3 = $db->query("
        SELECT sc.*, c.course_code, c.course_name, 
               CONCAT(st.first_name, ' ', st.last_name) as student_name
        FROM student_courses sc
        LEFT JOIN courses c ON sc.course_id = c.id
        LEFT JOIN students st ON sc.student_id = st.id
        ORDER BY sc.id DESC
        LIMIT 10
    ");
    $enrollments = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Recent enrollments:</p><table border='1' cellpadding='5'><tr><th>ID</th><th>Student</th><th>Course</th><th>Status</th></tr>";
    foreach ($enrollments as $enrollment) {
        echo "<tr>";
        echo "<td>" . $enrollment['id'] . "</td>";
        echo "<td>" . htmlspecialchars($enrollment['student_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($enrollment['course_code'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($enrollment['status'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error checking enrollments: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='app/views/student/student_courses.php'>Go to Student Courses Page</a></p>";
echo "<p><a href='app/views/it/it_enrollments.php'>Go to IT Enrollments Page</a></p>";
?>

