
<?php
/**
 * ModernClinic — Public Website Header
 * Public pages must remain accessible without login.
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/functions.php';

$title = $title ?? 'Modern Clinic';
$user = current_user();
$baseUrl = '/clinic-management-system';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($title) ?> | Modern Clinic</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          rel="stylesheet">

    <link href="<?= $baseUrl ?>/assets/css/style.css?v=20261017"
          rel="stylesheet">

    <link href="<?= $baseUrl ?>/assets/css/premium.css?v=20261012"
          rel="stylesheet">

    <link href="<?= $baseUrl ?>/assets/css/refined.css?v=20261016"
          rel="stylesheet">

    <link href="<?= $baseUrl ?>/assets/css/midnight.css?v=20261017"
          rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg clinic-nav">
    <div class="container">
        <a class="navbar-brand fw-bold"
           href="<?= $baseUrl ?>/index.php">
            <i class="fa-solid fa-plus me-2"></i>ModernClinic
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#publicNavbar"
                aria-controls="publicNavbar"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="publicNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $baseUrl ?>/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $baseUrl ?>/doctors.php">Doctors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $baseUrl ?>/departments.php">Departments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $baseUrl ?>/services.php">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $baseUrl ?>/about.php">About</a>
                </li>
                

                <?php if ($user): ?>
                    <?php if (($user['role'] ?? '') === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="<?= $baseUrl ?>/admin/index.php">Admin Panel</a>
                        </li>
                    <?php elseif (($user['role'] ?? '') === 'patient'): ?>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="<?= $baseUrl ?>/patient/profile.php">Profile</a>
                        </li>
                    <?php endif; ?>

                   
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="<?= $baseUrl ?>/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm"
                           href="<?= $baseUrl ?>/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="site-main container py-4">

<?php foreach (flashes() as $f): ?>
    <?php
    $allowedAlerts = ['success', 'danger', 'warning', 'info', 'primary', 'secondary'];
    $alertType = in_array($f['type'] ?? '', $allowedAlerts, true)
        ? $f['type']
        : 'info';
    ?>
    <div class="alert alert-<?= e($alertType) ?> alert-dismissible fade show"
         role="alert">
        <?= e($f['message'] ?? '') ?>
        <button type="button" class="btn-close"
                data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endforeach; ?>
