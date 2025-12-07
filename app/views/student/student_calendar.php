<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Student Portal</title>
    <link rel="stylesheet" href="../css/styles.css">
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
            <a href="student_dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="student_courses.php" class="nav-item">
                <i class="fas fa-book"></i> Courses
            </a>
            <a href="student_schedule.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i> Official Schedule
            </a>
            <a href="student_assignments.php" class="nav-item">
                <i class="fas fa-tasks"></i> Assignments
            </a>
            <a href="student_attendance.php" class="nav-item">
                <i class="fas fa-calendar-check"></i> Attendance
            </a>
            <a href="student_calendar.php" class="nav-item active">
                <i class="fas fa-calendar-alt"></i> Calendar
            </a>
            <a href="student_notifications.php" class="nav-item">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a href="student_profile.php" class="nav-item">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="../settings.php" class="nav-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="../auth/logout.php" class="nav-item">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Academic Calendar</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">View assignments, exams, and important dates.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="refreshCalendar()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="addEvent()">
                        <i class="fas fa-plus"></i> Add Event
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Calendar Navigation -->
            <section class="calendar-navigation" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <button class="btn btn-outline" onclick="previousMonth()">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <h2 style="margin: 0; color: var(--text-primary);" id="currentMonth">September 2024</h2>
                            <button class="btn btn-outline" onclick="nextMonth()">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="toggleView('month')" id="monthViewBtn">
                                <i class="fas fa-calendar-alt"></i> Month
                            </button>
                            <button class="btn btn-outline" onclick="toggleView('week')" id="weekViewBtn">
                                <i class="fas fa-calendar-week"></i> Week
                            </button>
                            <button class="btn btn-outline" onclick="toggleView('list')" id="listViewBtn">
                                <i class="fas fa-list"></i> List
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Calendar View -->
            <section class="calendar-view" id="calendarView">
                <div class="card">
                    <div class="calendar">
                        <div class="calendar-header">
                            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background-color: var(--border-color);">
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Sunday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Monday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Tuesday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Wednesday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Thursday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Friday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Saturday</div>
                            </div>
                        </div>
                        <div class="calendar-grid" id="calendarGrid">
                            <!-- Calendar days will be generated by JavaScript -->
                        </div>
                    </div>
                </div>
            </section>

            <!-- Week View -->
            <section class="week-view" id="weekView" style="display: none;">
                <div class="card">
                    <div class="calendar">
                        <div class="calendar-header">
                            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background-color: var(--border-color);">
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Sunday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Monday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Tuesday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Wednesday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Thursday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Friday</div>
                                <div style="background-color: var(--primary-color); color: white; padding: 1rem; text-align: center; font-weight: 600;">Saturday</div>
                            </div>
                        </div>
                        <div class="calendar-grid" id="weekGrid" style="grid-template-columns: repeat(7, 1fr);">
                            <!-- Week days will be generated by JavaScript -->
                        </div>
                    </div>
                </div>
            </section>

            <!-- List View -->
            <section class="list-view" id="listView" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-list" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Upcoming Events
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <select class="form-input" id="eventFilter" onchange="filterEvents()">
                                <option value="">All Events</option>
                                <option value="assignment">Assignments</option>
                                <option value="exam">Exams</option>
                                <option value="lecture">Lectures</option>
                                <option value="personal">Personal</option>
                            </select>
                        </div>
                    </div>
                    <div class="events-list" id="eventsList">
                        <!-- Events will be populated by JavaScript -->
                    </div>
                </div>
            </section>

            <!-- Today's Events -->
            <section class="todays-events" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-calendar-day" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Today's Schedule
                        </h2>
                        <div style="text-align: right;">
                            <div style="font-size: 1.2rem; font-weight: 700; color: var(--text-primary);" id="todayDate">September 15, 2024</div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);">Sunday</div>
                        </div>
                    </div>
                    <div class="todays-schedule">
                        <div class="schedule-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 4px; height: 40px; background-color: var(--primary-color); border-radius: 2px;"></div>
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Math Homework #5 Due</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Calculus I - Assignment</p>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--warning-color); font-weight: 500;">11:59 PM</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">Due Today</div>
                            </div>
                        </div>
                        <div class="schedule-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 4px; height: 40px; background-color: var(--accent-color); border-radius: 2px;"></div>
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Study Group</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">Library - Physics Review</p>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">6:00 PM</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">2 hours</div>
                            </div>
                        </div>
                        <div class="schedule-item" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 4px; height: 40px; background-color: var(--success-color); border-radius: 2px;"></div>
                                <div>
                                    <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Personal Study Time</h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS101 - Programming Practice</p>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">8:00 PM</div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary);">1 hour</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Event Legend -->
            <section class="event-legend" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-info-circle" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Event Legend
                        </h2>
                    </div>
                    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--warning-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Assignments</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--error-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Exams</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--primary-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Lectures</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--success-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Personal Events</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background-color: var(--accent-color); border-radius: 2px;"></div>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Study Groups</span>
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
                <h3>Calendar Chat</h3>
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
                        <input type="text" name="subject" class="form-input" placeholder="Calendar question" required>
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
        let currentView = 'month';
        let currentDate = new Date(2024, 8, 15); // September 15, 2024

        // Mock events data
        const events = [
            { id: 1, title: 'Math Homework #5 Due', date: '2024-09-15', time: '11:59 PM', type: 'assignment', course: 'Calculus I', color: 'var(--warning-color)' },
            { id: 2, title: 'Physics Lab Report Due', date: '2024-09-18', time: '11:59 PM', type: 'assignment', course: 'Physics I', color: 'var(--warning-color)' },
            { id: 3, title: 'CS101 Midterm Exam', date: '2024-09-20', time: '10:00 AM', type: 'exam', course: 'CS 101', color: 'var(--error-color)' },
            { id: 4, title: 'English Essay Due', date: '2024-09-22', time: '11:59 PM', type: 'assignment', course: 'English Literature', color: 'var(--warning-color)' },
            { id: 5, title: 'Study Group', date: '2024-09-15', time: '6:00 PM', type: 'personal', course: 'Physics Review', color: 'var(--accent-color)' },
            { id: 6, title: 'Personal Study Time', date: '2024-09-15', time: '8:00 PM', type: 'personal', course: 'CS101 Practice', color: 'var(--success-color)' },
            { id: 7, title: 'Calculus Lecture', date: '2024-09-16', time: '10:00 AM', type: 'lecture', course: 'Calculus I', color: 'var(--primary-color)' },
            { id: 8, title: 'Physics Lab', date: '2024-09-17', time: '2:00 PM', type: 'lecture', course: 'Physics I', color: 'var(--primary-color)' },
            { id: 9, title: 'Math Quiz #4', date: '2024-09-25', time: '10:00 AM', type: 'exam', course: 'Calculus I', color: 'var(--error-color)' },
            { id: 10, title: 'Programming Project Due', date: '2024-09-28', time: '11:59 PM', type: 'assignment', course: 'CS 101', color: 'var(--warning-color)' }
        ];

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

            // Initialize calendar
            generateCalendar();
            updateViewButtons();
            updateTodayDate();
        });

        // Generate calendar
        function generateCalendar() {
            const calendarGrid = document.getElementById('calendarGrid');
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            // Get first day of month and number of days
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();
            
            // Clear calendar
            calendarGrid.innerHTML = '';
            
            // Add empty cells for days before month starts
            for (let i = 0; i < firstDay; i++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.style.cssText = `
                    background-color: var(--background-color);
                    padding: 1rem;
                    min-height: 80px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                `;
                dayElement.innerHTML = `<span style="color: var(--text-secondary);">${daysInPrevMonth - firstDay + i + 1}</span>`;
                calendarGrid.appendChild(dayElement);
            }
            
            // Add days of current month
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.style.cssText = `
                    background-color: var(--surface-color);
                    padding: 1rem;
                    min-height: 80px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                    border: 1px solid var(--border-color);
                `;
                
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayEvents = events.filter(event => event.date === dateStr);
                
                let dayContent = `<div style="font-weight: 600; margin-bottom: 0.25rem;">${day}</div>`;
                
                if (dayEvents.length > 0) {
                    dayContent += `<div style="display: flex; gap: 2px; flex-wrap: wrap;">`;
                    dayEvents.slice(0, 3).forEach(event => {
                        dayContent += `<div style="width: 6px; height: 6px; background-color: ${event.color}; border-radius: 50%;"></div>`;
                    });
                    if (dayEvents.length > 3) {
                        dayContent += `<div style="font-size: 0.7rem; color: var(--text-secondary);">+${dayEvents.length - 3}</div>`;
                    }
                    dayContent += `</div>`;
                }
                
                dayElement.innerHTML = dayContent;
                dayElement.onclick = () => showDayEvents(dateStr, dayEvents);
                calendarGrid.appendChild(dayElement);
            }
            
            // Add empty cells for days after month ends
            const totalCells = calendarGrid.children.length;
            const remainingCells = 42 - totalCells; // 6 weeks * 7 days
            for (let i = 1; i <= remainingCells; i++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.style.cssText = `
                    background-color: var(--background-color);
                    padding: 1rem;
                    min-height: 80px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                `;
                dayElement.innerHTML = `<span style="color: var(--text-secondary);">${i}</span>`;
                calendarGrid.appendChild(dayElement);
            }
            
            // Update month display
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'];
            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
        }

        // Show day events
        function showDayEvents(dateStr, dayEvents) {
            if (dayEvents.length === 0) {
                showNotification('No events for this day', 'info');
                return;
            }
            
            let eventDetails = `Events for ${dateStr}:\n\n`;
            dayEvents.forEach(event => {
                eventDetails += `• ${event.title}\n`;
                eventDetails += `  Time: ${event.time}\n`;
                eventDetails += `  Course: ${event.course}\n\n`;
            });
            
            showNotification(eventDetails, 'info');
        }

        // Toggle view
        function toggleView(view) {
            currentView = view;
            const calendarView = document.getElementById('calendarView');
            const weekView = document.getElementById('weekView');
            const listView = document.getElementById('listView');
            
            calendarView.style.display = 'none';
            weekView.style.display = 'none';
            listView.style.display = 'none';
            
            if (view === 'month') {
                calendarView.style.display = 'block';
            } else if (view === 'week') {
                weekView.style.display = 'block';
            } else if (view === 'list') {
                listView.style.display = 'block';
                generateEventList();
            }
            
            updateViewButtons();
        }

        // Update view buttons
        function updateViewButtons() {
            const monthBtn = document.getElementById('monthViewBtn');
            const weekBtn = document.getElementById('weekViewBtn');
            const listBtn = document.getElementById('listViewBtn');
            
            [monthBtn, weekBtn, listBtn].forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline');
            });
            
            if (currentView === 'month') {
                monthBtn.classList.remove('btn-outline');
                monthBtn.classList.add('btn-primary');
            } else if (currentView === 'week') {
                weekBtn.classList.remove('btn-outline');
                weekBtn.classList.add('btn-primary');
            } else if (currentView === 'list') {
                listBtn.classList.remove('btn-outline');
                listBtn.classList.add('btn-primary');
            }
        }

        // Generate event list
        function generateEventList() {
            const eventsList = document.getElementById('eventsList');
            const sortedEvents = events.sort((a, b) => new Date(a.date) - new Date(b.date));
            
            eventsList.innerHTML = '';
            
            sortedEvents.forEach(event => {
                const eventElement = document.createElement('div');
                eventElement.style.cssText = `
                    display: flex; 
                    justify-content: space-between; 
                    align-items: center; 
                    padding: 1rem; 
                    border-bottom: 1px solid var(--border-color);
                `;
                
                eventElement.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 4px; height: 40px; background-color: ${event.color}; border-radius: 2px;"></div>
                        <div>
                            <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">${event.title}</h4>
                            <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">${event.course}</p>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.9rem; color: var(--text-primary); font-weight: 500;">${event.time}</div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">${event.date}</div>
                    </div>
                `;
                
                eventsList.appendChild(eventElement);
            });
        }

        // Filter events
        function filterEvents() {
            const filter = document.getElementById('eventFilter').value;
            const eventElements = document.querySelectorAll('#eventsList > div');
            
            eventElements.forEach(element => {
                const eventTitle = element.querySelector('h4').textContent.toLowerCase();
                const shouldShow = !filter || eventTitle.includes(filter) || 
                    events.find(e => e.title === element.querySelector('h4').textContent)?.type === filter;
                
                element.style.display = shouldShow ? 'flex' : 'none';
            });
        }

        // Previous month
        function previousMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            generateCalendar();
        }

        // Next month
        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            generateCalendar();
        }

        // Update today's date
        function updateTodayDate() {
            const today = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('todayDate').textContent = today.toLocaleDateString('en-US', options);
        }

        // Refresh calendar
        function refreshCalendar() {
            showNotification('Refreshing calendar...', 'info');
            generateCalendar();
            setTimeout(() => {
                showNotification('Calendar refreshed successfully', 'success');
            }, 1000);
        }

        // Add event
        function addEvent() {
            showNotification('Opening add event dialog...', 'info');
            // In a real implementation, this would open a modal to add new events
        }
    </script>
</body>
</html>
