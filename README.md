# SIS

SIS Frontend TODO (Pages)
-------------------------

- [x] Auth: Login, Register, Forgot/Reset Password
- [x] Dashboard: KPI widgets and quick actions
- [x] Students: list, filters, CRUD modals
- [x] Student Profile: info, enrollments, grades, attendance tabs
- [x] Courses: list, filters, create/edit course
- [x] Course Details: schedule, prerequisites, roster
- [x] Enrollments: requests, approvals, waitlist management
- [x] Grades: entry forms, revisions log, bulk actions
- [x] Attendance: session tracker, CSV bulk upload
- [x] Reports: transcript, attendance summaries, exports (PDF/CSV)
- [x] Notifications: compose, scheduling, delivery history
- [x] Settings: profile, preferences, theme
- [x] Admin: users, roles, permissions management
- [x] Help/Support and custom 404

Structure
---------
- `pages/auth/` Login, Register, Forgot, Reset
- `pages/app/` Dashboard, Students, Courses, Enrollments, Grades, Attendance, Reports, Notifications, Settings, Admin Users, Admin Roles & Permissions, Help, 404, Student Profile, Course Details
- `css/` layout, components, themes (light/dark), auth.css, high-contrast.css
- `js/` shell.js (shared), main.js (root), auth.js (auth pages), i18n.js, modal.js, toast.js
- `assets/` favicon.ico, app-icon.svg

Next Up
-------
- [x] Student Profile page (tabs: info, enrollments, grades, attendance)
- [x] Course Details page (schedule, prerequisites, roster)

Recent Enhancements
-------------------
- [x] Table sorting, pagination, column visibility (Admin Users)
- [x] Form validation utilities (inline errors, Admin Users modal)
- [x] Loading skeletons and empty states (Admin Users)
- [x] Dashboard chart scaffold (Dashboard)
- [x] Print styles for reports/transcripts (Reports)
- [x] Favicon and app icons (assets/favicon.ico, assets/app-icon.svg)
- [x] Responsive/mobile QA and polish (CSS grid, media queries)
- [x] Accessibility audit (keyboard navigation, ARIA landmarks, focus rings)
- [x] Internationalization (i18n) scaffolding (js/i18n.js)
- [x] Theme polish + high-contrast option (css/high-contrast.css)
- [x] Shared modal component (js/modal.js)
- [x] Toast/notification component (js/toast.js)
- [x] Role-based UI/permissions matrix (README, code)
- [x] Role-based navbar visibility and Manage menu entries (code)
- [x] Gated Dashboard, Attendance, Reports, Notifications content by role (code)
- [x] Gated Manage pages (Settings, Students, Courses, Enrollments, Grades) by role (code)

Global Enhancements
-------------------
- [x] Shared modal component
- [x] Toast/notification component
- [x] Internationalization (i18n) scaffolding
- [x] Theme polish + high-contrast option

Role-Based Views (What each role sees)
-------------------------------------
- Student
  - Dashboard: personal KPIs (enrolled courses, attendance warnings), quick links
  - Attendance: personal attendance records and warnings
  - Reports: transcripts and personal summaries (download)
  - Notifications: inbox of announcements, deadlines
  - Settings: profile, preferences, theme
  - Manage menu: hidden (no Students/Courses/Enrollments/Grades admin tools)
- Teacher/TA
  - Dashboard: course-level KPIs (classes, pending grade entries, attendance alerts)
  - Attendance: mark sessions for own courses, bulk CSV upload
  - Reports: course performance summaries, export options
  - Notifications: compose messages to course cohorts
  - Manage: limited to own courses (Students read-only roster, Courses for own, Grades entry)
  - Settings: profile, preferences, theme
- Admin
  - Dashboard: institution KPIs, pending approvals, anomaly alerts
  - Attendance: global oversight, CSV imports
  - Reports: institution/program analytics, scheduled reports
  - Notifications: global announcements, scheduling, delivery history
  - Manage (full access): Settings, Students, Courses, Enrollments, Grades

Role-Based Page Visibility
-------------------------
Student should see → dashboard.html, student-profile.html, courses.html, attendance.html, reports.html, notifications.html, help.html, settings.html, 404.html

Doctor should see → dashboard.html, courses.html, course-details.html, attendance.html, grades.html, reports.html, notifications.html, help.html, settings.html, 404.html

Admin should see → dashboard.html, admin-users.html, admin-roles.html, students.html, courses.html, enrollments.html, grades.html, attendance.html, reports.html, notifications.html, help.html, settings.html, 404.html