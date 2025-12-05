# Quick Fix Instructions - 500 Error

## 🔍 STEP 1: Check the ACTUAL Error Message

The error message should now be visible in your browser console. 

1. Open your browser
2. Press **F12** to open Developer Tools
3. Go to the **Console** tab
4. Try creating a student again
5. Look for the error message - it should show something like:
   ```json
   {
     "success": false,
     "message": "...",
     "error": "EXACT ERROR MESSAGE HERE",
     "file": "...",
     "line": ...
   }
   ```

## 🧪 STEP 2: Use Debug Endpoint

I've created a debug endpoint that will show you EXACTLY what's happening:

1. Open your browser
2. Go to: `http://localhost/sis/public/api/debug_create_student.php`
3. This will show you step-by-step what's failing

## 📋 STEP 3: Check What You See

### If you see "Database connection failed":
- Check `app/config/database.php`
- Make sure database `university_portal` exists
- Make sure MySQL is running

### If you see "Table doesn't exist":
- Run migrations: `http://localhost/sis/run-migrations.php`

### If you see "Column doesn't exist":
- Run migration `005_create_role_tables.sql` and `006_update_users_table_for_new_structure.sql`

### If you see "Class not found":
- Check file paths are correct
- Check PHP error logs

## 🚨 MOST IMPORTANT: Share the Error Message

**Copy and paste the EXACT error message from:**
1. Browser console (F12 → Console tab)
2. OR the debug endpoint output
3. OR PHP error logs

The error message will tell us EXACTLY what's wrong!

## 📝 What I Fixed:

1. ✅ Removed duplicate `ob_clean()` and `header()` calls
2. ✅ Added comprehensive error handling with detailed messages
3. ✅ Added file existence checks
4. ✅ Added class existence checks
5. ✅ Created debug endpoint

**The error message should now be visible instead of just "500 error"!**

