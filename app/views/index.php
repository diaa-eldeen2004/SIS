<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Portal - Campus Management System</title>
    <link rel="stylesheet" href="../css/styles.css">
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
            <li><a href="#home">Home</a></li>
            <li><a href="app/about.html">About Us</a></li>
            <li><a href="app/contact.html">Contact</a></li>
            <li><a href="app/help_center.html">Help</a></li>
            <li><a href="auth/auth_login.html" class="btn btn-primary">Login</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <h1>Welcome to University Portal</h1>
            <p>Your comprehensive campus management system for students, doctors, and administrators</p>
            <div class="hero-buttons">
                <a href="auth/auth_login.html" class="btn btn-primary">Get Started</a>
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

    <!-- Role-Based Features -->
    <section class="roles-section" style="padding: 4rem 2rem;">
        <div class="container">
            <h2 class="text-center mb-4">Designed for Everyone</h2>
            <div class="grid grid-3">
                <!-- Student Features -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title" style="color: var(--primary-color);">
                            <i class="fas fa-user-graduate"></i> For Students
                        </h3>
                    </div>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Course enrollment and management
                        </li>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Assignment submission and tracking
                        </li>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Attendance monitoring
                        </li>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Grade tracking and notifications
                        </li>
                        <li style="padding: 0.5rem 0;">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Real-time communication with instructors
                        </li>
                    </ul>
                </div>

                <!-- Doctor Features -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title" style="color: var(--accent-color);">
                            <i class="fas fa-chalkboard-teacher"></i> For Doctors
                        </h3>
                    </div>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Course content management
                        </li>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Assignment creation and grading
                        </li>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Attendance marking and tracking
                        </li>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Student calendar management
                        </li>
                        <li style="padding: 0.5rem 0;">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Notification and announcement system
                        </li>
                    </ul>
                </div>

                <!-- Admin Features -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title" style="color: var(--error-color);">
                            <i class="fas fa-user-shield"></i> For Administrators
                        </h3>
                    </div>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            User account management
                        </li>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Course and curriculum oversight
                        </li>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            System-wide calendar control
                        </li>
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Comprehensive reporting and analytics
                        </li>
                        <li style="padding: 0.5rem 0;">
                            <i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            System configuration and maintenance
                        </li>
                    </ul>
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
                <a href="auth/auth_login.html" class="btn btn-primary" style="margin-right: 1rem;">Login Now</a>
                <a href="auth/auth_signup.html" class="btn btn-outline">Create Account</a>
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
                <a href="#home">Home</a>
                <a href="app/about.html">About Us</a>
                <a href="app/contact.html">Contact</a>
                <a href="app/help_center.html">Help Center</a>
            </div>
            <div class="footer-section">
                <h3>User Access</h3>
                <a href="auth/auth_login.html">Student Login</a>
                <a href="auth/auth_login.html">Doctor Login</a>
                <a href="auth/auth_login.html">Admin Login</a>
                <a href="auth/auth_signup.html">Register</a>
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
