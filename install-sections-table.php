<?php
/**
 * Direct installation of sections table
 * Simply visit: http://localhost/sis/install-sections-table.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Install Sections Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .error {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .info {
            color: #004085;
            background: #cce5ff;
            border: 1px solid #b3d7ff;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .code {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        a {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        button {
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px 0 0;
        }
        button:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Install Sections Table</h1>
        
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/app/core/Database.php';
    require_once __DIR__ . '/app/config/database.php';
    
    $db = Database::getInstance()->getConnection();
    
    // Check if table already exists
    $check = $db->query("SHOW TABLES LIKE 'sections'");
    if ($check->rowCount() > 0) {
        echo "<div class='success'>";
        echo "<h2>✓ Sections table already exists!</h2>";
        echo "<p>The table is ready to use.</p>";
        echo "<p><a href='app/views/it/it_schedule.php'>→ Go to Semester Schedule Page</a></p>";
        echo "</div>";
        
        // Show table info
        $columns = $db->query("SHOW COLUMNS FROM sections")->fetchAll(PDO::FETCH_ASSOC);
        echo "<div class='info'>";
        echo "<h3>Table Structure:</h3><ul>";
        foreach ($columns as $col) {
            echo "<li><strong>{$col['Field']}</strong> - {$col['Type']}" . 
                 ($col['Null'] === 'NO' ? ' (NOT NULL)' : '') . 
                 ($col['Key'] ? " [{$col['Key']}]" : '') . "</li>";
        }
        echo "</ul></div>";
        echo "</div></body></html>";
        exit;
    }
    
    echo "<div class='info'>Creating sections table...</div>";
    
    // SQL to create the table (without foreign keys first, in case tables don't exist)
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
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "<div class='success'>";
    echo "<h2>✓ Success! Sections table created successfully!</h2>";
    echo "<p>The table has been created and is ready to use.</p>";
    echo "</div>";
    
    // Try to add foreign keys if parent tables exist
    try {
        // Check if courses table exists
        $coursesCheck = $db->query("SHOW TABLES LIKE 'courses'");
        if ($coursesCheck->rowCount() > 0) {
            try {
                $db->exec("ALTER TABLE `sections` ADD CONSTRAINT `fk_sections_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE");
                echo "<div class='info'>✓ Added foreign key to courses table</div>";
            } catch (Exception $e) {
                // Foreign key might already exist or constraint issue
                echo "<div class='info'>⚠ Could not add foreign key to courses (may already exist)</div>";
            }
        }
        
        // Check if doctors table exists
        $doctorsCheck = $db->query("SHOW TABLES LIKE 'doctors'");
        if ($doctorsCheck->rowCount() > 0) {
            try {
                $db->exec("ALTER TABLE `sections` ADD CONSTRAINT `fk_sections_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE SET NULL");
                echo "<div class='info'>✓ Added foreign key to doctors table</div>";
            } catch (Exception $e) {
                // Foreign key might already exist or constraint issue
                echo "<div class='info'>⚠ Could not add foreign key to doctors (may already exist)</div>";
            }
        }
    } catch (Exception $e) {
        // Foreign keys are optional, continue
        echo "<div class='info'>⚠ Note: Foreign keys could not be added (this is okay if parent tables don't exist yet)</div>";
    }
    
    // Verify
    $verify = $db->query("SHOW TABLES LIKE 'sections'");
    if ($verify->rowCount() > 0) {
        echo "<div class='success'>";
        echo "<h3>✓ Verification Passed</h3>";
        echo "<p>The sections table exists in your database.</p>";
        echo "</div>";
        
        // Show table structure
        $columns = $db->query("SHOW COLUMNS FROM sections")->fetchAll(PDO::FETCH_ASSOC);
        echo "<div class='info'>";
        echo "<h3>Table Created With Columns:</h3><ul>";
        foreach ($columns as $col) {
            echo "<li><strong>{$col['Field']}</strong> - {$col['Type']}</li>";
        }
        echo "</ul></div>";
    } else {
        throw new Exception("Table was created but verification failed. Please check database permissions.");
    }
    
    echo "<div style='margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 4px;'>";
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li><a href='app/views/it/it_schedule.php'>View Semester Schedule Page</a> - Create and manage sections</li>";
    echo "<li><a href='app/views/it/it_dashboard.php'>IT Dashboard</a> - Go to IT dashboard</li>";
    echo "<li><a href='run-migrations.php'>Run All Migrations</a> - Run any other pending migrations</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>✗ Database Error</h2>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    echo "<p><strong>Error Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Provide specific guidance based on error code
    $errorCode = $e->getCode();
    if ($errorCode == 1045) {
        echo "<p><strong>Issue:</strong> Access denied - Wrong username or password</p>";
        echo "<p>Check <code>app/config/database.php</code> and verify:</p>";
        echo "<ul>";
        echo "<li>Username: 'root' (or your MySQL username)</li>";
        echo "<li>Password: '' (empty for XAMPP default) or your MySQL password</li>";
        echo "</ul>";
    } elseif ($errorCode == 1049) {
        echo "<p><strong>Issue:</strong> Database 'university_portal' does not exist</p>";
        echo "<p>Create the database first:</p>";
        echo "<div class='code'>CREATE DATABASE IF NOT EXISTS university_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</div>";
    } elseif ($errorCode == 2002 || $errorCode == 2003) {
        echo "<p><strong>Issue:</strong> Cannot connect to MySQL server</p>";
        echo "<ul>";
        echo "<li>Make sure XAMPP MySQL is running</li>";
        echo "<li>Check that MySQL service is started in XAMPP Control Panel</li>";
        echo "<li>Verify host is 'localhost' in <code>app/config/database.php</code></li>";
        echo "</ul>";
    }
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>Manual Installation:</h3>";
    echo "<p>If automatic installation fails, you can manually run this SQL in phpMyAdmin:</p>";
    echo "<div class='code'>";
    echo htmlspecialchars("CREATE TABLE IF NOT EXISTS `sections` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>✗ Error</h2>";
    echo "<p><strong>Error Type:</strong> " . get_class($e) . "</p>";
    echo "<p><strong>Error Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>

    </div>
</body>
</html>

