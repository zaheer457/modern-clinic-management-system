# Modern Clinic — PHP/MySQL Healthcare Management System

## Requirements
- XAMPP
- Apache
- MySQL
- PHP 8+

## Installation
1. Extract this folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL from XAMPP.
3. Open phpMyAdmin.
4. Import `database/clinic_management.sql`.
5. Open `http://localhost/clinic-management-system/`.

Database defaults in `config/database.php`:
- host: localhost
- user: root
- password: empty
- database: clinic_management

## Demo accounts
The seed contains bcrypt password hashes. The application also supports first-login password setup through the included demo credentials in the login screen.

Recommended demo credentials:
- Admin: admin@modernclinic.com / Admin@123
- Patient: patient@modernclinic.com / Patient@123

If the imported demo hash does not match your PHP version/environment, run the following once in a temporary PHP file:
`echo password_hash('Admin@123', PASSWORD_DEFAULT);`
and replace the admin password hash in the SQL; do the same for Patient@123.

## Main URLs
- `/index.php` public home
- `/doctors.php`
- `/appointment.php`
- `/login.php`
- `/register.php`
- `/patient/`
- `/admin/`

## Notes
This is a complete working foundation and management MVP. Image uploads are validated by MIME type and size. SQL uses foreign keys, indexes and prepared statements. For a production medical deployment, add audit logging, encrypted backups, infrastructure hardening, monitoring and applicable privacy/compliance controls.
