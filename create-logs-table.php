<?php
/**
 * Quick script to create system_logs table
 * Visit: http://localhost/sis/create-logs-table.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/config/database.php';

echo "<!DOCTYPE html><html><head><title>Create System Logs Table</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;}";
echo ".success{color:green;padding:10px;background:#d4edda;border:1px solid #c3e6cb;border-radius:4px;margin:10px 0;}";
echo ".error{color:red;padding:10px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:4px;margin:10px 0;}";
echo ".info{color:blue;padding:10px;background:#d1ecf1;border:1px solid #bee5eb;border-radius:4px;margin:10px 0;}";
echo "</style></head><body>";
echo "<h1>Create System Logs Table</h1>";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if table already exists
    $check = $db->query("SHOW TABLES LIKE 'system_logs'");
    if ($check->rowCount() > 0) {
        echo "<div class='info'>✓ System logs table already exists!</div>";
        echo "<p><a href='app/views/it/it_logs.php'>Go to System Logs Page</a></p>";
        echo "</body></html>";
        exit;
    }
    
    // Read and execute migration
    $migrationFile = __DIR__ . '/database/migrations/009_create_system_logs_table.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    echo "<div class='info'>Reading migration file...</div>";
    $sql = file_get_contents($migrationFile);
    
    // Remove SQL comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Split by semicolon
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "<div class='info'>Executing SQL statements...</div>";
    
    $executed = 0;
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^\s*$/', $statement)) {
            try {
                $db->exec($statement);
                $executed++;
                echo "<div class='success'>✓ Executed: " . substr($statement, 0, 80) . "...</div>";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    echo "<div class='info'>⚠ Table already exists (this is okay)</div>";
                } else {
                    throw $e;
                }
            }
        }
    }
    
    if ($executed > 0) {
        echo "<div class='success'><h2>✓ Success! System logs table created successfully.</h2></div>";
        echo "<p>You can now:</p>";
        echo "<ul>";
        echo "<li><a href='app/views/it/it_logs.php'>View System Logs Page</a></li>";
        echo "<li><a href='run-migrations.php'>Run All Migrations</a></li>";
        echo "</ul>";
    } else {
        echo "<div class='info'>Table may already exist or no statements were executed.</div>";
    }
    
    // Verify table was created
    $verify = $db->query("SHOW TABLES LIKE 'system_logs'");
    if ($verify->rowCount() > 0) {
        echo "<div class='success'>✓ Verification: system_logs table exists in database</div>";
        
        // Show table structure
        $columns = $db->query("SHOW COLUMNS FROM system_logs")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Table Structure:</h3><ul>";
        foreach ($columns as $col) {
            echo "<li><strong>{$col['Field']}</strong> - {$col['Type']}</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><h2>✗ Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p></div>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>Database connection settings in app/config/database.php</li>";
    echo "<li>That your database server is running</li>";
    echo "<li>That you have proper database permissions</li>";
    echo "</ul>";
}

echo "</body></html>";
?>

