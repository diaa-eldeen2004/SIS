<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Admin Portal</title>
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
            <a href="admin_manage_courses.php" class="nav-item">
                <i class="fas fa-book"></i> Manage Courses
            </a>
            <a href="admin_reports.php" class="nav-item">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="admin_calendar.php" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Calendar Management</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Manage university events, exams, and academic schedules.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshCalendar()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="createEvent()">
                        <i class="fas fa-plus"></i> Create Event
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Calendar Overview -->
            <section class="calendar-overview" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">48</div>
                        <div style="color: var(--text-secondary);">This Month</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">12</div>
                        <div style="color: var(--text-secondary);">Exams Scheduled</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">3</div>
                        <div style="color: var(--text-secondary);">Conflicts</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">1,335</div>
                        <div style="color: var(--text-secondary);">People Affected</div>
                    </div>
                </div>
            </section>

            <!-- Calendar Filter -->
            <section class="calendar-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <select class="form-input" id="eventTypeFilter" onchange="filterCalendar()">
                                <option value="">All Event Types</option>
                                <option value="exam">Exam</option>
                                <option value="assignment">Assignment Due</option>
                                <option value="holiday">Holiday</option>
                                <option value="meeting">Meeting</option>
                                <option value="event">University Event</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="departmentFilter" onchange="filterCalendar()">
                                <option value="">All Departments</option>
                                <option value="computer-science">Computer Science</option>
                                <option value="mathematics">Mathematics</option>
                                <option value="physics">Physics</option>
                                <option value="engineering">Engineering</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="monthFilter" onchange="filterCalendar()">
                                <option value="">All Months</option>
                                <option value="september">September 2024</option>
                                <option value="october">October 2024</option>
                                <option value="november">November 2024</option>
                                <option value="december">December 2024</option>
                            </select>
                        </div>
                        <div>
                            <button class="btn btn-outline" onclick="resetCalendarFilters()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Calendar View -->
            <section class="calendar-view">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-calendar-alt" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            September 2024 Calendar
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="previousMonth()">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-outline" onclick="nextMonth()">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <button class="btn btn-primary" onclick="goToToday()">
                                <i class="fas fa-calendar-day"></i> Today
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="calendar-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background-color: var(--border-color);">
                        <!-- Calendar Headers -->
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Sun</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Mon</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Tue</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Wed</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Thu</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Fri</div>
                        <div style="background-color: var(--surface-color); padding: 1rem; text-align: center; font-weight: 600; color: var(--text-primary);">Sat</div>

                        <!-- Calendar Days -->
                        <!-- Week 1 -->
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">1</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">2</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">3</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">4</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">5</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">6</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">7</div>
                        </div>

                        <!-- Week 2 -->
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">8</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">9</div>
                            <div style="background-color: var(--primary-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.25rem;">CS101 Class</div>
                            <div style="background-color: var(--accent-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem;">Faculty Meeting</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">10</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">11</div>
                            <div style="background-color: var(--primary-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.25rem;">CS101 Class</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">12</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">13</div>
                            <div style="background-color: var(--primary-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.25rem;">CS101 Class</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">14</div>
                        </div>

                        <!-- Week 3 -->
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">15</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">16</div>
                            <div style="background-color: var(--primary-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.25rem;">CS101 Class</div>
                            <div style="background-color: var(--accent-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem;">Faculty Meeting</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">17</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">18</div>
                            <div style="background-color: var(--primary-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.25rem;">CS101 Class</div>
                            <div style="background-color: var(--error-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem;">Assignment Due</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">19</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">20</div>
                            <div style="background-color: var(--primary-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.25rem;">CS101 Class</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">21</div>
                        </div>

                        <!-- Week 4 -->
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">22</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">23</div>
                            <div style="background-color: var(--primary-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.25rem;">CS101 Class</div>
                            <div style="background-color: var(--accent-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem;">Faculty Meeting</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">24</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">25</div>
                            <div style="background-color: var(--primary-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.25rem;">CS101 Class</div>
                            <div style="background-color: var(--warning-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem;">Midterm Exam</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">26</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">27</div>
                            <div style="background-color: var(--primary-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.25rem;">CS101 Class</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">28</div>
                        </div>

                        <!-- Week 5 -->
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">29</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">30</div>
                            <div style="background-color: var(--primary-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem; margin-bottom: 0.25rem;">CS101 Class</div>
                            <div style="background-color: var(--accent-color); color: white; padding: 0.25rem; border-radius: 4px; font-size: 0.8rem;">Faculty Meeting</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">1</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">2</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">3</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">4</div>
                        </div>
                        <div style="background-color: var(--surface-color); padding: 0.5rem; min-height: 120px; border: 1px solid var(--border-color);">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">5</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Upcoming Events -->
            <section class="upcoming-events" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-clock" style="color: var(--warning-color); margin-right: 0.5rem;"></i>
                            Upcoming Events
                        </h2>
                        <button class="btn btn-outline" onclick="viewAllEvents()">
                            <i class="fas fa-list"></i> View All
                        </button>
                    </div>
                    <div class="events-list">
                        <div class="event-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">CS101 - Introduction to Programming</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Regular class session</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">Today</div>
                                <div style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;">10:00 AM</div>
                            </div>
                        </div>
                        <div class="event-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--accent-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Faculty Meeting</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Monthly faculty coordination meeting</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">Today</div>
                                <div style="font-size: 0.9rem; color: var(--accent-color); font-weight: 500;">2:00 PM</div>
                            </div>
                        </div>
                        <div class="event-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--error-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Assignment Due - CS101</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Programming Assignment #5 submission deadline</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">Tomorrow</div>
                                <div style="font-size: 0.9rem; color: var(--error-color); font-weight: 500;">11:59 PM</div>
                            </div>
                        </div>
                        <div class="event-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem;">
                            <div style="width: 40px; height: 40px; background-color: var(--warning-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Midterm Exam - CS101</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Introduction to Programming midterm examination</p>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--text-secondary);">Sep 25, 2024</div>
                                <div style="font-size: 0.9rem; color: var(--warning-color); font-weight: 500;">9:00 AM</div>
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
                        <button class="btn btn-primary" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="createEvent()">
                            <i class="fas fa-plus" style="font-size: 2rem;"></i>
                            <span>Create Event</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="scheduleExam()">
                            <i class="fas fa-file-alt" style="font-size: 2rem;"></i>
                            <span>Schedule Exam</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="manageConflicts()">
                            <i class="fas fa-exclamation-triangle" style="font-size: 2rem;"></i>
                            <span>Manage Conflicts</span>
                        </button>
                        <button class="btn btn-outline" style="padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;" onclick="exportCalendar()">
                            <i class="fas fa-download" style="font-size: 2rem;"></i>
                            <span>Export Calendar</span>
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
                <h3>Calendar Chat</h3>
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
                        <input type="email" name="to" class="form-input" placeholder="user@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-input" placeholder="Calendar update" required>
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

        // Filter calendar
        function filterCalendar() {
            const eventTypeFilter = document.getElementById('eventTypeFilter').value;
            const departmentFilter = document.getElementById('departmentFilter').value;
            const monthFilter = document.getElementById('monthFilter').value;

            showNotification('Calendar filtered successfully', 'success');
        }

        // Reset calendar filters
        function resetCalendarFilters() {
            document.getElementById('eventTypeFilter').value = '';
            document.getElementById('departmentFilter').value = '';
            document.getElementById('monthFilter').value = '';
            showNotification('Calendar filters reset', 'success');
        }

        // Calendar navigation
        function previousMonth() {
            showNotification('Loading previous month...', 'info');
        }

        function nextMonth() {
            showNotification('Loading next month...', 'info');
        }

        function goToToday() {
            showNotification('Jumping to today...', 'info');
        }

        // Event management
        function createEvent() {
            showNotification('Opening event creation dialog...', 'info');
        }

        function scheduleExam() {
            showNotification('Opening exam scheduling dialog...', 'info');
        }

        function manageConflicts() {
            showNotification('Opening conflict management...', 'info');
        }

        function exportCalendar() {
            showNotification('Exporting calendar data...', 'info');
        }

        function viewAllEvents() {
            showNotification('Loading all events...', 'info');
        }

        function refreshCalendar() {
            showNotification('Refreshing calendar...', 'info');
            setTimeout(() => {
                showNotification('Calendar refreshed successfully', 'success');
            }, 1000);
        }
    </script>
</body>
</html>
