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
            
            // Remove SQL comments (-- style comments)
            $sql = preg_replace('/--.*$/m', '', $sql);
            
            // Split by semicolon to handle multiple statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            $executed = 0;
            foreach ($statements as $statement) {
                $statement = trim($statement);
                // Skip empty statements and comments
                if (!empty($statement) && !preg_match('/^\s*$/', $statement)) {
                    try {
                        // Use exec() for DDL statements (CREATE TABLE, ALTER TABLE, etc.)
                        // as they may not work with prepare/execute
                        $db->exec($statement);
                        $executed++;
                    } catch (PDOException $e) {
                        // If table already exists, that's okay (IF NOT EXISTS)
                        if (strpos($e->getMessage(), 'already exists') !== false || 
                            strpos($e->getMessage(), 'Duplicate') !== false) {
                            // Table already exists, skip
                            continue;
                        }
                        throw $e;
                    }
                }
            }
            
            if ($executed > 0) {
                echo "<span style='color: green;'>✓ Success ($executed statement(s) executed)</span></li>\n";
            } else {
                echo "<span style='color: orange;'>⚠ Skipped (already exists or no statements)</span></li>\n";
            }
        } catch (Exception $e) {
            echo "<span style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></li>\n";
        }
    }
    
    echo "</ul>\n";
    echo "<p><strong>Migrations completed!</strong></p>\n";
    echo "<p style='margin-top: 2rem;'>";
    echo "<a href='app/views/home.php' style='margin-right: 1rem; padding: 0.5rem 1rem; background: #2563eb; color: white; text-decoration: none; border-radius: 4px;'>Return to Home</a>";
    echo "<a href='app/views/admin/admin_manage_students.php' style='padding: 0.5rem 1rem; background: #10b981; color: white; text-decoration: none; border-radius: 4px;'>Go to Students Page</a>";
    echo "</p>\n";
    
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
