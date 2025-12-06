<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - IT Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .log-entry {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            padding: 0.5rem;
            border-left: 3px solid;
            margin-bottom: 0.5rem;
            background-color: var(--surface-color);
            border-radius: 4px;
        }
        .log-entry.error { border-color: var(--error-color); }
        .log-entry.warning { border-color: var(--warning-color); }
        .log-entry.info { border-color: var(--primary-color); }
        .log-entry.success { border-color: var(--success-color); }
        .log-timestamp {
            color: var(--text-secondary);
            font-weight: 600;
            margin-right: 1rem;
        }
        .log-level {
            font-weight: 700;
            margin-right: 0.5rem;
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }
        .log-level.error { background-color: var(--error-color); color: white; }
        .log-level.warning { background-color: var(--warning-color); color: white; }
        .log-level.info { background-color: var(--primary-color); color: white; }
        .log-level.success { background-color: var(--success-color); color: white; }
    </style>
</head>
<body>
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
            <a href="it_backups.php" class="nav-item">
                <i class="fas fa-database"></i> Backups & Restores
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
                    <button class="btn btn-primary" onclick="refreshLogs()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                    <button class="btn btn-outline" onclick="exportLogs()">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <button class="btn btn-outline" onclick="clearLogs()">
                        <i class="fas fa-trash"></i> Clear
                    </button>
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
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;" id="errorCount">0</div>
                        <div style="color: var(--text-secondary);">Errors</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;" id="warningCount">0</div>
                        <div style="color: var(--text-secondary);">Warnings</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;" id="infoCount">0</div>
                        <div style="color: var(--text-secondary);">Info</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;" id="totalLogs">0</div>
                        <div style="color: var(--text-secondary);">Total Logs</div>
                    </div>
                </div>
            </section>

            <!-- Filters -->
            <section class="log-filters" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label class="form-label">Search</label>
                            <input type="text" id="searchInput" class="form-input" placeholder="Search logs..." onkeyup="filterLogs()">
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Level</label>
                            <select id="levelFilter" class="form-input" onchange="filterLogs()">
                                <option value="">All Levels</option>
                                <option value="error">Error</option>
                                <option value="warning">Warning</option>
                                <option value="info">Info</option>
                                <option value="success">Success</option>
                            </select>
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Date Range</label>
                            <select id="dateRangeFilter" class="form-input" onchange="filterLogs()">
                                <option value="today">Today</option>
                                <option value="week">Last Week</option>
                                <option value="month" selected>Last Month</option>
                                <option value="all">All Time</option>
                            </select>
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Source</label>
                            <select id="sourceFilter" class="form-input" onchange="filterLogs()">
                                <option value="">All Sources</option>
                                <option value="database">Database</option>
                                <option value="api">API</option>
                                <option value="auth">Authentication</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                    </div>
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
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" id="autoRefresh" onchange="toggleAutoRefresh()">
                            <span>Auto-refresh (30s)</span>
                        </label>
                    </div>
                    <div id="logsContainer" style="max-height: 600px; overflow-y: auto; padding: 1rem;">
                        <!-- Logs will be loaded here -->
                    </div>
                    <div id="noLogs" style="padding: 3rem; text-align: center; display: none;">
                        <i class="fas fa-file-alt" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                        <p style="color: var(--text-secondary);">No logs found for the selected filters.</p>
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
    <script>
        let autoRefreshInterval = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadLogs();
            loadLogStats();
        });

        function loadLogs() {
            const filters = {
                level: document.getElementById('levelFilter')?.value || '',
                dateRange: document.getElementById('dateRangeFilter')?.value || 'month',
                source: document.getElementById('sourceFilter')?.value || '',
                search: document.getElementById('searchInput')?.value || ''
            };

            fetch(`../../public/api/it.php?action=list-logs&${new URLSearchParams(filters)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        displayLogs(data.data);
                    } else {
                        document.getElementById('noLogs').style.display = 'block';
                        document.getElementById('logsContainer').innerHTML = '';
                    }
                })
                .catch(error => {
                    console.error('Error loading logs:', error);
                    document.getElementById('noLogs').style.display = 'block';
                    document.getElementById('logsContainer').innerHTML = '';
                });
        }

        function loadLogStats() {
            fetch('../../public/api/it.php?action=log-stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.stats) {
                        document.getElementById('errorCount').textContent = data.stats.errors || 0;
                        document.getElementById('warningCount').textContent = data.stats.warnings || 0;
                        document.getElementById('infoCount').textContent = data.stats.info || 0;
                        document.getElementById('totalLogs').textContent = data.stats.total || 0;
                    }
                })
                .catch(error => console.error('Error loading log stats:', error));
        }

        function displayLogs(logs) {
            const container = document.getElementById('logsContainer');
            if (!logs || logs.length === 0) {
                document.getElementById('noLogs').style.display = 'block';
                container.innerHTML = '';
                return;
            }

            document.getElementById('noLogs').style.display = 'none';
            container.innerHTML = logs.map(log => `
                <div class="log-entry ${log.level || 'info'}">
                    <span class="log-timestamp">${formatTimestamp(log.timestamp)}</span>
                    <span class="log-level ${log.level || 'info'}">${(log.level || 'info').toUpperCase()}</span>
                    <span style="color: var(--text-primary);">[${log.source || 'system'}]</span>
                    <span style="color: var(--text-secondary);">${log.message || ''}</span>
                    ${log.details ? `<div style="margin-top: 0.5rem; padding-left: 2rem; color: var(--text-secondary); font-size: 0.8rem;">${log.details}</div>` : ''}
                </div>
            `).join('');

            // Auto-scroll to bottom
            container.scrollTop = container.scrollHeight;
        }

        function formatTimestamp(timestamp) {
            if (!timestamp) return 'N/A';
            const date = new Date(timestamp);
            return date.toLocaleString();
        }

        function filterLogs() {
            loadLogs();
        }

        function refreshLogs() {
            loadLogs();
            loadLogStats();
            showNotification('Logs refreshed!', 'success');
        }

        function exportLogs() {
            const filters = {
                level: document.getElementById('levelFilter')?.value || '',
                dateRange: document.getElementById('dateRangeFilter')?.value || 'month',
                source: document.getElementById('sourceFilter')?.value || ''
            };
            window.location.href = `../../public/api/it.php?action=export-logs&${new URLSearchParams(filters)}`;
        }

        function clearLogs() {
            if (confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
                fetch('../../public/api/it.php?action=clear-logs', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Logs cleared successfully!', 'success');
                        loadLogs();
                        loadLogStats();
                    } else {
                        showNotification(data.message || 'Failed to clear logs', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
            }
        }

        function toggleAutoRefresh() {
            const enabled = document.getElementById('autoRefresh').checked;
            if (enabled) {
                autoRefreshInterval = setInterval(() => {
                    loadLogs();
                    loadLogStats();
                }, 30000); // 30 seconds
            } else {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                }
            }
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        });
    </script>
</body>
</html>
