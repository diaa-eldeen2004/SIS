<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors - Admin Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php
// Dynamic doctor listing powered by DB
require_once __DIR__ . '/../../core/Database.php';

$db = Database::getInstance()->getConnection();

// Read filters from query params
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$departmentFilter = isset($_GET['department']) ? trim($_GET['department']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Base where clause
$where = "WHERE 1=1";
$params = [];

if ($search !== '') {
    $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($departmentFilter !== '') {
    $where .= " AND department = ?";
    $params[] = $departmentFilter;
}

// Initialize default values
$totalDoctors = 0;
$doctorsThisMonth = 0;
$activeDoctors = 0;
$doctors = [];

// Check if doctors table exists, handle gracefully if it doesn't
try {
    // Count total doctors
    $countStmt = $db->prepare("SELECT COUNT(*) as cnt FROM doctors $where");
    $countStmt->execute($params);
    $totalDoctors = (int)$countStmt->fetchColumn();

    // Count doctors created this month
    $monthStmt = $db->prepare("SELECT COUNT(*) as cnt FROM doctors WHERE YEAR(created_at)=YEAR(CURRENT_DATE()) AND MONTH(created_at)=MONTH(CURRENT_DATE())");
    $monthStmt->execute();
    $doctorsThisMonth = (int)$monthStmt->fetchColumn();

    // Active doctors (assuming all are active for now, can add status field later)
    $activeStmt = $db->prepare("SELECT COUNT(*) as cnt FROM doctors");
    $activeStmt->execute();
    $activeDoctors = (int)$activeStmt->fetchColumn();

    // Fetch doctors rows (limit 100 for performance)
    $dataStmt = $db->prepare("SELECT id, first_name, last_name, email, phone, department, bio, created_at FROM doctors $where ORDER BY created_at DESC LIMIT 100");
    $dataStmt->execute($params);
    $doctors = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If doctors table doesn't exist, show friendly message
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $tableError = true;
        $errorMessage = "The doctors table doesn't exist yet. Please run the database migrations first.";
    } else {
        // Re-throw other database errors
        throw $e;
    }
}

// Handle export to CSV (only if table exists and we have data)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    if (!isset($tableError) && !empty($doctors)) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="doctors_export.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','First Name','Last Name','Email','Phone','Department','Bio','Created At']);
        foreach ($doctors as $d) {
            fputcsv($out, [
                $d['id'],
                $d['first_name'],
                $d['last_name'],
                $d['email'],
                $d['phone'] ?? '',
                $d['department'] ?? '',
                $d['bio'] ?? '',
                $d['created_at']
            ]);
        }
        fclose($out);
        exit;
    } else {
        // Redirect back if table doesn't exist
        header('Location: admin_manage_doctors.php');
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
            <a href="admin_manage_doctors.php" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Manage Doctors</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Add, update, and manage faculty accounts and course assignments.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshDoctors()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="addDoctor()">
                        <i class="fas fa-plus"></i> Add Doctor
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Doctor Statistics -->
            <section class="doctor-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($totalDoctors); ?></div>
                        <div style="color: var(--text-secondary);">Total Faculty</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($activeDoctors); ?></div>
                        <div style="color: var(--text-secondary);">Active Faculty</div>
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
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($doctorsThisMonth); ?></div>
                        <div style="color: var(--text-secondary);">New This Month</div>
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
                        <li>Or run the SQL file manually in your database: <code style="background: #f0f0f0; padding: 0.25rem 0.5rem; border-radius: 4px;">database/migrations/005_create_role_tables.sql</code></li>
                    </ol>
                    <div style="margin-top: 1.5rem;">
                        <a href="../../../run-migrations.php" class="btn btn-primary" target="_blank" style="display: inline-block; padding: 0.75rem 1.5rem; text-decoration: none;">
                            <i class="fas fa-database"></i> Run Migrations Now
                        </a>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Doctor Filter -->
            <section class="doctor-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search doctors..." id="doctorSearch" value="<?php echo htmlspecialchars($search); ?>" onkeyup="if(event.key==='Enter'){filterDoctors();}">
                        </div>
                        <div>
                            <select class="form-input" id="departmentFilter" onchange="filterDoctors()">
                                <option value="">All Departments</option>
                                <option value="Computer Science" <?php echo ($departmentFilter==='Computer Science')? 'selected' : ''; ?>>Computer Science</option>
                                <option value="Mathematics" <?php echo ($departmentFilter==='Mathematics')? 'selected' : ''; ?>>Mathematics</option>
                                <option value="Physics" <?php echo ($departmentFilter==='Physics')? 'selected' : ''; ?>>Physics</option>
                                <option value="Engineering" <?php echo ($departmentFilter==='Engineering')? 'selected' : ''; ?>>Engineering</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterDoctors()">
                                <option value="">All Status</option>
                                <option value="active" <?php echo ($statusFilter==='active')? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($statusFilter==='inactive')? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Doctors List -->
            <section class="doctors-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chalkboard-teacher" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Faculty Directory
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="exportDoctors()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button class="btn btn-outline" onclick="bulkActions()">
                                <i class="fas fa-tasks"></i> Bulk Actions
                            </button>
                        </div>
                    </div>

                    <!-- Doctors Table -->
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                                    <th>Doctor</th>
                                    <th>ID</th>
                                    <th>Department</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($doctors)): ?>
                                    <?php foreach ($doctors as $d): ?>
                                        <tr>
                                            <td><input type="checkbox" class="doctor-checkbox" value="<?php echo htmlspecialchars($d['id']); ?>"></td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <div style="width: 40px; height: 40px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="fas fa-user-md"></i>
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?></div>
                                                        <div style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars($d['email']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($d['id']); ?></td>
                                            <td><?php echo htmlspecialchars($d['department'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($d['email']); ?></td>
                                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($d['created_at']))); ?></td>
                                            <td>
                                                <div style="display: flex; gap: 0.25rem;">
                                                    <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewDoctor('<?php echo htmlspecialchars($d['id']); ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editDoctor('<?php echo htmlspecialchars($d['id']); ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="deleteDoctor('<?php echo htmlspecialchars($d['id']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7">No doctors found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div style="display: flex; justify-content: between; align-items: center; padding: 1rem; border-top: 1px solid var(--border-color);">
                        <div style="color: var(--text-secondary); font-size: 0.9rem;">
                            Showing <?php echo count($doctors); ?> of <?php echo htmlspecialchars($totalDoctors); ?> faculty members
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="previousPage()">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-primary">1</button>
                            <button class="btn btn-outline">2</button>
                            <button class="btn btn-outline">3</button>
                            <span style="padding: 0.5rem;">...</span>
                            <button class="btn btn-outline">17</button>
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
                        <button class="btn btn-primary" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="addDoctor()">
                            <i class="fas fa-plus" style="font-size: 2rem;"></i>
                            <span>Add Doctor</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="importDoctors()">
                            <i class="fas fa-upload" style="font-size: 2rem;"></i>
                            <span>Import Faculty</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="bulkActions()">
                            <i class="fas fa-tasks" style="font-size: 2rem;"></i>
                            <span>Bulk Actions</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="exportDoctors()">
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

    <!-- Add/Edit Doctor Modal -->
    <div id="doctorFormModal" class="modal" data-header-style="primary" hidden>
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 id="doctorModalTitle">Add Doctor</h2>
                <button class="modal-close" onclick="closeDoctorFormModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="doctorForm" onsubmit="handleDoctorFormSubmit(event)">
                <input type="hidden" id="doctorId" name="id">
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
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-input" placeholder="e.g., +1234567890">
                </div>
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
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-input" rows="3" placeholder="Brief biography..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Leave blank to auto-generate">
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Save Doctor
                    </button>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeDoctorFormModal()">
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
                <h3>Faculty Management Chat</h3>
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
                        <input type="email" name="to" class="form-input" placeholder="doctor@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Faculty notification" required>
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
            // Get current page URL
            const currentUrl = window.location.href;
            const url = new URL(currentUrl);
            const pathParts = url.pathname.split('/').filter(Boolean);
            
            // Find 'sis' or project root directory
            let rootIndex = pathParts.indexOf('sis');
            if (rootIndex === -1) {
                // If 'sis' not found, assume first directory is root
                rootIndex = 0;
            }
            
            // Build path: /sis/public/api/endpoint
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

        // Filter doctors - server-side filtering
        function filterDoctors() {
            const searchTerm = encodeURIComponent(document.getElementById('doctorSearch').value.trim());
            const departmentFilter = encodeURIComponent(document.getElementById('departmentFilter').value);
            const statusFilter = encodeURIComponent(document.getElementById('statusFilter').value);

            const params = [];
            if (searchTerm) params.push('search=' + searchTerm);
            if (departmentFilter) params.push('department=' + departmentFilter);
            if (statusFilter) params.push('status=' + statusFilter);

            const query = params.length ? ('?' + params.join('&')) : '';
            window.location.href = window.location.pathname + query;
        }

        // Toggle select all
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const doctorCheckboxes = document.querySelectorAll('.doctor-checkbox');

            doctorCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        // Doctor actions
        function viewDoctor(doctorId) {
            showNotification(`Viewing doctor ${doctorId}...`, 'info');
        }

        function editDoctor(doctorId) {
            // Fetch doctor data and populate form
            const apiPath = getApiPath('doctors.php?action=get&id=' + doctorId);
            fetch(apiPath)
                .then(r => r.json())
                .then(result => {
                    if (result.success && result.data) {
                        const doctor = result.data;
                        const form = document.getElementById('doctorForm');
                        form.elements['first_name'].value = doctor.first_name || '';
                        form.elements['last_name'].value = doctor.last_name || '';
                        form.elements['email'].value = doctor.email || '';
                        form.elements['phone'].value = doctor.phone || '';
                        form.elements['department'].value = doctor.department || '';
                        form.elements['bio'].value = doctor.bio || '';
                        document.getElementById('doctorId').value = doctor.id;
                        document.getElementById('doctorModalTitle').textContent = 'Edit Doctor';
                        openModal('doctorFormModal');
                    } else {
                        showNotification('Failed to load doctor', 'error');
                    }
                })
                .catch(e => { console.error(e); showNotification('Error loading doctor', 'error'); });
        }

        function deleteDoctor(doctorId) {
            if (confirm('Are you sure you want to delete this doctor?')) {
                const apiPath = getApiPath('doctors.php?action=delete');
                fetch(apiPath, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: doctorId })
                })
                .then(r => r.json())
                .then(result => {
                    if (result.success) {
                        showNotification('Doctor deleted successfully', 'success');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showNotification(result.message || 'Failed to delete doctor', 'error');
                    }
                })
                .catch(e => { console.error(e); showNotification('An error occurred', 'error'); });
            }
        }

        // General actions
        function addDoctor() {
            document.getElementById('doctorForm').reset();
            document.getElementById('doctorId').value = '';
            document.getElementById('doctorModalTitle').textContent = 'Add Doctor';
            openModal('doctorFormModal');
        }

        function importDoctors() {
            showNotification('Import functionality coming soon...', 'info');
        }

        function bulkActions() {
            const checkedBoxes = document.querySelectorAll('.doctor-checkbox:checked');
            if (checkedBoxes.length === 0) {
                showNotification('Please select at least one doctor', 'warning');
                return;
            }
            showNotification('Bulk actions functionality coming soon...', 'info');
        }

        function exportDoctors() {
            // Export current filtered list as CSV
            const searchTerm = encodeURIComponent(document.getElementById('doctorSearch').value.trim());
            const departmentFilter = encodeURIComponent(document.getElementById('departmentFilter').value);
            const params = [];
            if (searchTerm) params.push('search=' + searchTerm);
            if (departmentFilter) params.push('department=' + departmentFilter);
            params.push('export=csv');
            const query = params.length ? ('?' + params.join('&')) : '';
            window.location.href = window.location.pathname + query;
        }

        function refreshDoctors() {
            showNotification('Refreshing faculty data...', 'info');
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

        function closeDoctorFormModal() {
            const modal = document.getElementById('doctorFormModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('hidden', '');
            const overlay = document.getElementById('modalOverlay');
            overlay.classList.remove('active');
            overlay.setAttribute('hidden', '');
        }

        // Form submission handler
        async function handleDoctorFormSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('doctorForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            const doctorId = data.id;
            delete data.id;

            // Remove empty fields (but keep required fields)
            Object.keys(data).forEach(k => {
                if (k !== 'department' && k !== 'first_name' && k !== 'last_name' && k !== 'email' && !data[k]) {
                    delete data[k];
                }
            });

            try {
                const action = doctorId ? 'update' : 'create';
                if (doctorId) data.id = doctorId;

                // Get API path using helper function
                const apiPath = getApiPath('doctors.php?action=' + action);
                console.log('API Path:', apiPath); // Debug log
                const response = await fetch(apiPath, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                // Check if response is OK
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    try {
                        const errorJson = JSON.parse(errorText);
                        showNotification(errorJson.message || 'Failed to save doctor', 'error');
                    } catch {
                        showNotification('Server error: ' + response.status, 'error');
                    }
                    return;
                }

                const result = await response.json();
                if (result.success) {
                    showNotification(result.message || 'Doctor saved successfully', 'success');
                    closeDoctorFormModal();
                    setTimeout(() => location.reload(), 500);
                } else {
                    showNotification(result.message || 'Failed to save doctor', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred: ' + error.message, 'error');
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
