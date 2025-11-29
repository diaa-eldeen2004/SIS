# Password Reset System - Setup Guide

## Overview
The password reset system is fully implemented with:
- 3-step form (Email → Verify Code → New Password)
- Email verification codes (6-digit numeric)
- 30-minute code expiration
- Password strength validation
- Server-side validation and security

## ✅ Files Ready

### Frontend Files
- **`app/views/auth/auth_forgot_password.php`** - Clean HTML form
- **`app/views/auth/reset-password.css`** - Separate stylesheet (382 lines)
- **`app/views/auth/reset-password.js`** - Form logic with API integration

### Backend Files
- **`app/controllers/AuthController.php`** - Contains:
  - `requestPasswordReset()` - Generates code, sends email
  - `resetPassword()` - Validates code and updates password
  - `sendPasswordResetEmail()` - HTML email template

- **`app/models/User.php`** - Contains:
  - `generateResetCode()` - Creates 6-digit code with 30-min expiration
  - `verifyResetCode()` - Validates code hasn't expired
  - `updatePassword()` - Hashes and updates password
  - `clearResetCode()` - Clears reset data

- **`public/api/auth.php`** - API routes:
  - `?action=request-password-reset` → requestPasswordReset()
  - `?action=reset-password` → resetPassword()

## 🚀 Required Setup Steps

### Step 1: Run Database Migration

Run this SQL in your MySQL database to add the password reset columns:

```sql
ALTER TABLE `users`
ADD COLUMN `password_reset_code` VARCHAR(255) DEFAULT NULL AFTER `password`,
ADD COLUMN `password_reset_expires` DATETIME DEFAULT NULL AFTER `password_reset_code`;

CREATE INDEX `idx_reset_code` ON `users`(`password_reset_code`);
```

**How to run:**
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select your database (usually `sis`)
3. Go to "SQL" tab
4. Copy and paste the SQL above
5. Click "Go"

### Step 2: Test Email Configuration (Optional)

By default, the system uses PHP's `mail()` function. 

**For Windows/XAMPP:**
- Edit `php.ini` and configure sendmail
- OR the reset code is logged to `error_log` for testing purposes

**For Testing Only:**
- Check `php_errors.log` in your XAMPP Apache logs folder
- The reset code will be logged as:
  ```
  Password Reset Code for email@example.com: 123456 (Expires: 2025-11-28 12:30:45)
  ```

### Step 3: Test the System

1. **Access the page:**
   - Go to: `http://localhost/sis/app/views/auth/auth_forgot_password.php`
   - Or click "Forgot Password?" link from login page

2. **Step 1 - Request Reset Code:**
   - Enter email address registered in your system
   - Click "Send Reset Code"
   - Check email or error_log for the 6-digit code

3. **Step 2 - Verify Code:**
   - Enter the 6-digit code in the individual boxes
   - Code auto-advances between boxes
   - Click "Verify Code"

4. **Step 3 - Create New Password:**
   - Enter new password (min 8 characters)
   - Confirm password matches
   - See password strength indicator
   - Click "Reset Password"
   - Should redirect to login page

## 🔐 Security Features

✅ **Server-side validation** - Role whitelist prevents privilege escalation
✅ **Email validation** - Checks email format and existence
✅ **Password hashing** - Uses PASSWORD_DEFAULT (bcrypt)
✅ **Code expiration** - 30 minutes
✅ **Prepared statements** - Prevents SQL injection
✅ **Rate limiting ready** - Can add to prevent brute force
✅ **Error logging** - All errors logged to error_log

## 📝 Database Schema

### Users Table Changes
```sql
Column: password_reset_code (VARCHAR(255))
- Stores the 6-digit reset code
- Null when no reset requested

Column: password_reset_expires (DATETIME)
- Stores expiration timestamp
- Used to validate code hasn't expired

Index: idx_reset_code
- Enables fast lookups by code
```

## 🐛 Troubleshooting

### Error: "Route not found" (404)
**Solution:** The API route might not be registered. Check `public/api/auth.php` has:
```php
case 'request-password-reset':
    $authController->requestPasswordReset();
    break;
case 'reset-password':
    $authController->resetPassword();
    break;
```

### Error: "Failed to generate reset code"
**Solution:** Database migration hasn't been run. The columns don't exist yet.

### Error: "Code sent but email not received"
**Solution:** 
- Check `php_errors.log` for the reset code
- Verify SMTP/sendmail is configured on your server
- Or use the logged code for testing

### Error: "Invalid or expired code"
**Solution:**
- Check code matches exactly (case-sensitive for digits)
- Verify code hasn't expired (30 minutes)
- Request a new code if expired

## 📊 API Examples

### Request Password Reset
```bash
POST /sis/public/api/auth.php?action=request-password-reset
Content-Type: application/json

{
  "email": "student@university.edu"
}

Response (200):
{
  "success": true,
  "message": "Reset code sent! Check your email..."
}
```

### Reset Password
```bash
POST /sis/public/api/auth.php?action=reset-password
Content-Type: application/json

{
  "email": "student@university.edu",
  "code": "123456",
  "newPassword": "NewPassword123!",
  "confirmPassword": "NewPassword123!"
}

Response (200):
{
  "success": true,
  "message": "Password reset successfully!"
}
```

## 🔄 Password Reset Flow

```
User → Click "Forgot Password"
  ↓
User → Enter email address
  ↓ (Step 1 Complete)
API → requestPasswordReset()
  ├→ Check email exists
  ├→ Generate 6-digit code
  ├→ Save code + 30-min expiration to DB
  └→ Send email with code (or log to error_log)
  ↓ (Email sent)
User → Enter 6-digit code
  ↓ (Step 2 Complete)
API → resetPassword() with code only
  ├→ Verify code exists and not expired
  └→ Return success (don't reset yet)
  ↓ (Code verified)
User → Enter new password
  ↓ (Step 3 Complete)
API → resetPassword() with new password
  ├→ Verify code again
  ├→ Hash new password
  ├→ Update DB with hashed password
  ├→ Clear reset code
  └→ Return success
  ↓
User → Redirected to login
  ↓
User → Login with new password ✓
```

## 📁 File Locations

```
app/
├── controllers/
│   └── AuthController.php (requestPasswordReset, resetPassword, sendPasswordResetEmail)
├── models/
│   └── User.php (generateResetCode, verifyResetCode, updatePassword, clearResetCode)
└── views/
    └── auth/
        ├── auth_forgot_password.php (HTML form)
        ├── reset-password.css (Styles)
        └── reset-password.js (Form logic)

public/
└── api/
    └── auth.php (API router with routes)

database/
└── migrations/
    └── 002_add_password_reset_to_users.sql (Schema migration)
```

## ✨ Next Enhancements (Optional)

- Rate limiting (5 requests per hour per email)
- SMS instead of email
- Passwordless login (email code instead of password)
- Two-factor authentication
- Account verification email after signup
- Admin panel for manual role assignment

---

**Last Updated:** November 28, 2025
**Status:** Ready for Testing ✅
