<?php
// Dynamic admin profile powered by DB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../core/Database.php';

$db = Database::getInstance()->getConnection();

// Get logged-in admin ID from session
$adminId = $_SESSION['user']['id'] ?? null;
$adminEmail = $_SESSION['user']['email'] ?? null;

// Initialize default values
$admin = null;
$totalStudents = 0;
$totalDoctors = 0;
$totalCourses = 0;
$totalReports = 0;
$studentsThisMonth = 0;
$doctorsThisMonth = 0;
$coursesThisSemester = 0;

// Fetch admin data
if ($adminId && $adminEmail) {
    try {
        // Try to get from admins table first
        $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, admin_level, permissions, created_at FROM admins WHERE id = ? OR email = ?");
        $stmt->execute([$adminId, $adminEmail]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If not found in admins table, try users table
        if (!$admin) {
            $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, created_at FROM users WHERE id = ? OR email = ?");
            $stmt->execute([$adminId, $adminEmail]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($admin) {
                $admin['admin_level'] = 'admin';
            }
        }
        
        // Fetch statistics
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM students");
        $totalStudents = (int)$stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM students WHERE YEAR(created_at)=YEAR(CURRENT_DATE()) AND MONTH(created_at)=MONTH(CURRENT_DATE())");
        $studentsThisMonth = (int)$stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM doctors");
        $totalDoctors = (int)$stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM doctors WHERE YEAR(created_at)=YEAR(CURRENT_DATE()) AND MONTH(created_at)=MONTH(CURRENT_DATE())");
        $doctorsThisMonth = (int)$stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM courses");
        $totalCourses = (int)$stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM courses WHERE YEAR(created_at)=YEAR(CURRENT_DATE()) AND MONTH(created_at)=MONTH(CURRENT_DATE())");
        $coursesThisSemester = (int)$stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM reports");
        $totalReports = (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        // Handle gracefully if tables don't exist
        error_log('Profile page database error: ' . $e->getMessage());
    }
}

// Default values if admin not found
if (!$admin) {
    $admin = [
        'first_name' => 'Admin',
        'last_name' => 'User',
        'email' => $adminEmail ?? 'admin@university.edu',
        'phone' => '',
        'admin_level' => 'admin',
        'permissions' => null
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Admin Portal</title>
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
            <a href="admin_reports.php" class="nav-item">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="admin_calendar.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Calendar
            </a>
            <a href="admin_profile.php" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Admin Profile</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Manage your administrative account and system preferences.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshProfile()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="editProfile()">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Profile Overview -->
            <section class="profile-overview" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; align-items: center; gap: 2rem; padding: 2rem;">
                        <div style="text-align: center;">
                            <div style="width: 120px; height: 120px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; margin-bottom: 1rem;">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <button class="btn btn-outline" onclick="changeProfilePicture()">
                                <i class="fas fa-camera"></i> Change Photo
                            </button>
                        </div>
                        <div style="flex: 1;">
                            <h2 style="margin: 0 0 0.5rem 0; color: var(--text-primary);"><?php echo htmlspecialchars(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? '')); ?></h2>
                            <p style="margin: 0 0 1rem 0; color: var(--text-secondary); font-size: 1.1rem;">System Administrator</p>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Access Level</div>
                                    <div style="font-size: 1.1rem; font-weight: 600; color: var(--accent-color);"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $admin['admin_level'] ?? 'admin'))); ?></div>
                                </div>
                                <div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Email</div>
                                    <div style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($admin['email'] ?? 'N/A'); ?></div>
                                </div>
                                <div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Phone</div>
                                    <div style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($admin['phone'] ?? 'N/A'); ?></div>
                                </div>
                                <div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Member Since</div>
                                    <div style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary);"><?php echo $admin['created_at'] ? htmlspecialchars(date('M Y', strtotime($admin['created_at']))) : 'N/A'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- System Statistics -->
            <section class="system-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($totalStudents); ?></div>
                        <div style="color: var(--text-secondary);">Students Managed</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($totalDoctors); ?></div>
                        <div style="color: var(--text-secondary);">Faculty Managed</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($totalCourses); ?></div>
                        <div style="color: var(--text-secondary);">Courses Managed</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($totalReports); ?></div>
                        <div style="color: var(--text-secondary);">Reports Generated</div>
                    </div>
                </div>
            </section>

            <!-- Profile Information -->
            <div class="grid grid-2" style="gap: 2rem;">
                <!-- Personal Information -->
                <section class="personal-info">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-user" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                                Personal Information
                            </h2>
                            <button class="btn btn-outline" onclick="editPersonalInfo()">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                        <div class="info-list">
                            <div class="info-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Full Name</div>
                                    <div style="font-weight: 600; color: var(--text-primary);" id="displayName"><?php echo htmlspecialchars(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? '')); ?></div>
                                </div>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editField('name')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                            <div class="info-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Email Address</div>
                                    <div style="font-weight: 600; color: var(--text-primary);" id="displayEmail"><?php echo htmlspecialchars($admin['email'] ?? 'N/A'); ?></div>
                                </div>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editField('email')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                            <div class="info-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Phone Number</div>
                                    <div style="font-weight: 600; color: var(--text-primary);" id="displayPhone"><?php echo htmlspecialchars($admin['phone'] ?? 'N/A'); ?></div>
                                </div>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editField('phone')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                            <div class="info-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Access Level</div>
                                    <div style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $admin['admin_level'] ?? 'admin'))); ?></div>
                                </div>
                            </div>
                            <div class="info-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                                <div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Member Since</div>
                                    <div style="font-weight: 600; color: var(--text-primary);"><?php echo $admin['created_at'] ? htmlspecialchars(date('F Y', strtotime($admin['created_at']))) : 'N/A'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- System Access -->
                <section class="system-access">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-shield-alt" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                                System Access
                            </h2>
                            <button class="btn btn-outline" onclick="managePermissions()">
                                <i class="fas fa-cog"></i> Manage
                            </button>
                        </div>
                        <div class="access-list">
                            <div class="access-item" style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <h4 style="margin: 0; color: var(--text-primary);">Student Management</h4>
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Full Access</span>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                    <div style="text-align: center;">
                                        <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);"><?php echo htmlspecialchars($totalStudents); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Students</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="font-size: 1.2rem; font-weight: 700; color: var(--accent-color);">0</div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Pending</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);"><?php echo htmlspecialchars($studentsThisMonth); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">New This Month</div>
                                    </div>
                                </div>
                            </div>
                            <div class="access-item" style="padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <h4 style="margin: 0; color: var(--text-primary);">Faculty Management</h4>
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Full Access</span>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                    <div style="text-align: center;">
                                        <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);"><?php echo htmlspecialchars($totalDoctors); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Faculty</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="font-size: 1.2rem; font-weight: 700; color: var(--accent-color);">0</div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Pending</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);"><?php echo htmlspecialchars($doctorsThisMonth); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">New This Month</div>
                                    </div>
                                </div>
                            </div>
                            <div class="access-item" style="padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <h4 style="margin: 0; color: var(--text-primary);">Course Management</h4>
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Full Access</span>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                    <div style="text-align: center;">
                                        <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);"><?php echo htmlspecialchars($totalCourses); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Courses</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="font-size: 1.2rem; font-weight: 700; color: var(--accent-color);">0</div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Pending</div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);"><?php echo htmlspecialchars($coursesThisSemester); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);">New This Semester</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Password Management -->
            <section class="password-management" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-lock" style="color: var(--error-color); margin-right: 0.5rem;"></i>
                            Password Management
                        </h2>
                    </div>
                    <div style="padding: 1.5rem;">
                        <form class="password-form" id="passwordForm" onsubmit="handlePasswordUpdate(event)">
                            <div class="form-group">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" id="currentPassword" class="form-input" placeholder="Enter current password" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" id="newPassword" class="form-input" placeholder="Enter new password" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirmPassword" class="form-input" placeholder="Confirm new password" required>
                            </div>
                            <div style="display: flex; gap: 1rem;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Password
                                </button>
                                <button type="button" class="btn btn-outline" onclick="resetPassword()">
                                    <i class="fas fa-undo"></i> Reset Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <!-- System Settings -->
            <section class="system-settings" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-cog" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            System Settings
                        </h2>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                            <div>
                                <h4 style="margin-bottom: 1rem; color: var(--text-primary);">Notification Preferences</h4>
                                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" checked>
                                        <span>System alerts and warnings</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" checked>
                                        <span>User registration notifications</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" checked>
                                        <span>Security breach alerts</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox">
                                        <span>Daily system reports</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <h4 style="margin-bottom: 1rem; color: var(--text-primary);">Security Settings</h4>
                                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" checked>
                                        <span>Two-factor authentication</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" checked>
                                        <span>Session timeout (30 minutes)</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" checked>
                                        <span>Login attempt monitoring</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox">
                                        <span>IP address restrictions</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 1.5rem;">
                            <button class="btn btn-primary" onclick="saveSettings()">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Modal Overlay (shared for all modals) -->
    <div id="modalOverlay" class="modal-overlay" onclick="closeAllModals()" hidden></div>

    <!-- Edit Profile Modal -->
    <div id="profileEditModal" class="modal" data-header-style="primary" hidden>
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Edit Profile</h2>
                <button class="modal-close" onclick="closeProfileEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="profileEditForm" onsubmit="handleProfileEditSubmit(event)">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" id="editFirstName" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" id="editLastName" class="form-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" id="editEmail" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" id="editPhone" class="form-input" placeholder="e.g., +1234567890">
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeProfileEditModal()">
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
                <h3>Admin Profile Chat</h3>
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
                        <input type="text" name="subject" class="form-input" placeholder="System notification" required>
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

        // Profile actions
        function editProfile() {
            openProfileEditModal();
        }

        function editPersonalInfo() {
            openProfileEditModal();
        }

        function editField(fieldName) {
            openFieldEditModal(fieldName);
        }

        async function handlePasswordUpdate(e) {
            e.preventDefault();
            const form = document.getElementById('passwordForm');
            const formData = new FormData(form);
            const currentPassword = formData.get('current_password');
            const newPassword = formData.get('new_password');
            const confirmPassword = formData.get('confirm_password');

            if (newPassword !== confirmPassword) {
                showNotification('New passwords do not match', 'error');
                return;
            }

            if (newPassword.length < 8) {
                showNotification('Password must be at least 8 characters long', 'error');
                return;
            }

            try {
                const response = await fetch('/sis/public/api/auth.php?action=update-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        current_password: currentPassword,
                        new_password: newPassword
                    })
                });

                const result = await response.json();
                if (result.success) {
                    showNotification('Password updated successfully!', 'success');
                    form.reset();
                } else {
                    showNotification(result.message || 'Failed to update password', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            }
        }

        function openProfileEditModal() {
            // Populate form with current admin data
            document.getElementById('editFirstName').value = '<?php echo htmlspecialchars($admin['first_name'] ?? ''); ?>';
            document.getElementById('editLastName').value = '<?php echo htmlspecialchars($admin['last_name'] ?? ''); ?>';
            document.getElementById('editEmail').value = '<?php echo htmlspecialchars($admin['email'] ?? ''); ?>';
            document.getElementById('editPhone').value = '<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>';
            openModal('profileEditModal');
        }

        function openFieldEditModal(fieldName) {
            // For now, open the full profile edit modal
            openProfileEditModal();
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

        function closeProfileEditModal() {
            const modal = document.getElementById('profileEditModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('hidden', '');
            const overlay = document.getElementById('modalOverlay');
            overlay.classList.remove('active');
            overlay.setAttribute('hidden', '');
        }

        // Form submission handler for profile edit
        async function handleProfileEditSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('profileEditForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Remove empty fields
            Object.keys(data).forEach(k => !data[k] && delete data[k]);

            try {
                // Determine if admin is in admins table or users table
                const adminId = <?php echo $adminId ?? 'null'; ?>;
                if (!adminId) {
                    showNotification('Admin ID not found', 'error');
                    return;
                }

                // Try to update in admins table first, fallback to users table
                let response = await fetch('/sis/public/api/admins.php?action=update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: adminId, ...data })
                });

                let result = await response.json();
                
                // If admins API fails, try updating via users API
                if (!result.success) {
                    response = await fetch('/sis/public/api/users.php?action=update', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: adminId, ...data })
                    });
                    result = await response.json();
                }

                if (result.success) {
                    showNotification('Profile updated successfully', 'success');
                    // Update display immediately
                    if (data.first_name && data.last_name) {
                        document.getElementById('displayName').textContent = data.first_name + ' ' + data.last_name;
                    }
                    if (data.email) {
                        document.getElementById('displayEmail').textContent = data.email;
                    }
                    if (data.phone !== undefined) {
                        document.getElementById('displayPhone').textContent = data.phone || 'N/A';
                    }
                    closeProfileEditModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(result.message || 'Failed to update profile', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            }
        }

        function changeProfilePicture() {
            showNotification('Opening profile picture upload...', 'info');
        }

        function viewLoginHistory() {
            showNotification('Opening login history...', 'info');
        }

        function managePermissions() {
            showNotification('Opening permission management...', 'info');
        }

        function resetPassword() {
            if (confirm('Are you sure you want to reset your password? You will receive an email with instructions.')) {
                showNotification('Password reset email sent!', 'success');
            }
        }

        function saveSettings() {
            showNotification('Settings saved successfully!', 'success');
        }

        function refreshProfile() {
            showNotification('Refreshing profile data...', 'info');
            setTimeout(() => {
                showNotification('Profile refreshed successfully', 'success');
            }, 1000);
        }
    </script>
</body>
</html>
