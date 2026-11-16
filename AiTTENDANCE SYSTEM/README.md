# AiTTENDANCE System

PHP and MySQL attendance, QR scanning, employee administration, and Job Order payroll reporting for XAMPP.

## MVC structure

- `app/Config` contains the PDO database connection.
- `app/Controllers` handles HTTP actions and chooses views.
- `app/Models` contains employee, attendance, user, and payroll database operations.
- `app/Views` contains the kiosk, HR pages, login, and error templates.
- `public/index.php` is the front controller; `public/assets` contains browser assets.
- `database/schema.sql` creates the initial MySQL database and tables.

## Local setup

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Import `database/schema.sql` in phpMyAdmin or run it with the MySQL client.
3. If MySQL credentials differ from the defaults, set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the Apache/PHP environment. Defaults are `127.0.0.1:3306`, database `aittendance_db`, user `root`, and an empty password.
4. Open `http://localhost/AiTTENDANCE%20SYSTEM/`. When no accounts exist, the system shows a first-run form to create the HR administrator. After that, sign in with the username and password you created. Open the scanner from the attendance page; camera access requires `localhost` or HTTPS.

## Dashboards and accounts

After sign-in, HR, Payroll, and DepartmentHead accounts open the management home dashboard. HR can manage employees and JO payroll; Payroll can access payroll reports; DepartmentHead can review attendance. Employee accounts see only their own attendance history and monthly rendered hours (and estimated JO gross earnings where applicable).

HR can open **Print DTR**, choose an active employee and month, and print the daily time record or save it as PDF. The report shows only recorded time-in/time-out, rendered hours, and status; dates without a saved log remain blank and are not automatically treated as absences.

For an existing database created before employee dashboards were added, run `database/migrate_employee_dashboard.sql` once in phpMyAdmin. HR can create a linked employee login from the employee directory using **Create login**; choose a username and a password of at least 12 characters, then share the temporary password privately.

HR can disable or re-enable a linked employee portal login from the employee directory. Disabling blocks new sign-ins and ends the employee's existing portal session on its next request; it does not deactivate the employee record or QR attendance badge.

Employee QR badges contain the employee code. QR badge generation uses the bundled local library at `public/assets/vendor/qrcode.min.js` and does not require internet access. The live camera scanner still loads its camera-scanning script from a CDN, so that scanner page currently needs internet access to initialize. The scanner records one open time-in at a time; scanning again records time-out. Completed shifts of five hours or more subtract one hour for lunch. JO gross earnings are calculated as `(daily rate / 8) * verified rendered hours`; daily rates are limited to ₱401.00–₱414.00.

The system logs database errors to the PHP error log and shows generic user-facing errors. Configure a non-root MySQL account with a strong password before deployment; use HTTPS and restrict scanner access to the municipal network.

## Online deployment

The XAMPP address `localhost` is only reachable on the computer running XAMPP. To access the system over the internet from phones, tablets, and other computers, deploy it to a PHP/MySQL hosting provider and connect a domain or subdomain.

1. Choose hosting that provides PHP 8.2+, MySQL/MariaDB, PDO MySQL, HTTPS certificates, and configurable environment variables.
2. Upload the project so the hosting document root points specifically to this project's `public/` folder. Keep `app/`, `database/`, and `scripts/` outside the public document root.
3. Create a production database and a dedicated database user with a strong password and access only to that database. Import `database/schema.sql` (or apply `database/migrate_employee_dashboard.sql` to an existing installation).
4. In the hosting control panel, set `APP_ENV=production`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`. Production mode refuses root or blank-password MySQL credentials. Do not commit credentials to source control.
5. Enable HTTPS and force HTTP-to-HTTPS redirects in the hosting control panel. Test login, employee QR camera access, and each role from a phone using the HTTPS domain.
6. Create the first HR login through the first-run setup page. Back up the database securely and restrict public access to employee and payroll data.

This source workspace is not itself publicly hosted; a hosting provider, domain/DNS, and production database credentials are required before it can have an internet URL. The QR scanner requires HTTPS for camera access on non-localhost devices.