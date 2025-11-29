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
    <title>Contact Us - University Portal</title>
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
                <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 700;">Contact Us</h1>
                <p style="font-size: 1.2rem; opacity: 0.9;">We're here to help! Get in touch with our support team.</p>
            </section>

            <!-- Contact Methods -->
            <section class="contact-methods" style="margin-bottom: 4rem;">
                <div class="grid grid-3">
                    <div class="card" style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3 style="margin-bottom: 1rem;">Email Support</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 1rem;">Get help via email</p>
                        <a href="mailto:support@university.edu" style="color: var(--primary-color); font-weight: 500;">support@university.edu</a>
                    </div>
                    <div class="card" style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1rem;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3 style="margin-bottom: 1rem;">Phone Support</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 1rem;">Call us directly</p>
                        <a href="tel:+15551234567" style="color: var(--primary-color); font-weight: 500;">+1 (555) 123-4567</a>
                    </div>
                    <div class="card" style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; color: var(--success-color); margin-bottom: 1rem;">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 style="margin-bottom: 1rem;">Live Chat</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 1rem;">Chat with our team</p>
                        <button class="btn btn-primary" onclick="openLiveChat()">Start Chat</button>
                    </div>
                </div>
            </section>

            <!-- Contact Form and Info -->
            <section class="contact-section">
                <div class="grid grid-2" style="gap: 3rem;">
                    <!-- Contact Form -->
                    <div class="card" style="padding: 2rem;">
                        <h2 style="margin-bottom: 1.5rem; color: var(--text-primary);">Send us a Message</h2>
                        <form id="contactForm" class="contact-form">
                            <div class="form-group">
                                <label class="form-label" for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="form-input" placeholder="Enter your full name" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="subject">Subject</label>
                                <select id="subject" name="subject" class="form-input" required>
                                    <option value="">Select a subject</option>
                                    <option value="technical-support">Technical Support</option>
                                    <option value="account-issues">Account Issues</option>
                                    <option value="feature-request">Feature Request</option>
                                    <option value="bug-report">Bug Report</option>
                                    <option value="general-inquiry">General Inquiry</option>
                                    <option value="billing">Billing Questions</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="priority">Priority</label>
                                <select id="priority" name="priority" class="form-input" required>
                                    <option value="">Select priority</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="message">Message</label>
                                <textarea id="message" name="message" class="form-input form-textarea" placeholder="Describe your issue or question in detail" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                                <i class="fas fa-paper-plane"></i>
                                Send Message
                            </button>
                        </form>
                    </div>

                    <!-- Contact Information -->
                    <div class="card" style="padding: 2rem;">
                        <h2 style="margin-bottom: 1.5rem; color: var(--text-primary);">Get in Touch</h2>
                        
                        <div style="margin-bottom: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-map-marker-alt"></i>
                                Office Address
                            </h3>
                            <p style="color: var(--text-secondary); line-height: 1.6;">
                                123 University Avenue<br>
                                Campus City, State 12345<br>
                                United States
                            </p>
                        </div>
                        
                        <div style="margin-bottom: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-clock"></i>
                                Business Hours
                            </h3>
                            <div style="color: var(--text-secondary);">
                                <p><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM</p>
                                <p><strong>Saturday:</strong> 10:00 AM - 4:00 PM</p>
                                <p><strong>Sunday:</strong> Closed</p>
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-headset"></i>
                                Support Hours
                            </h3>
                            <div style="color: var(--text-secondary);">
                                <p><strong>24/7 Technical Support</strong></p>
                                <p>Email: support@university.edu</p>
                                <p>Phone: +1 (555) 123-4567</p>
                            </div>
                        </div>
                        
                        <div>
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <i class="fas fa-share-alt"></i>
                                Follow Us
                            </h3>
                            <div style="display: flex; gap: 1rem;">
                                <a href="#" style="color: var(--primary-color); font-size: 1.5rem;" title="Facebook">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <a href="#" style="color: var(--primary-color); font-size: 1.5rem;" title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" style="color: var(--primary-color); font-size: 1.5rem;" title="LinkedIn">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <a href="#" style="color: var(--primary-color); font-size: 1.5rem;" title="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section class="faq-section" style="margin-top: 4rem;">
                <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 3rem; color: var(--text-primary);">Frequently Asked Questions</h2>
                <div class="grid grid-2">
                    <div class="card" style="padding: 2rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                            <i class="fas fa-question-circle"></i>
                            How do I reset my password?
                        </h3>
                        <p style="color: var(--text-secondary); line-height: 1.6;">
                            Click on "Forgot Password" on the login page and enter your email address. You'll receive a reset link within a few minutes.
                        </p>
                    </div>
                    <div class="card" style="padding: 2rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                            <i class="fas fa-question-circle"></i>
                            Can I access the portal on mobile?
                        </h3>
                        <p style="color: var(--text-secondary); line-height: 1.6;">
                            Yes! Our portal is fully responsive and works perfectly on all mobile devices, tablets, and computers.
                        </p>
                    </div>
                    <div class="card" style="padding: 2rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                            <i class="fas fa-question-circle"></i>
                            How do I contact my instructor?
                        </h3>
                        <p style="color: var(--text-secondary); line-height: 1.6;">
                            Use the chat feature in your dashboard or send an email through the messaging system. Your instructor will respond within 24 hours.
                        </p>
                    </div>
                    <div class="card" style="padding: 2rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                            <i class="fas fa-question-circle"></i>
                            Is my data secure?
                        </h3>
                        <p style="color: var(--text-secondary); line-height: 1.6;">
                            Absolutely! We use enterprise-grade security measures including SSL encryption and regular security audits to protect your information.
                        </p>
                    </div>
                </div>
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
                <a href="home.php">Home</a>
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
        // Contact form submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const name = formData.get('name');
            const email = formData.get('email');
            const subject = formData.get('subject');
            const priority = formData.get('priority');
            const message = formData.get('message');
            
            // Basic validation
            if (!name || !email || !subject || !priority || !message) {
                showNotification('Please fill in all fields', 'error');
                return;
            }
            
            // Simulate form submission
            showNotification('Message sent successfully! We\'ll get back to you within 24 hours.', 'success');
            this.reset();
        });

        // Live chat function
        function openLiveChat() {
            showNotification('Live chat is currently offline. Please use email or phone support.', 'warning');
        }
    </script>
</body>
</html>
