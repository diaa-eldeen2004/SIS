# Quick Check: Is Migration Run?

## To check if system_logs table exists:

1. **Open your browser** and go to: `http://localhost/sis/run-migrations.php`
   - This will show you which migrations have been run

2. **Or check directly in phpMyAdmin:**
   - Open phpMyAdmin
   - Select your database (`university_portal` or similar)
   - Look for `system_logs` table in the list
   - If it doesn't exist, the migration needs to be run

## If the table doesn't exist:

The migration file is located at: `database/migrations/009_create_system_logs_table.sql`

Run it manually in phpMyAdmin or through the migration runner.

## What the new features need:

1. **System Logs page** (`it_logs.php`) - requires `system_logs` table
2. **Student Enrollment Requests** - requires student to be logged in with proper session
3. **Logging Integration** - will create logs automatically once table exists

