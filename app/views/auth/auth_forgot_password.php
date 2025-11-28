<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - University Portal</title>
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
            <li><a href="../index.html">Home</a></li>
            <li><a href="../app/about.html">About Us</a></li>
            <li><a href="../app/contact.html">Contact</a></li>
            <li><a href="../app/help_center.html">Help</a></li>
            <li><a href="auth_login.html" class="btn btn-primary">Login</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content" style="min-height: calc(100vh - 200px); display: flex; align-items: center; justify-content: center; padding: 2rem;">
        <div class="container" style="max-width: 500px;">
            <!-- Forgot Password Card -->
            <div class="card" style="padding: 2rem;">
                <div class="card-header" style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 80px; height: 80px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                        <i class="fas fa-key"></i>
                    </div>
                    <h1 style="margin: 0; color: var(--text-primary);">Forgot Password?</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <!-- Forgot Password Form -->
                <form class="forgot-password-form" id="forgotPasswordForm">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div style="position: relative;">
                            <input type="email" name="email" class="form-input" placeholder="Enter your email address" required>
                            <i class="fas fa-envelope" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">User Type</label>
                        <select name="userType" class="form-input" required>
                            <option value="">Select your role</option>
                            <option value="student">Student</option>
                            <option value="doctor">Doctor</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>
                </form>

                <!-- Alternative Methods -->
                <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                    <p style="margin: 0 0 1rem 0; color: var(--text-secondary); font-size: 0.9rem;">Can't access your email?</p>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <button class="btn btn-outline" onclick="contactSupport()">
                            <i class="fas fa-headset"></i> Contact Support
                        </button>
                        <button class="btn btn-outline" onclick="verifyIdentity()">
                            <i class="fas fa-id-card"></i> Verify Identity
                        </button>
                    </div>
                </div>

                <!-- Back to Login -->
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="auth_login.html" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="card" style="margin-top: 2rem; padding: 1.5rem; background-color: rgba(37, 99, 235, 0.1); border-left: 4px solid var(--primary-color);">
                <div style="display: flex; align-items: start; gap: 1rem;">
                    <i class="fas fa-shield-alt" style="color: var(--primary-color); font-size: 1.2rem; margin-top: 0.25rem;"></i>
                    <div>
                        <h4 style="margin: 0 0 0.5rem 0; color: var(--primary-color);">Security Notice</h4>
                        <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
                            For your security, password reset links expire after 24 hours. If you don't receive an email within 5 minutes, check your spam folder or contact our support team.
                        </p>
                    </div>
                </div>
            </div>
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
                <a href="../index.html">Home</a>
                <a href="../app/about.html">About Us</a>
                <a href="../app/contact.html">Contact</a>
                <a href="../app/help_center.html">Help Center</a>
            </div>
            <div class="footer-section">
                <h3>User Access</h3>
                <a href="auth_login.html">Student Login</a>
                <a href="auth_login.html">Doctor Login</a>
                <a href="auth_login.html">Admin Login</a>
                <a href="auth_signup.html">Register</a>
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
            // Set up forgot password form submission
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            if (forgotPasswordForm) {
                forgotPasswordForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const email = this.querySelector('input[name="email"]').value;
                    const userType = this.querySelector('select[name="userType"]').value;
                    
                    if (!email || !userType) {
                        showNotification('Please fill in all fields', 'error');
                        return;
                    }
                    
                    // Simulate sending reset email
                    showNotification('Sending password reset email...', 'info');
                    
                    setTimeout(() => {
                        showNotification('Password reset email sent successfully! Check your inbox.', 'success');
                        this.reset();
                        
                        // Show success message
                        const successMessage = document.createElement('div');
                        successMessage.className = 'card';
                        successMessage.style.cssText = 'margin-top: 2rem; padding: 1.5rem; background-color: rgba(34, 197, 94, 0.1); border-left: 4px solid var(--success-color);';
                        successMessage.innerHTML = `
                            <div style="display: flex; align-items: start; gap: 1rem;">
                                <i class="fas fa-check-circle" style="color: var(--success-color); font-size: 1.2rem; margin-top: 0.25rem;"></i>
                                <div>
                                    <h4 style="margin: 0 0 0.5rem 0; color: var(--success-color);">Email Sent Successfully</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
                                        We've sent a password reset link to <strong>${email}</strong>. Please check your email and follow the instructions to reset your password.
                                    </p>
                                </div>
                            </div>
                        `;
                        
                        this.parentNode.insertBefore(successMessage, this.nextSibling);
                        
                        // Remove success message after 10 seconds
                        setTimeout(() => {
                            if (successMessage.parentNode) {
                                successMessage.parentNode.removeChild(successMessage);
                            }
                        }, 10000);
                    }, 2000);
                });
            }
        });

        // Alternative methods
        function contactSupport() {
            showNotification('Opening support contact form...', 'info');
            // In a real application, this would open a support form or redirect to support page
        }

        function verifyIdentity() {
            showNotification('Opening identity verification process...', 'info');
            // In a real application, this would open an identity verification form
        }
    </script>
</body>
</html>
