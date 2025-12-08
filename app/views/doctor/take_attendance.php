<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance - Doctor Portal</title>
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
                    <h1 style="margin: 0; color: var(--text-primary);">Take Attendance</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Mark attendance for your current class session.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="goBack()">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button class="btn btn-primary" onclick="saveAttendance()">
                        <i class="fas fa-save"></i> Save Attendance
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Class Information -->
            <section class="class-info" style="margin-bottom: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chalkboard-teacher" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Class Information
                        </h2>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1.5rem;">
                        <div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Course</div>
                            <div style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary);">CS101 - Introduction to Programming</div>
                        </div>
                        <div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Date & Time</div>
                            <div style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary);">Today, 10:00 AM - 11:30 AM</div>
                        </div>
                        <div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Room</div>
                            <div style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary);">Room 201</div>
                        </div>
                        <div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Total Students</div>
                            <div style="font-size: 1.1rem; font-weight: 600; color: var(--primary-color);">45</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="quick-actions" style="margin-bottom: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-bolt" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Quick Actions
                        </h2>
                    </div>
                    <div style="display: flex; gap: 1rem; padding: 1.5rem; flex-wrap: wrap;">
                        <button class="btn btn-success" onclick="markAllPresent()">
                            <i class="fas fa-check-circle"></i> Mark All Present
                        </button>
                        <button class="btn btn-warning" onclick="markAllAbsent()">
                            <i class="fas fa-times-circle"></i> Mark All Absent
                        </button>
                        <button class="btn btn-outline" onclick="loadPreviousAttendance()">
                            <i class="fas fa-history"></i> Load Previous
                        </button>
                        <button class="btn btn-outline" onclick="exportAttendance()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </section>

            <!-- Attendance List -->
            <section class="attendance-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-users" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Student Attendance
                        </h2>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <div style="display: flex; gap: 0.5rem;">
                                <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                    <i class="fas fa-check"></i> Present: <span id="presentCount">0</span>
                                </span>
                                <span style="background-color: var(--error-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                    <i class="fas fa-times"></i> Absent: <span id="absentCount">0</span>
                                </span>
                            </div>
                            <input type="text" class="form-input" placeholder="Search students..." id="studentSearch" onkeyup="searchStudents()" style="width: 200px;">
                        </div>
                    </div>

                    <!-- Students Table -->
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Student ID</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTable">
                                <!-- Student Row 1 -->
                                <tr data-student-id="2024001234">
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
                                    <td>
                                        <select class="form-input" style="width: 120px;" onchange="updateAttendanceCount()">
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="late">Late</option>
                                            <option value="excused">Excused</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="markPresent('2024001234')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-error" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="markAbsent('2024001234')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Student Row 2 -->
                                <tr data-student-id="2024001235">
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
                                    <td>2024001235</td>
                                    <td>
                                        <select class="form-input" style="width: 120px;" onchange="updateAttendanceCount()">
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="late">Late</option>
                                            <option value="excused">Excused</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="markPresent('2024001235')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-error" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="markAbsent('2024001235')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Student Row 3 -->
                                <tr data-student-id="2024001236">
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
                                    <td>
                                        <select class="form-input" style="width: 120px;" onchange="updateAttendanceCount()">
                                            <option value="present">Present</option>
                                            <option value="absent" selected>Absent</option>
                                            <option value="late">Late</option>
                                            <option value="excused">Excused</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="markPresent('2024001236')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-error" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="markAbsent('2024001236')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Student Row 4 -->
                                <tr data-student-id="2024001237">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 40px; height: 40px; background-color: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--text-primary);">Sarah Wilson</div>
                                                <div style="font-size: 0.9rem; color: var(--text-secondary);">sarah.wilson@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>2024001237</td>
                                    <td>
                                        <select class="form-input" style="width: 120px;" onchange="updateAttendanceCount()">
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="late" selected>Late</option>
                                            <option value="excused">Excused</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="markPresent('2024001237')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-error" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="markAbsent('2024001237')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Student Row 5 -->
                                <tr data-student-id="2024001238">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 40px; height: 40px; background-color: var(--error-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: var(--text-primary);">Alex Brown</div>
                                                <div style="font-size: 0.9rem; color: var(--text-secondary);">alex.brown@university.edu</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>2024001238</td>
                                    <td>
                                        <select class="form-input" style="width: 120px;" onchange="updateAttendanceCount()">
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="late">Late</option>
                                            <option value="excused" selected>Excused</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="markPresent('2024001238')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-error" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="markAbsent('2024001238')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Attendance Summary -->
            <section class="attendance-summary" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-chart-pie" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Attendance Summary
                        </h2>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; padding: 1.5rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--success-color); margin-bottom: 0.5rem;">42</div>
                            <div style="color: var(--text-secondary);">Present</div>
                            <div class="progress" style="margin-top: 0.5rem;">
                                <div class="progress-bar" style="width: 93%; background-color: var(--success-color);"></div>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--error-color); margin-bottom: 0.5rem;">1</div>
                            <div style="color: var(--text-secondary);">Absent</div>
                            <div class="progress" style="margin-top: 0.5rem;">
                                <div class="progress-bar" style="width: 2%; background-color: var(--error-color);"></div>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--warning-color); margin-bottom: 0.5rem;">1</div>
                            <div style="color: var(--text-secondary);">Late</div>
                            <div class="progress" style="margin-top: 0.5rem;">
                                <div class="progress-bar" style="width: 2%; background-color: var(--warning-color);"></div>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">1</div>
                            <div style="color: var(--text-secondary);">Excused</div>
                            <div class="progress" style="margin-top: 0.5rem;">
                                <div class="progress-bar" style="width: 2%; background-color: var(--primary-color);"></div>
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
                <h3>Attendance Chat</h3>
                <button class="chat-close" onclick="closeChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chat-body">
                <form class="chat-form">
                    <div class="form-group">
                        <label class="form-label">From</label>
                        <input type="email" name="from" class="form-input" placeholder="doctor@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="email" name="to" class="form-input" placeholder="student@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Attendance notification" required>
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
            
            // Initialize attendance counts
            updateAttendanceCount();
        });

        // Attendance functions
        function markPresent(studentId) {
            const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
            const select = row.querySelector('select');
            select.value = 'present';
            updateAttendanceCount();
            showNotification(`Marked student ${studentId} as present`, 'success');
        }

        function markAbsent(studentId) {
            const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
            const select = row.querySelector('select');
            select.value = 'absent';
            updateAttendanceCount();
            showNotification(`Marked student ${studentId} as absent`, 'info');
        }

        function updateAttendanceCount() {
            const rows = document.querySelectorAll('#studentsTable tr');
            let presentCount = 0;
            let absentCount = 0;
            let lateCount = 0;
            let excusedCount = 0;

            rows.forEach(row => {
                const select = row.querySelector('select');
                if (select) {
                    switch (select.value) {
                        case 'present':
                            presentCount++;
                            break;
                        case 'absent':
                            absentCount++;
                            break;
                        case 'late':
                            lateCount++;
                            break;
                        case 'excused':
                            excusedCount++;
                            break;
                    }
                }
            });

            document.getElementById('presentCount').textContent = presentCount;
            document.getElementById('absentCount').textContent = absentCount;
        }

        function markAllPresent() {
            const selects = document.querySelectorAll('#studentsTable select');
            selects.forEach(select => {
                select.value = 'present';
            });
            updateAttendanceCount();
            showNotification('All students marked as present', 'success');
        }

        function markAllAbsent() {
            const selects = document.querySelectorAll('#studentsTable select');
            selects.forEach(select => {
                select.value = 'absent';
            });
            updateAttendanceCount();
            showNotification('All students marked as absent', 'info');
        }

        function searchStudents() {
            const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
            const rows = document.querySelectorAll('#studentsTable tr');

            rows.forEach(row => {
                const studentName = row.querySelector('td:first-child').textContent.toLowerCase();
                const studentId = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                
                if (studentName.includes(searchTerm) || studentId.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function loadPreviousAttendance() {
            showNotification('Loading previous attendance data...', 'info');
        }

        function exportAttendance() {
            showNotification('Exporting attendance data...', 'info');
        }

        function saveAttendance() {
            showNotification('Saving attendance data...', 'info');
            setTimeout(() => {
                showNotification('Attendance saved successfully!', 'success');
            }, 1500);
        }

        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
