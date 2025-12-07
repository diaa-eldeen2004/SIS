<?php
/**
 * Direct installation of system_logs table
 * Simply visit: http://localhost/sis/install-logs-table.php
 */

// Start output buffering to prevent header errors
ob_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type header early
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Install System Logs Table</title>
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
        <h1>🔧 Install System Logs Table</h1>
        
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/app/core/Database.php';
    require_once __DIR__ . '/app/config/database.php';
    
    $db = Database::getInstance()->getConnection();
    
    // Check if table already exists
    $check = $db->query("SHOW TABLES LIKE 'system_logs'");
    if ($check->rowCount() > 0) {
        echo "<div class='success'>";
        echo "<h2>✓ System logs table already exists!</h2>";
        echo "<p>The table is ready to use.</p>";
        echo "<p><a href='app/views/it/it_logs.php'>→ Go to System Logs Page</a></p>";
        echo "</div>";
        
        // Show table info
        $columns = $db->query("SHOW COLUMNS FROM system_logs")->fetchAll(PDO::FETCH_ASSOC);
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
    
    echo "<div class='info'>Creating system_logs table...</div>";
    
    // SQL to create the table
    $sql = "CREATE TABLE IF NOT EXISTS `system_logs` (
        `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `level` ENUM('error', 'warning', 'info', 'success', 'critical') NOT NULL DEFAULT 'info',
        `source` VARCHAR(100) NOT NULL DEFAULT 'system',
        `message` TEXT NOT NULL,
        `details` TEXT NULL,
        `user_id` INT(11) UNSIGNED NULL,
        `user_role` VARCHAR(50) NULL,
        `ip_address` VARCHAR(45) NULL,
        `user_agent` VARCHAR(255) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_level` (`level`),
        INDEX `idx_source` (`source`),
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_user_role` (`user_role`),
        INDEX `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    // Execute the SQL
    $db->exec($sql);
    
    echo "<div class='success'>";
    echo "<h2>✓ Success! System logs table created successfully!</h2>";
    echo "<p>The table has been created and is ready to use.</p>";
    echo "</div>";
    
    // Verify
    $verify = $db->query("SHOW TABLES LIKE 'system_logs'");
    if ($verify->rowCount() > 0) {
        echo "<div class='success'>";
        echo "<h3>✓ Verification Passed</h3>";
        echo "<p>The system_logs table exists in your database.</p>";
        echo "</div>";
        
        // Show table structure
        $columns = $db->query("SHOW COLUMNS FROM system_logs")->fetchAll(PDO::FETCH_ASSOC);
        echo "<div class='info'>";
        echo "<h3>Table Created With Columns:</h3><ul>";
        foreach ($columns as $col) {
            echo "<li><strong>{$col['Field']}</strong> - {$col['Type']}</li>";
        }
        echo "</ul></div>";
        
        // Insert a test log entry
        try {
            $testLog = $db->prepare("INSERT INTO system_logs (level, source, message, details) VALUES ('success', 'system', 'System logs table initialized', 'Table created successfully via install script')");
            $testLog->execute();
            echo "<div class='info'>";
            echo "<p>✓ Test log entry created - you should see it in the logs page!</p>";
            echo "</div>";
        } catch (Exception $e) {
            // Ignore test log errors
        }
        
    } else {
        throw new Exception("Table was created but verification failed. Please check database permissions.");
    }
    
    echo "<div style='margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 4px;'>";
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li><a href='app/views/it/it_logs.php'>View System Logs Page</a> - See all system logs</li>";
    echo "<li><a href='app/views/it/it_dashboard.php'>IT Dashboard</a> - Go to IT dashboard</li>";
    echo "<li><a href='run-migrations.php'>Run All Migrations</a> - Run any other pending migrations</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>✗ Database Error</h2>";
    echo "<p><strong>Error Code:</strong> " . htmlspecialchars($e->getCode()) . "</p>";
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
    } else {
        echo "<p>This usually means:</p>";
        echo "<ul>";
        echo "<li>Database connection failed - check <code>app/config/database.php</code></li>";
        echo "<li>Database server is not running</li>";
        echo "<li>Insufficient database permissions</li>";
        echo "</ul>";
    }
    
    echo "<div class='info'>";
    echo "<h3>Quick Checks:</h3>";
    echo "<ol>";
    echo "<li><strong>XAMPP Control Panel:</strong> Is MySQL service running (green)?</li>";
    echo "<li><strong>phpMyAdmin:</strong> Can you access <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a>?</li>";
    echo "<li><strong>Database Config:</strong> Check <code>app/config/database.php</code> - host, dbname, username, password</li>";
    echo "</ol>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>Manual Installation:</h3>";
    echo "<p>If automatic installation fails, you can manually run this SQL in phpMyAdmin:</p>";
    echo "<div class='code'>";
    echo htmlspecialchars("CREATE TABLE IF NOT EXISTS `system_logs` (
    `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `level` ENUM('error', 'warning', 'info', 'success', 'critical') NOT NULL DEFAULT 'info',
    `source` VARCHAR(100) NOT NULL DEFAULT 'system',
    `message` TEXT NOT NULL,
    `details` TEXT NULL,
    `user_id` INT(11) UNSIGNED NULL,
    `user_role` VARCHAR(50) NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_level` (`level`),
    INDEX `idx_source` (`source`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_user_role` (`user_role`),
    INDEX `idx_created_at` (`created_at`)
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
    
    // Show stack trace for debugging (only if display_errors is on)
    if (ini_get('display_errors')) {
        echo "<div class='code' style='max-height: 200px; overflow-y: auto;'>";
        echo "<strong>Stack Trace:</strong><br>";
        echo nl2br(htmlspecialchars($e->getTraceAsString()));
        echo "</div>";
    }
    echo "</div>";
}

// End output buffering and flush
ob_end_flush();
?>

    </div>
</body>
</html>

