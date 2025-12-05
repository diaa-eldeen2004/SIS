<?php
// Dynamic reports page powered by DB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../core/Database.php';

$db = Database::getInstance()->getConnection();

// Read filters from query params
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$typeFilter = isset($_GET['type']) ? trim($_GET['type']) : '';
$periodFilter = isset($_GET['period']) ? trim($_GET['period']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Initialize default values
$totalReports = 0;
$reportsToday = 0;
$scheduledReports = 0;
$totalDownloads = 0;
$reports = [];
$tableError = false;

// Check if reports table exists, handle gracefully if it doesn't
try {
    // Count total reports
    $countStmt = $db->query("SELECT COUNT(*) as cnt FROM reports");
    $totalReports = (int)$countStmt->fetchColumn();

    // Count reports generated today
    $todayStmt = $db->query("SELECT COUNT(*) as cnt FROM reports WHERE DATE(created_at) = CURRENT_DATE()");
    $reportsToday = (int)$todayStmt->fetchColumn();

    // Count scheduled reports
    $scheduledStmt = $db->query("SELECT COUNT(*) as cnt FROM reports WHERE status = 'scheduled'");
    $scheduledReports = (int)$scheduledStmt->fetchColumn();

    // Count total downloads (approximate from file_path existence)
    $downloadsStmt = $db->query("SELECT COUNT(*) as cnt FROM reports WHERE file_path IS NOT NULL");
    $totalDownloads = (int)$downloadsStmt->fetchColumn();

    // Build WHERE clause for filtering
    $where = "WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $where .= " AND (title LIKE ? OR type LIKE ?)";
        $like = "%$search%";
        $params[] = $like;
        $params[] = $like;
    }

    if ($typeFilter !== '') {
        $where .= " AND type = ?";
        $params[] = $typeFilter;
    }

    if ($periodFilter !== '') {
        $where .= " AND period = ?";
        $params[] = $periodFilter;
    }

    if ($statusFilter !== '') {
        $where .= " AND status = ?";
        $params[] = $statusFilter;
    }

    // Fetch reports (limit 100 for performance)
    $dataStmt = $db->prepare("SELECT * FROM reports $where ORDER BY created_at DESC LIMIT 100");
    $dataStmt->execute($params);
    $reports = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If reports table doesn't exist, show friendly message
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $tableError = true;
        $errorMessage = "The reports table doesn't exist yet. Please run the database migrations first.";
    } else {
        error_log('Reports page database error: ' . $e->getMessage());
        $tableError = true;
        $errorMessage = "Database error: " . $e->getMessage();
    }
}

// Calculate statistics by type
$reportsByType = [
    'academic' => 0,
    'attendance' => 0,
    'financial' => 0,
    'system' => 0,
    'other' => 0
];
$reportsByStatus = [
    'completed' => 0,
    'generating' => 0,
    'scheduled' => 0,
    'failed' => 0
];

