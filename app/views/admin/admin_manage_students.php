<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
// Dynamic student listing powered by DB
// Correct require path: from app/views/admin go up two levels to app/, then into core/
require_once __DIR__ . '/../../core/Database.php';

$db = Database::getInstance()->getConnection();

// Read filters from query params (search, year)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$yearFilter = isset($_GET['year']) ? trim($_GET['year']) : '';
$programFilterVar = isset($_GET['program']) ? trim($_GET['program']) : '';
$statusFilterVar = isset($_GET['status']) ? trim($_GET['status']) : '';

// Base where: query students table
$where = "WHERE 1=1";
$params = [];

if ($search !== '') {
    $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR student_number LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

// Check if year_enrolled column exists
try {
    $checkYear = $db->query("SHOW COLUMNS FROM students LIKE 'year_enrolled'");
    $hasYearEnrolled = $checkYear->rowCount() > 0;
} catch (PDOException $e) {
    $hasYearEnrolled = false;
}

if ($yearFilter !== '') {
    // Check if year_enrolled column exists before filtering
    if ($hasYearEnrolled) {
    $where .= " AND year_enrolled = ?";
    $params[] = $yearFilter;
    }
}

if ($statusFilterVar !== '') {
    $where .= " AND status = ?";
    $params[] = $statusFilterVar;
}

// Count total students
$countStmt = $db->prepare("SELECT COUNT(*) as cnt FROM students $where");
$countStmt->execute($params);
$totalStudents = (int)$countStmt->fetchColumn();

// Count students created this month
$monthStmt = $db->prepare("SELECT COUNT(*) as cnt FROM students WHERE YEAR(created_at)=YEAR(CURRENT_DATE()) AND MONTH(created_at)=MONTH(CURRENT_DATE())");
$monthStmt->execute();
$studentsThisMonth = (int)$monthStmt->fetchColumn();

// Count active students
$activeStmt = $db->prepare("SELECT COUNT(*) as cnt FROM students WHERE status = 'active'");
$activeStmt->execute();
$activeStudents = (int)$activeStmt->fetchColumn();

// Fetch students rows (limit 100 for performance)
// Conditionally include year_enrolled if the column exists
$yearField = $hasYearEnrolled ? 'year_enrolled,' : '';
$dataStmt = $db->prepare("SELECT id, student_number, first_name, last_name, email, phone, $yearField major, minor, gpa, status, created_at FROM students $where ORDER BY created_at DESC LIMIT 100");
$dataStmt->execute($params);
$students = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle export to CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_export.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','First Name','Last Name','Email','Created At']);
    foreach ($students as $s) {
        fputcsv($out, [$s['id'],$s['first_name'],$s['last_name'],$s['email'],$s['created_at']]);
    }
    fclose($out);
    exit;
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
            <h2><i class="fas fa-graduation-cap"></i> Admin Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="admin_manage_students.php" class="nav-item active">
                <i class="fas fa-user-graduate"></i> Manage Students
            </a>
            <a href="admin_manage_doctors.php" class="nav-item">
                <i class="fas fa-chalkboard-teacher"></i> Manage Doctors
            </a>
            <a href="admin_manage_courses.php" class="nav-item">
                <i class="fas fa-book"></i> Manage Courses
            </a>
            <a href="admin_reports.php" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Manage Students</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Add, update, and manage student accounts and information.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshStudents()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="addStudent()">
                        <i class="fas fa-plus"></i> Add Student
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Student Statistics -->
            <section class="student-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($totalStudents); ?></div>
                        <div style="color: var(--text-secondary);">Total Students</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($activeStudents); ?></div>
                        <div style="color: var(--text-secondary);">Active Students</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">0</div>
                        <div style="color: var(--text-secondary);">Pending Approval</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($studentsThisMonth); ?></div>
                        <div style="color: var(--text-secondary);">New This Month</div>
                    </div>
                </div>
            </section>

            <!-- Student Filter -->
            <section class="student-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search students..." id="studentSearch" value="<?php echo htmlspecialchars($search); ?>" onkeyup="if(event.key==='Enter'){filterStudents();}">
                        </div>
                        <div>
                            <select class="form-input" id="programFilter" onchange="filterStudents()">
                                <option value="">All Programs</option>
                                <option value="computer-science" <?php echo ($programFilterVar==='computer-science')? 'selected' : ''; ?>>Computer Science</option>
                                <option value="mathematics" <?php echo ($programFilterVar==='mathematics')? 'selected' : ''; ?>>Mathematics</option>
                                <option value="physics" <?php echo ($programFilterVar==='physics')? 'selected' : ''; ?>>Physics</option>
                                <option value="engineering" <?php echo ($programFilterVar==='engineering')? 'selected' : ''; ?>>Engineering</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="yearFilter" onchange="filterStudents()">
                                <option value="">All Years</option>
                                <option value="2024" <?php echo ($yearFilter==='2024')? 'selected' : ''; ?>>2024</option>
                                <option value="2023" <?php echo ($yearFilter==='2023')? 'selected' : ''; ?>>2023</option>
                                <option value="2022" <?php echo ($yearFilter==='2022')? 'selected' : ''; ?>>2022</option>
                                <option value="2021" <?php echo ($yearFilter==='2021')? 'selected' : ''; ?>>2021</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterStudents()">
                                <option value="">All Status</option>
                                <option value="active" <?php echo ($statusFilterVar==='active')? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($statusFilterVar==='inactive')? 'selected' : ''; ?>>Inactive</option>
                                <option value="pending" <?php echo ($statusFilterVar==='pending')? 'selected' : ''; ?>>Pending</option>
                                <option value="suspended" <?php echo ($statusFilterVar==='suspended')? 'selected' : ''; ?>>Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Students List -->
            <section class="students-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-user-graduate" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Student Directory
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="exportStudents()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>

                    <!-- Students Table -->
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                                    <th>Student</th>
                                    <th>Student Number</th>
                                    <th>Email</th>
                                    <th>Major</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($students)): ?>
                                    <?php foreach ($students as $s): ?>
                                        <tr>
                                            <td><input type="checkbox" class="student-checkbox" value="<?php echo htmlspecialchars($s['id']); ?>"></td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <div style="width: 40px; height: 40px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></div>
                                                        <div style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars($s['email']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($s['student_number'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($s['email']); ?></td>
                                            <td><?php echo htmlspecialchars($s['major'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span style="background-color: <?php 
                                                    echo $s['status'] === 'active' ? 'var(--success-color)' : 
                                                        ($s['status'] === 'suspended' ? 'var(--error-color)' : 'var(--warning-color)'); 
                                                ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                                    <?php echo ucfirst($s['status'] ?? 'active'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($s['created_at']))); ?></td>
                                            <td>
                                                <div style="display: flex; gap: 0.25rem;">
                                                    <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewStudent('<?php echo htmlspecialchars($s['id']); ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editStudent('<?php echo htmlspecialchars($s['id']); ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="suspendStudent('<?php echo htmlspecialchars($s['id']); ?>')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7">No students found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div style="display: flex; justify-content: between; align-items: center; padding: 1rem; border-top: 1px solid var(--border-color);">
                        <div style="color: var(--text-secondary); font-size: 0.9rem;">
                            Showing 1-5 of 1,250 students
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="previousPage()">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-primary">1</button>
                            <button class="btn btn-outline">2</button>
                            <button class="btn btn-outline">3</button>
                            <span style="padding: 0.5rem;">...</span>
                            <button class="btn btn-outline">250</button>
                            <button class="btn btn-outline" onclick="nextPage()">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="quick-actions" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-bolt" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Quick Actions
                        </h2>
                    </div>
                    <div class="grid grid-4">
                        <button class="btn btn-primary" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="addStudent()">
                            <i class="fas fa-plus" style="font-size: 2rem;"></i>
                            <span>Add Student</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="importStudents()">
                            <i class="fas fa-upload" style="font-size: 2rem;"></i>
                            <span>Import Students</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="bulkActions()">
                            <i class="fas fa-tasks" style="font-size: 2rem;"></i>
                            <span>Bulk Actions</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="exportStudents()">
                            <i class="fas fa-download" style="font-size: 2rem;"></i>
                            <span>Export Data</span>
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Modal Overlay (shared for all modals) -->
    <div id="modalOverlay" class="modal-overlay" onclick="closeAllModals()" hidden></div>

    <!-- Add/Edit Student Modal -->
    <div id="studentFormModal" class="modal" data-header-style="primary" hidden>
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 id="studentModalTitle">Add Student</h2>
                <button class="modal-close" onclick="closeStudentFormModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="studentForm" onsubmit="handleStudentFormSubmit(event)">
                <input type="hidden" id="studentId" name="id">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-input" placeholder="e.g., +1234567890">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Leave blank to auto-generate">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Student Number</label>
                        <input type="text" name="student_number" class="form-input" placeholder="e.g., 2025001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Year Enrolled</label>
                        <input type="number" name="year_enrolled" class="form-input" min="2000" max="2099" placeholder="e.g., 2024">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Major</label>
                        <input type="text" name="major" class="form-input" placeholder="e.g., Computer Science">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Minor</label>
                        <input type="text" name="minor" class="form-input" placeholder="Optional">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">GPA</label>
                        <input type="number" name="gpa" class="form-input" step="0.01" min="0" max="4" placeholder="e.g., 3.5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-input">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Save Student
                    </button>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeStudentFormModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Students CSV Modal -->
    <div id="importModal" class="modal" data-header-style="accent" hidden>
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Import Students from CSV</h2>
                <button class="modal-close" onclick="closeImportModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="importForm" onsubmit="handleImportSubmit(event)">
                <div class="form-group">
                    <label class="form-label">CSV File *</label>
                    <input type="file" name="csvFile" id="csvFile" class="form-input" accept=".csv" required>
                    <small style="color: var(--text-secondary); margin-top: 0.5rem; display: block;">
                        Required columns: first_name, last_name, email. Optional: student_number, major, minor, gpa, password, status
                    </small>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="skipHeader" id="skipHeader" checked>
                        <span style="margin-left: 0.5rem;">First row contains headers</span>
                    </label>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-upload"></i> Import
                    </button>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeImportModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Edit Modal -->
    <div id="bulkEditModal" class="modal" data-header-style="secondary" hidden>
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Bulk Edit Students</h2>
                <button class="modal-close" onclick="closeBulkEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="bulkEditForm" onsubmit="handleBulkEditSubmit(event)">
                <div class="form-group" style="background-color: var(--surface-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Selected Students: <span id="bulkSelectedCount">0</span></label>
                    <small style="color: var(--text-secondary);">Check the boxes in the table above to select students for bulk editing.</small>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Major</label>
                        <input type="text" name="major" class="form-input" placeholder="Leave blank to skip">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Minor</label>
                        <input type="text" name="minor" class="form-input" placeholder="Leave blank to skip">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">GPA</label>
                        <input type="number" name="gpa" class="form-input" step="0.01" min="0" max="4" placeholder="Leave blank to skip">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-input">
                            <option value="">-- No Change --</option>
                            <option value="not_active">Not Active</option>
                            <option value="active">Active</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-check"></i> Apply Changes
                    </button>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeBulkEditModal()">
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
                <h3>Student Management Chat</h3>
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
                        <input type="email" name="to" class="form-input" placeholder="student@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Account notification" required>
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

        // Filter students
        function filterStudents() {
            // Server-side filtering: redirect with query params
            const searchTerm = encodeURIComponent(document.getElementById('studentSearch').value.trim());
            const programFilter = encodeURIComponent(document.getElementById('programFilter').value);
            const yearFilter = encodeURIComponent(document.getElementById('yearFilter').value);
            const statusFilter = encodeURIComponent(document.getElementById('statusFilter').value);

            const params = [];
            if (searchTerm) params.push('search=' + searchTerm);
            if (yearFilter) params.push('year=' + yearFilter);
            if (programFilter) params.push('program=' + programFilter);
            if (statusFilter) params.push('status=' + statusFilter);

            const query = params.length ? ('?' + params.join('&')) : '';
            window.location.href = window.location.pathname + query;
        }

        // Toggle select all
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const studentCheckboxes = document.querySelectorAll('.student-checkbox');

            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        // Student actions
        function viewStudent(studentId) {
            showNotification(`Viewing student ${studentId}...`, 'info');
        }

        function editStudent(studentId) {
            showNotification(`Editing student ${studentId}...`, 'info');
        }

        function suspendStudent(studentId) {
            if (confirm('Are you sure you want to suspend this student?')) {
                showNotification(`Student ${studentId} suspended`, 'success');
            }
        }

        function approveStudent(studentId) {
            if (confirm('Are you sure you want to approve this student?')) {
                showNotification(`Student ${studentId} approved`, 'success');
            }
        }

        function reactivateStudent(studentId) {
            if (confirm('Are you sure you want to reactivate this student?')) {
                showNotification(`Student ${studentId} reactivated`, 'success');
            }
        }

        // General actions - Quick Actions
        function addStudent() {
            document.getElementById('studentForm').reset();
            document.getElementById('studentId').value = '';
            document.getElementById('studentModalTitle').textContent = 'Add Student';
            openModal('studentFormModal');
        }

        function importStudents() {
            document.getElementById('importForm').reset();
            openModal('importModal');
        }

        function bulkActions() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            const selectedCount = checkedBoxes.length;
            if (selectedCount === 0) {
                showNotification('Please select at least one student', 'warning');
                return;
            }
            document.getElementById('bulkSelectedCount').textContent = selectedCount;
            openModal('bulkEditModal');
        }

        function exportStudents() {
            // Export current filtered list as CSV by reloading with export=csv
            const searchTerm = encodeURIComponent(document.getElementById('studentSearch').value.trim());
            const yearFilter = encodeURIComponent(document.getElementById('yearFilter').value);
            const params = [];
            if (searchTerm) params.push('search=' + searchTerm);
            if (yearFilter) params.push('year=' + yearFilter);
            params.push('export=csv');
            const query = params.length ? ('?' + params.join('&')) : '';
            window.location.href = window.location.pathname + query;
        }

        function refreshStudents() {
            showNotification('Refreshing student data...', 'info');
            setTimeout(() => {
                location.reload();
            }, 500);
        }

        // Modal functions
        function openModal(modalId) {
            // Close any other active modals first so only one shows at a time
            document.querySelectorAll('.modal.active').forEach(m => {
                if (m.id !== modalId) {
                    m.classList.remove('active');
                    m.setAttribute('hidden', '');
                }
            });

            const modal = document.getElementById(modalId);
            const overlay = document.getElementById('modalOverlay');
            if (!modal) return;

            // Show overlay
            overlay.classList.add('active');
            overlay.removeAttribute('hidden');

            // Show modal
            modal.classList.add('active');
            modal.removeAttribute('hidden');

            // Apply header color/style based on data attribute
            const header = modal.querySelector('.modal-header');
            if (header) {
                header.classList.remove('modal-header--primary','modal-header--secondary','modal-header--accent');
                const style = modal.dataset.headerStyle || 'primary';
                header.classList.add('modal-header--' + style);
            }

            // focus the first input for accessibility
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

        function closeStudentFormModal() {
            const modal = document.getElementById('studentFormModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('hidden', '');
            const overlay = document.getElementById('modalOverlay');
            overlay.classList.remove('active');
            overlay.setAttribute('hidden', '');
        }

        function closeImportModal() {
            const modal = document.getElementById('importModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('hidden', '');
            const overlay = document.getElementById('modalOverlay');
            overlay.classList.remove('active');
            overlay.setAttribute('hidden', '');
        }

        function closeBulkEditModal() {
            const modal = document.getElementById('bulkEditModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('hidden', '');
            const overlay = document.getElementById('modalOverlay');
            overlay.classList.remove('active');
            overlay.setAttribute('hidden', '');
        }

        // Form submission handlers
        async function handleStudentFormSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('studentForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            const studentId = data.id;
            delete data.id;

            // Remove empty fields
            Object.keys(data).forEach(k => !data[k] && delete data[k]);

            try {
                const action = studentId ? 'update' : 'create';
                if (studentId) data.id = studentId;

                // Use admin_users API for creating, students API for updating
                const apiPath = action === 'create' 
                    ? getApiPath('admin_users.php?action=create-student')
                    : getApiPath('students.php?action=' + action);
                console.log('API Path:', apiPath); // Debug log
                const response = await fetch(apiPath, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                // Check if response is OK
                if (!response.ok) {
                    // Try to get error message from response
                    let errorData;
                    try {
                        errorData = await response.json();
                    } catch (e) {
                        // If JSON parsing fails, get text
                        const text = await response.text();
                        console.error('Response text:', text);
                        showNotification('Server error: ' + text.substring(0, 200), 'error');
                        return;
                    }
                    console.error('Error response:', errorData);
                    showNotification(errorData.message || errorData.error || 'Failed to save student', 'error');
                    if (errorData.driver_message) {
                        console.error('Database error:', errorData.driver_message);
                        alert('Database Error: ' + errorData.driver_message + '\n\nCheck console for full details.');
                    }
                    return;
                }
                
                const result = await response.json();
                if (result.success) {
                    showNotification(result.message || 'Student saved successfully', 'success');
                    closeStudentFormModal();
                    setTimeout(() => location.reload(), 500);
                } else {
                    console.error('Failed response:', result);
                    showNotification(result.message || result.error || 'Failed to save student', 'error');
                    if (result.driver_message) {
                        console.error('Database error:', result.driver_message);
                        alert('Database Error: ' + result.driver_message + '\n\nCheck console for full details.');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                console.error('Error stack:', error.stack);
                showNotification('An error occurred: ' + error.message, 'error');
            }
        }

        async function handleImportSubmit(e) {
            e.preventDefault();
            const fileInput = document.getElementById('csvFile');
            const file = fileInput.files[0];
            if (!file) {
                showNotification('Please select a CSV file', 'warning');
                return;
            }

            const reader = new FileReader();
            reader.onload = async (event) => {
                try {
                    const csv = event.target.result;
                    const lines = csv.split('\n').filter(l => l.trim());
                    const skipHeader = document.getElementById('skipHeader').checked;
                    const startIdx = skipHeader ? 1 : 0;

                    const students = [];
                    const headers = skipHeader ? lines[0].split(',').map(h => h.trim().toLowerCase()) : ['first_name','last_name','email','student_number','major','minor','gpa','password','status'];

                    for (let i = startIdx; i < lines.length; i++) {
                        const values = lines[i].split(',').map(v => v.trim());
                        const student = {};
                        headers.forEach((h, idx) => {
                            if (values[idx]) student[h] = values[idx];
                        });
                        if (student.first_name && student.last_name && student.email) {
                            students.push(student);
                        }
                    }

                    if (students.length === 0) {
                        showNotification('No valid students found in CSV', 'warning');
                        return;
                    }

                    const response = await fetch(getApiPath('students.php?action=import'), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ students })
                    });

                    const result = await response.json();
                    if (result.success) {
                        showNotification(`${students.length} students imported successfully`, 'success');
                        closeImportModal();
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showNotification(result.message || 'Import failed', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showNotification('Error parsing CSV file', 'error');
                }
            };
            reader.readAsText(file);
        }

        async function handleBulkEditSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('bulkEditForm');
            const formData = new FormData(form);
            const updates = Object.fromEntries(formData);
            
            // Remove empty fields
            Object.keys(updates).forEach(k => !updates[k] && delete updates[k]);

            if (Object.keys(updates).length === 0) {
                showNotification('Please fill at least one field', 'warning');
                return;
            }

            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            const studentIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));

            if (studentIds.length === 0) {
                showNotification('No students selected', 'warning');
                return;
            }

            try {
                const response = await fetch(getApiPath('students.php?action=bulk-update'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: studentIds, updates })
                });

                const result = await response.json();
                if (result.success) {
                    showNotification(`${studentIds.length} students updated`, 'success');
                    closeBulkEditModal();
                    setTimeout(() => location.reload(), 500);
                } else {
                    showNotification(result.message || 'Bulk update failed', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            }
        }

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

        // Edit student from table
        function editStudent(studentId) {
            // Fetch student data and populate form
            const apiPath = getApiPath('students.php?action=get&id=' + studentId);
            fetch(apiPath)
                .then(r => r.json())
                .then(result => {
                    if (result.success && result.data) {
                        const student = result.data;
                        const form = document.getElementById('studentForm');
                        form.elements['first_name'].value = student.first_name || '';
                        form.elements['last_name'].value = student.last_name || '';
                        form.elements['email'].value = student.email || '';
                        form.elements['phone'].value = student.phone || '';
                        form.elements['student_number'].value = student.student_number || '';
                        form.elements['year_enrolled'].value = student.year_enrolled || '';
                        form.elements['major'].value = student.major || '';
                        form.elements['minor'].value = student.minor || '';
                        form.elements['gpa'].value = student.gpa || '';
                        form.elements['status'].value = student.status || 'active';
                        document.getElementById('studentId').value = student.id;
                        document.getElementById('studentModalTitle').textContent = 'Edit Student';
                        openModal('studentFormModal');
                    } else {
                        showNotification('Failed to load student', 'error');
                    }
                })
                .catch(e => { console.error(e); showNotification('Error loading student', 'error'); });
        }

        // View student (for now just show notification, can expand later)
        function viewStudent(studentId) {
            showNotification(`Viewing student ${studentId}...`, 'info');
            // TODO: Add a view-only modal or redirect to student detail page
        }

        // Suspend student
        function suspendStudent(studentId) {
            if (confirm('Are you sure you want to suspend this student?')) {
                handleStudentStatusUpdate(studentId, 'not_active');
            }
        }

        async function handleStudentStatusUpdate(studentId, status) {
            try {
                const response = await fetch(getApiPath('students.php?action=update'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: studentId, status })
                });

                const result = await response.json();
                if (result.success) {
                    showNotification('Status updated', 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showNotification(result.message || 'Failed to update status', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            }
        }

        // Pagination
        function previousPage() {
            showNotification('Loading previous page...', 'info');
        }

        function nextPage() {
            showNotification('Loading next page...', 'info');
        }
    </script>
</body>
</html>
