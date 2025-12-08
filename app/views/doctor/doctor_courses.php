<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Doctor Portal</title>
    <link rel="stylesheet" href="../../css/styles.css">
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
            <h2><i class="fas fa-graduation-cap"></i> Doctor Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="doctor_dashboard.html" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="doctor_courses.html" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">My Courses</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Manage your courses, upload materials, and track student progress.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshCourses()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="createCourse()">
                        <i class="fas fa-plus"></i> Create Course
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Course Filter -->
            <section class="course-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search courses..." id="courseSearch" onkeyup="filterCourses()">
                        </div>
                        <div>
                            <select class="form-input" id="semesterFilter" onchange="filterCourses()">
                                <option value="">All Semesters</option>
                                <option value="Fall 2024">Fall 2024</option>
                                <option value="Spring 2024">Spring 2024</option>
                                <option value="Summer 2024">Summer 2024</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterCourses()">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Course Statistics -->
            <section class="course-stats" style="margin-bottom: 2rem;">
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
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">48</div>
                        <div style="color: var(--text-secondary);">Materials Uploaded</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">12</div>
                        <div style="color: var(--text-secondary);">Pending Tasks</div>
                    </div>
                </div>
            </section>

            <!-- My Courses -->
            <section class="my-courses">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chalkboard-teacher" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            My Courses
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="toggleView('grid')" id="gridViewBtn">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="btn btn-outline" onclick="toggleView('list')" id="listViewBtn">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Course Grid View -->
                    <div id="courseGridView" class="course-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
                        <!-- Course Card 1 -->
                        <div class="course-card" data-course="cs101" data-semester="Fall 2024" data-status="active">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 class="course-title">CS101 - Introduction to Programming</h3>
                                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.25rem 0;">Computer Science Department</p>
                                </div>
                                <div style="text-align: right;">
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span>
                                </div>
                            </div>
                            <p class="course-description">Introduction to programming concepts using Python. Learn variables, functions, and basic algorithms.</p>
                            <div class="course-meta">
                                <div>
                                    <i class="fas fa-users" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">45 Students</span>
                                </div>
                                <div>
                                    <i class="fas fa-calendar" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Mon, Wed, Fri 10:00 AM</span>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Progress</span>
                                    <span style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">Week 8 of 16</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 50%; background-color: var(--primary-color);"></div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <button class="btn btn-primary" style="flex: 1;" onclick="manageCourse('cs101')">
                                    <i class="fas fa-cog"></i> Manage
                                </button>
                                <button class="btn btn-outline" onclick="viewCourse('cs101')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline" onclick="editCourse('cs101')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Course Card 2 -->
                        <div class="course-card" data-course="cs201" data-semester="Fall 2024" data-status="active">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 class="course-title">CS201 - Data Structures</h3>
                                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.25rem 0;">Computer Science Department</p>
                                </div>
                                <div style="text-align: right;">
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span>
                                </div>
                            </div>
                            <p class="course-description">Study of fundamental data structures including arrays, linked lists, stacks, queues, and trees.</p>
                            <div class="course-meta">
                                <div>
                                    <i class="fas fa-users" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">38 Students</span>
                                </div>
                                <div>
                                    <i class="fas fa-calendar" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Tue, Thu 2:00 PM</span>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Progress</span>
                                    <span style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">Week 6 of 16</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 37.5%; background-color: var(--accent-color);"></div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <button class="btn btn-primary" style="flex: 1;" onclick="manageCourse('cs201')">
                                    <i class="fas fa-cog"></i> Manage
                                </button>
                                <button class="btn btn-outline" onclick="viewCourse('cs201')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline" onclick="editCourse('cs201')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Course Card 3 -->
                        <div class="course-card" data-course="cs301" data-semester="Fall 2024" data-status="active">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 class="course-title">CS301 - Algorithms</h3>
                                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0.25rem 0;">Computer Science Department</p>
                                </div>
                                <div style="text-align: right;">
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span>
                                </div>
                            </div>
                            <p class="course-description">Analysis and design of algorithms. Sorting, searching, graph algorithms, and complexity analysis.</p>
                            <div class="course-meta">
                                <div>
                                    <i class="fas fa-users" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">42 Students</span>
                                </div>
                                <div>
                                    <i class="fas fa-calendar" style="color: var(--text-secondary); margin-right: 0.25rem;"></i>
                                    <span style="font-size: 0.9rem;">Mon, Wed 3:00 PM</span>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Progress</span>
                                    <span style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">Week 4 of 16</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 25%; background-color: var(--success-color);"></div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <button class="btn btn-primary" style="flex: 1;" onclick="manageCourse('cs301')">
                                    <i class="fas fa-cog"></i> Manage
                                </button>
                                <button class="btn btn-outline" onclick="viewCourse('cs301')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline" onclick="editCourse('cs301')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Course List View -->
                    <div id="courseListView" class="course-list" style="display: none;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Students</th>
                                    <th>Schedule</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-course="cs101" data-semester="Fall 2024" data-status="active">
                                    <td>
                                        <div>
                                            <strong>CS101 - Introduction to Programming</strong>
                                            <br><small style="color: var(--text-secondary);">Computer Science Department</small>
                                        </div>
                                    </td>
                                    <td>45 Students</td>
                                    <td>Mon, Wed, Fri 10:00 AM</td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 100px;">
                                                <div class="progress-bar" style="width: 50%; background-color: var(--primary-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem;">Week 8/16</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="manageCourse('cs101')">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('cs101')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editCourse('cs101')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-course="cs201" data-semester="Fall 2024" data-status="active">
                                    <td>
                                        <div>
                                            <strong>CS201 - Data Structures</strong>
                                            <br><small style="color: var(--text-secondary);">Computer Science Department</small>
                                        </div>
                                    </td>
                                    <td>38 Students</td>
                                    <td>Tue, Thu 2:00 PM</td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 100px;">
                                                <div class="progress-bar" style="width: 37.5%; background-color: var(--accent-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem;">Week 6/16</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="manageCourse('cs201')">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('cs201')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editCourse('cs201')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-course="cs301" data-semester="Fall 2024" data-status="active">
                                    <td>
                                        <div>
                                            <strong>CS301 - Algorithms</strong>
                                            <br><small style="color: var(--text-secondary);">Computer Science Department</small>
                                        </div>
                                    </td>
                                    <td>42 Students</td>
                                    <td>Mon, Wed 3:00 PM</td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 100px;">
                                                <div class="progress-bar" style="width: 25%; background-color: var(--success-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem;">Week 4/16</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="manageCourse('cs301')">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('cs301')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editCourse('cs301')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                        <button class="btn btn-primary" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="uploadMaterials()">
                            <i class="fas fa-upload" style="font-size: 2rem;"></i>
                            <span>Upload Materials</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="createAssignment()">
                            <i class="fas fa-plus" style="font-size: 2rem;"></i>
                            <span>Create Assignment</span>
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
                <h3>Course Chat</h3>
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
    <script src="../../js/main.js"></script>
    <script>
        let currentView = 'grid';

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

        // Filter courses
        function filterCourses() {
            const searchTerm = document.getElementById('courseSearch').value.toLowerCase();
            const semesterFilter = document.getElementById('semesterFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const courseCards = document.querySelectorAll('.course-card');
            const courseRows = document.querySelectorAll('tbody tr');

            courseCards.forEach(card => {
                const courseName = card.querySelector('.course-title').textContent.toLowerCase();
                const semester = card.getAttribute('data-semester');
                const status = card.getAttribute('data-status');

                const matchesSearch = courseName.includes(searchTerm);
                const matchesSemester = !semesterFilter || semester === semesterFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesSemester && matchesStatus) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            courseRows.forEach(row => {
                const courseName = row.querySelector('td').textContent.toLowerCase();
                const semester = row.getAttribute('data-semester');
                const status = row.getAttribute('data-status');

                const matchesSearch = courseName.includes(searchTerm);
                const matchesSemester = !semesterFilter || semester === semesterFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesSemester && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Toggle view between grid and list
        function toggleView(view) {
            currentView = view;
            const gridView = document.getElementById('courseGridView');
            const listView = document.getElementById('courseListView');

            if (view === 'grid') {
                gridView.style.display = 'grid';
                listView.style.display = 'none';
            } else {
                gridView.style.display = 'none';
                listView.style.display = 'block';
            }

            updateViewButtons();
        }

        // Update view button states
        function updateViewButtons() {
            const gridBtn = document.getElementById('gridViewBtn');
            const listBtn = document.getElementById('listViewBtn');

            if (currentView === 'grid') {
                gridBtn.classList.add('btn-primary');
                gridBtn.classList.remove('btn-outline');
                listBtn.classList.add('btn-outline');
                listBtn.classList.remove('btn-primary');
            } else {
                listBtn.classList.add('btn-primary');
                listBtn.classList.remove('btn-outline');
                gridBtn.classList.add('btn-outline');
                gridBtn.classList.remove('btn-primary');
            }
        }

        // Manage course
        function manageCourse(courseId) {
            showNotification(`Managing course ${courseId}...`, 'info');
            setTimeout(() => {
                window.location.href = '../course-info.html';
            }, 1000);
        }

        // View course
        function viewCourse(courseId) {
            showNotification(`Viewing course ${courseId}...`, 'info');
            // In a real implementation, this would open course details
        }

        // Edit course
        function editCourse(courseId) {
            showNotification(`Editing course ${courseId}...`, 'info');
            // In a real implementation, this would open course editor
        }

        // Refresh courses
        function refreshCourses() {
            showNotification('Refreshing courses...', 'info');
            setTimeout(() => {
                showNotification('Courses refreshed successfully', 'success');
            }, 1000);
        }

        // Create course
        function createCourse() {
            showNotification('Opening course creation dialog...', 'info');
            // In a real implementation, this would open course creation form
        }

        // Quick actions
        function uploadMaterials() {
            showNotification('Opening material upload dialog...', 'info');
        }

        function createAssignment() {
            showNotification('Opening assignment creation dialog...', 'info');
        }

        function sendNotification() {
            showNotification('Opening notification dialog...', 'info');
        }

        function viewReports() {
            showNotification('Opening reports page...', 'info');
        }
    </script>
</body>
</html>
