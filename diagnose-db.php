<?php
/**
 * Database Connection Diagnostic Tool
 * Visit: http://localhost/sis/diagnose-db.php
 */

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 20px; }
        ul { margin: 10px 0; }
        li { margin: 5px 0; }
    </style>
</head>
<body>
    <h1>🔍 Database Connection Diagnostic</h1>
    
    <?php
    $issues = [];
    $warnings = [];
    $successes = [];
    
    // Check 1: Database config file exists
    echo "<div class='box'>";
    echo "<h2>1. Configuration File</h2>";
    $configFile = __DIR__ . '/app/config/database.php';
    if (file_exists($configFile)) {
        echo "<div class='success'>✓ Configuration file exists: <code>$configFile</code></div>";
        $successes[] = "Config file exists";
        
        // Try to read config
        try {
            $config = require $configFile;
            echo "<div class='info'>";
            echo "<strong>Configuration Values:</strong><ul>";
            echo "<li>Host: " . htmlspecialchars($config['host'] ?? 'NOT SET') . "</li>";
            echo "<li>Database: " . htmlspecialchars($config['dbname'] ?? 'NOT SET') . "</li>";
            echo "<li>Username: " . htmlspecialchars($config['username'] ?? 'NOT SET') . "</li>";
            echo "<li>Password: " . (empty($config['password']) ? '(empty - OK for XAMPP)' : '***SET***') . "</li>";
            echo "</ul></div>";
        } catch (Exception $e) {
            echo "<div class='error'>✗ Error reading config: " . htmlspecialchars($e->getMessage()) . "</div>";
            $issues[] = "Cannot read config file";
        }
    } else {
        echo "<div class='error'>✗ Configuration file NOT found: <code>$configFile</code></div>";
        $issues[] = "Config file missing";
    }
    echo "</div>";
    
    // Check 2: Database.php class exists
    echo "<div class='box'>";
    echo "<h2>2. Database Class</h2>";
    $dbClassFile = __DIR__ . '/app/core/Database.php';
    if (file_exists($dbClassFile)) {
        echo "<div class='success'>✓ Database class file exists</div>";
        $successes[] = "Database class exists";
    } else {
        echo "<div class='error'>✗ Database class file NOT found: <code>$dbClassFile</code></div>";
        $issues[] = "Database class missing";
    }
    echo "</div>";
    
    // Check 3: Try to load classes
    echo "<div class='box'>";
    echo "<h2>3. Loading Classes</h2>";
    try {
        require_once __DIR__ . '/app/core/Database.php';
        echo "<div class='success'>✓ Database class loaded successfully</div>";
        $successes[] = "Classes loaded";
    } catch (Exception $e) {
        echo "<div class='error'>✗ Error loading Database class: " . htmlspecialchars($e->getMessage()) . "</div>";
        $issues[] = "Cannot load Database class";
        echo "</div></body></html>";
        exit;
    }
    echo "</div>";
    
    // Check 4: Try database connection
    echo "<div class='box'>";
    echo "<h2>4. Database Connection</h2>";
    try {
        require_once __DIR__ . '/app/config/database.php';
        $db = Database::getInstance()->getConnection();
        echo "<div class='success'>✓ Database connection successful!</div>";
        $successes[] = "Database connected";
        
        // Test query
        $result = $db->query("SELECT VERSION() as version, DATABASE() as dbname");
        $info = $result->fetch();
        echo "<div class='info'>";
        echo "<strong>Connection Info:</strong><ul>";
        echo "<li>MySQL Version: " . htmlspecialchars($info['version'] ?? 'Unknown') . "</li>";
        echo "<li>Current Database: " . htmlspecialchars($info['dbname'] ?? 'Unknown') . "</li>";
        echo "</ul></div>";
        
    } catch (PDOException $e) {
        echo "<div class='error'>";
        echo "<h3>✗ Database Connection Failed</h3>";
        echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
        echo "<p><strong>Error Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
        $issues[] = "Database connection failed";
        
        // Provide specific solutions
        echo "<div class='warning'>";
        echo "<h4>Common Solutions:</h4>";
        if ($e->getCode() == 1045) {
            echo "<p><strong>Access Denied (1045):</strong></p>";
            echo "<ul>";
            echo "<li>Check username/password in <code>app/config/database.php</code></li>";
            echo "<li>For XAMPP default: username='root', password='' (empty)</li>";
            echo "</ul>";
        } elseif ($e->getCode() == 1049) {
            echo "<p><strong>Database Not Found (1049):</strong></p>";
            echo "<ul>";
            echo "<li>Create database: <code>CREATE DATABASE university_portal;</code></li>";
            echo "<li>Or change dbname in <code>app/config/database.php</code></li>";
            echo "</ul>";
        } elseif ($e->getCode() == 2002 || $e->getCode() == 2003) {
            echo "<p><strong>Cannot Connect (2002/2003):</strong></p>";
            echo "<ul>";
            echo "<li>Start MySQL service in XAMPP Control Panel</li>";
            echo "<li>Check if MySQL is running on port 3306</li>";
            echo "</ul>";
        }
        echo "</div>";
    } catch (Exception $e) {
        echo "<div class='error'>✗ Unexpected error: " . htmlspecialchars($e->getMessage()) . "</div>";
        $issues[] = "Unexpected error";
    }
    echo "</div>";
    
    // Check 5: Check if system_logs table exists
    if (!empty($db)) {
        echo "<div class='box'>";
        echo "<h2>5. System Logs Table</h2>";
        try {
            $check = $db->query("SHOW TABLES LIKE 'system_logs'");
            if ($check->rowCount() > 0) {
                echo "<div class='success'>✓ system_logs table exists</div>";
                $successes[] = "system_logs table exists";
                
                // Show structure
                $columns = $db->query("SHOW COLUMNS FROM system_logs")->fetchAll(PDO::FETCH_ASSOC);
                echo "<div class='info'><strong>Table Columns:</strong> " . count($columns) . " columns</div>";
            } else {
                echo "<div class='warning'>⚠ system_logs table does NOT exist</div>";
                echo "<p><a href='install-logs-table.php' style='background:#2563eb;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;'>Create Table Now</a></p>";
                $warnings[] = "system_logs table missing";
            }
        } catch (Exception $e) {
            echo "<div class='error'>✗ Error checking table: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        echo "</div>";
    }
    
    // Summary
    echo "<div class='box'>";
    echo "<h2>📊 Summary</h2>";
    if (empty($issues)) {
        echo "<div class='success'><h3>✓ All Checks Passed!</h3>";
        echo "<p>Your database setup looks good. " . count($successes) . " check(s) passed.</p></div>";
        if (!empty($warnings)) {
            echo "<div class='warning'><p>⚠ " . count($warnings) . " warning(s): " . implode(", ", $warnings) . "</p></div>";
        }
    } else {
        echo "<div class='error'><h3>✗ Issues Found</h3>";
        echo "<p>" . count($issues) . " issue(s) need to be fixed:</p><ul>";
        foreach ($issues as $issue) {
            echo "<li>" . htmlspecialchars($issue) . "</li>";
        }
        echo "</ul></div>";
    }
    echo "</div>";
    
    // Quick actions
    echo "<div class='box'>";
    echo "<h2>🔧 Quick Actions</h2>";
    echo "<p>";
    echo "<a href='install-logs-table.php' style='background:#2563eb;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;margin-right:10px;'>Install Logs Table</a>";
    echo "<a href='run-migrations.php' style='background:#10b981;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;margin-right:10px;'>Run All Migrations</a>";
    echo "<a href='app/views/it/it_logs.php' style='background:#6c757d;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;'>View Logs Page</a>";
    echo "</p>";
    echo "</div>";
    ?>
</body>
</html>

