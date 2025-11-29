<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - University Portal</title>
    <link rel="stylesheet" href="css/styles.css">
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
            <h2><i class="fas fa-graduation-cap"></i> Student Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="student_dashboard.html" class="nav-item active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="student_courses.html" class="nav-item">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="student_assignments.html" class="nav-item">
                <i class="fas fa-tasks"></i> Assignments
            </a>
            <a href="student_attendance.html" class="nav-item">
                <i class="fas fa-calendar-check"></i> Attendance
            </a>
            <a href="student_calendar.html" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Calendar
            </a>
            <a href="student_notifications.html" class="nav-item">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a href="student_profile.html" class="nav-item">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="../app/settings.html" class="nav-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="../app/logout.html" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Welcome back, John!</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Here's what's happening with your courses today.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <div style="text-align: right;">
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">Current GPA</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">3.75</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">Credits</div>
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-color);">45</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Quick Stats -->
            <section class="quick-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">5</div>
                        <div style="color: var(--text-secondary);">Enrolled Courses</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">3</div>
                        <div style="color: var(--text-secondary);">Pending Assignments</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">95%</div>
                        <div style="color: var(--text-secondary);">Attendance Rate</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">7</div>
                        <div style="color: var(--text-secondary);">New Notifications</div>
                    </div>
                </div>
            </section>

            <!-- Main Content Grid -->
            <div class="grid grid-2" style="gap: 2rem;">
                <!-- Upcoming Assignments -->
                <section class="upcoming-assignments">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-clock" style="color: var(--warning-color); margin-right: 0.5rem;"></i>
                                Upcoming Assignments
                            </h2>
                            <a href="student_assignments.html" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                View All
                            </a>
                        </div>
                        <div class="assignments-list">
                            <div class="assignment-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Math Homework #5</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Calculus I - Dr. Smith</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--warning-color); font-weight: 500;">Due Tomorrow</div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Sep 15, 2024</div>
                                </div>
                            </div>
                            <div class="assignment-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Programming Project</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS101 - Dr. Johnson</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;">Due in 3 days</div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Sep 18, 2024</div>
                                </div>
                            </div>
                            <div class="assignment-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Physics Lab Report</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Physics I - Dr. Brown</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">Due in 1 week</div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">Sep 22, 2024</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Recent Grades -->
                <section class="recent-grades">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-chart-line" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                Recent Grades
                            </h2>
                            <a href="student_profile.html" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                View All
                            </a>
                        </div>
                        <div class="grades-list">
                            <div class="grade-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Math Quiz #3</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Calculus I</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);">A-</div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">92/100</div>
                                </div>
                            </div>
                            <div class="grade-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Programming Assignment</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS101</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);">A</div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">98/100</div>
                                </div>
                            </div>
                            <div class="grade-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Physics Lab</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Physics I</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--accent-color);">B+</div>
                                    <div style="font-size: 0.8rem; color: var(--text-secondary);">87/100</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Course Progress -->
            <section class="course-progress" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-pie" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Course Progress
                        </h2>
                    </div>
                    <div class="progress-courses">
                        <div class="course-progress-item" style="margin-bottom: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <h4 style="margin: 0; color: var(--text-primary);">Calculus I</h4>
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">75% Complete</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: 75%; background-color: var(--primary-color);"></div>
                            </div>
                        </div>
                        <div class="course-progress-item" style="margin-bottom: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <h4 style="margin: 0; color: var(--text-primary);">Computer Science 101</h4>
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">60% Complete</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: 60%; background-color: var(--accent-color);"></div>
                            </div>
                        </div>
                        <div class="course-progress-item" style="margin-bottom: 1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <h4 style="margin: 0; color: var(--text-primary);">Physics I</h4>
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">45% Complete</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: 45%; background-color: var(--success-color);"></div>
                            </div>
                        </div>
                        <div class="course-progress-item">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <h4 style="margin: 0; color: var(--text-primary);">English Literature</h4>
                                <span style="color: var(--text-secondary); font-size: 0.9rem;">30% Complete</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: 30%; background-color: var(--error-color);"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Today's Schedule -->
            <section class="todays-schedule" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-calendar-day" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Today's Schedule
                        </h2>
                        <a href="student_calendar.html" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                            View Calendar
                        </a>
                    </div>
                    <div class="schedule-list">
                        <div class="schedule-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 4px; height: 40px; background-color: var(--primary-color); border-radius: 2px;"></div>
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Calculus I Lecture</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Room 201 - Dr. Smith</p>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">10:00 AM</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">1 hour</div>
                            </div>
                        </div>
                        <div class="schedule-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 4px; height: 40px; background-color: var(--accent-color); border-radius: 2px;"></div>
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">CS101 Lab</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Computer Lab - Dr. Johnson</p>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">2:00 PM</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">2 hours</div>
                            </div>
                        </div>
                        <div class="schedule-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 4px; height: 40px; background-color: var(--success-color); border-radius: 2px;"></div>
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Study Group</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Library - Physics Review</p>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">6:00 PM</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">1 hour</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Chat Widget -->
    <div class="chat-widget">
        <button class="chat-toggle" onclick="toggleChat()">
            <i class="fas fa-comments"></i>
        </button>
        <div class="chat-box">
            <div class="chat-header">
                <h3>Student Chat</h3>
                <button class="chat-close" onclick="closeChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chat-body">
                <form class="chat-form">
                    <div class="form-group">
                        <label class="form-label">From</label>
                        <input type="email" name="from" class="form-input" placeholder="john.doe@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="email" name="to" class="form-input" placeholder="instructor@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Course question" required>
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
                <a href="../index.html">Home</a>
                <a href="../app/about.html">About Us</a>
                <a href="../app/contact.html">Contact</a>
                <a href="../app/help_center.html">Help Center</a>
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
    </script>
</body>
</html>
