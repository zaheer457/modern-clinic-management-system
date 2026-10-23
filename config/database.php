<?php
declare(strict_types=1);
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'clinic_management';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli($host, $user, $pass, $db);
    $db->set_charset('utf8mb4');
} catch (Throwable $e) {
    http_response_code(500);
    exit('Database connection failed. Please check config/database.php and make sure MySQL is running.');
}
