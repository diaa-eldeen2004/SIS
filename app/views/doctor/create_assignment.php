<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment - Doctor Portal</title>
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
            <a href="doctor_assignments.html" class="nav-item active">
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
                    <h1 style="margin: 0; color: var(--text-primary);">Create Assignment</h1>
                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary);">Create and publish new assignments for your students.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="goBack()">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button class="btn btn-primary" onclick="saveDraft()">
                        <i class="fas fa-save"></i> Save Draft
                    </button>
                </div>
            </div>
        </header>

        <!-- Content Body -->
        <div class="content-body">
            <!-- Assignment Form -->
            <section class="assignment-form">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-plus-circle" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Assignment Details
                        </h2>
                    </div>
                    <form class="assignment-form-content" id="assignmentForm">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; padding: 1.5rem;">
                            <!-- Left Column -->
                            <div>
                                <div class="form-group">
                                    <label class="form-label">Course</label>
                                    <select class="form-input" name="course" required>
                                        <option value="">Select course</option>
                                        <option value="cs101">CS101 - Introduction to Programming</option>
                                        <option value="cs201">CS201 - Data Structures</option>
                                        <option value="cs301">CS301 - Algorithms</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Assignment Title</label>
                                    <input type="text" class="form-input" name="title" placeholder="Enter assignment title" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Assignment Type</label>
                                    <select class="form-input" name="type" required>
                                        <option value="">Select type</option>
                                        <option value="homework">Homework</option>
                                        <option value="project">Project</option>
                                        <option value="quiz">Quiz</option>
                                        <option value="lab">Lab Assignment</option>
                                        <option value="exam">Exam</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Due Date & Time</label>
                                    <input type="datetime-local" class="form-input" name="dueDate" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Points</label>
                                    <input type="number" class="form-input" name="points" placeholder="100" min="1" max="1000" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Late Submission Policy</label>
                                    <select class="form-input" name="latePolicy" required>
                                        <option value="no-late">No Late Submissions</option>
                                        <option value="penalty">Penalty per Day</option>
                                        <option value="grace-period">24 Hour Grace Period</option>
                                        <option value="flexible">Flexible Deadline</option>
                                    </select>
                                </div>

                                <div class="form-group" id="penaltyGroup" style="display: none;">
                                    <label class="form-label">Penalty Percentage</label>
                                    <input type="number" class="form-input" name="penaltyPercentage" placeholder="10" min="1" max="100">
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div>
                                <div class="form-group">
                                    <label class="form-label">Assignment Description</label>
                                    <textarea class="form-input" name="description" rows="6" placeholder="Describe the assignment requirements, objectives, and instructions..." required></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Submission Instructions</label>
                                    <textarea class="form-input" name="submissionInstructions" rows="4" placeholder="Provide specific instructions for submission format, file types, etc."></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Rubric/Grading Criteria</label>
                                    <textarea class="form-input" name="rubric" rows="4" placeholder="Describe how the assignment will be graded..."></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Additional Resources</label>
                                    <textarea class="form-input" name="resources" rows="3" placeholder="List any helpful resources, readings, or materials..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- File Attachments -->
                        <div style="padding: 1.5rem; border-top: 1px solid var(--border-color);">
                            <div class="form-group">
                                <label class="form-label">Assignment Files</label>
                                <div class="file-upload-area" onclick="document.getElementById('assignmentFiles').click()">
                                    <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                                    <p style="margin: 0; color: var(--text-secondary);">Click to upload assignment files or drag and drop</p>
                                    <p style="margin: 0.5rem 0 0 0; font-size: 0.8rem; color: var(--text-secondary);">PDF, DOC, DOCX, ZIP, RAR (Max 50MB per file)</p>
                                </div>
                                <input type="file" id="assignmentFiles" multiple style="display: none;" onchange="handleFileUpload()">
                                <div id="fileList" style="margin-top: 1rem;"></div>
                            </div>
                        </div>

                        <!-- Assignment Settings -->
                        <div style="padding: 1.5rem; border-top: 1px solid var(--border-color);">
                            <h3 style="margin-bottom: 1rem; color: var(--text-primary);">Assignment Settings</h3>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                                <div>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" name="allowGroupWork">
                                        <span>Allow Group Work</span>
                                    </label>
                                </div>
                                <div>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" name="requirePlagiarismCheck">
                                        <span>Require Plagiarism Check</span>
                                    </label>
                                </div>
                                <div>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" name="allowMultipleSubmissions">
                                        <span>Allow Multiple Submissions</span>
                                    </label>
                                </div>
                                <div>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" name="sendNotifications">
                                        <span>Send Notifications to Students</span>
                                    </label>
                                </div>
                                <div>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" name="publishImmediately">
                                        <span>Publish Immediately</span>
                                    </label>
                                </div>
                                <div>
                                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="checkbox" name="showInCalendar">
                                        <span>Show in Student Calendar</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 1rem; padding: 1.5rem; border-top: 1px solid var(--border-color);">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Publish Assignment
                            </button>
                            <button type="button" class="btn btn-outline" onclick="previewAssignment()">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="button" class="btn btn-outline" onclick="saveDraft()">
                                <i class="fas fa-save"></i> Save Draft
                            </button>
                            <button type="button" class="btn btn-outline" onclick="clearForm()">
                                <i class="fas fa-trash"></i> Clear Form
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Assignment Templates -->
            <section class="assignment-templates" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-magic" style="color: var(--accent-color); margin-right: 0.5rem;"></i>
                            Assignment Templates
                        </h2>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; padding: 1.5rem;">
                        <button class="btn btn-outline" onclick="loadTemplate('programming-assignment')" style="padding: 1rem; text-align: left;">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">Programming Assignment</div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);">Template for coding assignments with specific requirements</div>
                        </button>
                        <button class="btn btn-outline" onclick="loadTemplate('written-assignment')" style="padding: 1rem; text-align: left;">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">Written Assignment</div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);">Template for essays, reports, and written work</div>
                        </button>
                        <button class="btn btn-outline" onclick="loadTemplate('project-assignment')" style="padding: 1rem; text-align: left;">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">Project Assignment</div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);">Template for larger projects with milestones</div>
                        </button>
                        <button class="btn btn-outline" onclick="loadTemplate('quiz-assignment')" style="padding: 1rem; text-align: left;">
                            <div style="font-weight: 600; margin-bottom: 0.5rem;">Quiz Assignment</div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);">Template for quizzes and short assessments</div>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Recent Assignments -->
            <section class="recent-assignments" style="margin-top: 2rem;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-history" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                            Recent Assignments
                        </h2>
                        <button class="btn btn-outline" onclick="viewAllAssignments()">
                            <i class="fas fa-list"></i> View All
                        </button>
                    </div>
                    <div class="assignments-list">
                        <div class="assignment-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Assignment #5 - Data Structures</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS101 • Due: Tomorrow at 11:59 PM • 100 points</p>
                            </div>
                            <div style="display: flex; gap: 0.25rem;">
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="duplicateAssignment('1')">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editAssignment('1')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                        <div class="assignment-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-bottom: 1px solid var(--border-color);">
                            <div style="width: 40px; height: 40px; background-color: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Final Project - Web Application</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS201 • Due: Dec 15, 2024 • 200 points</p>
                            </div>
                            <div style="display: flex; gap: 0.25rem;">
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="duplicateAssignment('2')">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editAssignment('2')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                        <div class="assignment-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem;">
                            <div style="width: 40px; height: 40px; background-color: var(--warning-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.25rem 0; color: var(--text-primary);">Quiz #3 - Algorithms</h4>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">CS301 • Due: Next Week • 50 points</p>
                            </div>
                            <div style="display: flex; gap: 0.25rem;">
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="duplicateAssignment('3')">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="editAssignment('3')">
                                    <i class="fas fa-edit"></i>
                                </button>
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
                        <input type="email" name="from" class="form-input" placeholder="doctor@university.edu" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To</label>
                        <input type="email" name="to" class="form-input" placeholder="student@university.edu" required>
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

            // Set up assignment form submission
            const assignmentForm = document.getElementById('assignmentForm');
            if (assignmentForm) {
                assignmentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    publishAssignment();
                });
            }

            // Set up late policy change handler
            const latePolicySelect = document.querySelector('select[name="latePolicy"]');
            if (latePolicySelect) {
                latePolicySelect.addEventListener('change', function() {
                    const penaltyGroup = document.getElementById('penaltyGroup');
                    if (this.value === 'penalty') {
                        penaltyGroup.style.display = 'block';
                    } else {
                        penaltyGroup.style.display = 'none';
                    }
                });
            }
        });

        // Assignment functions
        function handleFileUpload() {
            const fileInput = document.getElementById('assignmentFiles');
            const fileList = document.getElementById('fileList');
            
            Array.from(fileInput.files).forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.style.cssText = `
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    padding: 0.5rem;
                    background-color: var(--background-color);
                    border-radius: 4px;
                    margin-bottom: 0.5rem;
                `;
                fileItem.innerHTML = `
                    <i class="fas fa-file" style="color: var(--primary-color);"></i>
                    <span style="flex: 1;">${file.name}</span>
                    <span style="font-size: 0.8rem; color: var(--text-secondary);">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                    <button type="button" onclick="removeFile(this)" style="background: none; border: none; color: var(--error-color); cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                fileList.appendChild(fileItem);
            });
        }

        function removeFile(button) {
            button.parentNode.remove();
        }

        function loadTemplate(templateType) {
            const templates = {
                'programming-assignment': {
                    title: 'Programming Assignment #6',
                    type: 'homework',
                    description: 'Write a program that implements the following requirements:\n\n1. Create a class that represents a [data structure]\n2. Implement methods for [specific operations]\n3. Include proper error handling\n4. Write comprehensive test cases\n5. Document your code with comments\n\nRequirements:\n- Use [programming language]\n- Follow coding standards\n- Submit source code and executable',
                    submissionInstructions: 'Submit your source code files (.java, .py, .cpp, etc.) in a ZIP archive. Include a README file with compilation and execution instructions.',
                    rubric: 'Grading Criteria:\n- Correctness (40%)\n- Code Quality (25%)\n- Documentation (15%)\n- Testing (20%)',
                    points: 100
                },
                'written-assignment': {
                    title: 'Written Assignment #4',
                    type: 'homework',
                    description: 'Write a comprehensive essay on the following topic:\n\n[Topic]\n\nYour essay should:\n- Be 1000-1500 words\n- Include proper citations\n- Follow academic writing standards\n- Address all aspects of the topic\n- Include a bibliography',
                    submissionInstructions: 'Submit as a PDF document. Use 12pt Times New Roman font, double-spaced, with 1-inch margins.',
                    rubric: 'Grading Criteria:\n- Content and Analysis (40%)\n- Organization and Structure (25%)\n- Writing Quality (20%)\n- Citations and References (15%)',
                    points: 100
                },
                'project-assignment': {
                    title: 'Final Project',
                    type: 'project',
                    description: 'Complete a comprehensive project that demonstrates your understanding of the course material.\n\nProject Requirements:\n- Choose a topic related to [course subject]\n- Implement a working solution\n- Create documentation\n- Present your work\n\nMilestones:\n- Proposal (Week 2)\n- Progress Report (Week 4)\n- Final Submission (Week 6)\n- Presentation (Week 7)',
                    submissionInstructions: 'Submit all project files, documentation, and a presentation video.',
                    rubric: 'Grading Criteria:\n- Technical Implementation (35%)\n- Documentation (25%)\n- Presentation (20%)\n- Innovation and Creativity (20%)',
                    points: 200
                },
                'quiz-assignment': {
                    title: 'Quiz #3',
                    type: 'quiz',
                    description: 'Complete the following quiz covering topics from Chapters 5-7.\n\nQuiz Format:\n- Multiple Choice Questions (20 points)\n- Short Answer Questions (15 points)\n- Problem Solving (15 points)\n\nTime Limit: 60 minutes\nAttempts: 2',
                    submissionInstructions: 'Complete the quiz online. Ensure you have a stable internet connection.',
                    rubric: 'Grading Criteria:\n- Multiple Choice: 1 point each\n- Short Answer: 3 points each\n- Problem Solving: 5 points each',
                    points: 50
                }
            };

            const template = templates[templateType];
            if (template) {
                document.querySelector('input[name="title"]').value = template.title;
                document.querySelector('select[name="type"]').value = template.type;
                document.querySelector('textarea[name="description"]').value = template.description;
                document.querySelector('textarea[name="submissionInstructions"]').value = template.submissionInstructions;
                document.querySelector('textarea[name="rubric"]').value = template.rubric;
                document.querySelector('input[name="points"]').value = template.points;
                showNotification('Template loaded successfully', 'success');
            }
        }

        function publishAssignment() {
            const formData = new FormData(document.getElementById('assignmentForm'));
            const title = formData.get('title');
            const course = formData.get('course');
            const type = formData.get('type');
            const dueDate = formData.get('dueDate');
            const points = formData.get('points');

            if (!title || !course || !type || !dueDate || !points) {
                showNotification('Please fill in all required fields', 'error');
                return;
            }

            showNotification('Publishing assignment...', 'info');
            
            setTimeout(() => {
                showNotification('Assignment published successfully!', 'success');
                document.getElementById('assignmentForm').reset();
                document.getElementById('penaltyGroup').style.display = 'none';
                document.getElementById('fileList').innerHTML = '';
            }, 2000);
        }

        function previewAssignment() {
            const formData = new FormData(document.getElementById('assignmentForm'));
            const title = formData.get('title');
            const description = formData.get('description');
            const course = formData.get('course');
            const type = formData.get('type');
            const dueDate = formData.get('dueDate');
            const points = formData.get('points');

            if (!title || !description) {
                showNotification('Please fill in title and description to preview', 'error');
                return;
            }

            // Create preview modal
            const previewModal = document.createElement('div');
            previewModal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            `;
            
            previewModal.innerHTML = `
                <div style="background: var(--surface-color); padding: 2rem; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80%; overflow-y: auto;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h3 style="margin: 0; color: var(--text-primary);">Assignment Preview</h3>
                        <button onclick="this.parentNode.parentNode.parentNode.remove()" style="background: none; border: none; font-size: 1.5rem; color: var(--text-secondary); cursor: pointer;">&times;</button>
                    </div>
                    <div style="border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 4px; background: var(--background-color);">
                        <h2 style="margin: 0 0 1rem 0; color: var(--text-primary);">${title}</h2>
                        <div style="margin-bottom: 1rem;">
                            <strong>Course:</strong> ${course} | <strong>Type:</strong> ${type} | <strong>Points:</strong> ${points} | <strong>Due:</strong> ${dueDate}
                        </div>
                        <div>
                            <strong>Description:</strong><br>
                            <div style="white-space: pre-wrap; margin-top: 0.5rem;">${description}</div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(previewModal);
        }

        function saveDraft() {
            showNotification('Draft saved successfully!', 'success');
        }

        function clearForm() {
            if (confirm('Are you sure you want to clear the form? All unsaved changes will be lost.')) {
                document.getElementById('assignmentForm').reset();
                document.getElementById('penaltyGroup').style.display = 'none';
                document.getElementById('fileList').innerHTML = '';
                showNotification('Form cleared', 'info');
            }
        }

        function duplicateAssignment(assignmentId) {
            showNotification(`Duplicating assignment ${assignmentId}...`, 'info');
        }

        function editAssignment(assignmentId) {
            showNotification(`Editing assignment ${assignmentId}...`, 'info');
        }

        function viewAllAssignments() {
            showNotification('Loading all assignments...', 'info');
        }

        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