if (!$tableError) {
    foreach ($reports as $report) {
        $type = $report['type'] ?? $report['report_type'] ?? 'other';
        $status = $report['status'] ?? 'completed';
        if (isset($reportsByType[$type])) {
            $reportsByType[$type]++;
        }
        if (isset($reportsByStatus[$status])) {
            $reportsByStatus[$status]++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php if ($tableError): ?>
    <section class="error-message" style="margin: 2rem; padding: 2rem; background-color: var(--error-color); color: white; border-radius: 8px;">
        <h2 style="margin-top: 0;"><i class="fas fa-exclamation-triangle"></i> Database Table Not Found</h2>
        <p><?php echo htmlspecialchars($errorMessage); ?></p>
        <p>To fix this issue, please run the database migrations:</p>
        <a href="../../../run-migrations.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">
            <i class="fas fa-database"></i> Run Migrations
        </a>
    </section>
<?php endif; ?>
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
            <a href="admin_manage_advisor.php" class="nav-item">
                <i class="fas fa-user-tie"></i> Manage Advisors
            </a>
            <a href="admin_manage_it.php" class="nav-item">
                <i class="fas fa-laptop-code"></i> Manage IT Officers
            </a>
            <a href="admin_manage_user.php" class="nav-item">
                <i class="fas fa-users"></i> Manage Users
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
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($totalReports); ?></div>
                        <div style="color: var(--text-secondary);">Total Reports</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($reportsToday); ?></div>
                        <div style="color: var(--text-secondary);">Generated Today</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($scheduledReports); ?></div>
                        <div style="color: var(--text-secondary);">Scheduled</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-download"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($totalDownloads); ?></div>
                        <div style="color: var(--text-secondary);">Downloads</div>
                    </div>
                </div>
            </section>

            <!-- Report Filter -->
            <section class="report-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search reports..." id="reportSearch" value="<?php echo htmlspecialchars($search); ?>" onkeyup="filterReports()">
                        </div>
                        <div>
                            <select class="form-input" id="typeFilter" onchange="filterReports()">
                                <option value="">All Types</option>
                                <option value="academic" <?php echo $typeFilter === 'academic' ? 'selected' : ''; ?>>Academic</option>
                                <option value="attendance" <?php echo $typeFilter === 'attendance' ? 'selected' : ''; ?>>Attendance</option>
                                <option value="financial" <?php echo $typeFilter === 'financial' ? 'selected' : ''; ?>>Financial</option>
                                <option value="system" <?php echo $typeFilter === 'system' ? 'selected' : ''; ?>>System</option>
                                <option value="other" <?php echo $typeFilter === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="periodFilter" onchange="filterReports()">
                                <option value="">All Periods</option>
                                <option value="daily" <?php echo $periodFilter === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                <option value="weekly" <?php echo $periodFilter === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                <option value="monthly" <?php echo $periodFilter === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                <option value="yearly" <?php echo $periodFilter === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                                <option value="on_demand" <?php echo $periodFilter === 'on_demand' ? 'selected' : ''; ?>>On Demand</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterReports()">
                                <option value="">All Status</option>
                                <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="generating" <?php echo $statusFilter === 'generating' ? 'selected' : ''; ?>>Generating</option>
                                <option value="scheduled" <?php echo $statusFilter === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="failed" <?php echo $statusFilter === 'failed' ? 'selected' : ''; ?>>Failed</option>
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
                    <div class="reports-list" id="reportsList">
                        <?php if (empty($reports) && !$tableError): ?>
                            <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>No reports found. Generate your first report to get started.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reports as $report): 
                                $status = $report['status'] ?? 'completed';
                                $type = $report['type'] ?? $report['report_type'] ?? 'other';
                                $period = $report['period'] ?? $report['report_period'] ?? 'on_demand';
                                
                                // Status colors
                                $statusColors = [
                                    'completed' => 'var(--success-color)',
                                    'generating' => 'var(--warning-color)',
                                    'scheduled' => 'var(--primary-color)',
                                    'failed' => 'var(--error-color)'
                                ];
                                $statusColor = $statusColors[$status] ?? 'var(--text-secondary)';
                                
                                // Status icons
                                $statusIcons = [
                                    'completed' => 'fa-file-alt',
                                    'generating' => 'fa-spinner fa-spin',
                                    'scheduled' => 'fa-clock',
                                    'failed' => 'fa-exclamation-triangle'
                                ];
                                $statusIcon = $statusIcons[$status] ?? 'fa-file-alt';
                                
                                // Format date
                                $genDate = $report['created_at'] ?? $report['generation_date'] ?? '';
                                $timeAgo = '';
                                if ($genDate) {
                                    $date = new DateTime($genDate);
                                    $now = new DateTime();
                                    $diff = $now->diff($date);
                                    if ($diff->days > 0) {
                                        $timeAgo = $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
                                    } elseif ($diff->h > 0) {
                                        $timeAgo = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
                                    } elseif ($diff->i > 0) {
                                        $timeAgo = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
                                    } else {
                                        $timeAgo = 'Just now';
                                    }
                                }
                            ?>
                                <div class="report-item" data-type="<?php echo htmlspecialchars($type); ?>" data-period="<?php echo htmlspecialchars($period); ?>" data-status="<?php echo htmlspecialchars($status); ?>" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                    <div style="width: 40px; height: 40px; background-color: <?php echo $statusColor; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="fas <?php echo $statusIcon; ?>"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                            <h4 style="margin: 0; color: var(--text-primary);"><?php echo htmlspecialchars($report['title'] ?? $report['report_name'] ?? 'Untitled Report'); ?></h4>
                                            <span style="background-color: <?php echo $statusColor; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; text-transform: capitalize;"><?php echo htmlspecialchars($status); ?></span>
                                        </div>
                                        <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);"><?php echo htmlspecialchars(ucfirst($type) . ' report - ' . ucfirst(str_replace('_', ' ', $period)) . ' period'); ?></p>
                                        <div style="display: flex; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                            <span><i class="fas fa-tag" style="margin-right: 0.25rem;"></i><?php echo htmlspecialchars(ucfirst($type)); ?></span>
                                            <span><i class="fas fa-calendar" style="margin-right: 0.25rem;"></i><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $period))); ?></span>
                                            <span><i class="fas fa-clock" style="margin-right: 0.25rem;"></i><?php echo $timeAgo ?: 'N/A'; ?></span>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 0.25rem;">
                                        <?php if ($status === 'completed'): ?>
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewReport(<?php echo $report['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="downloadReport(<?php echo $report['id']; ?>)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        <?php elseif ($status === 'generating'): ?>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="cancelReport(<?php echo $report['id']; ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif ($status === 'scheduled'): ?>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editReport(<?php echo $report['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="cancelSchedule(<?php echo $report['id']; ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif ($status === 'failed'): ?>
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="retryReport(<?php echo $report['id']; ?>)">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewError(<?php echo $report['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editReport(<?php echo $report['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="deleteReport(<?php echo $report['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                                    <span style="font-weight: 700; color: var(--text-primary);"><?php echo htmlspecialchars($totalReports); ?></span>
                                </div>
                            </div>
                            <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.25rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 12px; height: 12px; background-color: var(--primary-color); border-radius: 2px;"></div>
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Academic: <?php echo htmlspecialchars($reportsByType['academic']); ?></span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 12px; height: 12px; background-color: var(--accent-color); border-radius: 2px;"></div>
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Attendance: <?php echo htmlspecialchars($reportsByType['attendance']); ?></span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 12px; height: 12px; background-color: var(--success-color); border-radius: 2px;"></div>
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Financial: <?php echo htmlspecialchars($reportsByType['financial']); ?></span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 12px; height: 12px; background-color: var(--warning-color); border-radius: 2px;"></div>
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">System: <?php echo htmlspecialchars($reportsByType['system']); ?></span>
                                </div>
                                <?php if ($reportsByType['other'] > 0): ?>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 12px; height: 12px; background-color: var(--text-secondary); border-radius: 2px;"></div>
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Other: <?php echo htmlspecialchars($reportsByType['other']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Report Status</h3>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Completed</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars($reportsByStatus['completed']); ?></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?php echo $totalReports > 0 ? ($reportsByStatus['completed'] / $totalReports * 100) : 0; ?>%; background-color: var(--success-color);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Generating</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars($reportsByStatus['generating']); ?></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?php echo $totalReports > 0 ? ($reportsByStatus['generating'] / $totalReports * 100) : 0; ?>%; background-color: var(--warning-color);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Scheduled</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars($reportsByStatus['scheduled']); ?></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?php echo $totalReports > 0 ? ($reportsByStatus['scheduled'] / $totalReports * 100) : 0; ?>%; background-color: var(--primary-color);"></div>
                                    </div>
                                </div>
                                <div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Failed</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars($reportsByStatus['failed']); ?></span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?php echo $totalReports > 0 ? ($reportsByStatus['failed'] / $totalReports * 100) : 0; ?>%; background-color: var(--error-color);"></div>
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

    <!-- Modal Overlay (shared for all modals) -->
    <div id="modalOverlay" class="modal-overlay" onclick="closeAllModals()" hidden></div>

    <!-- Add/Edit Report Modal -->
    <div id="reportFormModal" class="modal" data-header-style="primary" hidden>
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 id="reportModalTitle">Generate Report</h2>
                <button class="modal-close" onclick="closeReportFormModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="reportForm" onsubmit="handleReportFormSubmit(event)">
                <input type="hidden" id="reportId" name="id">
                <div class="form-group">
                    <label class="form-label">Report Name *</label>
                    <input type="text" name="report_name" id="reportName" class="form-input" placeholder="e.g., Student Enrollment Report" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Report Type *</label>
                        <select name="report_type" id="reportType" class="form-input" required>
                            <option value="">Select Type</option>
                            <option value="academic">Academic</option>
                            <option value="attendance">Attendance</option>
                            <option value="financial">Financial</option>
                            <option value="system">System</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Report Period *</label>
                        <select name="report_period" id="reportPeriod" class="form-input" required>
                            <option value="">Select Period</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="on_demand">On Demand</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="reportStatus" class="form-input">
                        <option value="completed">Completed</option>
                        <option value="generating">Generating</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">File Path (optional)</label>
                    <input type="text" name="file_path" id="filePath" class="form-input" placeholder="e.g., /reports/enrollment_2024.pdf">
                </div>
                <div class="form-group">
                    <label class="form-label">Parameters (JSON, optional)</label>
                    <textarea name="parameters" id="reportParameters" class="form-input" rows="3" placeholder='{"department": "Computer Science", "year": 2024}'></textarea>
                    <small style="color: var(--text-secondary);">Enter JSON format parameters for the report</small>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Save Report
                    </button>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeReportFormModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

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
    <script src="../js/main.js"></script>
    <script>
        // Helper function to get API base path
        function getApiPath(endpoint) {
            const currentUrl = window.location.href;
            const url = new URL(currentUrl);
            const pathParts = url.pathname.split('/').filter(Boolean);
            let rootIndex = pathParts.indexOf('sis');
            if (rootIndex === -1) rootIndex = 0;
            const projectRoot = '/' + pathParts.slice(0, rootIndex + 1).join('/');
            return projectRoot + '/public/api/' + endpoint;
        }

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

        // Filter reports (client-side filtering for instant feedback)
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

        // Load reports from API
        async function loadReports() {
            try {
                const search = document.getElementById('reportSearch').value;
                const type = document.getElementById('typeFilter').value;
                const period = document.getElementById('periodFilter').value;
                const status = document.getElementById('statusFilter').value;

                const params = new URLSearchParams();
                if (search) params.append('search', search);
                if (type) params.append('type', type);
                if (period) params.append('period', period);
                if (status) params.append('status', status);

                const apiPath = getApiPath(`reports.php?action=list&${params.toString()}`);
                const response = await fetch(apiPath);
                const result = await response.json();

                if (result.success) {
                    // Update reports list (simplified - full implementation would rebuild the list)
                    showNotification('Reports refreshed', 'success');
                } else {
                    showNotification(result.message || 'Failed to load reports', 'error');
                }
            } catch (error) {
                console.error('Error loading reports:', error);
                showNotification('An error occurred while loading reports', 'error');
            }
        }

        // Report actions
        async function viewReport(reportId) {
            try {
                const apiPath = getApiPath(`reports.php?action=get&id=${reportId}`);
                const response = await fetch(apiPath);
                const result = await response.json();
                if (result.success) {
                    editReport(reportId, result.data);
                } else {
                    showNotification(result.message || 'Failed to load report', 'error');
                }
            } catch (error) {
                console.error('Error viewing report:', error);
                showNotification('An error occurred', 'error');
            }
        }

        function editReport(reportId, reportData = null) {
            if (reportData) {
                // Populate form with existing data (map database fields to form fields)
                document.getElementById('reportId').value = reportData.id;
                document.getElementById('reportName').value = reportData.title || reportData.report_name || '';
                document.getElementById('reportType').value = reportData.type || reportData.report_type || '';
                document.getElementById('reportPeriod').value = reportData.period || reportData.report_period || '';
                document.getElementById('reportStatus').value = reportData.status || 'completed';
                document.getElementById('filePath').value = reportData.file_path || '';
                document.getElementById('reportParameters').value = reportData.parameters ? (typeof reportData.parameters === 'string' ? reportData.parameters : JSON.stringify(reportData.parameters, null, 2)) : '';
                document.getElementById('reportModalTitle').textContent = 'Edit Report';
            } else {
                // Fetch report data
                const apiPath = getApiPath(`reports.php?action=get&id=${reportId}`);
                fetch(apiPath)
                    .then(r => r.json())
                    .then(result => {
                        if (result.success && result.data) {
                            editReport(reportId, result.data);
                        } else {
                            showNotification('Failed to load report', 'error');
                        }
                    })
                    .catch(e => {
                        console.error(e);
                        showNotification('Error loading report', 'error');
                    });
                return;
            }
            openModal('reportFormModal');
        }

        async function downloadReport(reportId) {
            try {
                const apiPath = getApiPath(`reports.php?action=get&id=${reportId}`);
                const response = await fetch(apiPath);
                const result = await response.json();
                if (result.success && result.data.file_path) {
                    window.open(result.data.file_path, '_blank');
                    showNotification('Downloading report...', 'success');
                } else {
                    showNotification('Report file not available', 'error');
                }
            } catch (error) {
                console.error('Error downloading report:', error);
                showNotification('An error occurred', 'error');
            }
        }

        function scheduleReport(reportId) {
            showNotification(`Scheduling report ${reportId}...`, 'info');
            // TODO: Implement schedule modal
        }

        async function cancelReport(reportId) {
            if (confirm('Are you sure you want to cancel this report generation?')) {
                try {
                    const apiPath = getApiPath('reports.php?action=update');
                    const response = await fetch(apiPath, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: reportId, status: 'cancelled' })
                    });
                    const result = await response.json();
                    if (result.success) {
                        showNotification('Report cancelled', 'success');
                        loadReports();
                    } else {
                        showNotification(result.message || 'Failed to cancel report', 'error');
                    }
                } catch (error) {
                    console.error('Error cancelling report:', error);
                    showNotification('An error occurred', 'error');
                }
            }
        }

        function editSchedule(reportId) {
            editReport(reportId);
        }

        async function cancelSchedule(reportId) {
            if (confirm('Are you sure you want to cancel this scheduled report?')) {
                try {
                    const apiPath = getApiPath('reports.php?action=update');
                    const response = await fetch(apiPath, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: reportId, status: 'cancelled' })
                    });
                    const result = await response.json();
                    if (result.success) {
                        showNotification('Scheduled report cancelled', 'success');
                        loadReports();
                    } else {
                        showNotification(result.message || 'Failed to cancel schedule', 'error');
                    }
                } catch (error) {
                    console.error('Error cancelling schedule:', error);
                    showNotification('An error occurred', 'error');
                }
            }
        }

        async function retryReport(reportId) {
            try {
                const apiPath = getApiPath('reports.php?action=update');
                const response = await fetch(apiPath, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: reportId, status: 'generating' })
                });
                const result = await response.json();
                if (result.success) {
                    showNotification('Report generation restarted', 'success');
                    loadReports();
                } else {
                    showNotification(result.message || 'Failed to retry report', 'error');
                }
            } catch (error) {
                console.error('Error retrying report:', error);
                showNotification('An error occurred', 'error');
            }
        }

        function viewError(reportId) {
            showNotification(`Viewing error details for report ${reportId}...`, 'info');
            // TODO: Implement error viewer modal
        }

        async function deleteReport(reportId) {
            if (confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
                try {
                    const apiPath = getApiPath('reports.php?action=delete');
                    const response = await fetch(apiPath, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: reportId })
                    });
                    const result = await response.json();
                    if (result.success) {
                        showNotification('Report deleted successfully', 'success');
                        loadReports();
                        location.reload(); // Reload to refresh the list
                    } else {
                        showNotification(result.message || 'Failed to delete report', 'error');
                    }
                } catch (error) {
                    console.error('Error deleting report:', error);
                    showNotification('An error occurred', 'error');
                }
            }
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
            document.getElementById('reportForm').reset();
            document.getElementById('reportId').value = '';
            document.getElementById('reportModalTitle').textContent = 'Generate Report';
            document.getElementById('reportStatus').value = 'generating';
            openModal('reportFormModal');
        }

        // Modal functions
        function openModal(modalId) {
            document.querySelectorAll('.modal.active').forEach(m => {
                if (m.id !== modalId) {
                    m.classList.remove('active');
                    m.setAttribute('hidden', '');
                }
            });

            const modal = document.getElementById(modalId);
            const overlay = document.getElementById('modalOverlay');
            if (!modal) return;

            overlay.classList.add('active');
            overlay.removeAttribute('hidden');

            modal.classList.add('active');
            modal.removeAttribute('hidden');

            const header = modal.querySelector('.modal-header');
            if (header) {
                header.classList.remove('modal-header--primary','modal-header--secondary','modal-header--accent');
                const style = modal.dataset.headerStyle || 'primary';
                header.classList.add('modal-header--' + style);
            }

            const firstInput = modal.querySelector('input, select, textarea, button');
            if (firstInput) firstInput.focus();
        }

        function closeAllModals() {
            document.querySelectorAll('.modal').forEach(m => {
                m.classList.remove('active');
                m.setAttribute('hidden', '');
            });
            const overlay = document.getElementById('modalOverlay');
            overlay.classList.remove('active');
            overlay.setAttribute('hidden', '');
        }

        function closeReportFormModal() {
            const modal = document.getElementById('reportFormModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('hidden', '');
            const overlay = document.getElementById('modalOverlay');
            overlay.classList.remove('active');
            overlay.setAttribute('hidden', '');
        }

        // Form submission handler
        async function handleReportFormSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('reportForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            const reportId = data.id;
            delete data.id;

            // Validate required fields before mapping
            if (!data.report_name || data.report_name.trim() === '') {
                showNotification('Report Name is required', 'error');
                document.getElementById('reportName').focus();
                return;
            }
            if (!data.report_type || data.report_type.trim() === '') {
                showNotification('Report Type is required', 'error');
                document.getElementById('reportType').focus();
                return;
            }
            if (!data.report_period || data.report_period.trim() === '') {
                showNotification('Report Period is required', 'error');
                document.getElementById('reportPeriod').focus();
                return;
            }

            // Map form field names to database field names (always map required fields)
            const mappedData = {
                title: data.report_name.trim(),
                type: data.report_type.trim(),
                period: data.report_period.trim()
            };
            
            // Map optional fields
            if (data.status && data.status.trim()) {
                mappedData.status = data.status.trim();
            }
            if (data.file_path && data.file_path.trim()) {
                mappedData.file_path = data.file_path.trim();
            }
            
            // Parse parameters if provided
            if (data.parameters && data.parameters.trim()) {
                try {
                    mappedData.parameters = JSON.parse(data.parameters.trim());
                } catch (e) {
                    showNotification('Invalid JSON in parameters field: ' + e.message, 'error');
                    document.getElementById('reportParameters').focus();
                    return;
                }
            }

            try {
                const action = reportId ? 'update' : 'create';
                if (reportId) mappedData.id = reportId;

                // Get API path using helper function
                const apiPath = getApiPath(`reports.php?action=${action}`);
                console.log('API Path:', apiPath); // Debug log
                console.log('Form data:', data); // Debug log
                console.log('Mapped data:', mappedData); // Debug log
                
                // Verify required fields are present
                if (!mappedData.title || mappedData.title.trim() === '') {
                    showNotification('Error: Report Name (title) is missing after mapping', 'error');
                    console.error('Title is missing:', mappedData);
                    return;
                }

                const response = await fetch(apiPath, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(mappedData)
                });

                // Check if response is OK
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    console.error('Request body was:', JSON.stringify(mappedData));
                    try {
                        const errorJson = JSON.parse(errorText);
                        showNotification(errorJson.message || 'Failed to save report', 'error');
                    } catch {
                        showNotification('Server error: ' + response.status + ' - ' + errorText.substring(0, 100), 'error');
                    }
                    return;
                }

                const result = await response.json();
                if (result.success) {
                    showNotification(result.message || 'Report saved successfully', 'success');
                    closeReportFormModal();
                    setTimeout(() => location.reload(), 500);
                } else {
                    showNotification(result.message || 'Failed to save report', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                console.error('Error stack:', error.stack);
                showNotification('An error occurred: ' + error.message, 'error');
            }
        }

        function viewAllReports() {
            showNotification('Loading all reports...', 'info');
        }

        function scheduleReport() {
            showNotification('Opening report scheduling dialog...', 'info');
        }

        function refreshReports() {
            loadReports();
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
