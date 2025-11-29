<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Portal</title>
    <link rel="stylesheet" href="../../css/styles.css">
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
            <h2><i class="fas fa-graduation-cap"></i> Admin Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="admin_manage_students.php" class="nav-item">
                <i class="fas fa-user-graduate"></i> Manage Students
            </a>
            <a href="admin_manage_doctors.php" class="nav-item">
                <i class="fas fa-chalkboard-teacher"></i> Manage Doctors
            </a>
            <a href="admin_manage_courses.php" class="nav-item">
                <i class="fas fa-book"></i> Manage Courses
            </a>
            <a href="admin_reports.php" class="nav-item active">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="admin_calendar.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Calendar
            </a>
            <a href="admin_profile.php" class="nav-item">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="../app/settings.php" class="nav-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="../app/logout.php" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Reports & Analytics</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Generate comprehensive reports and view system analytics.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshReports()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="generateReport()">
                        <i class="fas fa-plus"></i> Generate Report
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Report Statistics -->
            <section class="report-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">24</div>
                        <div style="color: var(--text-secondary);">Total Reports</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">18</div>
                        <div style="color: var(--text-secondary);">Generated Today</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">3</div>
                        <div style="color: var(--text-secondary);">Scheduled</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-download"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">156</div>
                        <div style="color: var(--text-secondary);">Downloads</div>
                    </div>
                </div>
            </section>

            <!-- Report Filter -->
            <section class="report-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search reports..." id="reportSearch" onkeyup="filterReports()">
                        </div>
                        <div>
                            <select class="form-input" id="typeFilter" onchange="filterReports()">
                                <option value="">All Types</option>
                                <option value="academic">Academic</option>
                                <option value="attendance">Attendance</option>
                                <option value="financial">Financial</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="periodFilter" onchange="filterReports()">
                                <option value="">All Periods</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterReports()">
                                <option value="">All Status</option>
                                <option value="completed">Completed</option>
                                <option value="generating">Generating</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Reports -->
            <section class="quick-reports" style="margin-bottom: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-bolt" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Quick Reports
                        </h2>
                    </div>
                    <div class="grid grid-4">
                        <button class="btn btn-primary" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="generateStudentReport()">
                            <i class="fas fa-user-graduate" style="font-size: 2rem;"></i>
                            <span>Student Report</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="generateAttendanceReport()">
                            <i class="fas fa-calendar-check" style="font-size: 2rem;"></i>
                            <span>Attendance Report</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="generateGradeReport()">
                            <i class="fas fa-chart-line" style="font-size: 2rem;"></i>
                            <span>Grade Report</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="generateSystemReport()">
                            <i class="fas fa-server" style="font-size: 2rem;"></i>
                            <span>System Report</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Recent Reports -->
            <section class="recent-reports">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-history" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Recent Reports
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="viewAllReports()">
                                <i class="fas fa-list"></i> View All
                            </button>
                            <button class="btn btn-outline" onclick="scheduleReport()">
                                <i class="fas fa-clock"></i> Schedule
                            </button>
                        </div>
                    </div>

                    <!-- Reports List -->
                    <div class="reports-list">
                        <!-- Report Item 1 -->
                        <div class="report-item" data-type="academic" data-period="monthly" data-status="completed" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <h4 style="margin: 0; color: var(--text-primary);">Student Enrollment Report</h4>
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Completed</span>
                                </div>
                                <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">Monthly enrollment statistics and trends for all departments</p>
                                <div style="display: flex; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                    <span><i class="fas fa-tag" style="margin-right: 0.25rem;"></i>Academic</span>
                                    <span><i class="fas fa-calendar" style="margin-right: 0.25rem;"></i>Monthly</span>
                                    <span><i class="fas fa-clock" style="margin-right: 0.25rem;"></i>2 hours ago</span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.25rem;">
                                <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewReport('report1')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="downloadReport('report1')">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="scheduleReport('report1')">
                                    <i class="fas fa-clock"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Report Item 2 -->
                        <div class="report-item" data-type="attendance" data-period="weekly" data-status="generating" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--warning-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <h4 style="margin: 0; color: var(--text-primary);">Attendance Summary Report</h4>
                                    <span style="background-color: var(--warning-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Generating</span>
                                </div>
                                <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">Weekly attendance summary for all courses and students</p>
                                <div style="display: flex; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                    <span><i class="fas fa-tag" style="margin-right: 0.25rem;"></i>Attendance</span>
                                    <span><i class="fas fa-calendar" style="margin-right: 0.25rem;"></i>Weekly</span>
                                    <span><i class="fas fa-clock" style="margin-right: 0.25rem;"></i>In Progress</span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.25rem;">
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="cancelReport('report2')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Report Item 3 -->
                        <div class="report-item" data-type="financial" data-period="monthly" data-status="scheduled" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <h4 style="margin: 0; color: var(--text-primary);">Financial Summary Report</h4>
                                    <span style="background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Scheduled</span>
                                </div>
                                <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">Monthly financial summary including tuition and expenses</p>
                                <div style="display: flex; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                    <span><i class="fas fa-tag" style="margin-right: 0.25rem;"></i>Financial</span>
                                    <span><i class="fas fa-calendar" style="margin-right: 0.25rem;"></i>Monthly</span>
                                    <span><i class="fas fa-calendar-alt" style="margin-right: 0.25rem;"></i>Scheduled for Sep 30</span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.25rem;">
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editSchedule('report3')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="cancelSchedule('report3')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Report Item 4 -->
                        <div class="report-item" data-type="system" data-period="daily" data-status="failed" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--error-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <h4 style="margin: 0; color: var(--text-primary);">System Performance Report</h4>
                                    <span style="background-color: var(--error-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Failed</span>
                                </div>
                                <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">Daily system performance metrics and health status</p>
                                <div style="display: flex; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                    <span><i class="fas fa-tag" style="margin-right: 0.25rem;"></i>System</span>
                                    <span><i class="fas fa-calendar" style="margin-right: 0.25rem;"></i>Daily</span>
                                    <span><i class="fas fa-clock" style="margin-right: 0.25rem;"></i>1 hour ago</span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.25rem;">
                                <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="retryReport('report4')">
                                    <i class="fas fa-redo"></i>
                                </button>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewError('report4')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Report Item 5 -->
                        <div class="report-item" data-type="academic" data-period="yearly" data-status="completed" style="display: flex; align-items: center; gap: 1rem; padding: 1rem;">
                            <div style="width: 40px; height: 40px; background-color: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <h4 style="margin: 0; color: var(--text-primary);">Annual Academic Report</h4>
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Completed</span>
                                </div>
                                <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">Comprehensive annual academic performance and statistics</p>
                                <div style="display: flex; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                    <span><i class="fas fa-tag" style="margin-right: 0.25rem;"></i>Academic</span>
                                    <span><i class="fas fa-calendar" style="margin-right: 0.25rem;"></i>Yearly</span>
                                    <span><i class="fas fa-clock" style="margin-right: 0.25rem;"></i>1 day ago</span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.25rem;">
                                <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewReport('report5')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="downloadReport('report5')">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="scheduleReport('report5')">
                                    <i class="fas fa-clock"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Analytics Dashboard -->
            <section class="analytics-dashboard" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-pie" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Analytics Dashboard
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="refreshAnalytics()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button class="btn btn-outline" onclick="exportAnalytics()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-3">
                        <div style="text-align: center;">
                            <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Report Types</h3>
                            <div style="width: 200px; height: 200px; margin: 0 auto; background: conic-gradient(var(--primary-color) 0deg 144deg, var(--accent-color) 144deg 216deg, var(--success-color) 216deg 288deg, var(--warning-color) 288deg 360deg); border-radius: 50%; position: relative;">
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: var(--surface-color); width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <span style="font-weight: 700; color: var(--text-primary);">24</span>
                                </div>
                            </div>
                            <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.25rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 12px; height: 12px; background-color: var(--primary-color); border-radius: 2px;"></div>
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Academic: 10</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 12px; height: 12px; background-color: var(--accent-color); border-radius: 2px;"></div>
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Attendance: 6</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 12px; height: 12px; background-color: var(--success-color); border-radius: 2px;"></div>
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Financial: 5</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 12px; height: 12px; background-color: var(--warning-color); border-radius: 2px;"></div>
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">System: 3</span>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Report Status</h3>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Completed</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">18</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 75%; background-color: var(--success-color);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Generating</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">3</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 12.5%; background-color: var(--warning-color);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Scheduled</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">2</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 8.3%; background-color: var(--primary-color);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Failed</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">1</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 4.2%; background-color: var(--error-color);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Performance Metrics</h3>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Avg Generation Time</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">2.3 min</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 46%; background-color: var(--success-color);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Success Rate</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">94%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 94%; background-color: var(--success-color);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Storage Used</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">68%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 68%; background-color: var(--warning-color);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Chat Widget -->
    <div class="chat-widget">
        <button class="chat-toggle" onclick="toggleChat()">
            <i class="fas fa-comments"></i>
        </button>
        <div class="chat-box">
            <div class="chat-header">
                <h3>Reports Chat</h3>
                <button class="chat-close" onclick="closeChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chat-body">
                <form class="chat-form">
                    <div class="form-group">
                        <label class="form-label">From</label>
                        <input type="email" name="from" class="form-input" placeholder="admin@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="email" name="to" class="form-input" placeholder="user@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Report notification" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-input" rows="4" placeholder="Type your message here..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>University Portal</h3>
                <p>Comprehensive campus management system designed to streamline academic operations and enhance the learning experience.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="../index.php">Home</a>
                <a href="../app/about.php">About Us</a>
                <a href="../app/contact.php">Contact</a>
                <a href="../app/help_center.php">Help Center</a>
            </div>
            <div class="footer-section">
                <h3>User Access</h3>
                <a href="../auth/auth_login.php">Student Login</a>
                <a href="../auth/auth_login.php">Doctor Login</a>
                <a href="../auth/auth_login.php">Admin Login</a>
                <a href="../auth/auth_signup.php">Register</a>
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
    <script src="../../js/main.js"></script>
    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Set up chat form submission
            const chatForm = document.querySelector('.chat-form');
            if (chatForm) {
                chatForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    showNotification('Message sent successfully!', 'success');
                    this.reset();
                    closeChat();
                });
            }
        });

        // Filter reports
        function filterReports() {
            const searchTerm = document.getElementById('reportSearch').value.toLowerCase();
            const typeFilter = document.getElementById('typeFilter').value;
            const periodFilter = document.getElementById('periodFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const reportItems = document.querySelectorAll('.report-item');

            reportItems.forEach(item => {
                const reportTitle = item.querySelector('h4').textContent.toLowerCase();
                const type = item.getAttribute('data-type');
                const period = item.getAttribute('data-period');
                const status = item.getAttribute('data-status');

                const matchesSearch = reportTitle.includes(searchTerm);
                const matchesType = !typeFilter || type === typeFilter;
                const matchesPeriod = !periodFilter || period === periodFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesType && matchesPeriod && matchesStatus) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Report actions
        function viewReport(reportId) {
            showNotification(`Viewing report ${reportId}...`, 'info');
        }

        function downloadReport(reportId) {
            showNotification(`Downloading report ${reportId}...`, 'info');
        }

        function scheduleReport(reportId) {
            showNotification(`Scheduling report ${reportId}...`, 'info');
        }

        function cancelReport(reportId) {
            if (confirm('Are you sure you want to cancel this report generation?')) {
                showNotification(`Report ${reportId} cancelled`, 'success');
            }
        }

        function editSchedule(reportId) {
            showNotification(`Editing schedule for report ${reportId}...`, 'info');
        }

        function cancelSchedule(reportId) {
            if (confirm('Are you sure you want to cancel this scheduled report?')) {
                showNotification(`Scheduled report ${reportId} cancelled`, 'success');
            }
        }

        function retryReport(reportId) {
            showNotification(`Retrying report ${reportId}...`, 'info');
        }

        function viewError(reportId) {
            showNotification(`Viewing error details for report ${reportId}...`, 'info');
        }

        // Quick report generation
        function generateStudentReport() {
            showNotification('Generating student report...', 'info');
        }

        function generateAttendanceReport() {
            showNotification('Generating attendance report...', 'info');
        }

        function generateGradeReport() {
            showNotification('Generating grade report...', 'info');
        }

        function generateSystemReport() {
            showNotification('Generating system report...', 'info');
        }

        // General actions
        function generateReport() {
            showNotification('Opening report generation dialog...', 'info');
        }

        function viewAllReports() {
            showNotification('Loading all reports...', 'info');
        }

        function scheduleReport() {
            showNotification('Opening report scheduling dialog...', 'info');
        }

        function refreshReports() {
            showNotification('Refreshing reports...', 'info');
            setTimeout(() => {
                showNotification('Reports refreshed successfully', 'success');
            }, 1000);
        }

        function refreshAnalytics() {
            showNotification('Refreshing analytics...', 'info');
        }

        function exportAnalytics() {
            showNotification('Exporting analytics data...', 'info');
        }
    </script>
</body>
</html>
