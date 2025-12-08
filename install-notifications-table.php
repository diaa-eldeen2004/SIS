<?php
/**
 * Install notifications table
 * This script creates the notifications table if it doesn't exist
 */
require_once __DIR__ . '/app/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h1>Install Notifications Table</h1>";
    
    // Check if table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'notifications'");
    $tableExists = $tableCheck->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color: green;'>✓ Notifications table already exists.</p>";
    } else {
        echo "<p style='color: orange;'>Creating notifications table...</p>";
        
        // Read and execute migration
        $migrationFile = __DIR__ . '/database/migrations/012_create_notifications_table.sql';
        if (file_exists($migrationFile)) {
            $sql = file_get_contents($migrationFile);
            
            // Execute each statement separately
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $db->exec($statement);
                    } catch (PDOException $e) {
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            throw $e;
                        }
                    }
                }
            }
            
            echo "<p style='color: green;'>✓ Notifications table created successfully!</p>";
        } else {
            echo "<p style='color: red;'>✗ Migration file not found: {$migrationFile}</p>";
            exit;
        }
    }
    
    // Show table structure
    echo "<h2>Table Structure:</h2>";
    $columns = $db->query("SHOW COLUMNS FROM notifications")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count records
    $count = $db->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
    echo "<p>Total notifications in database: <strong>{$count}</strong></p>";
    
    echo "<hr>";
    echo "<p><a href='app/views/student/student_notifications.php'>Go to Student Notifications Page</a></p>";
    echo "<p><a href='run-migrations.php'>Run All Migrations</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

