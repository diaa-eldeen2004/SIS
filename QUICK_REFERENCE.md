# Password Reset System - Quick Reference

## 🚀 Getting Started (5 Minutes)

### Step 1: Run Database Migration
Visit: `http://localhost/sis/run-migrations.php`
- Adds `password_reset_code` and `password_reset_expires` columns
- Creates index on reset code

### Step 2: Test the System
Visit: `http://localhost/sis/app/views/auth/auth_forgot_password.php`
- Enter email from existing user account
- Check email or `php_errors.log` for 6-digit code
- Enter code to verify
- Set new password and confirm

### Step 3: Login
Visit: `http://localhost/sis/app/views/auth/auth_login.php`
- Use same email address
- Enter new password you just set

---

## 📋 Files Overview

| File | Purpose | Lines |
|------|---------|-------|
| `auth_forgot_password.php` | HTML form | 135 |
| `reset-password.css` | Styles | 382 |
| `reset-password.js` | Form logic | 344 |
| `AuthController.php` | Backend logic | 481 |
| `User.php` | Database methods | 123 |
| `public/api/auth.php` | API routes | 45 |

---

## 🔑 Key Features

- ✅ 3-step form (Email → Code → Password)
- ✅ 6-digit numeric reset code
- ✅ 30-minute code expiration
- ✅ Password strength indicator
- ✅ Email sending (with logging fallback)
- ✅ Server-side validation
- ✅ SQL injection prevention
- ✅ Password hashing (bcrypt)

---

## 🧪 Testing Scenarios

### Scenario 1: Normal Reset
1. Email: `student@example.com` (existing user)
2. Code: From email/logs
3. New Password: `NewPassword123`
4. Result: ✅ Success

### Scenario 2: Non-existent Email
1. Email: `doesnotexist@example.com`
2. Result: ✅ Generic success message (security)

### Scenario 3: Expired Code
1. Wait 31+ minutes
2. Try to verify code
3. Result: ❌ "Invalid or expired code"

### Scenario 4: Wrong Password Match
1. Password: `NewPassword123`
2. Confirm: `Different123`
3. Result: ❌ "Passwords do not match"

### Scenario 5: Weak Password
1. Password: `short`
2. Result: ❌ "Password must be at least 8 characters"

---

## 📊 API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `?action=request-password-reset` | POST | Generate & send reset code |
| `?action=reset-password` | POST | Verify code & reset password |

---

## 🔍 Debug Tips

### Check Reset Code Was Generated
```sql
SELECT email, password_reset_code, password_reset_expires 
FROM users 
WHERE email = 'test@example.com' 
AND password_reset_code IS NOT NULL;
```

### View Error Logs
- Windows XAMPP: `C:\xampp\apache\logs\php_errors.log`
- Linux: `/var/log/php_errors.log`
- Look for: `Password Reset Code for...`

### Test API Directly (Browser Console)
```javascript
fetch('/sis/public/api/auth.php?action=request-password-reset', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'test@example.com' })
}).then(r => r.json()).then(console.log)
```

---

## ⚠️ Common Issues

| Issue | Check |
|-------|-------|
| 404 error | Migration not run? Check `users` table columns |
| Code not received | Check `error_log` for reset code |
| Can't verify code | Code expired? Typo in code? |
| Password too weak | Must be 8+ characters |

---

## 🔐 Security Notes

- Passwords hashed with bcrypt (PASSWORD_DEFAULT)
- Codes expire after 30 minutes
- Database uses prepared statements
- Email existence not revealed (security)
- All errors logged for debugging

---

## 📱 UI Features

- Step indicator with progress
- Auto-focus between code digits
- Backspace support for code inputs
- Password strength visualization
- 60-second resend timer
- Responsive mobile design
- Smooth step transitions

---

## 💾 Database Changes

**Before:**
```
users table
├── id
├── first_name
├── last_name
├── email
├── password
└── role
```

**After:**
```
users table
├── id
├── first_name
├── last_name
├── email
├── password
├── password_reset_code (NEW)
├── password_reset_expires (NEW)
└── role
```

---

## 🎯 Next Steps

After testing:
1. ✅ Verify email configuration for production
2. ✅ Test with real email addresses
3. ✅ Add rate limiting if needed
4. ✅ Monitor error logs for issues
5. ✅ Update user documentation

---

## 📞 Quick Help

**Forgot Password Link:**
```html
<a href="app/views/auth/auth_forgot_password.php">Forgot Password?</a>
```

**Migration Runner:**
```
http://localhost/sis/run-migrations.php
```

**API Endpoint:**
```
POST /sis/public/api/auth.php?action=request-password-reset
```

---

**Version:** 1.0
**Status:** Ready ✅
**Last Updated:** Nov 28, 2025
