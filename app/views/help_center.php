<?php
// Start session and determine login state for conditional UI
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = false;
if (!empty($_SESSION['user'])) {
    $isLoggedIn = true;
}
if (!$isLoggedIn && !empty($_COOKIE['token'])) {
    $isLoggedIn = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - University Portal</title>
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
             <?php if (!$isLoggedIn): ?>
            <li><a href="auth/auth_login.php" class="btn btn-primary">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Main Content -->
    <main style="padding: 4rem 2rem;">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <!-- Hero Section -->
            <section class="hero" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; padding: 4rem 2rem; border-radius: 16px; margin-bottom: 4rem; text-align: center;">
                <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 700;">Help Center</h1>
                <p style="font-size: 1.2rem; opacity: 0.9;">Find answers to common questions and learn how to use the portal effectively.</p>
            </section>

            <!-- Search Section -->
            <section class="search-section" style="margin-bottom: 4rem;">
                <div class="card" style="padding: 2rem; text-align: center;">
                    <h2 style="margin-bottom: 1rem; color: var(--text-primary);">Search Help Articles</h2>
                    <div style="display: flex; gap: 1rem; max-width: 600px; margin: 0 auto;">
                        <input type="text" id="searchInput" placeholder="Search for help topics..." class="form-input" style="flex: 1;">
                        <button class="btn btn-primary" onclick="searchHelp()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Quick Help Categories -->
            <section class="help-categories" style="margin-bottom: 4rem;">
                <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 3rem; color: var(--text-primary);">Quick Help</h2>
                <div class="grid grid-3">
                    <div class="card" style="text-align: center; padding: 2rem; cursor: pointer;" onclick="showHelpSection('getting-started')">
                        <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <h3 style="margin-bottom: 1rem;">Getting Started</h3>
                        <p style="color: var(--text-secondary);">Learn the basics of using the portal</p>
                    </div>
                    <div class="card" style="text-align: center; padding: 2rem; cursor: pointer;" onclick="showHelpSection('account-management')">
                        <div style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1rem;">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <h3 style="margin-bottom: 1rem;">Account Management</h3>
                        <p style="color: var(--text-secondary);">Manage your profile and settings</p>
                    </div>
                    <div class="card" style="text-align: center; padding: 2rem; cursor: pointer;" onclick="showHelpSection('technical-support')">
                        <div style="font-size: 3rem; color: var(--success-color); margin-bottom: 1rem;">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3 style="margin-bottom: 1rem;">Technical Support</h3>
                        <p style="color: var(--text-secondary);">Troubleshoot technical issues</p>
                    </div>
                </div>
            </section>

            <!-- Help Sections -->
            <section class="help-sections">
                <!-- Getting Started -->
                <div id="getting-started" class="help-section" style="margin-bottom: 4rem;">
                    <h2 style="font-size: 2rem; margin-bottom: 2rem; color: var(--text-primary);">
                        <i class="fas fa-play-circle" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                        Getting Started
                    </h2>
                    <div class="grid grid-2">
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">First Time Login</h3>
                            <ol style="color: var(--text-secondary); line-height: 1.8; padding-left: 1.5rem;">
                                <li>Go to the login page</li>
                                <li>Enter your email and password</li>
                                <li>Select your role (Student/Doctor/Admin)</li>
                                <li>Click "Sign In"</li>
                                <li>Complete your profile setup</li>
                            </ol>
                        </div>
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Dashboard Overview</h3>
                            <ul style="color: var(--text-secondary); line-height: 1.8; padding-left: 1.5rem;">
                                <li>View your courses and assignments</li>
                                <li>Check notifications and messages</li>
                                <li>Access calendar and events</li>
                                <li>Manage your profile settings</li>
                                <li>Use the chat feature for communication</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Account Management -->
                <div id="account-management" class="help-section" style="margin-bottom: 4rem;">
                    <h2 style="font-size: 2rem; margin-bottom: 2rem; color: var(--text-primary);">
                        <i class="fas fa-user-cog" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                        Account Management
                    </h2>
                    <div class="grid grid-2">
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Updating Profile</h3>
                            <ol style="color: var(--text-secondary); line-height: 1.8; padding-left: 1.5rem;">
                                <li>Go to your profile page</li>
                                <li>Click "Edit Profile"</li>
                                <li>Update your information</li>
                                <li>Save changes</li>
                            </ol>
                        </div>
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Changing Password</h3>
                            <ol style="color: var(--text-secondary); line-height: 1.8; padding-left: 1.5rem;">
                                <li>Go to Settings</li>
                                <li>Click "Change Password"</li>
                                <li>Enter current password</li>
                                <li>Enter new password</li>
                                <li>Confirm new password</li>
                                <li>Save changes</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Technical Support -->
                <div id="technical-support" class="help-section" style="margin-bottom: 4rem;">
                    <h2 style="font-size: 2rem; margin-bottom: 2rem; color: var(--text-primary);">
                        <i class="fas fa-tools" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                        Technical Support
                    </h2>
                    <div class="grid grid-2">
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Browser Compatibility</h3>
                            <p style="color: var(--text-secondary); margin-bottom: 1rem;">Supported browsers:</p>
                            <ul style="color: var(--text-secondary); line-height: 1.8; padding-left: 1.5rem;">
                                <li>Chrome (latest version)</li>
                                <li>Firefox (latest version)</li>
                                <li>Safari (latest version)</li>
                                <li>Edge (latest version)</li>
                            </ul>
                        </div>
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Mobile Access</h3>
                            <p style="color: var(--text-secondary); line-height: 1.8;">
                                The portal is fully responsive and works on all mobile devices. 
                                For the best experience, use the latest version of your mobile browser.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Role-Specific Help -->
                <section class="role-help" style="margin-bottom: 4rem;">
                    <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 3rem; color: var(--text-primary);">Role-Specific Help</h2>
                    <div class="grid grid-3">
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem; text-align: center;">
                                <i class="fas fa-user-graduate"></i>
                                Student Guide
                            </h3>
                            <ul style="color: var(--text-secondary); line-height: 1.8;">
                                <li>Enrolling in courses</li>
                                <li>Submitting assignments</li>
                                <li>Checking grades</li>
                                <li>Viewing attendance</li>
                                <li>Using the calendar</li>
                                <li>Communicating with instructors</li>
                            </ul>
                        </div>
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--accent-color); margin-bottom: 1rem; text-align: center;">
                                <i class="fas fa-chalkboard-teacher"></i>
                                Doctor Guide
                            </h3>
                            <ul style="color: var(--text-secondary); line-height: 1.8;">
                                <li>Managing course content</li>
                                <li>Creating assignments</li>
                                <li>Grading submissions</li>
                                <li>Taking attendance</li>
                                <li>Managing student calendars</li>
                                <li>Sending notifications</li>
                            </ul>
                        </div>
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--error-color); margin-bottom: 1rem; text-align: center;">
                                <i class="fas fa-user-shield"></i>
                                Admin Guide
                            </h3>
                            <ul style="color: var(--text-secondary); line-height: 1.8;">
                                <li>Managing user accounts</li>
                                <li>Creating courses</li>
                                <li>Assigning instructors</li>
                                <li>Generating reports</li>
                                <li>System configuration</li>
                                <li>Calendar management</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- FAQ Section -->
                <section class="faq-section" style="margin-bottom: 4rem;">
                    <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 3rem; color: var(--text-primary);">Frequently Asked Questions</h2>
                    <div class="grid grid-2">
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-question-circle"></i>
                                How do I reset my password?
                            </h3>
                            <p style="color: var(--text-secondary); line-height: 1.6;">
                                Click on "Forgot Password" on the login page and enter your email address. 
                                You'll receive a reset link within a few minutes. Check your spam folder if you don't see it.
                            </p>
                        </div>
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-question-circle"></i>
                                Can I access the portal on mobile?
                            </h3>
                            <p style="color: var(--text-secondary); line-height: 1.6;">
                                Yes! Our portal is fully responsive and works perfectly on all mobile devices, 
                                tablets, and computers. No app installation required.
                            </p>
                        </div>
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-question-circle"></i>
                                How do I contact my instructor?
                            </h3>
                            <p style="color: var(--text-secondary); line-height: 1.6;">
                                Use the chat feature in your dashboard or send an email through the messaging system. 
                                Your instructor will respond within 24 hours during business days.
                            </p>
                        </div>
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-question-circle"></i>
                                Is my data secure?
                            </h3>
                            <p style="color: var(--text-secondary); line-height: 1.6;">
                                Absolutely! We use enterprise-grade security measures including SSL encryption, 
                                regular security audits, and comply with educational data protection standards.
                            </p>
                        </div>
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-question-circle"></i>
                                How do I upload assignments?
                            </h3>
                            <p style="color: var(--text-secondary); line-height: 1.6;">
                                Go to the Assignments page, find your assignment, click "Submit", 
                                upload your file, and click "Submit Assignment". You'll receive a confirmation.
                            </p>
                        </div>
                        <div class="card" style="padding: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-question-circle"></i>
                                What file types are supported?
                            </h3>
                            <p style="color: var(--text-secondary); line-height: 1.6;">
                                We support PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, and image files (JPG, PNG, GIF). 
                                Maximum file size is 50MB per file.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Contact Support -->
                <section class="contact-support" style="background-color: var(--surface-color); padding: 3rem 2rem; border-radius: 16px; text-align: center;">
                    <h2 style="font-size: 2rem; margin-bottom: 1rem; color: var(--text-primary);">Still Need Help?</h2>
                    <p style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 2rem;">
                        Our support team is here to help you 24/7
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="contact.html" class="btn btn-primary">
                            <i class="fas fa-envelope"></i>
                            Contact Support
                        </a>
                        <a href="mailto:support@university.edu" class="btn btn-outline">
                            <i class="fas fa-paper-plane"></i>
                            Email Us
                        </a>
                        <a href="tel:+15551234567" class="btn btn-outline">
                            <i class="fas fa-phone"></i>
                            Call Us
                        </a>
                    </div>
                </section>
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
                <a href="index.html">Home</a>
                <a href="about.html">About Us</a>
                <a href="contact.html">Contact</a>
                <a href="help_center.html">Help Center</a>
            </div>
            <div class="footer-section">
                <h3>User Access</h3>
                <a href="../auth/auth_login.html">Student Login</a>
                <a href="../auth/auth_login.html">Doctor Login</a>
                <a href="../auth/auth_login.html">Admin Login</a>
                <a href="../auth/auth_signup.html">Register</a>
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
        // Search functionality
        function searchHelp() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            if (!searchTerm) {
                showNotification('Please enter a search term', 'warning');
                return;
            }
            
            // Simple search simulation
            showNotification(`Searching for "${searchTerm}"...`, 'info');
            
            // In a real implementation, this would search through help articles
            setTimeout(() => {
                showNotification('Search completed. Found 5 relevant articles.', 'success');
            }, 1000);
        }

        // Show help section
        function showHelpSection(sectionId) {
            const sections = document.querySelectorAll('.help-section');
            sections.forEach(section => {
                section.style.display = 'none';
            });
            
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.style.display = 'block';
                targetSection.scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Initialize help sections
        document.addEventListener('DOMContentLoaded', function() {
            // Show getting started by default
            showHelpSection('getting-started');
        });

        // Search input enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchHelp();
            }
        });
    </script>
</body>
</html>
