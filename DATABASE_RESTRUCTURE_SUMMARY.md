# Database Restructure Summary

## Overview
The system has been restructured to use 6 separate database tables for different user roles instead of a single `users` table with role-based fields.

## Changes Made

### 1. Database Structure
Created migration file: `database/migrations/005_create_role_tables.sql`

**New Tables:**
- `students` - Student-specific data (name, email, phone, student_number, admission_year, major, minor, gpa, transcript, status)
- `doctors` - Doctor-specific data (name, email, phone, department, bio)
- `advisors` - Advisor-specific data (name, email, phone, department, specialization, office_location, office_hours)
- `admins` - Admin-specific data (name, email, phone, admin_level, permissions)
- `it_officers` - IT Officer-specific data (name, email, phone, department, specialization, office_location)
- `users` - Default user role (name, email, phone, password reset fields)

**Junction Tables:**
- `doctor_courses` - Links doctors to courses they teach
- `student_current_courses` - Links students to courses they're taking
- `student_attendance` - Tracks student attendance

### 2. Signup Page Updates
**File:** `app/views/auth/auth_sign.php`
- ✅ Removed role selector dropdown
- ✅ Added phone number field
- ✅ Role is always set to 'user' for self-signup
- ✅ Updated form validation to include phone

### 3. Authentication Controller
**File:** `app/controllers/AuthController.php`
- ✅ Updated signup to always create users in `users` table with role 'user'
- ✅ Added phone field validation
- ✅ Updated login to search across all role tables
- ✅ Returns role and table information in session

### 4. User Model
**File:** `app/models/User.php`
- ✅ Complete rewrite to work with separate tables
- ✅ `emailExists()` - Checks all role tables
- ✅ `findByEmail()` - Searches all tables and returns role/table info
- ✅ `create()` - Creates user in `users` table
- ✅ Added methods for each role:
  - `createStudent()`, `updateStudent()`, `deleteStudent()`
  - `createDoctor()`, `updateDoctor()`, `deleteDoctor()`
  - `createAdvisor()`, `updateAdvisor()`, `deleteAdvisor()`
  - `createAdmin()`, `updateAdmin()`, `deleteAdmin()`
  - `createITOfficer()`, `updateITOfficer()`, `deleteITOfficer()`
- ✅ Password reset methods work across all tables

### 5. Controllers Created
- ✅ `app/controllers/StudentController.php` - Updated to use `students` table
- ✅ `app/controllers/DoctorController.php` - New controller for doctors
- ✅ `app/controllers/AdvisorController.php` - New controller for advisors
- ✅ `app/controllers/AdminController.php` - New controller for admins
- ✅ `app/controllers/ITOfficerController.php` - New controller for IT officers

### 6. API Endpoints Created
- ✅ `public/api/students.php` - Updated
- ✅ `public/api/doctors.php` - New endpoint
- ✅ `public/api/advisors.php` - New endpoint
- ✅ `public/api/admins.php` - New endpoint
- ✅ `public/api/it_officers.php` - New endpoint

### 7. Admin Forms
**File:** `app/views/admin/admin_manage_students.php`
- ✅ Updated to query from `students` table
- ✅ Added phone and admission_year fields to form
- ✅ Updated status options (active, inactive, suspended)
- ✅ Updated export CSV to include new fields

**Files Needing UI Updates:**
- `app/views/admin/admin_manage_doctors.php` - Needs form modal for create/edit
- `app/views/admin/admin_manage_advisor.php` - Needs form modal for create/edit
- `app/views/admin/admin_manage_it.php` - Needs form modal for create/edit
- Admin dashboard - May need admin user management page

## Next Steps

1. **Run Migration:**
   ```sql
   -- Execute database/migrations/005_create_role_tables.sql
   ```

2. **Update Admin Forms:**
   - Add create/edit modals for doctors, advisors, admins, and IT officers
   - Connect forms to respective API endpoints
   - Update table displays to show data from new tables

3. **Data Migration (if needed):**
   - If you have existing data in the old `users` table, create a migration script to move data to appropriate role tables

4. **Testing:**
   - Test signup flow (should create in `users` table)
   - Test admin creating each role type
   - Test login for each role type
   - Test password reset for each role type

## Important Notes

- **Self-Signup:** Users can only sign up as 'user' role. All other roles must be created by admins.
- **Email Uniqueness:** Email addresses must be unique across ALL role tables.
- **Password Reset:** Works across all tables - the system searches all tables to find the user.
- **Login:** The system searches all role tables to find the user and determines their role automatically.

## API Endpoints

All endpoints follow the pattern: `/public/api/{role}.php?action={action}`

**Actions available:**
- `list` - GET - List all records
- `get` - GET - Get single record (requires `id` parameter)
- `create` - POST - Create new record
- `update` - POST - Update existing record (requires `id` in body)
- `delete` - POST - Delete record (requires `id` in body)

**Example:**
```
POST /public/api/doctors.php?action=create
Body: {
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@university.edu",
  "phone": "+1234567890",
  "department": "Computer Science",
  "bio": "Professor of Computer Science",
  "password": "optional_password"
}
```

