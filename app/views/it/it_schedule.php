<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Schedule - IT Portal</title>
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
            <h2><i class="fas fa-laptop-code"></i> IT Portal</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="it_dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="it_schedule.php" class="nav-item active">
                <i class="fas fa-calendar-alt"></i> Semester Schedule
            </a>
            <a href="it_courses.php" class="nav-item">
                <i class="fas fa-book"></i> Course Management
            </a>
            <a href="it_enrollments.php" class="nav-item">
                <i class="fas fa-user-check"></i> Enrollment Requests
            </a>
            <a href="it_backups.php" class="nav-item">
                <i class="fas fa-database"></i> Backups & Restores
            </a>
            <a href="it_logs.php" class="nav-item">
                <i class="fas fa-file-alt"></i> System Logs
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
                    <h1 style="margin: 0; color: var(--text-primary);">Semester Schedule</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Build and manage official semester schedule sections.</p>
                </div>
                <button class="btn btn-primary" onclick="showCreateSectionModal()">
                    <i class="fas fa-plus"></i> Create Section
                </button>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Filters -->
            <section class="schedule-filters" style="margin-bottom: 2rem;">
                <div class="card">
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 200px;">
                            <label class="form-label">Search</label>
                            <input type="text" id="searchInput" class="form-input" placeholder="Search by course code, section..." onkeyup="filterSections()">
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Semester</label>
                            <select id="semesterFilter" class="form-input" onchange="filterSections()">
                                <option value="">All Semesters</option>
                                <option value="Fall 2024">Fall 2024</option>
                                <option value="Spring 2025">Spring 2025</option>
                                <option value="Summer 2025">Summer 2025</option>
                            </select>
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Type</label>
                            <select id="typeFilter" class="form-input" onchange="filterSections()">
                                <option value="">All Types</option>
                                <option value="lecture">Lecture</option>
                                <option value="lab">Lab</option>
                                <option value="tutorial">Tutorial</option>
                            </select>
                        </div>
                        <div class="form-group" style="min-width: 150px;">
                            <label class="form-label">Status</label>
                            <select id="statusFilter" class="form-input" onchange="filterSections()">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sections List -->
            <section class="sections-list">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-list" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Course Sections
                        </h2>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn btn-outline" onclick="exportSchedule()">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <div id="sectionsTable">
                        <table class="table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--border-color);">
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Course</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Section</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Type</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Doctor</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Room</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Time</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Capacity</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Enrolled</th>
                                    <th style="padding: 1rem; text-align: left; color: var(--text-primary);">Status</th>
                                    <th style="padding: 1rem; text-align: center; color: var(--text-primary);">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="sectionsTableBody">
                                <!-- Sections will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div id="noSections" style="padding: 3rem; text-align: center; display: none;">
                        <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                        <p style="color: var(--text-secondary);">No sections found. Create your first section to get started.</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Create Section Modal -->
    <div id="createSectionModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Create New Section</h2>
                <button class="modal-close" onclick="closeCreateSectionModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="createSectionForm" onsubmit="createSection(event)">
                    <div class="form-group">
                        <label class="form-label">Course <span style="color: var(--error-color);">*</span></label>
                        <select id="sectionCourse" class="form-input" required>
                            <option value="">Select Course</option>
                            <!-- Options will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Section Code <span style="color: var(--error-color);">*</span></label>
                        <input type="text" id="sectionCode" class="form-input" placeholder="e.g., A, B, 01" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type <span style="color: var(--error-color);">*</span></label>
                        <select id="sectionType" class="form-input" required>
                            <option value="">Select Type</option>
                            <option value="lecture">Lecture</option>
                            <option value="lab">Lab</option>
                            <option value="tutorial">Tutorial</option>
                        </select>
                    </div>
                    <div class="grid grid-2" style="gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Doctor <span style="color: var(--error-color);">*</span></label>
                            <select id="sectionDoctor" class="form-input" required>
                                <option value="">Select Doctor</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Room <span style="color: var(--error-color);">*</span></label>
                            <input type="text" id="sectionRoom" class="form-input" placeholder="e.g., Room 201" required>
                        </div>
                    </div>
                    <div class="grid grid-2" style="gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Days <span style="color: var(--error-color);">*</span></label>
                            <input type="text" id="sectionDays" class="form-input" placeholder="e.g., MWF, TTH" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time <span style="color: var(--error-color);">*</span></label>
                            <input type="text" id="sectionTime" class="form-input" placeholder="e.g., 10:00 AM - 11:30 AM" required>
                        </div>
                    </div>
                    <div class="grid grid-2" style="gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Capacity <span style="color: var(--error-color);">*</span></label>
                            <input type="number" id="sectionCapacity" class="form-input" min="1" value="30" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Semester <span style="color: var(--error-color);">*</span></label>
                            <select id="sectionSemester" class="form-input" required>
                                <option value="">Select Semester</option>
                                <option value="Fall 2024">Fall 2024</option>
                                <option value="Spring 2025">Spring 2025</option>
                                <option value="Summer 2025">Summer 2025</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea id="sectionNotes" class="form-input" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                    <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                        <button type="button" class="btn btn-outline" onclick="closeCreateSectionModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Section</button>
                    </div>
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
                <a href="../home.php">Home</a>
                <a href="../about.php">About Us</a>
                <a href="../contact.php">Contact</a>
                <a href="../help_center.php">Help Center</a>
            </div>
            <div class="footer-section">
                <h3>User Access</h3>
                <a href="../auth/auth_login.php">Student Login</a>
                <a href="../auth/auth_login.php">Doctor Login</a>
                <a href="../auth/auth_login.php">Admin Login</a>
                <a href="../auth/auth_sign.php">Register</a>
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
        // Load sections and form data
        document.addEventListener('DOMContentLoaded', function() {
            loadSections();
            loadCourses();
            loadDoctors();
        });

        function loadSections() {
            fetch('../../public/api/it.php?action=list-sections')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        displaySections(data.data);
                    } else {
                        document.getElementById('noSections').style.display = 'block';
                        document.getElementById('sectionsTable').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading sections:', error);
                    // Show sample data for demo
                    displaySections([]);
                });
        }

        function loadCourses() {
            fetch('../../public/api/courses.php?action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const select = document.getElementById('sectionCourse');
                        data.data.forEach(course => {
                            const option = document.createElement('option');
                            option.value = course.id;
                            option.textContent = `${course.course_code} - ${course.course_name}`;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading courses:', error));
        }

        function loadDoctors() {
            fetch('../../public/api/doctors.php?action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const select = document.getElementById('sectionDoctor');
                        data.data.forEach(doctor => {
                            const option = document.createElement('option');
                            option.value = doctor.id;
                            option.textContent = `Dr. ${doctor.first_name} ${doctor.last_name}`;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading doctors:', error));
        }

        function displaySections(sections) {
            const tbody = document.getElementById('sectionsTableBody');
            if (!sections || sections.length === 0) {
                document.getElementById('noSections').style.display = 'block';
                document.getElementById('sectionsTable').style.display = 'none';
                return;
            }

            document.getElementById('noSections').style.display = 'none';
            document.getElementById('sectionsTable').style.display = 'block';

            tbody.innerHTML = sections.map(section => `
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1rem;">
                        <div style="font-weight: 500; color: var(--text-primary);">${section.course_code || 'N/A'}</div>
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">${section.course_name || ''}</div>
                    </td>
                    <td style="padding: 1rem; color: var(--text-primary);">${section.section_code || ''}</td>
                    <td style="padding: 1rem;">
                        <span class="badge" style="background-color: ${getTypeColor(section.type)}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                            ${(section.type || '').charAt(0).toUpperCase() + (section.type || '').slice(1)}
                        </span>
                    </td>
                    <td style="padding: 1rem; color: var(--text-primary);">${section.doctor_name || 'TBD'}</td>
                    <td style="padding: 1rem; color: var(--text-primary);">${section.room || 'TBD'}</td>
                    <td style="padding: 1rem; color: var(--text-primary);">${section.schedule || 'TBD'}</td>
                    <td style="padding: 1rem; color: var(--text-primary);">${section.capacity || 0}</td>
                    <td style="padding: 1rem; color: var(--text-primary);">${section.enrolled || 0}</td>
                    <td style="padding: 1rem;">
                        <span class="badge" style="background-color: ${getStatusColor(section.status)}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                            ${(section.status || 'active').charAt(0).toUpperCase() + (section.status || 'active').slice(1)}
                        </span>
                    </td>
                    <td style="padding: 1rem; text-align: center;">
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editSection(${section.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.25rem;" onclick="deleteSection(${section.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function getTypeColor(type) {
            const colors = {
                'lecture': 'var(--primary-color)',
                'lab': 'var(--accent-color)',
                'tutorial': 'var(--success-color)'
            };
            return colors[type] || 'var(--secondary-color)';
        }

        function getStatusColor(status) {
            const colors = {
                'active': 'var(--success-color)',
                'pending': 'var(--warning-color)',
                'closed': 'var(--error-color)'
            };
            return colors[status] || 'var(--secondary-color)';
        }

        function filterSections() {
            // Implement filtering logic
            loadSections();
        }

        function showCreateSectionModal() {
            document.getElementById('createSectionModal').style.display = 'flex';
        }

        function closeCreateSectionModal() {
            document.getElementById('createSectionModal').style.display = 'none';
            document.getElementById('createSectionForm').reset();
        }

        function createSection(event) {
            event.preventDefault();
            const formData = {
                course_id: document.getElementById('sectionCourse').value,
                section_code: document.getElementById('sectionCode').value,
                type: document.getElementById('sectionType').value,
                doctor_id: document.getElementById('sectionDoctor').value,
                room: document.getElementById('sectionRoom').value,
                days: document.getElementById('sectionDays').value,
                time: document.getElementById('sectionTime').value,
                capacity: document.getElementById('sectionCapacity').value,
                semester: document.getElementById('sectionSemester').value,
                notes: document.getElementById('sectionNotes').value
            };

            fetch('../../public/api/it.php?action=create-section', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Section created successfully!', 'success');
                    closeCreateSectionModal();
                    loadSections();
                } else {
                    showNotification(data.message || 'Failed to create section', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            });
        }

        function editSection(id) {
            showNotification('Edit section functionality coming soon', 'info');
        }

        function deleteSection(id) {
            if (confirm('Are you sure you want to delete this section?')) {
                fetch('../../public/api/it.php?action=delete-section', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Section deleted successfully!', 'success');
                        loadSections();
                    } else {
                        showNotification(data.message || 'Failed to delete section', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
            }
        }

        function exportSchedule() {
            showNotification('Export functionality coming soon', 'info');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('createSectionModal');
            if (event.target == modal) {
                closeCreateSectionModal();
            }
        }
    </script>
</body>
</html>
