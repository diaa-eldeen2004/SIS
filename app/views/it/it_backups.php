<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backups & Restores - IT Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            <a href="it_backups.php" class="nav-item active">
                <i class="fas fa-database"></i> Backups & Restores
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
                    <h1 style="margin: 0; color: var(--text-primary);">Backups & Restores</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Manage system backups and restore operations.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-primary" onclick="createBackup()">
                        <i class="fas fa-plus"></i> Create Backup
                    </button>
                    <button class="btn btn-outline" onclick="configureBackups()">
                        <i class="fas fa-cog"></i> Configure
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Backup Stats -->
            <section class="backup-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-database"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;" id="totalBackups">0</div>
                        <div style="color: var(--text-secondary);">Total Backups</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;" id="lastBackupDate">N/A</div>
                        <div style="color: var(--text-secondary);">Last Backup</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-hdd"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;" id="backupSize">0 GB</div>
                        <div style="color: var(--text-secondary);">Total Size</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;" id="nextBackup">N/A</div>
                        <div style="color: var(--text-secondary);">Next Backup</div>
                    </div>
                </div>
            </section>

            <!-- Backup Configuration -->
            <section class="backup-config" style="margin-bottom: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-cog" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Backup Configuration
                        </h2>
                    </div>
                    <div class="grid grid-2" style="gap: 2rem; padding: 1.5rem;">
                        <div>
                            <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Automated Backups</h3>
                            <div class="form-group">
                                <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="checkbox" id="autoBackupEnabled" checked onchange="updateAutoBackup()">
                                    <span>Enable automated backups</span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Frequency</label>
                                <select id="backupFrequency" class="form-input" onchange="updateAutoBackup()">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Time</label>
                                <input type="time" id="backupTime" class="form-input" value="02:00" onchange="updateAutoBackup()">
                            </div>
                        </div>
                        <div>
                            <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Retention Policy</h3>
                            <div class="form-group">
                                <label class="form-label">Keep Backups For</label>
                                <select id="retentionPeriod" class="form-input" onchange="updateRetention()">
                                    <option value="7">7 days</option>
                                    <option value="14">14 days</option>
                                    <option value="30" selected>30 days</option>
                                    <option value="90">90 days</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Max Backups</label>
                                <input type="number" id="maxBackups" class="form-input" value="10" min="1" onchange="updateRetention()">
                            </div>
                            <div style="padding: 1rem; background-color: rgba(16, 185, 129, 0.1); border-radius: 8px; margin-top: 1rem;">
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
                                    <i class="fas fa-info-circle" style="color: var(--success-color);"></i>
                                    Old backups beyond the retention period will be automatically deleted.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Backup List -->
            <section class="backup-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-list" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Backup History
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="refreshBackups()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div id="backupTable">
                        <table class="table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--border-color);">
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Backup Name</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Type</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Size</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Created</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Status</th>
                                    <th style="padding: 1rem; text-align: center; color: var(--text-primary);">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="backupTableBody">
                                <!-- Backups will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div id="noBackups" style="padding: 3rem; text-align: center; display: none;">
                        <i class="fas fa-database" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                        <p style="color: var(--text-secondary);">No backups found. Create your first backup to get started.</p>
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
        document.addEventListener('DOMContentLoaded', function() {
            loadBackups();
            loadBackupConfig();
        });

        function loadBackups() {
            fetch('../../public/api/it.php?action=list-backups')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        displayBackups(data.data);
                        updateStats(data.stats);
                    } else {
                        document.getElementById('noBackups').style.display = 'block';
                        document.getElementById('backupTable').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading backups:', error);
                    document.getElementById('noBackups').style.display = 'block';
                    document.getElementById('backupTable').style.display = 'none';
                });
        }

        function loadBackupConfig() {
            fetch('../../public/api/it.php?action=get-backup-config')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.config) {
                        document.getElementById('autoBackupEnabled').checked = data.config.enabled || false;
                        document.getElementById('backupFrequency').value = data.config.frequency || 'daily';
                        document.getElementById('backupTime').value = data.config.time || '02:00';
                        document.getElementById('retentionPeriod').value = data.config.retention || 30;
                        document.getElementById('maxBackups').value = data.config.max_backups || 10;
                    }
                })
                .catch(error => console.error('Error loading backup config:', error));
        }

        function displayBackups(backups) {
            const tbody = document.getElementById('backupTableBody');
            if (!backups || backups.length === 0) {
                document.getElementById('noBackups').style.display = 'block';
                document.getElementById('backupTable').style.display = 'none';
                return;
            }

            document.getElementById('noBackups').style.display = 'none';
            document.getElementById('backupTable').style.display = 'table';

            tbody.innerHTML = backups.map(backup => {
                const statusColor = {
                    'completed': 'var(--success-color)',
                    'failed': 'var(--error-color)',
                    'in_progress': 'var(--warning-color)'
                }[backup.status] || 'var(--secondary-color)';

                return `
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 1rem;">
                            <div style="font-weight: 500; color: var(--text-primary);">${backup.name || 'Untitled Backup'}</div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);">${backup.description || ''}</div>
                        </td>
                        <td style="padding: 1rem; color: var(--text-primary);">
                            <span class="badge" style="background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                ${backup.type || 'Full'}
                            </span>
                        </td>
                        <td style="padding: 1rem; color: var(--text-primary);">${formatSize(backup.size)}</td>
                        <td style="padding: 1rem; color: var(--text-primary);">${formatDate(backup.created_at)}</td>
                        <td style="padding: 1rem;">
                            <span class="badge" style="background-color: ${statusColor}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                ${(backup.status || 'completed').replace('_', ' ').charAt(0).toUpperCase() + (backup.status || 'completed').replace('_', ' ').slice(1)}
                            </span>
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <button class="btn btn-outline" onclick="downloadBackup(${backup.id})" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                                ${backup.status === 'completed' ? `
                                    <button class="btn btn-outline" onclick="restoreBackup(${backup.id})" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" title="Restore">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                ` : ''}
                                <button class="btn btn-outline" onclick="deleteBackup(${backup.id})" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function updateStats(stats) {
            if (stats) {
                document.getElementById('totalBackups').textContent = stats.total || 0;
                document.getElementById('lastBackupDate').textContent = stats.last_backup || 'N/A';
                document.getElementById('backupSize').textContent = formatSize(stats.total_size || 0);
                document.getElementById('nextBackup').textContent = stats.next_backup || 'N/A';
            }
        }

        function formatSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }

        function createBackup() {
            if (confirm('Create a new backup now? This may take a few minutes.')) {
                showNotification('Creating backup...', 'info');
                fetch('../../public/api/it.php?action=create-backup', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: 'full' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Backup created successfully!', 'success');
                        loadBackups();
                    } else {
                        showNotification(data.message || 'Failed to create backup', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
            }
        }

        function restoreBackup(id) {
            if (confirm('Are you sure you want to restore this backup? This will overwrite current data.')) {
                showNotification('Restoring backup...', 'info');
                fetch('../../public/api/it.php?action=restore-backup', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Backup restored successfully!', 'success');
                    } else {
                        showNotification(data.message || 'Failed to restore backup', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
            }
        }

        function downloadBackup(id) {
            window.location.href = `../../public/api/it.php?action=download-backup&id=${id}`;
        }

        function deleteBackup(id) {
            if (confirm('Are you sure you want to delete this backup?')) {
                fetch('../../public/api/it.php?action=delete-backup', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Backup deleted successfully!', 'success');
                        loadBackups();
                    } else {
                        showNotification(data.message || 'Failed to delete backup', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
            }
        }

        function updateAutoBackup() {
            const config = {
                enabled: document.getElementById('autoBackupEnabled').checked,
                frequency: document.getElementById('backupFrequency').value,
                time: document.getElementById('backupTime').value
            };
            fetch('../../public/api/it.php?action=update-backup-config', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(config)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Backup configuration updated!', 'success');
                }
            })
            .catch(error => console.error('Error updating config:', error));
        }

        function updateRetention() {
            const config = {
                retention: parseInt(document.getElementById('retentionPeriod').value),
                max_backups: parseInt(document.getElementById('maxBackups').value)
            };
            fetch('../../public/api/it.php?action=update-backup-config', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(config)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Retention policy updated!', 'success');
                }
            })
            .catch(error => console.error('Error updating retention:', error));
        }

        function configureBackups() {
            showNotification('Backup configuration panel is already visible', 'info');
        }

        function refreshBackups() {
            loadBackups();
            showNotification('Backups refreshed!', 'success');
        }
    </script>
</body>
</html>
