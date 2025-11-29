<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin Portal</title>
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
            <a href="admin_manage_students.php" class="nav-item active">
                <i class="fas fa-user-graduate"></i> Manage Students
            </a>
            <a href="admin_manage_doctors.php" class="nav-item">
                <i class="fas fa-chalkboard-teacher"></i> Manage Doctors
            </a>
            <a href="admin_manage_courses.php" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Manage Students</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Add, update, and manage student accounts and information.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshStudents()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="addStudent()">
                        <i class="fas fa-plus"></i> Add Student
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Student Statistics -->
            <section class="student-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">1,250</div>
                        <div style="color: var(--text-secondary);">Total Students</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">1,180</div>
                        <div style="color: var(--text-secondary);">Active Students</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">45</div>
                        <div style="color: var(--text-secondary);">Pending Approval</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">25</div>
                        <div style="color: var(--text-secondary);">New This Month</div>
                    </div>
                </div>
            </section>

            <!-- Student Filter -->
            <section class="student-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search students..." id="studentSearch" onkeyup="filterStudents()">
                        </div>
                        <div>
                            <select class="form-input" id="programFilter" onchange="filterStudents()">
                                <option value="">All Programs</option>
                                <option value="computer-science">Computer Science</option>
                                <option value="mathematics">Mathematics</option>
                                <option value="physics">Physics</option>
                                <option value="engineering">Engineering</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="yearFilter" onchange="filterStudents()">
                                <option value="">All Years</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                                <option value="2021">2021</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterStudents()">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Students List -->
            <section class="students-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-user-graduate" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Student Directory
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="exportStudents()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button class="btn btn-outline" onclick="bulkActions()">
                                <i class="fas fa-tasks"></i> Bulk Actions
                            </button>
                        </div>
                    </div>

                    <!-- Students Table -->
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                    <th>Student</th>
                                    <th>Student ID</th>
                                    <th>Program</th>
                                    <th>Year</th>
                                    <th>Status</th>
                                    <th>GPA</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Student Row 1 -->
                                <tr data-program="computer-science" data-year="2024" data-status="active">
                                    <td>
                                        <input type="checkbox" class="student-checkbox" value="2024001234">
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 40px; height: 40px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--text-primary);">John Doe</div>
                                                <div style="font-size: 0.9rem; color: var(--text-secondary);">john.doe@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>2024001234</td>
                                    <td>Computer Science</td>
                                    <td>2024</td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 60px;">
                                                <div class="progress-bar" style="width: 85%; background-color: var(--success-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">3.4</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewStudent('2024001234')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editStudent('2024001234')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="suspendStudent('2024001234')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Student Row 2 -->
                                <tr data-program="mathematics" data-year="2023" data-status="active">
                                    <td>
                                        <input type="checkbox" class="student-checkbox" value="2023001235">
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 40px; height: 40px; background-color: var(--accent-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--text-primary);">Jane Smith</div>
                                                <div style="font-size: 0.9rem; color: var(--text-secondary);">jane.smith@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>2023001235</td>
                                    <td>Mathematics</td>
                                    <td>2023</td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 60px;">
                                                <div class="progress-bar" style="width: 95%; background-color: var(--success-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">3.8</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewStudent('2023001235')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editStudent('2023001235')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="suspendStudent('2023001235')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Student Row 3 -->
                                <tr data-program="physics" data-year="2024" data-status="pending">
                                    <td>
                                        <input type="checkbox" class="student-checkbox" value="2024001236">
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 40px; height: 40px; background-color: var(--warning-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--text-primary);">Mike Johnson</div>
                                                <div style="font-size: 0.9rem; color: var(--text-secondary);">mike.johnson@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>2024001236</td>
                                    <td>Physics</td>
                                    <td>2024</td>
                                    <td><span style="background-color: var(--warning-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Pending</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 60px;">
                                                <div class="progress-bar" style="width: 0%; background-color: var(--warning-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem; color: var(--text-secondary);">-</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewStudent('2024001236')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="approveStudent('2024001236')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editStudent('2024001236')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Student Row 4 -->
                                <tr data-program="engineering" data-year="2022" data-status="suspended">
                                    <td>
                                        <input type="checkbox" class="student-checkbox" value="2022001237">
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 40px; height: 40px; background-color: var(--error-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--text-primary);">Sarah Wilson</div>
                                                <div style="font-size: 0.9rem; color: var(--text-secondary);">sarah.wilson@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>2022001237</td>
                                    <td>Engineering</td>
                                    <td>2022</td>
                                    <td><span style="background-color: var(--error-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Suspended</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 60px;">
                                                <div class="progress-bar" style="width: 60%; background-color: var(--error-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem; color: var(--error-color); font-weight: 500;">2.4</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewStudent('2022001237')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="reactivateStudent('2022001237')">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editStudent('2022001237')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Student Row 5 -->
                                <tr data-program="computer-science" data-year="2023" data-status="active">
                                    <td>
                                        <input type="checkbox" class="student-checkbox" value="2023001238">
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 40px; height: 40px; background-color: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--text-primary);">Alex Brown</div>
                                                <div style="font-size: 0.9rem; color: var(--text-secondary);">alex.brown@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>2023001238</td>
                                    <td>Computer Science</td>
                                    <td>2023</td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Active</span></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress" style="width: 60px;">
                                                <div class="progress-bar" style="width: 90%; background-color: var(--success-color);"></div>
                                            </div>
                                            <span style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">3.6</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewStudent('2023001238')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editStudent('2023001238')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="suspendStudent('2023001238')">
                                                <i class="fas fa-ban"></i>
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
                            Showing 1-5 of 1,250 students
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="previousPage()">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-primary">1</button>
                            <button class="btn btn-outline">2</button>
                            <button class="btn btn-outline">3</button>
                            <span style="padding: 0.5rem;">...</span>
                            <button class="btn btn-outline">250</button>
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
                        <button class="btn btn-primary" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="addStudent()">
                            <i class="fas fa-plus" style="font-size: 2rem;"></i>
                            <span>Add Student</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="importStudents()">
                            <i class="fas fa-upload" style="font-size: 2rem;"></i>
                            <span>Import Students</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="bulkActions()">
                            <i class="fas fa-tasks" style="font-size: 2rem;"></i>
                            <span>Bulk Actions</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="exportStudents()">
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
                <h3>Student Management Chat</h3>
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
                        <input type="email" name="to" class="form-input" placeholder="student@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Account notification" required>
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

        // Filter students
        function filterStudents() {
            const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
            const programFilter = document.getElementById('programFilter').value;
            const yearFilter = document.getElementById('yearFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const studentRows = document.querySelectorAll('tbody tr');

            studentRows.forEach(row => {
                const studentName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const program = row.getAttribute('data-program');
                const year = row.getAttribute('data-year');
                const status = row.getAttribute('data-status');

                const matchesSearch = studentName.includes(searchTerm);
                const matchesProgram = !programFilter || program === programFilter;
                const matchesYear = !yearFilter || year === yearFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesProgram && matchesYear && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Toggle select all
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const studentCheckboxes = document.querySelectorAll('.student-checkbox');

            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        // Student actions
        function viewStudent(studentId) {
            showNotification(`Viewing student ${studentId}...`, 'info');
        }

        function editStudent(studentId) {
            showNotification(`Editing student ${studentId}...`, 'info');
        }

        function suspendStudent(studentId) {
            if (confirm('Are you sure you want to suspend this student?')) {
                showNotification(`Student ${studentId} suspended`, 'success');
            }
        }

        function approveStudent(studentId) {
            if (confirm('Are you sure you want to approve this student?')) {
                showNotification(`Student ${studentId} approved`, 'success');
            }
        }

        function reactivateStudent(studentId) {
            if (confirm('Are you sure you want to reactivate this student?')) {
                showNotification(`Student ${studentId} reactivated`, 'success');
            }
        }

        // General actions
        function addStudent() {
            showNotification('Opening add student form...', 'info');
        }

        function importStudents() {
            showNotification('Opening student import dialog...', 'info');
        }

        function bulkActions() {
            showNotification('Opening bulk actions menu...', 'info');
        }

        function exportStudents() {
            showNotification('Exporting student data...', 'info');
        }

        function refreshStudents() {
            showNotification('Refreshing student data...', 'info');
            setTimeout(() => {
                showNotification('Student data refreshed successfully', 'success');
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
