ARAWAN E-BAYANAN - FIXED VERSION

How to use:
1. Copy the contents of this folder into your XAMPP htdocs project folder.
2. Start Apache and MySQL in XAMPP.
3. Import config.sql in phpMyAdmin, or simply open the project once because db.php also creates/migrates required tables.
4. Open: http://localhost/your-folder-name/index.php
5. Admin login:
   Email: admin@arawan.local
   Password: Admin@1234

Main fixes included:
- Login is now handled by login.php with proper form errors and success messages.
- login.html now redirects to login.php to avoid duplicate login pages.
- Added CSRF validation to login, request submission, admin approval/rejection, and notification read actions.
- Changed admin approve/reject actions from unsafe GET links to POST forms.
- Added request submission validation and reference number generation using random bytes.
- Added user notifications when residents submit requests and when admins update/approve/reject requests.
- Dashboard notifications now load from the database instead of fixed sample text.
- Added mark-as-read behavior for notifications.
- Fixed missing dashboard CSS classes, sidebar layout, modal display, badges, tables, mobile sidebar, and responsive layout.
- Strengthened db.php so it creates/migrates missing tables and columns more safely.
- Updated config.sql to match the full project schema.
- Added helpers.php for escaping, notifications, and reusable helpers.

All PHP files passed php -l syntax checks.
