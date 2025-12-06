<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Student Portal</title>
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
            <a href="student_attendance.html" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Attendance</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Track your attendance across all courses.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshAttendance()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="exportAttendance()">
                        <i class="fas fa-download"></i> Export Report
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
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">95%</div>
                        <div style="color: var(--text-secondary);">Overall Attendance</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">38</div>
                        <div style="color: var(--text-secondary);">Classes Attended</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">2</div>
                        <div style="color: var(--text-secondary);">Classes Missed</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">1</div>
                        <div style="color: var(--text-secondary);">Late Arrivals</div>
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
                                <option value="calculus">Calculus I</option>
                                <option value="cs101">Computer Science 101</option>
                                <option value="physics">Physics I</option>
                                <option value="english">English Literature</option>
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

            <!-- Course Attendance Details -->
            <section class="course-attendance">
                <!-- Calculus I Attendance -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h2 class="card-title">
                                <i class="fas fa-calculator" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                                Calculus I - Dr. Sarah Smith
                            </h2>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);">96%</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Attendance Rate</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">24/25</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Classes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div class="progress">
                            <div class="progress-bar" style="width: 96%; background-color: var(--success-color);"></div>
                        </div>
                    </div>

                    <div class="attendance-calendar" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem; margin-bottom: 1rem;">
                        <div style="text-align: center; font-weight: 600; padding: 0.5rem; background-color: var(--surface-color); border-radius: 4px;">Mon</div>
                        <div style="text-align: center; font-weight: 600; padding: 0.5rem; background-color: var(--surface-color); border-radius: 4px;">Tue</div>
                        <div style="text-align: center; font-weight: 600; padding: 0.5rem; background-color: var(--surface-color); border-radius: 4px;">Wed</div>
                        <div style="text-align: center; font-weight: 600; padding: 0.5rem; background-color: var(--surface-color); border-radius: 4px;">Thu</div>
                        <div style="text-align: center; font-weight: 600; padding: 0.5rem; background-color: var(--surface-color); border-radius: 4px;">Fri</div>
                        <div style="text-align: center; font-weight: 600; padding: 0.5rem; background-color: var(--surface-color); border-radius: 4px;">Sat</div>
                        <div style="text-align: center; font-weight: 600; padding: 0.5rem; background-color: var(--surface-color); border-radius: 4px;">Sun</div>
                        
                        <!-- Calendar days with attendance status -->
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-02')">
                            <div style="font-weight: 600;">2</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-04')">
                            <div style="font-weight: 600;">4</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-06')">
                            <div style="font-weight: 600;">6</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-09')">
                            <div style="font-weight: 600;">9</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-11')">
                            <div style="font-weight: 600;">11</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day absent" style="text-align: center; padding: 0.75rem; background-color: var(--error-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-13')">
                            <div style="font-weight: 600;">13</div>
                            <div style="font-size: 0.8rem;">✗</div>
                        </div>
                        <div style="text-align: center; padding: 0.75rem; background-color: var(--background-color); border-radius: 4px;"></div>
                        
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-16')">
                            <div style="font-weight: 600;">16</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-18')">
                            <div style="font-weight: 600;">18</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-20')">
                            <div style="font-weight: 600;">20</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-23')">
                            <div style="font-weight: 600;">23</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-25')">
                            <div style="font-weight: 600;">25</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-27')">
                            <div style="font-weight: 600;">27</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div style="text-align: center; padding: 0.75rem; background-color: var(--background-color); border-radius: 4px;"></div>
                        
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-09-30')">
                            <div style="font-weight: 600;">30</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-02')">
                            <div style="font-weight: 600;">2</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-04')">
                            <div style="font-weight: 600;">4</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-07')">
                            <div style="font-weight: 600;">7</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-09')">
                            <div style="font-weight: 600;">9</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-11')">
                            <div style="font-weight: 600;">11</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div style="text-align: center; padding: 0.75rem; background-color: var(--background-color); border-radius: 4px;"></div>
                        
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-14')">
                            <div style="font-weight: 600;">14</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-16')">
                            <div style="font-weight: 600;">16</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-18')">
                            <div style="font-weight: 600;">18</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-21')">
                            <div style="font-weight: 600;">21</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-23')">
                            <div style="font-weight: 600;">23</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-25')">
                            <div style="font-weight: 600;">25</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div style="text-align: center; padding: 0.75rem; background-color: var(--background-color); border-radius: 4px;"></div>
                        
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-28')">
                            <div style="font-weight: 600;">28</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day present" style="text-align: center; padding: 0.75rem; background-color: var(--success-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-10-30')">
                            <div style="font-weight: 600;">30</div>
                            <div style="font-size: 0.8rem;">✓</div>
                        </div>
                        <div class="attendance-day absent" style="text-align: center; padding: 0.75rem; background-color: var(--error-color); color: white; border-radius: 4px; cursor: pointer;" onclick="showAttendanceDetails('calculus', '2024-11-01')">
                            <div style="font-weight: 600;">1</div>
                            <div style="font-size: 0.8rem;">✗</div>
                        </div>
                        <div style="text-align: center; padding: 0.75rem; background-color: var(--background-color); border-radius: 4px;"></div>
                        <div style="text-align: center; padding: 0.75rem; background-color: var(--background-color); border-radius: 4px;"></div>
                        <div style="text-align: center; padding: 0.75rem; background-color: var(--background-color); border-radius: 4px;"></div>
                        <div style="text-align: center; padding: 0.75rem; background-color: var(--background-color); border-radius: 4px;"></div>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--success-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Present (24)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--error-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Absent (1)</span>
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

                <!-- CS 101 Attendance -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h2 class="card-title">
                                <i class="fas fa-laptop-code" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                                Computer Science 101 - Dr. Michael Johnson
                            </h2>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);">92%</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Attendance Rate</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">23/25</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Classes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div class="progress">
                            <div class="progress-bar" style="width: 92%; background-color: var(--success-color);"></div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--success-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Present (23)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--error-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Absent (1)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--warning-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Late (1)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--primary-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Excused (0)</span>
                        </div>
                    </div>
                </div>

                <!-- Physics I Attendance -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h2 class="card-title">
                                <i class="fas fa-atom" style="color: var(--success-color); margin-right: 0.5rem;"></i>
                                Physics I - Dr. Emily Brown
                            </h2>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);">100%</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Attendance Rate</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">20/20</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Classes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div class="progress">
                            <div class="progress-bar" style="width: 100%; background-color: var(--success-color);"></div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--success-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Present (20)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--error-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Absent (0)</span>
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

                <!-- English Literature Attendance -->
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h2 class="card-title">
                                <i class="fas fa-book-open" style="color: var(--error-color); margin-right: 0.5rem;"></i>
                                English Literature - Dr. Robert Wilson
                            </h2>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--warning-color);">85%</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Attendance Rate</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--primary-color);">17/20</div>
                                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Classes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div class="progress">
                            <div class="progress-bar" style="width: 85%; background-color: var(--warning-color);"></div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--success-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Present (17)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--error-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Absent (3)</span>
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
                        <input type="email" name="from" class="form-input" placeholder="john.doe@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="email" name="to" class="form-input" placeholder="instructor@university.edu" required>
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

            // Filter attendance days based on status
            if (statusFilter) {
                const attendanceDays = document.querySelectorAll('.attendance-day');
                attendanceDays.forEach(day => {
                    if (day.classList.contains(statusFilter)) {
                        day.style.display = 'block';
                    } else {
                        day.style.display = 'none';
                    }
                });
            } else {
                const attendanceDays = document.querySelectorAll('.attendance-day');
                attendanceDays.forEach(day => {
                    day.style.display = 'block';
                });
            }
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

            const attendanceDays = document.querySelectorAll('.attendance-day');
            attendanceDays.forEach(day => {
                day.style.display = 'block';
            });

            showNotification('Filters reset successfully', 'success');
        }

        // Show attendance details
        function showAttendanceDetails(courseId, date) {
            showNotification(`Showing attendance details for ${courseId} on ${date}...`, 'info');
            // In a real implementation, this would open a modal with detailed attendance information
        }

        // Refresh attendance
        function refreshAttendance() {
            showNotification('Refreshing attendance data...', 'info');
            // In a real implementation, this would reload attendance data from the server
            setTimeout(() => {
                showNotification('Attendance data refreshed successfully', 'success');
            }, 1000);
        }

        // Export attendance report
        function exportAttendance() {
            showNotification('Generating attendance report...', 'info');
            // In a real implementation, this would generate and download a PDF/Excel report
            setTimeout(() => {
                showNotification('Attendance report exported successfully', 'success');
            }, 2000);
        }
    </script>
</body>
</html>
