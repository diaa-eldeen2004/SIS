<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - IT Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .log-entry {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            padding: 0.75rem;
            border-left: 3px solid;
            margin-bottom: 0.5rem;
            background-color: var(--surface-color);
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .log-entry:hover {
            background-color: rgba(var(--primary-color-rgb), 0.05);
        }
        .log-entry.error { border-color: var(--error-color); }
        .log-entry.warning { border-color: var(--warning-color); }
        .log-entry.info { border-color: var(--primary-color); }
        .log-entry.success { border-color: var(--success-color); }
        .log-entry.critical { border-color: var(--error-color); background-color: rgba(239, 68, 68, 0.1); }
        .log-timestamp {
            color: var(--text-secondary);
            font-weight: 600;
            margin-right: 1rem;
            font-size: 0.8rem;
        }
        .log-level {
            font-weight: 700;
            margin-right: 0.5rem;
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }
        .log-level.error { background-color: var(--error-color); color: white; }
        .log-level.warning { background-color: var(--warning-color); color: white; }
        .log-level.info { background-color: var(--primary-color); color: white; }
        .log-level.success { background-color: var(--success-color); color: white; }
        .log-level.critical { background-color: var(--error-color); color: white; }
        .log-source {
            color: var(--accent-color);
            font-weight: 500;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
<?php
// Load logs server-side
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Logger.php';
$db = Database::getInstance()->getConnection();

// Initialize message variables
$message = isset($_GET['message']) ? $_GET['message'] : '';
$messageType = isset($_GET['type']) ? $_GET['type'] : 'info';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create-table') {
        try {
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
            
            $db->exec($sql);
            
            // Insert a test log entry
            try {
                $testLog = $db->prepare("INSERT INTO system_logs (level, source, message, details) VALUES ('success', 'system', 'System logs table initialized', 'Table created successfully via IT logs page')");
                $testLog->execute();
            } catch (Exception $e) {
                // Ignore test log errors
            }
            
            header('Location: it_logs.php?message=' . urlencode('System logs table created successfully!') . '&type=success');
            exit;
        } catch (Exception $e) {
            header('Location: it_logs.php?message=' . urlencode('Error creating table: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'clear-logs') {
        try {
            Logger::clearLogs();
            header('Location: it_logs.php?message=' . urlencode('Logs cleared successfully') . '&type=success');
            exit;
        } catch (Exception $e) {
            header('Location: it_logs.php?message=' . urlencode('Error clearing logs: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    } elseif ($action === 'export-logs') {
        // Export logs as CSV
        try {
            $filters = [
                'level' => $_POST['level'] ?? '',
                'source' => $_POST['source'] ?? '',
                'dateRange' => $_POST['dateRange'] ?? 'month',
                'search' => $_POST['search'] ?? ''
            ];
            $logs = Logger::getLogs($filters);
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="system_logs_' . date('Y-m-d_His') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Level', 'Source', 'Message', 'Details', 'User ID', 'User Role', 'IP Address', 'Created At']);
            
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['id'],
                    $log['level'],
                    $log['source'],
                    $log['message'],
                    $log['details'],
                    $log['user_id'],
                    $log['user_role'],
                    $log['ip_address'],
                    $log['created_at']
                ]);
            }
            
            fclose($output);
            exit;
        } catch (Exception $e) {
            header('Location: it_logs.php?message=' . urlencode('Error exporting logs: ' . $e->getMessage()) . '&type=error');
            exit;
        }
    }
}

// Get filters
$levelFilter = isset($_GET['level']) ? trim($_GET['level']) : '';
$sourceFilter = isset($_GET['source']) ? trim($_GET['source']) : '';
$dateRangeFilter = isset($_GET['dateRange']) ? trim($_GET['dateRange']) : 'month';
$searchFilter = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build filters array
$filters = [];
if ($levelFilter !== '') {
    $filters['level'] = $levelFilter;
}
if ($sourceFilter !== '') {
    $filters['source'] = $sourceFilter;
}
if ($dateRangeFilter !== '') {
    $filters['dateRange'] = $dateRangeFilter;
}
if ($searchFilter !== '') {
    $filters['search'] = $searchFilter;
}

// Check if system_logs table exists
$logsTableExists = false;
try {
    $tableCheck = $db->query("SHOW TABLES LIKE 'system_logs'");
    $logsTableExists = $tableCheck->rowCount() > 0;
} catch (Exception $e) {
    $logsTableExists = false;
}

// Get logs and stats (only if table exists)
$logs = [];
$stats = [
    'total' => 0,
    'errors' => 0,
    'warnings' => 0,
    'info' => 0,
    'success' => 0,
    'critical' => 0
];
$sources = [];

if ($logsTableExists) {
    try {
        $logs = Logger::getLogs($filters);
        $stats = Logger::getStats($dateRangeFilter);
        
        // Get unique sources for filter
        try {
            $sourceStmt = $db->query("SELECT DISTINCT source FROM system_logs WHERE source IS NOT NULL AND source != '' ORDER BY source");
            $sources = $sourceStmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            // Ignore
        }
    } catch (Exception $e) {
        $message = 'Error loading logs: ' . $e->getMessage();
        $messageType = 'error';
    }
} else {
    $message = 'System logs table does not exist. Please run the migration: Visit run-migrations.php or execute database/migrations/009_create_system_logs_table.sql';
    $messageType = 'warning';
}
?>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" title="Toggle Sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" title="Switch to dark mode">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-laptop-code"></i> IT Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="it_dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="it_schedule.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Semester Schedule
            </a>
            <a href="it_courses.php" class="nav-item">
                <i class="fas fa-book"></i> Course Management
            </a>
            <a href="it_enrollments.php" class="nav-item">
                <i class="fas fa-user-check"></i> Enrollment Requests
            </a>
            <a href="it_logs.php" class="nav-item active">
                <i class="fas fa-file-alt"></i> System Logs
            </a>
            <a href="../settings.php" class="nav-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="../auth/logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Content Header -->
        <header class="content-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="margin: 0; color: var(--text-primary);">System Logs</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">View and monitor system activity logs.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <a href="it_logs.php" class="btn btn-primary">
                        <i class="fas fa-sync"></i> Refresh
                    </a>
                    <form method="POST" action="it_logs.php" style="display: inline;" onsubmit="return confirm('Export logs as CSV?');">
                        <input type="hidden" name="action" value="export-logs">
                        <input type="hidden" name="level" value="<?php echo htmlspecialchars($levelFilter); ?>">
                        <input type="hidden" name="source" value="<?php echo htmlspecialchars($sourceFilter); ?>">
                        <input type="hidden" name="dateRange" value="<?php echo htmlspecialchars($dateRangeFilter); ?>">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchFilter); ?>">
                        <button type="submit" class="btn btn-outline">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </form>
                    <form method="POST" action="it_logs.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to clear all logs? This action cannot be undone.');">
                        <input type="hidden" name="action" value="clear-logs">
                        <button type="submit" class="btn btn-outline">
                            <i class="fas fa-trash"></i> Clear
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Log Stats -->
            <section class="log-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['errors']); ?></div>
                        <div style="color: var(--text-secondary);">Errors</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['warnings']); ?></div>
                        <div style="color: var(--text-secondary);">Warnings</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['info']); ?></div>
                        <div style="color: var(--text-secondary);">Info</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['total']); ?></div>
                        <div style="color: var(--text-secondary);">Total Logs</div>
                    </div>
                </div>
            </section>

            <!-- Filters -->
            <section class="log-filters" style="margin-bottom: 2rem;">
                <div class="card">
                    <form method="GET" action="it_logs.php" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-input" placeholder="Search logs..." value="<?php echo htmlspecialchars($searchFilter); ?>">
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Level</label>
                            <select name="level" class="form-input" onchange="this.form.submit()">
                                <option value="">All Levels</option>
                                <option value="error" <?php echo $levelFilter === 'error' ? 'selected' : ''; ?>>Error</option>
                                <option value="warning" <?php echo $levelFilter === 'warning' ? 'selected' : ''; ?>>Warning</option>
                                <option value="info" <?php echo $levelFilter === 'info' ? 'selected' : ''; ?>>Info</option>
                                <option value="success" <?php echo $levelFilter === 'success' ? 'selected' : ''; ?>>Success</option>
                                <option value="critical" <?php echo $levelFilter === 'critical' ? 'selected' : ''; ?>>Critical</option>
                            </select>
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Date Range</label>
                            <select name="dateRange" class="form-input" onchange="this.form.submit()">
                                <option value="today" <?php echo $dateRangeFilter === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="week" <?php echo $dateRangeFilter === 'week' ? 'selected' : ''; ?>>Last Week</option>
                                <option value="month" <?php echo $dateRangeFilter === 'month' ? 'selected' : ''; ?>>Last Month</option>
                                <option value="all" <?php echo $dateRangeFilter === 'all' ? 'selected' : ''; ?>>All Time</option>
                            </select>
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Source</label>
                            <select name="source" class="form-input" onchange="this.form.submit()">
                                <option value="">All Sources</option>
                                <?php foreach ($sources as $source): ?>
                                    <option value="<?php echo htmlspecialchars($source); ?>" <?php echo $sourceFilter === $source ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($source); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="it_logs.php" class="btn btn-outline">Clear</a>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Logs Display -->
            <section class="logs-display">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-list" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            System Logs
                        </h2>
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">
                            Showing <?php echo count($logs); ?> log(s)
                        </div>
                    </div>
                    <div id="logsContainer" style="max-height: 600px; overflow-y: auto; padding: 1rem;">
                        <?php if (!$logsTableExists): ?>
                            <div id="noLogs" style="padding: 3rem; text-align: center;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--warning-color); margin-bottom: 1rem;"></i>
                                <p style="color: var(--text-secondary); font-weight: 500; margin-bottom: 0.5rem; font-size: 1.1rem;">System logs table does not exist</p>
                                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">Click the button below to create the table and enable logging:</p>
                                
                                <form method="POST" action="it_logs.php" style="display: inline-block;">
                                    <input type="hidden" name="action" value="create-table">
                                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">
                                        <i class="fas fa-database"></i> Create System Logs Table
                                    </button>
                                </form>
                                
                                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.5rem;">Other options:</p>
                                    <p style="color: var(--text-secondary); font-size: 0.85rem;">
                                        <a href="../../run-migrations.php" style="color: var(--primary-color); text-decoration: underline; margin-right: 1rem;">Run All Migrations</a>
                                        <a href="../../install-logs-table.php" style="color: var(--primary-color); text-decoration: underline;">Install Script</a>
                                    </p>
                                </div>
                            </div>
                        <?php elseif (empty($logs)): ?>
                            <div id="noLogs" style="padding: 3rem; text-align: center;">
                                <i class="fas fa-file-alt" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                                <p style="color: var(--text-secondary);">No logs found for the selected filters.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <div class="log-entry <?php echo htmlspecialchars($log['level']); ?>">
                                    <span class="log-timestamp"><?php echo date('M j, Y g:i:s A', strtotime($log['created_at'])); ?></span>
                                    <span class="log-level <?php echo htmlspecialchars($log['level']); ?>"><?php echo strtoupper($log['level']); ?></span>
                                    <span class="log-source">[<?php echo htmlspecialchars($log['source']); ?>]</span>
                                    <?php if (!empty($log['user_role'])): ?>
                                        <span style="color: var(--accent-color); font-size: 0.8rem;">[<?php echo htmlspecialchars($log['user_role']); ?>]</span>
                                    <?php endif; ?>
                                    <span style="color: var(--text-primary);"><?php echo htmlspecialchars($log['message']); ?></span>
                                    <?php if (!empty($log['details'])): ?>
                                        <div style="margin-top: 0.5rem; padding-left: 2rem; color: var(--text-secondary); font-size: 0.8rem; white-space: pre-wrap;"><?php echo htmlspecialchars($log['details']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($log['ip_address'])): ?>
                                        <div style="margin-top: 0.25rem; padding-left: 2rem; color: var(--text-secondary); font-size: 0.75rem;">
                                            <i class="fas fa-network-wired"></i> <?php echo htmlspecialchars($log['ip_address']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>University Portal</h3>
                <p>Comprehensive campus management system designed to streamline academic operations and enhance the learning experience.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="../home.php">Home</a>
                <a href="../about.php">About Us</a>
                <a href="../contact.php">Contact</a>
                <a href="../help_center.php">Help Center</a>
            </div>
            <div class="footer-section">
                <h3>User Access</h3>
                <a href="../auth/auth_login.php">Student Login</a>
                <a href="../auth/auth_login.php">Doctor Login</a>
                <a href="../auth/auth_login.php">Admin Login</a>
                <a href="../auth/auth_sign.php">Register</a>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p><i class="fas fa-envelope"></i> info@university.edu</p>
                <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                <p><i class="fas fa-map-marker-alt"></i> 123 University Ave, Campus City</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 University Portal. All rights reserved. | Privacy Policy | Terms of Service</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="../js/main.js"></script>
    <script>
        // Show toast notification on page load if there's a message
        <?php if (!empty($message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const messageType = '<?php echo htmlspecialchars($messageType, ENT_QUOTES); ?>';
            const message = <?php echo json_encode($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            
            let backgroundColor = '#2563eb'; // default blue
            if (messageType === 'success') {
                backgroundColor = '#10b981'; // green
            } else if (messageType === 'error') {
                backgroundColor = '#ef4444'; // red
            } else if (messageType === 'warning') {
                backgroundColor = '#f59e0b'; // orange
            }
            
            Toastify({
                text: message,
                duration: 5000,
                gravity: "top",
                position: "right",
                style: {
                    background: backgroundColor,
                },
                close: true,
            }).showToast();
            
            // Clean URL by removing message parameters
            if (window.location.search.includes('message=')) {
                const url = new URL(window.location);
                url.searchParams.delete('message');
                url.searchParams.delete('type');
                window.history.replaceState({}, '', url);
            }
        });
        <?php endif; ?>
        
        // Auto-scroll to bottom on page load
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('logsContainer');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    </script>
</body>
</html>
