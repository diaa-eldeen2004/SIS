<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admin Portal</title>
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
            <h2><i class="fas fa-graduation-cap"></i> Admin Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="admin_manage_students.php" class="nav-item">
                <i class="fas fa-user-graduate"></i> Manage Students
            </a>
            <a href="admin_manage_doctors.php" class="nav-item">
                <i class="fas fa-chalkboard-teacher"></i> Manage Doctors
            </a>
            <a href="admin_manage_courses.php" class="nav-item active">
                <i class="fas fa-book"></i> Manage Courses
            </a>
            <a href="admin_reports.php" class="nav-item">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="admin_calendar.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Calendar
            </a>
            <a href="admin_profile.php" class="nav-item">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="../app/settings.php" class="nav-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="../app/logout.php" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Manage Courses</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Create, edit, and manage course information and assignments.</p>
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
            <!-- Course Statistics -->
            <section class="course-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">150</div>
                        <div style="color: var(--text-secondary);">Total Courses</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">135</div>
                        <div style="color: var(--text-secondary);">Active Courses</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">10</div>
                        <div style="color: var(--text-secondary);">Pending Approval</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">8</div>
                        <div style="color: var(--text-secondary);">New This Semester</div>
                    </div>
                </div>
            </section>

            <!-- Course Filter -->
            <section class="course-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search courses..." id="courseSearch" onkeyup="filterCourses()">
                        </div>
                        <div>
                            <select class="form-input" id="departmentFilter" onchange="filterCourses()">
                                <option value="">All Departments</option>
                                <option value="computer-science">Computer Science</option>
                                <option value="mathematics">Mathematics</option>
                                <option value="physics">Physics</option>
                                <option value="engineering">Engineering</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="levelFilter" onchange="filterCourses()">
                                <option value="">All Levels</option>
                                <option value="100">100 Level</option>
                                <option value="200">200 Level</option>
                                <option value="300">300 Level</option>
                                <option value="400">400 Level</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterCourses()">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Courses List -->
            <section class="courses-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-book" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Course Directory
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="exportCourses()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button class="btn btn-outline" onclick="bulkActions()">
                                <i class="fas fa-tasks"></i> Bulk Actions
                            </button>
                        </div>
                    </div>

                    <!-- Courses Table -->
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                    <th>Course</th>
                                    <th>Code</th>
                                    <th>Department</th>
                                    <th>Instructor</th>
                                    <th>Students</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Course Row 1 -->
                                <tr data-department="computer-science" data-level="100" data-status="active">
                                    <td>
                                        <input type="checkbox" class="course-checkbox" value="CS101">
                                    </td>
                                    <td>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-primary);">Introduction to Programming</div>
                                            <div style="font-size: 0.9rem; color: var(--text-secondary);">Learn basic programming concepts using Python</div>
                                        </div>
                                    </td>
                                    <td>CS101</td>
                                    <td>Computer Science</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="width: 30px; height: 30px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8rem;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span style="font-size: 0.9rem;">Dr. Johnson</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;">45</span>
                                            <span style="font-size: 0.8rem; color: var(--text-secondary);">students</span>
                                        </div>
                                    </td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('CS101')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editCourse('CS101')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-accent" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="assignInstructor('CS101')">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Course Row 2 -->
                                <tr data-department="mathematics" data-level="200" data-status="active">
                                    <td>
                                        <input type="checkbox" class="course-checkbox" value="MATH201">
                                    </td>
                                    <td>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-primary);">Calculus II</div>
                                            <div style="font-size: 0.9rem; color: var(--text-secondary);">Advanced calculus concepts and applications</div>
                                        </div>
                                    </td>
                                    <td>MATH201</td>
                                    <td>Mathematics</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="width: 30px; height: 30px; background-color: var(--accent-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8rem;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span style="font-size: 0.9rem;">Dr. Williams</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;">38</span>
                                            <span style="font-size: 0.8rem; color: var(--text-secondary);">students</span>
                                        </div>
                                    </td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('MATH201')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editCourse('MATH201')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-accent" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="assignInstructor('MATH201')">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Course Row 3 -->
                                <tr data-department="physics" data-level="300" data-status="pending">
                                    <td>
                                        <input type="checkbox" class="course-checkbox" value="PHYS301">
                                    </td>
                                    <td>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-primary);">Quantum Mechanics</div>
                                            <div style="font-size: 0.9rem; color: var(--text-secondary);">Introduction to quantum physics principles</div>
                                        </div>
                                    </td>
                                    <td>PHYS301</td>
                                    <td>Physics</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="width: 30px; height: 30px; background-color: var(--warning-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8rem;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span style="font-size: 0.9rem;">Dr. Chen</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-size: 0.9rem; color: var(--text-secondary);">-</span>
                                            <span style="font-size: 0.8rem; color: var(--text-secondary);">students</span>
                                        </div>
                                    </td>
                                    <td><span style="background-color: var(--warning-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Pending</span></td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('PHYS301')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="approveCourse('PHYS301')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editCourse('PHYS301')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Course Row 4 -->
                                <tr data-department="engineering" data-level="400" data-status="inactive">
                                    <td>
                                        <input type="checkbox" class="course-checkbox" value="ENG401">
                                    </td>
                                    <td>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-primary);">Advanced Engineering Design</div>
                                            <div style="font-size: 0.9rem; color: var(--text-secondary);">Capstone engineering project course</div>
                                        </div>
                                    </td>
                                    <td>ENG401</td>
                                    <td>Engineering</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="width: 30px; height: 30px; background-color: var(--error-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8rem;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span style="font-size: 0.9rem;">Dr. Davis</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;">22</span>
                                            <span style="font-size: 0.8rem; color: var(--text-secondary);">students</span>
                                        </div>
                                    </td>
                                    <td><span style="background-color: var(--error-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Inactive</span></td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('ENG401')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="activateCourse('ENG401')">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editCourse('ENG401')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Course Row 5 -->
                                <tr data-department="computer-science" data-level="200" data-status="active">
                                    <td>
                                        <input type="checkbox" class="course-checkbox" value="CS201">
                                    </td>
                                    <td>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-primary);">Data Structures</div>
                                            <div style="font-size: 0.9rem; color: var(--text-secondary);">Study of fundamental data structures and algorithms</div>
                                        </div>
                                    </td>
                                    <td>CS201</td>
                                    <td>Computer Science</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="width: 30px; height: 30px; background-color: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8rem;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span style="font-size: 0.9rem;">Dr. Thompson</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;">42</span>
                                            <span style="font-size: 0.8rem; color: var(--text-secondary);">students</span>
                                        </div>
                                    </td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewCourse('CS201')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editCourse('CS201')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-accent" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="assignInstructor('CS201')">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div style="display: flex; justify-content: between; align-items: center; padding: 1rem; border-top: 1px solid var(--border-color);">
                        <div style="color: var(--text-secondary); font-size: 0.9rem;">
                            Showing 1-5 of 150 courses
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="previousPage()">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-primary">1</button>
                            <button class="btn btn-outline">2</button>
                            <button class="btn btn-outline">3</button>
                            <span style="padding: 0.5rem;">...</span>
                            <button class="btn btn-outline">30</button>
                            <button class="btn btn-outline" onclick="nextPage()">
                                <i class="fas fa-chevron-right"></i>
                            </button>
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
                        <button class="btn btn-primary" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="createCourse()">
                            <i class="fas fa-plus" style="font-size: 2rem;"></i>
                            <span>Create Course</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="importCourses()">
                            <i class="fas fa-upload" style="font-size: 2rem;"></i>
                            <span>Import Courses</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="bulkActions()">
                            <i class="fas fa-tasks" style="font-size: 2rem;"></i>
                            <span>Bulk Actions</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="exportCourses()">
                            <i class="fas fa-download" style="font-size: 2rem;"></i>
                            <span>Export Data</span>
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
                <h3>Course Management Chat</h3>
                <button class="chat-close" onclick="closeChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chat-body">
                <form class="chat-form">
                    <div class="form-group">
                        <label class="form-label">From</label>
                        <input type="email" name="from" class="form-input" placeholder="admin@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="email" name="to" class="form-input" placeholder="instructor@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Course assignment" required>
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
                <a href="../index.php">Home</a>
                <a href="../app/about.php">About Us</a>
                <a href="../app/contact.php">Contact</a>
                <a href="../app/help_center.php">Help Center</a>
            </div>
            <div class="footer-section">
                <h3>User Access</h3>
                <a href="../auth/auth_login.php">Student Login</a>
                <a href="../auth/auth_login.php">Doctor Login</a>
                <a href="../auth/auth_login.php">Admin Login</a>
                <a href="../auth/auth_signup.php">Register</a>
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

        // Filter courses
        function filterCourses() {
            const searchTerm = document.getElementById('courseSearch').value.toLowerCase();
            const departmentFilter = document.getElementById('departmentFilter').value;
            const levelFilter = document.getElementById('levelFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const courseRows = document.querySelectorAll('tbody tr');

            courseRows.forEach(row => {
                const courseName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const department = row.getAttribute('data-department');
                const level = row.getAttribute('data-level');
                const status = row.getAttribute('data-status');

                const matchesSearch = courseName.includes(searchTerm);
                const matchesDepartment = !departmentFilter || department === departmentFilter;
                const matchesLevel = !levelFilter || level === levelFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesDepartment && matchesLevel && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Toggle select all
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const courseCheckboxes = document.querySelectorAll('.course-checkbox');

            courseCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        // Course actions
        function viewCourse(courseCode) {
            showNotification(`Viewing course ${courseCode}...`, 'info');
        }

        function editCourse(courseCode) {
            showNotification(`Editing course ${courseCode}...`, 'info');
        }

        function assignInstructor(courseCode) {
            showNotification(`Assigning instructor to course ${courseCode}...`, 'info');
        }

        function approveCourse(courseCode) {
            if (confirm('Are you sure you want to approve this course?')) {
                showNotification(`Course ${courseCode} approved`, 'success');
            }
        }

        function activateCourse(courseCode) {
            if (confirm('Are you sure you want to activate this course?')) {
                showNotification(`Course ${courseCode} activated`, 'success');
            }
        }

        // General actions
        function createCourse() {
            showNotification('Opening course creation form...', 'info');
        }

        function importCourses() {
            showNotification('Opening course import dialog...', 'info');
        }

        function bulkActions() {
            showNotification('Opening bulk actions menu...', 'info');
        }

        function exportCourses() {
            showNotification('Exporting course data...', 'info');
        }

        function refreshCourses() {
            showNotification('Refreshing course data...', 'info');
            setTimeout(() => {
                showNotification('Course data refreshed successfully', 'success');
            }, 1000);
        }

        // Pagination
        function previousPage() {
            showNotification('Loading previous page...', 'info');
        }

        function nextPage() {
            showNotification('Loading next page...', 'info');
        }
    </script>
</body>
</html>
