<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admin Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
// Dynamic course listing powered by DB
require_once __DIR__ . '/../../core/Database.php';
///////////////////////////////////////////////////////////////////////////////
$db = Database::getInstance()->getConnection();

// Read filters from query params
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$departmentFilter = isset($_GET['department']) ? trim($_GET['department']) : '';
$levelFilter = isset($_GET['level']) ? trim($_GET['level']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Base where clause
$where = "WHERE 1=1";
$params = [];

if ($search !== '') {
    $where .= " AND (c.course_code LIKE ? OR c.course_name LIKE ? OR c.description LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($departmentFilter !== '') {
    $where .= " AND c.department = ?";
    $params[] = $departmentFilter;
}

if ($levelFilter !== '') {
    $where .= " AND c.level = ?";
    $params[] = (int)$levelFilter;
}

if ($statusFilter !== '') {
    $where .= " AND c.status = ?";
    $params[] = $statusFilter;
}

// Initialize default values
$totalCourses = 0;
$coursesThisSemester = 0;
$activeCourses = 0;
$courses = [];

// Check if courses table exists, handle gracefully if it doesn't
try {
    // Count total courses (need to adjust WHERE for count query)
    $countWhere = str_replace(['c.course_code', 'c.course_name', 'c.description', 'c.department', 'c.level', 'c.status'], 
                              ['course_code', 'course_name', 'description', 'department', 'level', 'status'], $where);
    $countStmt = $db->prepare("SELECT COUNT(*) as cnt FROM courses $countWhere");
    $countStmt->execute($params);
    $totalCourses = (int)$countStmt->fetchColumn();

    // Count courses created this semester (approximate by this month)
    $monthStmt = $db->prepare("SELECT COUNT(*) as cnt FROM courses WHERE YEAR(created_at)=YEAR(CURRENT_DATE()) AND MONTH(created_at)=MONTH(CURRENT_DATE())");
    $monthStmt->execute();
    $coursesThisSemester = (int)$monthStmt->fetchColumn();

    // Active courses
    $activeStmt = $db->prepare("SELECT COUNT(*) as cnt FROM courses WHERE status = 'active'");
    $activeStmt->execute();
    $activeCourses = (int)$activeStmt->fetchColumn();

    // Fetch courses with doctor info and student count
    $dataStmt = $db->prepare("
        SELECT c.id, c.course_code, c.course_name, c.description, c.department, 
               c.level, c.credits, c.status, c.max_students, c.created_at,
               c.doctor_id, d.first_name as doctor_first_name, d.last_name as doctor_last_name,
               (SELECT COUNT(*) FROM student_courses sc WHERE sc.course_id = c.id) as student_count
        FROM courses c
        LEFT JOIN doctors d ON c.doctor_id = d.id
        $where
        ORDER BY c.created_at DESC
        LIMIT 100
    ");
    $dataStmt->execute($params);
    $courses = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If courses table doesn't exist, show friendly message
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $tableError = true;
        $errorMessage = "The courses table doesn't exist yet. Please run the database migrations first.";
    } else {
        // Re-throw other database errors
        throw $e;
    }
}

// Handle export to CSV (only if table exists and we have data)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    if (!isset($tableError) && !empty($courses)) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="courses_export.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','Course Code','Course Name','Department','Level','Credits','Status','Instructor','Students','Created At']);
        foreach ($courses as $c) {
            $instructor = ($c['doctor_first_name'] && $c['doctor_last_name']) 
                ? $c['doctor_first_name'] . ' ' . $c['doctor_last_name'] 
                : 'Not Assigned';
            fputcsv($out, [
                $c['id'],
                $c['course_code'],
                $c['course_name'],
                $c['department'] ?? '',
                $c['level'] ?? '',
                $c['credits'] ?? 3,
                $c['status'] ?? 'active',
                $instructor,
                $c['student_count'] ?? 0,
                $c['created_at']
            ]);
        }
        fclose($out);
        exit;
    } else {
        // Redirect back if table doesn't exist
        header('Location: admin_manage_courses.php');
        exit;
    }
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
            <a href="admin_manage_students.php" class="nav-item">
                <i class="fas fa-user-graduate"></i> Manage Students
            </a>
            <a href="admin_manage_doctors.php" class="nav-item">
                <i class="fas fa-chalkboard-teacher"></i> Manage Doctors
            </a>
            <a href="admin_manage_courses.php" class="nav-item active">
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
            <a href="admin_reports.php" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Manage Courses</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Create, edit, and manage course information and assignments.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshCourses()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="createCourse()">
                        <i class="fas fa-plus"></i> Create Course
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Course Statistics -->
            <section class="course-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($totalCourses); ?></div>
                        <div style="color: var(--text-secondary);">Total Courses</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($activeCourses); ?></div>
                        <div style="color: var(--text-secondary);">Active Courses</div>
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
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($coursesThisSemester); ?></div>
                        <div style="color: var(--text-secondary);">New This Semester</div>
                    </div>
                </div>
            </section>

            <?php if (isset($tableError) && $tableError): ?>
            <!-- Error Message -->
            <section class="error-message" style="margin-bottom: 2rem;">
                <div class="card" style="background-color: #fee; border: 2px solid #fcc; padding: 2rem; text-align: center;">
                    <div style="font-size: 3rem; color: #c33; margin-bottom: 1rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h2 style="color: #c33; margin-bottom: 1rem;">Database Table Missing</h2>
                    <p style="color: #666; margin-bottom: 1.5rem; font-size: 1.1rem;">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </p>
                    <p style="color: #666; margin-bottom: 1rem;">
                        To fix this, please run the database migrations:
                    </p>
                    <ol style="text-align: left; display: inline-block; color: #666; margin-bottom: 1.5rem;">
                        <li>Visit: <a href="../../../run-migrations.php" style="color: var(--primary-color);" target="_blank">run-migrations.php</a> (opens in new tab)</li>
                        <li>Or run the SQL file manually in your database: <code style="background: #f0f0f0; padding: 0.25rem 0.5rem; border-radius: 4px;">database/migrations/003_create_courses_table.sql</code></li>
                    </ol>
                    <div style="margin-top: 1.5rem;">
                        <a href="../../../run-migrations.php" class="btn btn-primary" target="_blank" style="display: inline-block; padding: 0.75rem 1.5rem; text-decoration: none;">
                            <i class="fas fa-database"></i> Run Migrations Now
                        </a>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Course Filter -->
            <section class="course-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search courses..." id="courseSearch" value="<?php echo htmlspecialchars($search); ?>" onkeyup="if(event.key==='Enter'){filterCourses();}">
                        </div>
                        <div>
                            <select class="form-input" id="departmentFilter" onchange="filterCourses()">
                                <option value="">All Departments</option>
                                <option value="Computer Science" <?php echo ($departmentFilter==='Computer Science')? 'selected' : ''; ?>>Computer Science</option>
                                <option value="Mathematics" <?php echo ($departmentFilter==='Mathematics')? 'selected' : ''; ?>>Mathematics</option>
                                <option value="Physics" <?php echo ($departmentFilter==='Physics')? 'selected' : ''; ?>>Physics</option>
                                <option value="Engineering" <?php echo ($departmentFilter==='Engineering')? 'selected' : ''; ?>>Engineering</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="levelFilter" onchange="filterCourses()">
                                <option value="">All Levels</option>
                                <option value="100" <?php echo ($levelFilter==='100')? 'selected' : ''; ?>>100 Level</option>
                                <option value="200" <?php echo ($levelFilter==='200')? 'selected' : ''; ?>>200 Level</option>
                                <option value="300" <?php echo ($levelFilter==='300')? 'selected' : ''; ?>>300 Level</option>
                                <option value="400" <?php echo ($levelFilter==='400')? 'selected' : ''; ?>>400 Level</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterCourses()">
                                <option value="">All Status</option>
                                <option value="active" <?php echo ($statusFilter==='active')? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($statusFilter==='inactive')? 'selected' : ''; ?>>Inactive</option>
                                <option value="pending" <?php echo ($statusFilter==='pending')? 'selected' : ''; ?>>Pending</option>
                                <option value="archived" <?php echo ($statusFilter==='archived')? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Courses List -->
            <section class="courses-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-book" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Course Directory
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="exportCourses()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button class="btn btn-outline" onclick="bulkActions()">
                                <i class="fas fa-tasks"></i> Bulk Actions
                            </button>
                        </div>
                    </div>

                    <!-- Courses Table -->
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                                    <th>Course</th>
                                    <th>Code</th>
                                    <th>Department</th>
                                    <th>Instructor</th>
                                    <th>Students</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($courses)): ?>
                                    <?php foreach ($courses as $c): ?>
                                        <?php
                                        $instructorName = ($c['doctor_first_name'] && $c['doctor_last_name']) 
                                            ? 'Dr. ' . $c['doctor_first_name'] . ' ' . $c['doctor_last_name'] 
                                            : 'Not Assigned';
                                        $statusColor = [
                                            'active' => 'var(--success-color)',
                                            'inactive' => 'var(--error-color)',
                                            'pending' => 'var(--warning-color)',
                                            'archived' => 'var(--text-secondary)'
                                        ];
                                        $statusBg = $statusColor[$c['status']] ?? 'var(--text-secondary)';
                                        ?>
                                        <tr>
                                            <td><input type="checkbox" class="course-checkbox" value="<?php echo htmlspecialchars($c['id']); ?>"></td>
                                            <td>
                                                <div>
                                                    <div style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($c['course_name']); ?></div>
                                                    <div style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars(substr($c['description'] ?? 'No description', 0, 60)) . (strlen($c['description'] ?? '') > 60 ? '...' : ''); ?></div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($c['course_code']); ?></td>
                                            <td><?php echo htmlspecialchars($c['department'] ?? 'N/A'); ?></td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <div style="width: 30px; height: 30px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8rem;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <span style="font-size: 0.9rem;"><?php echo htmlspecialchars($instructorName); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <span style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;"><?php echo htmlspecialchars($c['student_count'] ?? 0); ?></span>
                                                    <span style="font-size: 0.8rem; color: var(--text-secondary);">students</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span style="background-color: <?php echo $statusBg; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; text-transform: capitalize;">
                                                    <?php echo htmlspecialchars($c['status'] ?? 'active'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 0.25rem;">
                                                    <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('<?php echo htmlspecialchars($c['id']); ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editCourse('<?php echo htmlspecialchars($c['id']); ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="deleteCourse('<?php echo htmlspecialchars($c['id']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8">No courses found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div style="display: flex; justify-content: between; align-items: center; padding: 1rem; border-top: 1px solid var(--border-color);">
                        <div style="color: var(--text-secondary); font-size: 0.9rem;">
                            Showing <?php echo count($courses); ?> of <?php echo htmlspecialchars($totalCourses); ?> courses
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="previousPage()">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-primary">1</button>
                            <button class="btn btn-outline">2</button>
                            <button class="btn btn-outline">3</button>
                            <span style="padding: 0.5rem;">...</span>
                            <button class="btn btn-outline">30</button>
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
                        <button class="btn btn-primary" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="createCourse()">
                            <i class="fas fa-plus" style="font-size: 2rem;"></i>
                            <span>Create Course</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="importCourses()">
                            <i class="fas fa-upload" style="font-size: 2rem;"></i>
                            <span>Import Courses</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="bulkActions()">
                            <i class="fas fa-tasks" style="font-size: 2rem;"></i>
                            <span>Bulk Actions</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="exportCourses()">
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

    <!-- Add/Edit Course Modal -->
    <div id="courseFormModal" class="modal" data-header-style="primary" hidden>
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2 id="courseModalTitle">Create Course</h2>
                <button class="modal-close" onclick="closeCourseFormModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="courseForm" onsubmit="handleCourseFormSubmit(event)">
                <input type="hidden" id="courseId" name="id">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Course Code *</label>
                        <input type="text" name="course_code" class="form-input" placeholder="e.g., CS101" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Credits</label>
                        <input type="number" name="credits" class="form-input" placeholder="3" min="1" max="6" value="3">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Course Name *</label>
                    <input type="text" name="course_name" class="form-input" placeholder="e.g., Introduction to Programming" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" rows="3" placeholder="Course description..."></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Department *</label>
                        <select name="department" class="form-input" required>
                            <option value="">Select Department</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Physics">Physics</option>
                            <option value="Engineering">Engineering</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Level</label>
                        <select name="level" class="form-input">
                            <option value="">Select Level</option>
                            <option value="100">100 Level</option>
                            <option value="200">200 Level</option>
                            <option value="300">300 Level</option>
                            <option value="400">400 Level</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Max Students</label>
                        <input type="number" name="max_students" class="form-input" placeholder="50" min="1" max="200" value="50">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-input">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Semester</label>
                    <input type="text" name="semester" class="form-input" placeholder="e.g., Fall 2024">
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Save Course
                    </button>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeCourseFormModal()">
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
                <h3>Course Management Chat</h3>
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
                        <input type="email" name="to" class="form-input" placeholder="instructor@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Course assignment" required>
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

        // Filter courses - server-side filtering
        function filterCourses() {
            const searchTerm = encodeURIComponent(document.getElementById('courseSearch').value.trim());
            const departmentFilter = encodeURIComponent(document.getElementById('departmentFilter').value);
            const levelFilter = encodeURIComponent(document.getElementById('levelFilter').value);
            const statusFilter = encodeURIComponent(document.getElementById('statusFilter').value);

            const params = [];
            if (searchTerm) params.push('search=' + searchTerm);
            if (departmentFilter) params.push('department=' + departmentFilter);
            if (levelFilter) params.push('level=' + levelFilter);
            if (statusFilter) params.push('status=' + statusFilter);

            const query = params.length ? ('?' + params.join('&')) : '';
            window.location.href = window.location.pathname + query;
        }

        // Toggle select all
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const courseCheckboxes = document.querySelectorAll('.course-checkbox');

            courseCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        // Course actions
        function viewCourse(courseId) {
            showNotification(`Viewing course ${courseId}...`, 'info');
        }

        function editCourse(courseId) {
            // Fetch course data and populate form
            fetch('/sis/public/api/courses.php?action=get&id=' + courseId)
                .then(r => r.json())
                .then(result => {
                    if (result.success && result.data) {
                        const course = result.data;
                        const form = document.getElementById('courseForm');
                        form.elements['course_code'].value = course.course_code || '';
                        form.elements['course_name'].value = course.course_name || '';
                        form.elements['description'].value = course.description || '';
                        form.elements['department'].value = course.department || '';
                        form.elements['level'].value = course.level || '';
                        form.elements['credits'].value = course.credits || 3;
                        form.elements['max_students'].value = course.max_students || 50;
                        form.elements['status'].value = course.status || 'active';
                        form.elements['semester'].value = course.semester || '';
                        document.getElementById('courseId').value = course.id;
                        document.getElementById('courseModalTitle').textContent = 'Edit Course';
                        openModal('courseFormModal');
                    } else {
                        showNotification('Failed to load course', 'error');
                    }
                })
                .catch(e => { console.error(e); showNotification('Error loading course', 'error'); });
        }

        function deleteCourse(courseId) {
            if (confirm('Are you sure you want to delete this course?')) {
                fetch('/sis/public/api/courses.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: courseId })
                })
                .then(r => r.json())
                .then(result => {
                    if (result.success) {
                        showNotification('Course deleted successfully', 'success');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showNotification(result.message || 'Failed to delete course', 'error');
                    }
                })
                .catch(e => { console.error(e); showNotification('An error occurred', 'error'); });
            }
        }

        // General actions
        function createCourse() {
            document.getElementById('courseForm').reset();
            document.getElementById('courseId').value = '';
            document.getElementById('courseModalTitle').textContent = 'Create Course';
            openModal('courseFormModal');
        }

        function importCourses() {
            showNotification('Opening course import dialog...', 'info');
        }

        function bulkActions() {
            const checkedBoxes = document.querySelectorAll('.course-checkbox:checked');
            if (checkedBoxes.length === 0) {
                showNotification('Please select at least one course', 'warning');
                return;
            }
            showNotification('Bulk actions functionality coming soon...', 'info');
        }

        function exportCourses() {
            // Export current filtered list as CSV
            const searchTerm = encodeURIComponent(document.getElementById('courseSearch').value.trim());
            const departmentFilter = encodeURIComponent(document.getElementById('departmentFilter').value);
            const levelFilter = encodeURIComponent(document.getElementById('levelFilter').value);
            const statusFilter = encodeURIComponent(document.getElementById('statusFilter').value);
            const params = [];
            if (searchTerm) params.push('search=' + searchTerm);
            if (departmentFilter) params.push('department=' + departmentFilter);
            if (levelFilter) params.push('level=' + levelFilter);
            if (statusFilter) params.push('status=' + statusFilter);
            params.push('export=csv');
            const query = params.length ? ('?' + params.join('&')) : '';
            window.location.href = window.location.pathname + query;
        }

        function refreshCourses() {
            showNotification('Refreshing course data...', 'info');
            setTimeout(() => {
                location.reload();
            }, 500);
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

        function closeCourseFormModal() {
            const modal = document.getElementById('courseFormModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('hidden', '');
            const overlay = document.getElementById('modalOverlay');
            overlay.classList.remove('active');
            overlay.setAttribute('hidden', '');
        }

        // Form submission handler
        async function handleCourseFormSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('courseForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            const courseId = data.id;
            delete data.id;

            // Convert level and credits to integers if present
            if (data.level) data.level = parseInt(data.level);
            if (data.credits) data.credits = parseInt(data.credits);
            if (data.max_students) data.max_students = parseInt(data.max_students);

            // Remove empty fields
            Object.keys(data).forEach(k => !data[k] && delete data[k]);

            try {
                const action = courseId ? 'update' : 'create';
                if (courseId) data.id = courseId;

                const response = await fetch('/sis/public/api/courses.php?action=' + action, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    showNotification(result.message || 'Course saved successfully', 'success');
                    closeCourseFormModal();
                    setTimeout(() => location.reload(), 500);
                } else {
                    showNotification(result.message || 'Failed to save course', 'error');
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
