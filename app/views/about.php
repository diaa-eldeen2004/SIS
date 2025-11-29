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
    <title>About Us - University Portal</title>
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
            <section class="hero" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; padding: 4rem 2rem; border-radius: 16px; margin-bottom: 4rem;">
                <h1 style="font-size: 3rem; margin-bottom: 1rem; font-weight: 700;">About University Portal</h1>
                <p style="font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">Empowering education through innovative technology and seamless campus management solutions.</p>
            </section>

            <!-- Mission Section -->
            <section class="mission-section" style="margin-bottom: 4rem;">
                <div class="grid grid-2" style="gap: 3rem; align-items: center;">
                    <div>
                        <h2 style="font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--text-primary);">Our Mission</h2>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-secondary); margin-bottom: 2rem;">
                            To revolutionize campus management by providing a comprehensive, user-friendly platform that connects students, faculty, and administrators in a seamless digital ecosystem. We believe in making education more accessible, organized, and efficient through cutting-edge technology.
                        </p>
                        <div class="grid grid-2" style="gap: 1rem;">
                            <div class="card" style="text-align: center; padding: 1.5rem;">
                                <i class="fas fa-lightbulb" style="font-size: 2rem; color: var(--accent-color); margin-bottom: 1rem;"></i>
                                <h3 style="margin-bottom: 0.5rem;">Innovation</h3>
                                <p style="font-size: 0.9rem; color: var(--text-secondary);">Cutting-edge solutions for modern education</p>
                            </div>
                            <div class="card" style="text-align: center; padding: 1.5rem;">
                                <i class="fas fa-users" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                                <h3 style="margin-bottom: 0.5rem;">Community</h3>
                                <p style="font-size: 0.9rem; color: var(--text-secondary);">Building stronger campus connections</p>
                            </div>
                        </div>
                    </div>
                    <div class="card" style="padding: 2rem; text-align: center;">
                        <img src="https://via.placeholder.com/400x300/2563eb/ffffff?text=University+Campus" alt="University Campus" style="width: 100%; border-radius: 8px; margin-bottom: 1.5rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Modern Campus Life</h3>
                        <p style="color: var(--text-secondary);">Experience the future of education management</p>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="features-section" style="margin-bottom: 4rem;">
                <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 3rem; color: var(--text-primary);">What Makes Us Different</h2>
                <div class="grid grid-3">
                    <div class="card" style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1.5rem;">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 style="margin-bottom: 1rem;">Mobile-First Design</h3>
                        <p style="color: var(--text-secondary); line-height: 1.6;">Responsive design that works perfectly on all devices, ensuring accessibility anywhere, anytime.</p>
                    </div>
                    <div class="card" style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1.5rem;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 style="margin-bottom: 1rem;">Secure & Reliable</h3>
                        <p style="color: var(--text-secondary); line-height: 1.6;">Enterprise-grade security with 99.9% uptime guarantee to protect your academic data.</p>
                    </div>
                    <div class="card" style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; color: var(--success-color); margin-bottom: 1.5rem;">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3 style="margin-bottom: 1rem;">Customizable</h3>
                        <p style="color: var(--text-secondary); line-height: 1.6;">Flexible platform that adapts to your institution's unique needs and workflows.</p>
                    </div>
                </div>
            </section>

            <!-- Team Section -->
            <section class="team-section" style="margin-bottom: 4rem;">
                <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 3rem; color: var(--text-primary);">Our Team</h2>
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 2rem;">
                        <img src="https://via.placeholder.com/150x150/2563eb/ffffff?text=JS" alt="John Smith" style="width: 120px; height: 120px; border-radius: 50%; margin-bottom: 1rem;">
                        <h3 style="margin-bottom: 0.5rem;">John Smith</h3>
                        <p style="color: var(--primary-color); font-weight: 500; margin-bottom: 0.5rem;">CEO & Founder</p>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);">15+ years in educational technology</p>
                    </div>
                    <div class="card" style="text-align: center; padding: 2rem;">
                        <img src="https://via.placeholder.com/150x150/f59e0b/ffffff?text=MD" alt="Maria Davis" style="width: 120px; height: 120px; border-radius: 50%; margin-bottom: 1rem;">
                        <h3 style="margin-bottom: 0.5rem;">Maria Davis</h3>
                        <p style="color: var(--primary-color); font-weight: 500; margin-bottom: 0.5rem;">CTO</p>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);">Expert in scalable systems architecture</p>
                    </div>
                    <div class="card" style="text-align: center; padding: 2rem;">
                        <img src="https://via.placeholder.com/150x150/10b981/ffffff?text=AJ" alt="Alex Johnson" style="width: 120px; height: 120px; border-radius: 50%; margin-bottom: 1rem;">
                        <h3 style="margin-bottom: 0.5rem;">Alex Johnson</h3>
                        <p style="color: var(--primary-color); font-weight: 500; margin-bottom: 0.5rem;">Lead Developer</p>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);">Full-stack development specialist</p>
                    </div>
                    <div class="card" style="text-align: center; padding: 2rem;">
                        <img src="https://via.placeholder.com/150x150/ef4444/ffffff?text=SW" alt="Sarah Wilson" style="width: 120px; height: 120px; border-radius: 50%; margin-bottom: 1rem;">
                        <h3 style="margin-bottom: 0.5rem;">Sarah Wilson</h3>
                        <p style="color: var(--primary-color); font-weight: 500; margin-bottom: 0.5rem;">UX Designer</p>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);">Creating intuitive user experiences</p>
                    </div>
                </div>
            </section>

            <!-- Statistics Section -->
            <section class="stats-section" style="background-color: var(--surface-color); padding: 4rem 2rem; border-radius: 16px; margin-bottom: 4rem;">
                <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 3rem; color: var(--text-primary);">Our Impact</h2>
                <div class="grid grid-4">
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">50+</div>
                        <div style="font-size: 1.2rem; color: var(--text-secondary);">Universities</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; font-weight: 700; color: var(--accent-color); margin-bottom: 0.5rem;">100K+</div>
                        <div style="font-size: 1.2rem; color: var(--text-secondary);">Active Users</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; font-weight: 700; color: var(--success-color); margin-bottom: 0.5rem;">99.9%</div>
                        <div style="font-size: 1.2rem; color: var(--text-secondary);">Uptime</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; font-weight: 700; color: var(--error-color); margin-bottom: 0.5rem;">24/7</div>
                        <div style="font-size: 1.2rem; color: var(--text-secondary);">Support</div>
                    </div>
                </div>
            </section>

            <!-- Call to Action -->
            <section class="cta-section" style="text-align: center; padding: 4rem 2rem; background: linear-gradient(135deg, var(--primary-color), var(--accent-color)); color: white; border-radius: 16px;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Ready to Transform Your Campus?</h2>
                <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9;">Join thousands of institutions already using our platform</p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="../auth/auth_login.html" class="btn" style="background-color: white; color: var(--primary-color); padding: 1rem 2rem; font-weight: 600;">Get Started</a>
                    <a href="contact.html" class="btn" style="background-color: transparent; border: 2px solid white; color: white; padding: 1rem 2rem; font-weight: 600;">Contact Us</a>
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
                <a href="../index.html">Home</a>
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
</body>
</html>
