<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - Student Portal</title>
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
            <a href="student_assignments.html" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Assignments</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">View and submit your course assignments.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshAssignments()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="showSubmissionHistory()">
                        <i class="fas fa-history"></i> Submission History
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Assignment Filter -->
            <section class="assignment-filter" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <input type="text" class="form-input" placeholder="Search assignments..." id="assignmentSearch" onkeyup="filterAssignments()">
                        </div>
                        <div>
                            <select class="form-input" id="courseFilter" onchange="filterAssignments()">
                                <option value="">All Courses</option>
                                <option value="calculus">Calculus I</option>
                                <option value="cs101">Computer Science 101</option>
                                <option value="physics">Physics I</option>
                                <option value="english">English Literature</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="statusFilter" onchange="filterAssignments()">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="submitted">Submitted</option>
                                <option value="graded">Graded</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                        <div>
                            <select class="form-input" id="dueDateFilter" onchange="filterAssignments()">
                                <option value="">All Due Dates</option>
                                <option value="today">Due Today</option>
                                <option value="week">Due This Week</option>
                                <option value="month">Due This Month</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Assignment Statistics -->
            <section class="assignment-stats" style="margin-bottom: 2rem;">
                <div class="grid grid-4">
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">3</div>
                        <div style="color: var(--text-secondary);">Pending</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">8</div>
                        <div style="color: var(--text-secondary);">Submitted</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">5</div>
                        <div style="color: var(--text-secondary);">Graded</div>
                    </div>
                    <div class="card" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem;">1</div>
                        <div style="color: var(--text-secondary);">Overdue</div>
                    </div>
                </div>
            </section>

            <!-- Assignments List -->
            <section class="assignments-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-tasks" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            All Assignments
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="toggleView('list')" id="listViewBtn">
                                <i class="fas fa-list"></i>
                            </button>
                            <button class="btn btn-outline" onclick="toggleView('cards')" id="cardsViewBtn">
                                <i class="fas fa-th"></i>
                            </button>
                        </div>
                    </div>

                    <!-- List View -->
                    <div id="listView" class="assignment-list-view">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Course</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Grade</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Pending Assignment -->
                                <tr data-course="calculus" data-status="pending" data-due="today">
                                    <td>
                                        <div>
                                            <strong>Math Homework #5</strong>
                                            <br><small style="color: var(--text-secondary);">Solve calculus problems from chapters 3-4</small>
                                        </div>
                                    </td>
                                    <td>Calculus I</td>
                                    <td>
                                        <div style="color: var(--warning-color); font-weight: 500;">Sep 15, 2024</div>
                                        <small style="color: var(--text-secondary);">Due Tomorrow</small>
                                    </td>
                                    <td><span style="background-color: var(--warning-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Pending</span></td>
                                    <td>-</td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewAssignment('math-hw5')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="submitAssignment('math-hw5')">
                                                <i class="fas fa-upload"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Submitted Assignment -->
                                <tr data-course="cs101" data-status="submitted" data-due="week">
                                    <td>
                                        <div>
                                            <strong>Programming Project</strong>
                                            <br><small style="color: var(--text-secondary);">Create a Python calculator application</small>
                                        </div>
                                    </td>
                                    <td>CS 101</td>
                                    <td>
                                        <div style="color: var(--primary-color); font-weight: 500;">Sep 18, 2024</div>
                                        <small style="color: var(--text-secondary);">Due in 3 days</small>
                                    </td>
                                    <td><span style="background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Submitted</span></td>
                                    <td>-</td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewAssignment('prog-project')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewSubmission('prog-project')">
                                                <i class="fas fa-file-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Graded Assignment -->
                                <tr data-course="physics" data-status="graded" data-due="month">
                                    <td>
                                        <div>
                                            <strong>Physics Lab Report</strong>
                                            <br><small style="color: var(--text-secondary);">Analysis of pendulum motion experiment</small>
                                        </div>
                                    </td>
                                    <td>Physics I</td>
                                    <td>
                                        <div style="color: var(--text-secondary); font-weight: 500;">Sep 10, 2024</div>
                                        <small style="color: var(--text-secondary);">Completed</small>
                                    </td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Graded</span></td>
                                    <td>
                                        <div style="color: var(--success-color); font-weight: 700;">A-</div>
                                        <small style="color: var(--text-secondary);">92/100</small>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewAssignment('physics-lab')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewFeedback('physics-lab')">
                                                <i class="fas fa-comments"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Overdue Assignment -->
                                <tr data-course="english" data-status="overdue" data-due="today">
                                    <td>
                                        <div>
                                            <strong>Essay Assignment</strong>
                                            <br><small style="color: var(--text-secondary);">Write a 1000-word analysis of Shakespeare's Hamlet</small>
                                        </div>
                                    </td>
                                    <td>English Literature</td>
                                    <td>
                                        <div style="color: var(--error-color); font-weight: 500;">Sep 12, 2024</div>
                                        <small style="color: var(--error-color);">Overdue</small>
                                    </td>
                                    <td><span style="background-color: var(--error-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Overdue</span></td>
                                    <td>-</td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewAssignment('essay-hamlet')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="submitAssignment('essay-hamlet')">
                                                <i class="fas fa-upload"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- More assignments... -->
                                <tr data-course="calculus" data-status="graded" data-due="month">
                                    <td>
                                        <div>
                                            <strong>Math Quiz #3</strong>
                                            <br><small style="color: var(--text-secondary);">Online quiz on derivatives</small>
                                        </div>
                                    </td>
                                    <td>Calculus I</td>
                                    <td>
                                        <div style="color: var(--text-secondary); font-weight: 500;">Sep 8, 2024</div>
                                        <small style="color: var(--text-secondary);">Completed</small>
                                    </td>
                                    <td><span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Graded</span></td>
                                    <td>
                                        <div style="color: var(--success-color); font-weight: 700;">A</div>
                                        <small style="color: var(--text-secondary);">98/100</small>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.25rem;">
                                            <button class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewAssignment('math-quiz3')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="viewFeedback('math-quiz3')">
                                                <i class="fas fa-comments"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cards View -->
                    <div id="cardsView" class="assignment-cards-view" style="display: none;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
                            <!-- Assignment Card 1 -->
                            <div class="assignment-card" data-course="calculus" data-status="pending" data-due="today" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background-color: var(--surface-color);">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <div>
                                        <h3 style="margin: 0; color: var(--text-primary);">Math Homework #5</h3>
                                        <p style="margin: 0.25rem 0; color: var(--text-secondary); font-size: 0.9rem;">Calculus I</p>
                                    </div>
                                    <span style="background-color: var(--warning-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Pending</span>
                                </div>
                                <p style="color: var(--text-secondary); margin-bottom: 1rem;">Solve calculus problems from chapters 3-4</p>
                                <div style="margin-bottom: 1rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Due Date</span>
                                        <span style="font-size: 0.9rem; color: var(--warning-color); font-weight: 500;">Sep 15, 2024</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Status</span>
                                        <span style="font-size: 0.9rem; color: var(--warning-color);">Due Tomorrow</span>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn btn-primary" style="flex: 1;" onclick="viewAssignment('math-hw5')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-success" onclick="submitAssignment('math-hw5')">
                                        <i class="fas fa-upload"></i> Submit
                                    </button>
                                </div>
                            </div>

                            <!-- Assignment Card 2 -->
                            <div class="assignment-card" data-course="cs101" data-status="submitted" data-due="week" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background-color: var(--surface-color);">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <div>
                                        <h3 style="margin: 0; color: var(--text-primary);">Programming Project</h3>
                                        <p style="margin: 0.25rem 0; color: var(--text-secondary); font-size: 0.9rem;">CS 101</p>
                                    </div>
                                    <span style="background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Submitted</span>
                                </div>
                                <p style="color: var(--text-secondary); margin-bottom: 1rem;">Create a Python calculator application</p>
                                <div style="margin-bottom: 1rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Due Date</span>
                                        <span style="font-size: 0.9rem; color: var(--primary-color); font-weight: 500;">Sep 18, 2024</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Status</span>
                                        <span style="font-size: 0.9rem; color: var(--primary-color);">Due in 3 days</span>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn btn-primary" style="flex: 1;" onclick="viewAssignment('prog-project')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-outline" onclick="viewSubmission('prog-project')">
                                        <i class="fas fa-file-alt"></i> Submission
                                    </button>
                                </div>
                            </div>

                            <!-- Assignment Card 3 -->
                            <div class="assignment-card" data-course="physics" data-status="graded" data-due="month" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background-color: var(--surface-color);">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <div>
                                        <h3 style="margin: 0; color: var(--text-primary);">Physics Lab Report</h3>
                                        <p style="margin: 0.25rem 0; color: var(--text-secondary); font-size: 0.9rem;">Physics I</p>
                                    </div>
                                    <span style="background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Graded</span>
                                </div>
                                <p style="color: var(--text-secondary); margin-bottom: 1rem;">Analysis of pendulum motion experiment</p>
                                <div style="margin-bottom: 1rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Grade</span>
                                        <span style="font-size: 1.2rem; color: var(--success-color); font-weight: 700;">A- (92/100)</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Due Date</span>
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Sep 10, 2024</span>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn btn-primary" style="flex: 1;" onclick="viewAssignment('physics-lab')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-outline" onclick="viewFeedback('physics-lab')">
                                        <i class="fas fa-comments"></i> Feedback
                                    </button>
                                </div>
                            </div>

                            <!-- Assignment Card 4 -->
                            <div class="assignment-card" data-course="english" data-status="overdue" data-due="today" style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; background-color: var(--surface-color);">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                    <div>
                                        <h3 style="margin: 0; color: var(--text-primary);">Essay Assignment</h3>
                                        <p style="margin: 0.25rem 0; color: var(--text-secondary); font-size: 0.9rem;">English Literature</p>
                                    </div>
                                    <span style="background-color: var(--error-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">Overdue</span>
                                </div>
                                <p style="color: var(--text-secondary); margin-bottom: 1rem;">Write a 1000-word analysis of Shakespeare's Hamlet</p>
                                <div style="margin-bottom: 1rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Due Date</span>
                                        <span style="font-size: 0.9rem; color: var(--error-color); font-weight: 500;">Sep 12, 2024</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-size: 0.9rem; color: var(--text-secondary);">Status</span>
                                        <span style="font-size: 0.9rem; color: var(--error-color);">Overdue</span>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn btn-primary" style="flex: 1;" onclick="viewAssignment('essay-hamlet')">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-warning" onclick="submitAssignment('essay-hamlet')">
                                        <i class="fas fa-upload"></i> Submit
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
                <h3>Assignment Chat</h3>
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
                        <input type="text" name="subject" class="form-input" placeholder="Assignment question" required>
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
        let currentView = 'list';

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

        // Filter assignments
        function filterAssignments() {
            const searchTerm = document.getElementById('assignmentSearch').value.toLowerCase();
            const courseFilter = document.getElementById('courseFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const dueDateFilter = document.getElementById('dueDateFilter').value;

            const assignmentRows = document.querySelectorAll('tbody tr');
            const assignmentCards = document.querySelectorAll('.assignment-card');

            assignmentRows.forEach(row => {
                const assignmentName = row.querySelector('td').textContent.toLowerCase();
                const course = row.getAttribute('data-course');
                const status = row.getAttribute('data-status');
                const dueDate = row.getAttribute('data-due');

                const matchesSearch = assignmentName.includes(searchTerm);
                const matchesCourse = !courseFilter || course === courseFilter;
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesDueDate = !dueDateFilter || dueDate === dueDateFilter;

                if (matchesSearch && matchesCourse && matchesStatus && matchesDueDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            assignmentCards.forEach(card => {
                const assignmentName = card.querySelector('h3').textContent.toLowerCase();
                const course = card.getAttribute('data-course');
                const status = card.getAttribute('data-status');
                const dueDate = card.getAttribute('data-due');

                const matchesSearch = assignmentName.includes(searchTerm);
                const matchesCourse = !courseFilter || course === courseFilter;
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesDueDate = !dueDateFilter || dueDate === dueDateFilter;

                if (matchesSearch && matchesCourse && matchesStatus && matchesDueDate) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Toggle view between list and cards
        function toggleView(view) {
            currentView = view;
            const listView = document.getElementById('listView');
            const cardsView = document.getElementById('cardsView');

            if (view === 'list') {
                listView.style.display = 'block';
                cardsView.style.display = 'none';
            } else {
                listView.style.display = 'none';
                cardsView.style.display = 'block';
            }

            updateViewButtons();
        }

        // Update view button states
        function updateViewButtons() {
            const listBtn = document.getElementById('listViewBtn');
            const cardsBtn = document.getElementById('cardsViewBtn');

            if (currentView === 'list') {
                listBtn.classList.add('btn-primary');
                listBtn.classList.remove('btn-outline');
                cardsBtn.classList.add('btn-outline');
                cardsBtn.classList.remove('btn-primary');
            } else {
                cardsBtn.classList.add('btn-primary');
                cardsBtn.classList.remove('btn-outline');
                listBtn.classList.add('btn-outline');
                listBtn.classList.remove('btn-primary');
            }
        }

        // View assignment details
        function viewAssignment(assignmentId) {
            showNotification(`Opening assignment details for ${assignmentId}...`, 'info');
            // In a real implementation, this would open a modal or navigate to assignment details
        }

        // Submit assignment
        function submitAssignment(assignmentId) {
            showNotification(`Opening submission form for ${assignmentId}...`, 'info');
            // In a real implementation, this would open a file upload modal
        }

        // View submission
        function viewSubmission(assignmentId) {
            showNotification(`Viewing submission for ${assignmentId}...`, 'info');
            // In a real implementation, this would show the submitted files
        }

        // View feedback
        function viewFeedback(assignmentId) {
            showNotification(`Viewing feedback for ${assignmentId}...`, 'info');
            // In a real implementation, this would show instructor feedback
        }

        // Refresh assignments
        function refreshAssignments() {
            showNotification('Refreshing assignments...', 'info');
            // In a real implementation, this would reload assignment data from the server
            setTimeout(() => {
                showNotification('Assignments refreshed successfully', 'success');
            }, 1000);
        }

        // Show submission history
        function showSubmissionHistory() {
            showNotification('Opening submission history...', 'info');
            // In a real implementation, this would open a modal or navigate to submission history
        }
    </script>
</body>
</html>
