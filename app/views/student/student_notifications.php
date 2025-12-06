<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Student Portal</title>
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
            <a href="student_dashboard.html" class="nav-item">
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
            <a href="student_notifications.html" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Notifications</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Stay updated with important announcements and messages.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshNotifications()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="markAllAsRead()">
                        <i class="fas fa-check-double"></i> Mark All Read
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Notification Statistics -->
            <section class="notification-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">7</div>
                        <div style="color: var(--text-secondary);">Unread</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">23</div>
                        <div style="color: var(--text-secondary);">Read</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">3</div>
                        <div style="color: var(--text-secondary);">Urgent</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">30</div>
                        <div style="color: var(--text-secondary);">Total</div>
                    </div>
                </div>
            </section>

            <!-- Notification Filter -->
            <section class="notification-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search notifications..." id="notificationSearch" onkeyup="filterNotifications()">
                        </div>
                        <div>
                            <select class="form-input" id="typeFilter" onchange="filterNotifications()">
                                <option value="">All Types</option>
                                <option value="assignment">Assignments</option>
                                <option value="exam">Exams</option>
                                <option value="announcement">Announcements</option>
                                <option value="grade">Grades</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterNotifications()">
                                <option value="">All Status</option>
                                <option value="unread">Unread</option>
                                <option value="read">Read</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="courseFilter" onchange="filterNotifications()">
                                <option value="">All Courses</option>
                                <option value="calculus">Calculus I</option>
                                <option value="cs101">Computer Science 101</option>
                                <option value="physics">Physics I</option>
                                <option value="english">English Literature</option>
                            </select>
                        </div>
                        <div>
                            <button class="btn btn-outline" onclick="resetFilters()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Notifications List -->
            <section class="notifications-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-bell" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            All Notifications
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="toggleView('all')" id="allViewBtn">
                                <i class="fas fa-list"></i> All
                            </button>
                            <button class="btn btn-outline" onclick="toggleView('unread')" id="unreadViewBtn">
                                <i class="fas fa-envelope"></i> Unread
                            </button>
                        </div>
                    </div>

                    <div class="notifications-container" id="notificationsContainer">
                        <!-- Urgent Notification -->
                        <div class="notification-item urgent unread" data-type="assignment" data-course="calculus" data-status="urgent" style="border-left: 4px solid var(--error-color); background-color: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-exclamation-triangle" style="color: var(--error-color);"></i>
                                        <span style="background-color: var(--error-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500;">URGENT</span>
                                        <span style="background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Assignment</span>
                                    </div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">Math Homework #5 Due Tomorrow</h3>
                                    <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">Your Math Homework #5 is due tomorrow at 11:59 PM. Please make sure to submit your solutions for chapters 3-4 problems.</p>
                                    <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                        <span><i class="fas fa-user"></i> Dr. Sarah Smith</span>
                                        <span><i class="fas fa-book"></i> Calculus I</span>
                                        <span><i class="fas fa-clock"></i> Sep 14, 2024 at 2:30 PM</span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <button class="btn btn-primary" style="padding: 0.5rem;" onclick="viewNotification(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline" style="padding: 0.5rem;" onclick="markAsRead(1)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Notification -->
                        <div class="notification-item unread" data-type="assignment" data-course="cs101" data-status="unread" style="border-left: 4px solid var(--warning-color); background-color: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-tasks" style="color: var(--warning-color);"></i>
                                        <span style="background-color: var(--warning-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Assignment</span>
                                    </div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">New Programming Project Assigned</h3>
                                    <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">A new programming project has been assigned. Create a Python calculator application with GUI. Due date: September 18, 2024.</p>
                                    <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                        <span><i class="fas fa-user"></i> Dr. Michael Johnson</span>
                                        <span><i class="fas fa-book"></i> CS 101</span>
                                        <span><i class="fas fa-clock"></i> Sep 13, 2024 at 10:15 AM</span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <button class="btn btn-primary" style="padding: 0.5rem;" onclick="viewNotification(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline" style="padding: 0.5rem;" onclick="markAsRead(2)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Exam Notification -->
                        <div class="notification-item unread" data-type="exam" data-course="cs101" data-status="unread" style="border-left: 4px solid var(--error-color); background-color: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-graduation-cap" style="color: var(--error-color);"></i>
                                        <span style="background-color: var(--error-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Exam</span>
                                    </div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">CS101 Midterm Exam Scheduled</h3>
                                    <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">The CS101 midterm exam is scheduled for September 20, 2024 at 10:00 AM in Room 201. Please bring your student ID and calculator.</p>
                                    <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                        <span><i class="fas fa-user"></i> Dr. Michael Johnson</span>
                                        <span><i class="fas fa-book"></i> CS 101</span>
                                        <span><i class="fas fa-clock"></i> Sep 12, 2024 at 3:45 PM</span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <button class="btn btn-primary" style="padding: 0.5rem;" onclick="viewNotification(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline" style="padding: 0.5rem;" onclick="markAsRead(3)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Grade Notification -->
                        <div class="notification-item unread" data-type="grade" data-course="physics" data-status="unread" style="border-left: 4px solid var(--success-color); background-color: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-chart-line" style="color: var(--success-color);"></i>
                                        <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Grade</span>
                                    </div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">Physics Lab Report Graded</h3>
                                    <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">Your Physics Lab Report has been graded. You received an A- (92/100). Great work on the pendulum motion analysis!</p>
                                    <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                        <span><i class="fas fa-user"></i> Dr. Emily Brown</span>
                                        <span><i class="fas fa-book"></i> Physics I</span>
                                        <span><i class="fas fa-clock"></i> Sep 11, 2024 at 4:20 PM</span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <button class="btn btn-primary" style="padding: 0.5rem;" onclick="viewNotification(4)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline" style="padding: 0.5rem;" onclick="markAsRead(4)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Announcement Notification -->
                        <div class="notification-item unread" data-type="announcement" data-course="english" data-status="unread" style="border-left: 4px solid var(--primary-color); background-color: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-bullhorn" style="color: var(--primary-color);"></i>
                                        <span style="background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Announcement</span>
                                    </div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">Essay Assignment Guidelines Updated</h3>
                                    <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">The guidelines for the Hamlet essay assignment have been updated. Please review the new requirements and formatting instructions on the course page.</p>
                                    <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                        <span><i class="fas fa-user"></i> Dr. Robert Wilson</span>
                                        <span><i class="fas fa-book"></i> English Literature</span>
                                        <span><i class="fas fa-clock"></i> Sep 10, 2024 at 1:30 PM</span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <button class="btn btn-primary" style="padding: 0.5rem;" onclick="viewNotification(5)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline" style="padding: 0.5rem;" onclick="markAsRead(5)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- System Notification -->
                        <div class="notification-item unread" data-type="system" data-course="" data-status="unread" style="border-left: 4px solid var(--accent-color); background-color: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-cog" style="color: var(--accent-color);"></i>
                                        <span style="background-color: var(--accent-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">System</span>
                                    </div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">System Maintenance Scheduled</h3>
                                    <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">The university portal will undergo scheduled maintenance on September 16, 2024 from 2:00 AM to 6:00 AM. During this time, the system may be temporarily unavailable.</p>
                                    <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                        <span><i class="fas fa-user"></i> System Administrator</span>
                                        <span><i class="fas fa-book"></i> System</span>
                                        <span><i class="fas fa-clock"></i> Sep 9, 2024 at 9:00 AM</span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <button class="btn btn-primary" style="padding: 0.5rem;" onclick="viewNotification(6)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline" style="padding: 0.5rem;" onclick="markAsRead(6)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Read Notification Example -->
                        <div class="notification-item read" data-type="announcement" data-course="calculus" data-status="read" style="border-left: 4px solid var(--primary-color); background-color: var(--background-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; opacity: 0.7;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-bullhorn" style="color: var(--primary-color);"></i>
                                        <span style="background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Announcement</span>
                                        <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Read</span>
                                    </div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">Welcome to Fall 2024 Semester</h3>
                                    <p style="margin: 0 0 0.5rem 0; color: var(--text-secondary);">Welcome to the Fall 2024 semester! Please review the course syllabus and important dates. Office hours are available Monday-Friday 2:00-4:00 PM.</p>
                                    <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                        <span><i class="fas fa-user"></i> Dr. Sarah Smith</span>
                                        <span><i class="fas fa-book"></i> Calculus I</span>
                                        <span><i class="fas fa-clock"></i> Sep 1, 2024 at 8:00 AM</span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <button class="btn btn-primary" style="padding: 0.5rem;" onclick="viewNotification(7)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline" style="padding: 0.5rem;" onclick="markAsUnread(7)">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </div>
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
                <h3>Notification Chat</h3>
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
                        <input type="text" name="subject" class="form-input" placeholder="Notification question" required>
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
    <script src="../../js/main.js"></script>
    <script>
        let currentView = 'all';

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

            // Initialize view buttons
            updateViewButtons();
        });

        // Filter notifications
        function filterNotifications() {
            const searchTerm = document.getElementById('notificationSearch').value.toLowerCase();
            const typeFilter = document.getElementById('typeFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const courseFilter = document.getElementById('courseFilter').value;

            const notificationItems = document.querySelectorAll('.notification-item');

            notificationItems.forEach(item => {
                const title = item.querySelector('h3').textContent.toLowerCase();
                const content = item.querySelector('p').textContent.toLowerCase();
                const type = item.getAttribute('data-type');
                const course = item.getAttribute('data-course');
                const status = item.getAttribute('data-status');

                const matchesSearch = title.includes(searchTerm) || content.includes(searchTerm);
                const matchesType = !typeFilter || type === typeFilter;
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesCourse = !courseFilter || course === courseFilter;

                if (matchesSearch && matchesType && matchesStatus && matchesCourse) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Toggle view
        function toggleView(view) {
            currentView = view;
            const notificationItems = document.querySelectorAll('.notification-item');

            notificationItems.forEach(item => {
                if (view === 'all') {
                    item.style.display = 'block';
                } else if (view === 'unread') {
                    if (item.classList.contains('unread')) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });

            updateViewButtons();
        }

        // Update view buttons
        function updateViewButtons() {
            const allBtn = document.getElementById('allViewBtn');
            const unreadBtn = document.getElementById('unreadViewBtn');

            if (currentView === 'all') {
                allBtn.classList.add('btn-primary');
                allBtn.classList.remove('btn-outline');
                unreadBtn.classList.add('btn-outline');
                unreadBtn.classList.remove('btn-primary');
            } else {
                unreadBtn.classList.add('btn-primary');
                unreadBtn.classList.remove('btn-outline');
                allBtn.classList.add('btn-outline');
                allBtn.classList.remove('btn-primary');
            }
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('notificationSearch').value = '';
            document.getElementById('typeFilter').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('courseFilter').value = '';

            const notificationItems = document.querySelectorAll('.notification-item');
            notificationItems.forEach(item => {
                item.style.display = 'block';
            });

            showNotification('Filters reset successfully', 'success');
        }

        // View notification
        function viewNotification(notificationId) {
            showNotification(`Opening notification ${notificationId}...`, 'info');
            // In a real implementation, this would open a modal with full notification details
        }

        // Mark as read
        function markAsRead(notificationId) {
            const notification = document.querySelector(`[onclick="markAsRead(${notificationId})"]`).closest('.notification-item');
            notification.classList.remove('unread');
            notification.classList.add('read');
            notification.style.opacity = '0.7';
            
            // Update button
            const button = notification.querySelector(`[onclick="markAsRead(${notificationId})"]`);
            button.innerHTML = '<i class="fas fa-envelope"></i>';
            button.setAttribute('onclick', `markAsUnread(${notificationId})`);
            
            showNotification('Notification marked as read', 'success');
        }

        // Mark as unread
        function markAsUnread(notificationId) {
            const notification = document.querySelector(`[onclick="markAsUnread(${notificationId})"]`).closest('.notification-item');
            notification.classList.remove('read');
            notification.classList.add('unread');
            notification.style.opacity = '1';
            
            // Update button
            const button = notification.querySelector(`[onclick="markAsUnread(${notificationId})"]`);
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.setAttribute('onclick', `markAsRead(${notificationId})`);
            
            showNotification('Notification marked as unread', 'success');
        }

        // Mark all as read
        function markAllAsRead() {
            const unreadNotifications = document.querySelectorAll('.notification-item.unread');
            unreadNotifications.forEach(notification => {
                notification.classList.remove('unread');
                notification.classList.add('read');
                notification.style.opacity = '0.7';
                
                // Update button
                const button = notification.querySelector('button[onclick*="markAsRead"]');
                if (button) {
                    button.innerHTML = '<i class="fas fa-envelope"></i>';
                    button.setAttribute('onclick', button.getAttribute('onclick').replace('markAsRead', 'markAsUnread'));
                }
            });
            
            showNotification('All notifications marked as read', 'success');
        }

        // Refresh notifications
        function refreshNotifications() {
            showNotification('Refreshing notifications...', 'info');
            // In a real implementation, this would reload notifications from the server
            setTimeout(() => {
                showNotification('Notifications refreshed successfully', 'success');
            }, 1000);
        }
    </script>
</body>
</html>
