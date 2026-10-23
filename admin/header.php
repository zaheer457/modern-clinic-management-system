
<?php

require_once __DIR__ . '/../config/auth.php';

require_admin();

$title = $title ?? 'Admin Dashboard';

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($title) ?> | Modern Clinic Admin</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet"
    >

    <!-- Main Website CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">

    <!-- Premium Theme CSS -->
    <link href="../assets/css/premium.css?v=20261012" rel="stylesheet">
    <link href="../assets/css/refined.css?v=20261016" rel="stylesheet">
    <link href="../assets/css/midnight.css?v=20261017" rel="stylesheet">
    <link href="../assets/css/admin-dashboard.css?v=20261022" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-doctors.css?v=20261019">

    <!-- Toast Notification Styling -->
    <style>
        .clinic-toast {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 99999;

            display: flex;
            align-items: center;
            gap: 12px;

            width: max-content;
            min-width: 320px;
            max-width: calc(100vw - 32px);

            padding: 17px 18px;

            color: #163047;
            background: #ffffff;

            border: 1px solid #dcefe7;
            border-left: 5px solid #32b67a;
            border-radius: 14px;

            box-shadow: 0 14px 40px rgba(8, 36, 58, 0.20);

            font-size: 14px;
            font-weight: 600;

            animation: clinicToastIn .35s ease both;
            overflow: hidden;
        }

        .clinic-toast.toast-danger {
            border-left-color: #e05260;
        }

        .clinic-toast.toast-warning {
            border-left-color: #e5a12b;
        }

        .clinic-toast .toast-icon {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 34px;
            height: 34px;
            flex-shrink: 0;

            border-radius: 50%;
            background: #e3f8ed;
            color: #20a464;

            font-size: 16px;
        }

        .clinic-toast.toast-danger .toast-icon {
            color: #d94352;
            background: #fff0f0;
        }

        .clinic-toast.toast-warning .toast-icon {
            color: #c78712;
            background: #fff5dc;
        }

        .clinic-toast .toast-message {
            overflow-wrap: anywhere;
        }

        .clinic-toast .toast-close {
            margin-left: auto;
            padding: 0 0 0 8px;

            border: 0;
            background: transparent;

            color: #718096;
            font-size: 23px;
            line-height: 1;

            cursor: pointer;
        }

        .clinic-toast .toast-close:hover {
            color: #163047;
        }

        .clinic-toast .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;

            width: 100%;
            height: 3px;

            background: #32b67a;
            transform-origin: left;
            animation: clinicToastProgress 3s linear forwards;
        }

        .clinic-toast.toast-danger .toast-progress {
            background: #e05260;
        }

        .clinic-toast.toast-warning .toast-progress {
            background: #e5a12b;
        }

        .clinic-toast.toast-hide {
            animation: clinicToastOut .3s ease forwards;
            pointer-events: none;
        }

        @keyframes clinicToastIn {
            from {
                opacity: 0;
                transform: translateX(35px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes clinicToastOut {
            to {
                opacity: 0;
                transform: translateX(35px);
            }
        }

        @keyframes clinicToastProgress {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }

        @media (max-width: 480px) {
            .clinic-toast {
                top: 15px;
                right: 15px;
                left: 15px;

                width: auto;
                min-width: 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .clinic-toast,
            .clinic-toast .toast-progress {
                animation-duration: 0.01ms;
            }
        }
    </style>
</head>

<body class="admin-page">

    <!-- ADMIN SIDEBAR -->
    <aside class="sidebar">

        <div class="h4 text-white fw-bold mb-4">
            <span class="brand-mark">
                <i class="fa-solid fa-plus"></i>
            </span>
            ModernClinic
        </div>

        <div class="small text-uppercase opacity-50 mb-2">
            Management
        </div>

        <?php

        $links = [
            'index.php' => ['fa-chart-line', 'Dashboard'],
            'doctors.php' => ['fa-user-doctor', 'Doctors'],
            'patients.php' => ['fa-users', 'Patients'],
            'appointments.php' => ['fa-calendar-check', 'Appointments'],
            'departments.php' => ['fa-hospital', 'Departments'],
            'services.php' => ['fa-kit-medical', 'Services'],
            'schedules.php' => ['fa-clock', 'Schedules'],
            'reviews.php' => ['fa-star', 'Reviews'],
            'messages.php' => ['fa-envelope', 'Messages'],
            'faqs.php' => ['fa-circle-question', 'FAQs']
        ];

        foreach ($links as $href => $item):
        ?>

            <a class="side-link" href="<?= e($href) ?>">
                <i class="fa-solid <?= e($item[0]) ?> fa-fw"></i>
                <?= e($item[1]) ?>
            </a>

        <?php endforeach; ?>

        <hr>

        <a class="side-link" href="../index.php">
            <i class="fa-solid fa-globe fa-fw"></i>
            View Website
        </a>

        <a class="side-link" href="../logout.php">
            <i class="fa-solid fa-right-from-bracket fa-fw"></i>
            Logout
        </a>

    </aside>

    <!-- ADMIN MAIN CONTENT -->
    <div class="admin-main">

        <header class="admin-top">

            <div>
                <strong><?= e($title) ?></strong>
            </div>

            <div>
                <i class="fa-regular fa-user me-2"></i>
                <?= e(current_user()['full_name'] ?? 'Admin') ?>
            </div>

        </header>

        <main class="admin-content">

            <!-- FLASH MESSAGES AS POPUP NOTIFICATIONS -->
            <?php foreach (flashes() as $f): ?>

                <?php
                    $type = $f['type'] ?? 'success';

                    $toastClass = 'toast-success';
                    $icon = 'fa-circle-check';

                    if (in_array($type, ['danger', 'error'], true)) {
                        $toastClass = 'toast-danger';
                        $icon = 'fa-circle-xmark';
                    } elseif (in_array($type, ['warning', 'warn'], true)) {
                        $toastClass = 'toast-warning';
                        $icon = 'fa-triangle-exclamation';
                    } elseif ($type === 'info') {
                        $icon = 'fa-circle-info';
                    }
                ?>

                <div
                    class="clinic-toast <?= e($toastClass) ?>"
                    role="status"
                    aria-live="polite"
                >
                    <span class="toast-icon">
                        <i class="fa-solid <?= e($icon) ?>"></i>
                    </span>

                    <span class="toast-message">
                        <?= e($f['message'] ?? '') ?>
                    </span>

                    <button
                        type="button"
                        class="toast-close"
                        aria-label="Close notification"
                    >&times;</button>

                    <span class="toast-progress"></span>
                </div>

            <?php endforeach; ?>

            <script>
                document.querySelectorAll('.clinic-toast').forEach(function (toast) {
                    let removed = false;

                    function dismissToast() {
                        if (removed) return;

                        removed = true;
                        toast.classList.add('toast-hide');

                        setTimeout(function () {
                            toast.remove();
                        }, 300);
                    }

                    const closeButton = toast.querySelector('.toast-close');

                    if (closeButton) {
                        closeButton.addEventListener('click', dismissToast);
                    }

                    setTimeout(dismissToast, 3000);
                });
            </script>
