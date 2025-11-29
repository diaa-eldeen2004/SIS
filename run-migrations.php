<?php
/**
 * Database Migration Runner
 * 
 * This script runs pending migrations to set up the database schema.
 * 
 * Usage: 
 * 1. Visit: http://localhost/sis/run-migrations.php
 * 2. Or run from command line: php run-migrations.php
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/config/database.php';

// Connect to database
try {
    // Get Database singleton instance and PDO connection
    $dbInstance = Database::getInstance();
    $db = $dbInstance->getConnection();
    echo "<h2>Database Migrations</h2>";
    echo "<p>Running pending migrations...</p>\n";
    
    // Get list of migrations
    $migrationsDir = __DIR__ . '/database/migrations';
    $migrations = glob($migrationsDir . '/*.sql');
    sort($migrations);
    
    if (empty($migrations)) {
        echo "<p style='color: orange;'>No migrations found in $migrationsDir</p>";
        exit;
    }
    
    echo "<p>Found " . count($migrations) . " migration file(s):</p>\n";
    echo "<ul>\n";
    
    // Run each migration
    foreach ($migrations as $migrationFile) {
        $filename = basename($migrationFile);
        echo "<li>$filename... ";
        
        try {
            // Read SQL file
            $sql = file_get_contents($migrationFile);
            
            // Split by semicolon to handle multiple statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    // Execute statement
                    $db->prepare($statement)->execute();
                }
            }
            
            echo "<span style='color: green;'>✓ Success</span></li>\n";
        } catch (Exception $e) {
            echo "<span style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></li>\n";
        }
    }
    
    echo "</ul>\n";
    echo "<p><strong>Migrations completed!</strong></p>\n";
    echo "<p><a href='app/views/home.php'>Return to Home</a></p>\n";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Database Connection Error</h2>\n";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Make sure your database is configured and running.</p>\n";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Migrations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        h2 {
            color: #333;
        }
        p {
            font-size: 14px;
            line-height: 1.6;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 8px;
            margin: 4px 0;
            background-color: #fff;
            border-left: 4px solid #ddd;
            border-radius: 4px;
        }
        a {
            color: #2563eb;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Content is generated above -->
</body>
</html>
