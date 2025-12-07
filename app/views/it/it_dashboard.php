<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Dashboard - University Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
// Load dashboard data server-side
require_once __DIR__ . '/../../core/Database.php';
$db = Database::getInstance()->getConnection();

// Initialize stats
$stats = [
    'total_sections' => 0,
    'pending_enrollments' => 0,
    'critical_logs' => 0
];

try {
    // Total active sections
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM sections WHERE status = 'active'");
    $stats['total_sections'] = (int)$stmt->fetchColumn();
    
    // Pending enrollments (check if enrollment_requests table exists, otherwise check student_courses)
    try {
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM enrollment_requests WHERE status = 'pending'");
        $stats['pending_enrollments'] = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM student_courses WHERE status = 'pending'");
            $stats['pending_enrollments'] = (int)$stmt->fetchColumn();
        } catch (Exception $e2) {
            $stats['pending_enrollments'] = 0;
        }
    }
    
    // Critical logs
    try {
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM system_logs WHERE level IN ('error', 'critical') AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['critical_logs'] = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        $stats['critical_logs'] = 0;
    }
    
} catch (Exception $e) {
    // Handle errors gracefully
    error_log("IT Dashboard error: " . $e->getMessage());
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
            <a href="it_dashboard.php" class="nav-item active">
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
            <a href="it_logs.php" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">IT Dashboard</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">System administration and schedule management.</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.9rem; color: var(--text-secondary);">System Status</div>
                    <div style="font-size: 1rem; font-weight: 700; color: var(--success-color);">Online</div>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- System Overview -->
            <section class="system-overview" style="margin-bottom: 2rem;">
                <div class="grid grid-3">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['total_sections']); ?></div>
                        <div style="color: var(--text-secondary);">Active Sections</div>
                        <div style="font-size: 0.8rem; color: var(--success-color); margin-top: 0.25rem;">All active</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['pending_enrollments']); ?></div>
                        <div style="color: var(--text-secondary);">Pending Enrollments</div>
                        <div style="font-size: 0.8rem; color: var(--warning-color); margin-top: 0.25rem;"><?php echo $stats['pending_enrollments'] > 0 ? 'Requires attention' : 'All processed'; ?></div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($stats['critical_logs']); ?></div>
                        <div style="color: var(--text-secondary);">Critical Logs</div>
                        <div style="font-size: 0.8rem; color: <?php echo $stats['critical_logs'] > 0 ? 'var(--error-color)' : 'var(--success-color)'; ?>; margin-top: 0.25rem;"><?php echo $stats['critical_logs'] > 0 ? 'Issues detected' : 'No issues'; ?></div>
                    </div>
                </div>
            </section>

            <!-- Main Content Grid -->
            <div class="grid grid-2" style="gap: 2rem;">
                <!-- Pending Actions -->
                <section class="pending-actions">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-tasks" style="color: var(--warning-color); margin-right: 0.5rem;"></i>
                                Pending Actions
                            </h2>
                            <a href="it_enrollments.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                View All
                            </a>
                        </div>
                        <div class="actions-list" id="pendingActionsList">
                            <div class="action-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div style="width: 40px; height: 40px; background-color: var(--warning-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Enrollment Requests</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Waiting for approval</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars($stats['pending_enrollments']); ?> requests</div>
                                    <a href="it_enrollments.php" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-top: 0.25rem; text-decoration: none; display: inline-block;">
                                        Review
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- System Status -->
                <section class="system-status">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-server" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                System Status
                            </h2>
                        </div>
                        <div class="status-list">
                            <div class="status-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Database</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Connection status</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">Online</div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Response: &lt;10ms</div>
                                </div>
                            </div>
                            <div class="status-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Log System</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Error tracking</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">Monitoring</div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Last check: Just now</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Quick Management -->
            <section class="quick-management" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-cogs" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Quick Management
                        </h2>
                    </div>
                    <div class="grid grid-4">
                        <button class="btn btn-primary" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="window.location.href='it_schedule.php'">
                            <i class="fas fa-calendar-plus" style="font-size: 2rem;"></i>
                            <span>Create Section</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="window.location.href='it_courses.php'">
                            <i class="fas fa-book" style="font-size: 2rem;"></i>
                            <span>Manage Courses</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="window.location.href='it_enrollments.php'">
                            <i class="fas fa-check-circle" style="font-size: 2rem;"></i>
                            <span>Review Enrollments</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="window.location.href='it_logs.php'">
                            <i class="fas fa-search" style="font-size: 2rem;"></i>
                            <span>View Logs</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Recent Activity -->
            <section class="recent-activity" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-history" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Recent System Activity
                        </h2>
                    </div>
                    <div class="activity-list" id="recentActivity">
                        <div class="activity-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Section Created</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS101 - Lecture Section A created</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">1 day ago</div>
                            </div>
                        </div>
                        <div class="activity-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem;">
                            <div style="width: 40px; height: 40px; background-color: var(--accent-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Enrollment Approved</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">5 enrollment requests approved</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">2 days ago</div>
                            </div>
                        </div>
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
    <script src="../js/main.js"></script>
</body>
</html>
