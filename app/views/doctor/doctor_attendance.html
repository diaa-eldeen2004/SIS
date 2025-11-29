<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Doctor Portal</title>
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
            <a href="doctor_courses.html" class="nav-item">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="doctor_assignments.html" class="nav-item">
                <i class="fas fa-tasks"></i> Assignments
            </a>
            <a href="doctor_attendance.html" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Attendance Management</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Mark attendance and track student participation.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshAttendance()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="takeAttendance()">
                        <i class="fas fa-plus"></i> Take Attendance
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Attendance Overview -->
            <section class="attendance-overview" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">92%</div>
                        <div style="color: var(--text-secondary);">Average Attendance</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">48</div>
                        <div style="color: var(--text-secondary);">Classes Conducted</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">8</div>
                        <div style="color: var(--text-secondary);">Low Attendance</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">125</div>
                        <div style="color: var(--text-secondary);">Total Students</div>
                    </div>
                </div>
            </section>

            <!-- Course Filter -->
            <section class="attendance-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <select class="form-input" id="courseFilter" onchange="filterAttendance()">
                                <option value="">All Courses</option>
                                <option value="cs101">CS101 - Introduction to Programming</option>
                                <option value="cs201">CS201 - Data Structures</option>
                                <option value="cs301">CS301 - Algorithms</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="monthFilter" onchange="filterAttendance()">
                                <option value="">All Months</option>
                                <option value="september">September 2024</option>
                                <option value="october">October 2024</option>
                                <option value="november">November 2024</option>
                                <option value="december">December 2024</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterAttendance()">
                                <option value="">All Status</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="excused">Excused</option>
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

            <!-- Course Attendance Management -->
            <section class="course-attendance">
                <!-- CS101 Attendance -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h2 class="card-title">
                                <i class="fas fa-laptop-code" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                                CS101 - Introduction to Programming
                            </h2>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);">94%</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Average Attendance</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">45</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Students</div>
                                </div>
                                <button class="btn btn-primary" onclick="takeAttendanceForCourse('cs101')">
                                    <i class="fas fa-plus"></i> Take Attendance
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div class="progress">
                            <div class="progress-bar" style="width: 94%; background-color: var(--success-color);"></div>
                        </div>
                    </div>

                    <!-- Recent Attendance Records -->
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="margin-bottom: 1rem; color: var(--text-primary);">Recent Classes</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-weight: 600; color: var(--text-primary);">Sep 15, 2024</span>
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Completed</span>
                                </div>
                                <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Mon, Wed, Fri 10:00 AM</div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Attendance:</span>
                                    <span style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">42/45 (93%)</span>
                                </div>
                            </div>
                            <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-weight: 600; color: var(--text-primary);">Sep 13, 2024</span>
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Completed</span>
                                </div>
                                <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Mon, Wed, Fri 10:00 AM</div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Attendance:</span>
                                    <span style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">44/45 (98%)</span>
                                </div>
                            </div>
                            <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-weight: 600; color: var(--text-primary);">Sep 11, 2024</span>
                                    <span style="background-color: var(--warning-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Pending</span>
                                </div>
                                <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Mon, Wed, Fri 10:00 AM</div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.9rem; color: var(--text-secondary);">Attendance:</span>
                                    <span style="font-size: 0.9rem; color: var(--warning-color); font-weight: 500;">Not Taken</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Attendance List -->
                    <div>
                        <h4 style="margin-bottom: 1rem; color: var(--text-primary);">Student Attendance Summary</h4>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Student ID</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Late</th>
                                        <th>Attendance %</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>John Doe</td>
                                        <td>2024001234</td>
                                        <td style="color: var(--success-color); font-weight: 500;">14</td>
                                        <td style="color: var(--error-color); font-weight: 500;">1</td>
                                        <td style="color: var(--warning-color); font-weight: 500;">0</td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <div class="progress" style="width: 60px;">
                                                    <div class="progress-bar" style="width: 93%; background-color: var(--success-color);"></div>
                                                </div>
                                                <span style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">93%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewStudentAttendance('2024001234')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Jane Smith</td>
                                        <td>2024001235</td>
                                        <td style="color: var(--success-color); font-weight: 500;">15</td>
                                        <td style="color: var(--error-color); font-weight: 500;">0</td>
                                        <td style="color: var(--warning-color); font-weight: 500;">0</td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <div class="progress" style="width: 60px;">
                                                    <div class="progress-bar" style="width: 100%; background-color: var(--success-color);"></div>
                                                </div>
                                                <span style="font-size: 0.9rem; color: var(--success-color); font-weight: 500;">100%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewStudentAttendance('2024001235')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Mike Johnson</td>
                                        <td>2024001236</td>
                                        <td style="color: var(--success-color); font-weight: 500;">12</td>
                                        <td style="color: var(--error-color); font-weight: 500;">3</td>
                                        <td style="color: var(--warning-color); font-weight: 500;">0</td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                <div class="progress" style="width: 60px;">
                                                    <div class="progress-bar" style="width: 80%; background-color: var(--warning-color);"></div>
                                                </div>
                                                <span style="font-size: 0.9rem; color: var(--warning-color); font-weight: 500;">80%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewStudentAttendance('2024001236')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- CS201 Attendance -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h2 class="card-title">
                                <i class="fas fa-sitemap" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                                CS201 - Data Structures
                            </h2>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);">89%</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Average Attendance</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">38</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Students</div>
                                </div>
                                <button class="btn btn-primary" onclick="takeAttendanceForCourse('cs201')">
                                    <i class="fas fa-plus"></i> Take Attendance
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div class="progress">
                            <div class="progress-bar" style="width: 89%; background-color: var(--accent-color);"></div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--success-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Present (34)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--error-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Absent (4)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--warning-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Late (2)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--primary-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Excused (1)</span>
                        </div>
                    </div>
                </div>

                <!-- CS301 Attendance -->
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h2 class="card-title">
                                <i class="fas fa-project-diagram" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                CS301 - Algorithms
                            </h2>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);">96%</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Average Attendance</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">42</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Students</div>
                                </div>
                                <button class="btn btn-primary" onclick="takeAttendanceForCourse('cs301')">
                                    <i class="fas fa-plus"></i> Take Attendance
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div class="progress">
                            <div class="progress-bar" style="width: 96%; background-color: var(--success-color);"></div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--success-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Present (40)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--error-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Absent (2)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--warning-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Late (0)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--primary-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Excused (0)</span>
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
                <h3>Attendance Chat</h3>
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
                        <input type="text" name="subject" class="form-input" placeholder="Attendance inquiry" required>
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

        // Filter attendance
        function filterAttendance() {
            const courseFilter = document.getElementById('courseFilter').value;
            const monthFilter = document.getElementById('monthFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const courseCards = document.querySelectorAll('.card');
            
            courseCards.forEach(card => {
                const courseTitle = card.querySelector('.card-title');
                if (courseTitle) {
                    const courseName = courseTitle.textContent.toLowerCase();
                    let shouldShow = true;

                    if (courseFilter && !courseName.includes(courseFilter)) {
                        shouldShow = false;
                    }

                    if (shouldShow) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });

            showNotification('Attendance filtered successfully', 'success');
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('courseFilter').value = '';
            document.getElementById('monthFilter').value = '';
            document.getElementById('statusFilter').value = '';
            
            const courseCards = document.querySelectorAll('.card');
            courseCards.forEach(card => {
                card.style.display = 'block';
            });

            showNotification('Filters reset successfully', 'success');
        }

        // Take attendance for specific course
        function takeAttendanceForCourse(courseId) {
            showNotification(`Opening attendance form for ${courseId}...`, 'info');
            // In a real implementation, this would open an attendance marking interface
        }

        // Take general attendance
        function takeAttendance() {
            showNotification('Opening attendance form...', 'info');
            // In a real implementation, this would open a general attendance form
        }

        // View student attendance
        function viewStudentAttendance(studentId) {
            showNotification(`Viewing attendance history for student ${studentId}...`, 'info');
            // In a real implementation, this would show detailed attendance history
        }

        // Refresh attendance
        function refreshAttendance() {
            showNotification('Refreshing attendance data...', 'info');
            // In a real implementation, this would reload attendance data from the server
            setTimeout(() => {
                showNotification('Attendance data refreshed successfully', 'success');
            }, 1000);
        }
    </script>
</body>
</html>
