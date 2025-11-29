<?php
// Start session if not already started and detect login state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = false;
// Primary session check
if (!empty($_SESSION['user'])) {
    $isLoggedIn = true;
}
// Optional: also allow token-based check (if your app sets a token cookie)
if (!$isLoggedIn && !empty($_COOKIE['token'])) {
    $isLoggedIn = true;
}

// Determine user role when available. Assumption: role is stored in $_SESSION['user']['role']
$userRole = null;
if (!empty($_SESSION['user']) && !empty($_SESSION['user']['role'])) {
    $userRole = $_SESSION['user']['role'];
}

// DEBUG: Log what's in the session for troubleshooting (remove when working)
error_log("home.php DEBUG - isLoggedIn: " . ($isLoggedIn ? 'yes' : 'no') . ", userRole: " . ($userRole ?? 'NULL') . ", session: " . json_encode($_SESSION['user'] ?? []));

// Determine an effective role to use for UI decisions:
// - If a role is present in the session use it
// - Else if user is logged in (token/session present) assume a generic 'user'
// - Else no effective role (not logged in)
$effectiveRole = null;
if (!empty($userRole)) {
    $effectiveRole = $userRole;
} elseif ($isLoggedIn) {
    $effectiveRole = 'user';
}

// Map roles to placeholder destinations and human labels. Replace placeholders later with real paths.
$roleDestinations = [
    // Relative paths from app/views/home.php to the dashboard .php files
    'doctor' => '../doctor/doctor_dashboard.php',
    'admin' => '../admin/admin_dashboard.php',
    'student' => '../student/student_dashboard.php',
    'advisor' => '../advisor/advisor_dashboard.php',
    'it' => '../it/it_dashboard.php',
    // Generic logged-in user should return to home as requested
    'user' => 'home.php'
];

$roleLabels = [
    'doctor' => 'Doctor Dashboard',
    'admin' => 'Admin Dashboard',
    'student' => 'Student Dashboard',
    'advisor' => 'Advisor Dashboard',
    'it' => 'IT Dashboard',
    'user' => 'Profile'
];

$dashboardDataDestination = '';
$dashboardLabel = 'Dashboard';
if (!empty($effectiveRole)) {
    if (!empty($roleDestinations[$effectiveRole])) {
        $dashboardDataDestination = $roleDestinations[$effectiveRole];
    }
    if (!empty($roleLabels[$effectiveRole])) {
        $dashboardLabel = $roleLabels[$effectiveRole];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Portal - Campus Management System</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" title="Switch to dark mode">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-graduation-cap"></i>
            University Portal
        </div>
       <ul class="navbar-nav">
            <li><a href="home.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="help_center.php">Help</a></li> 
            <?php if (!empty($effectiveRole)): ?>
            <li><a href="#" class="btn btn-primary dashboard-btn" data-destination="<?php echo htmlspecialchars($dashboardDataDestination); ?>"><?php echo htmlspecialchars($dashboardLabel); ?></a></li>
            <?php else: ?>
            <li><a href="auth/auth_login.php" class="btn btn-primary">Login</a></li>
            <?php endif; ?>
        </ul>
          
        
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <h1>Welcome to University Portal</h1>
            <p>Your comprehensive campus management system for students, doctors, and administrators</p>
            <div class="hero-buttons">
                <?php if (!empty($effectiveRole)): ?>
                <a href="#" class="btn btn-primary dashboard-btn" data-destination="<?php echo htmlspecialchars($dashboardDataDestination); ?>"><?php echo htmlspecialchars($dashboardLabel); ?></a>
                <?php else: ?>
                <a href="auth/auth_login.php" class="btn btn-primary">Get Started</a>
                <?php endif; ?>
                <a href="app/about.html" class="btn btn-outline">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" style="padding: 4rem 2rem; background-color: var(--surface-color);">
        <div class="container">
            <h2 class="text-center mb-4">Why Choose Our Portal?</h2>
            <div class="grid grid-3">
                <div class="card text-center">
                    <div class="card-icon" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Multi-Role Support</h3>
                    <p>Seamlessly manage different user roles - students, doctors, and administrators - all in one unified platform.</p>
                </div>
                <div class="card text-center">
                    <div class="card-icon" style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1rem;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Smart Calendar</h3>
                    <p>Integrated calendar system for assignments, events, and important dates with automatic synchronization.</p>
                </div>
                <div class="card text-center">
                    <div class="card-icon" style="font-size: 3rem; color: var(--success-color); margin-bottom: 1rem;">
                        <i class="fas fa-file-upload"></i>
                    </div>
                    <h3>File Management</h3>
                    <p>Easy upload and management of course materials, assignments, and documents with organized sections.</p>
                </div>
            </div>
        </div>
    </section>

   
    <!-- Statistics Section -->
    <section class="stats-section" style="padding: 4rem 2rem; background-color: var(--primary-color); color: white;">
        <div class="container">
            <h2 class="text-center mb-4">Portal Statistics</h2>
            <div class="grid grid-4">
                <div class="text-center">
                    <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;">1,250+</div>
                    <div style="font-size: 1.2rem; opacity: 0.9;">Active Students</div>
                </div>
                <div class="text-center">
                    <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;">85+</div>
                    <div style="font-size: 1.2rem; opacity: 0.9;">Faculty Members</div>
                </div>
                <div class="text-center">
                    <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;">150+</div>
                    <div style="font-size: 1.2rem; opacity: 0.9;">Courses Available</div>
                </div>
                <div class="text-center">
                    <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;">98%</div>
                    <div style="font-size: 1.2rem; opacity: 0.9;">User Satisfaction</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section" style="padding: 4rem 2rem; text-align: center;">
        <div class="container">
            <h2>Ready to Get Started?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; color: var(--text-secondary);">
                Join thousands of students and faculty members who are already using our portal
            </p>
            <div class="cta-buttons">
                <?php if (!empty($effectiveRole)): ?>
                <a href="#" class="btn btn-primary dashboard-btn" style="margin-right: 1rem;" data-destination="<?php echo htmlspecialchars($dashboardDataDestination); ?>"><?php echo htmlspecialchars($dashboardLabel); ?></a>
                <?php else: ?>
                <a href="auth/auth_login.php" class="btn btn-primary" style="margin-right: 1rem;">Login Now</a>
                <a href="auth/auth_sign.php" class="btn btn-outline">Create Account</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>University Portal</h3>
                <p>Comprehensive campus management system designed to streamline academic operations and enhance the learning experience.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="home.php">Home</a>
                <a href="app/about.html">About Us</a>
                <a href="app/contact.html">Contact</a>
                <a href="app/help_center.html">Help Center</a>
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
    <script src="js/main.js"></script>
    <script>
    // Dashboard button handler: navigates to data-destination when configured.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.dashboard-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                var dest = btn.getAttribute('data-destination');
                if (dest && dest.trim() !== '') {
                    // If you later prefer server-side links, replace data-destination with href values.
                    window.location.href = dest;
                } else {
                    // Placeholder behavior when destination is not yet configured.
                    e.preventDefault();
                    alert('Dashboard path is not configured for your role. Please set the data-destination attribute (e.g. PUT_PATH_FOR_DOCTOR).');
                }
            });
        });
    });
    </script>
</body>
</html>
