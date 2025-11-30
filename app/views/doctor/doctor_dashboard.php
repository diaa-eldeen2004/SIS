<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - University Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    ////////
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
            <h2><i class="fas fa-graduation-cap"></i> Doctor Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="doctor_dashboard.html" class="nav-item active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="doctor_courses.html" class="nav-item">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="doctor_assignments.html" class="nav-item">
                <i class="fas fa-tasks"></i> Assignments
            </a>
            <a href="doctor_attendance.html" class="nav-item">
                <i class="fas fa-calendar-check"></i> Attendance
            </a>
            <a href="doctor_notifications.html" class="nav-item">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a href="doctor_calendar.html" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Calendar
            </a>
            <a href="doctor_profile.html" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Welcome back, Dr. Johnson!</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Manage your courses and students effectively.</p>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <div style="text-align: right;">
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">Department</div>
                        <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">Computer Science</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">Experience</div>
                        <div style="font-size: 1.2rem; font-weight: 700; color: var(--accent-color);">8 Years</div>
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
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">3</div>
                        <div style="color: var(--text-secondary);">Active Courses</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">125</div>
                        <div style="color: var(--text-secondary);">Total Students</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">12</div>
                        <div style="color: var(--text-secondary);">Pending Grading</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">5</div>
                        <div style="color: var(--text-secondary);">New Messages</div>
                    </div>
                </div>
            </section>

            <!-- Main Content Grid -->
            <div class="grid grid-2" style="gap: 2rem;">
                <!-- My Courses -->
                <section class="my-courses">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-chalkboard-teacher" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                                My Courses
                            </h2>
                            <a href="doctor_courses.html" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                Manage All
                            </a>
                        </div>
                        <div class="courses-list">
                            <div class="course-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">CS101 - Introduction to Programming</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">45 students • Mon, Wed, Fri 10:00 AM</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;">Active</div>
                                    <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="manageCourse('cs101')">
                                        Manage
                                    </button>
                                </div>
                            </div>
                            <div class="course-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">CS201 - Data Structures</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">38 students • Tue, Thu 2:00 PM</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;">Active</div>
                                    <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="manageCourse('cs201')">
                                        Manage
                                    </button>
                                </div>
                            </div>
                            <div class="course-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">CS301 - Algorithms</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">42 students • Mon, Wed 3:00 PM</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;">Active</div>
                                    <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="manageCourse('cs301')">
                                        Manage
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Pending Tasks -->
                <section class="pending-tasks">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <i class="fas fa-clock" style="color: var(--warning-color); margin-right: 0.5rem;"></i>
                                Pending Tasks
                            </h2>
                            <a href="doctor_assignments.html" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                View All
                            </a>
                        </div>
                        <div class="tasks-list">
                            <div class="task-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Grade Programming Assignment</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS101 - 12 submissions pending</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--warning-color); font-weight: 500;">Due Today</div>
                                    <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="gradeAssignment('cs101-assignment1')">
                                        Grade
                                    </button>
                                </div>
                            </div>
                            <div class="task-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Take Attendance</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS201 - Today's class</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;">In 2 hours</div>
                                    <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="takeAttendance('cs201')">
                                        Take
                                    </button>
                                </div>
                            </div>
                            <div class="task-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Upload Lecture Materials</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS301 - Week 5 slides</p>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">This Week</div>
                                    <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="uploadMaterials('cs301')">
                                        Upload
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Recent Activity -->
            <section class="recent-activity" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-history" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                            Recent Activity
                        </h2>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-check"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Graded Assignment</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS101 - Programming Assignment #3 graded for 15 students</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">2 hours ago</div>
                            </div>
                        </div>
                        <div class="activity-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--accent-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Uploaded Materials</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS201 - Week 4 lecture slides and assignments uploaded</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">1 day ago</div>
                            </div>
                        </div>
                        <div class="activity-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Attendance Taken</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS301 - Attendance marked for 42 students</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">2 days ago</div>
                            </div>
                        </div>
                        <div class="activity-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem;">
                            <div style="width: 40px; height: 40px; background-color: var(--error-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Sent Notification</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS101 - Assignment deadline reminder sent to all students</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">3 days ago</div>
                            </div>
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
                        <button class="btn btn-primary" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="createAssignment()">
                            <i class="fas fa-plus" style="font-size: 2rem;"></i>
                            <span>Create Assignment</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="uploadMaterials()">
                            <i class="fas fa-upload" style="font-size: 2rem;"></i>
                            <span>Upload Materials</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="sendNotification()">
                            <i class="fas fa-bell" style="font-size: 2rem;"></i>
                            <span>Send Notification</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="viewReports()">
                            <i class="fas fa-chart-bar" style="font-size: 2rem;"></i>
                            <span>View Reports</span>
                        </button>
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
                <h3>Doctor Chat</h3>
                <button class="chat-close" onclick="closeChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chat-body">
                <form class="chat-form">
                    <div class="form-group">
                        <label class="form-label">From</label>
                        <input type="email" name="from" class="form-input" placeholder="dr.johnson@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="email" name="to" class="form-input" placeholder="student@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Course announcement" required>
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
    <script src="../js/main.js"></script>
    <script>
        // Manage course
        function manageCourse(courseId) {
            showNotification(`Managing course ${courseId}`, 'info');
            setTimeout(() => {
                window.location.href = '../course-info.html';
            }, 1000);
        }

        // Grade assignment
        function gradeAssignment(assignmentId) {
            showNotification(`Opening grading interface for ${assignmentId}`, 'info');
        }

        // Take attendance
        function takeAttendance(courseId) {
            showNotification(`Taking attendance for ${courseId}`, 'info');
        }

        // Upload materials
        function uploadMaterials(courseId) {
            showNotification(`Upload materials dialog for ${courseId}`, 'info');
        }

        // Quick actions
        function createAssignment() {
            showNotification('Create assignment dialog would open here', 'info');
        }

        function sendNotification() {
            showNotification('Send notification dialog would open here', 'info');
        }

        function viewReports() {
            showNotification('Reports page would open here', 'info');
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
    </script>
</body>
</html>
