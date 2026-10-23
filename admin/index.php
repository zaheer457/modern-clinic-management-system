
<?php

$title = 'Dashboard';

require __DIR__ . '/header.php';

/* =========================================================
   DASHBOARD METRICS
========================================================= */

$metrics = [

    'Patients' => count_rows('patients'),

    'Doctors' => count_rows('doctors'),

    'Appointments' => count_rows('appointments'),

    'Pending' => count_rows('appointments')
        ? (int) db_one(
            "SELECT COUNT(*) c
             FROM appointments
             WHERE status = 'Pending'"
        )['c']
        : 0,

    'Completed' => count_rows('appointments')
        ? (int) db_one(
            "SELECT COUNT(*) c
             FROM appointments
             WHERE status = 'Completed'"
        )['c']
        : 0,

    'Reviews' => count_rows('reviews')

];

/* =========================================================
   RECENT APPOINTMENTS
========================================================= */

$recent = db_all(
    "SELECT
        a.*,
        p.full_name AS patient_name,
        d.full_name AS doctor_name

     FROM appointments a

     JOIN patients pa
        ON pa.id = a.patient_id

     JOIN users p
        ON p.id = pa.user_id

     JOIN doctors dr
        ON dr.id = a.doctor_id

     JOIN users d
        ON d.id = dr.user_id

     ORDER BY a.created_at DESC

     LIMIT 8"
);

/* =========================================================
   METRIC ICONS
========================================================= */

$metricIcons = [

    'Patients' => [
        'fa-users',
        'aqua'
    ],

    'Doctors' => [
        'fa-user-doctor',
        'blue'
    ],

    'Appointments' => [
        'fa-calendar-check',
        'violet'
    ],

    'Pending' => [
        'fa-hourglass-half',
        'amber'
    ],

    'Completed' => [
        'fa-circle-check',
        'green'
    ],

    'Reviews' => [
        'fa-star',
        'rose'
    ]

];

?>

<!-- =========================================================
     DASHBOARD WELCOME SECTION
========================================================= -->

<section class="dashboard-welcome mb-4">

    <div class="welcome-copy">

        <span class="welcome-kicker">
            <i class="fa-solid fa-shield-heart me-2"></i>
            CLINIC OVERVIEW
        </span>

        <h1>
            Good to see you,
            <?= e(
                explode(
                    ' ',
                    trim(
                        (string) (
                            current_user()['full_name'] ?? 'Admin'
                        )
                    )
                )[0]
            ) ?>.
        </h1>

        <p>
            Here’s what’s happening across your clinic today.
            Keep patient care moving smoothly.
        </p>

        <div class="welcome-actions">

            <a
                href="appointments.php"
                class="btn btn-primary"
            >
                <i class="fa-regular fa-calendar-check me-2"></i>
                Manage appointments
            </a>

            <a
                href="doctors.php"
                class="welcome-secondary"
            >
                <i class="fa-solid fa-user-doctor me-2"></i>
                View doctors
                <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
            </a>

        </div>

    </div>

    <!-- Decorative Welcome Artwork -->
    <div class="welcome-art" aria-hidden="true">

        <div class="orbit orbit-one"></div>

        <div class="orbit orbit-two"></div>

        <div class="pulse-cross">
            <i class="fa-solid fa-heart-pulse"></i>
        </div>

        <div class="pulse-line"></div>

        <span class="art-caption">
            CARE • TRUST • WELLNESS
        </span>

    </div>

    <!-- Current Date -->
    <div class="welcome-date">
        <i class="fa-regular fa-calendar me-2"></i>
        <?= date('l, F j, Y') ?>
    </div>

</section>

<!-- =========================================================
     CLINIC PERFORMANCE HEADING
========================================================= -->

<div class="dashboard-section-heading">

    <div>

        <span class="welcome-kicker">
            AT A GLANCE
        </span>

        <h2>
            Clinic performance
        </h2>

    </div>

    <span class="live-indicator">
        <i></i>
        Live overview
    </span>

</div>

<!-- =========================================================
     DASHBOARD METRIC CARDS
========================================================= -->

<div class="row g-3 dashboard-metrics">

    <?php foreach ($metrics as $key => $value): ?>

        <?php
        $metricInfo = $metricIcons[$key] ?? [
            'fa-chart-simple',
            'aqua'
        ];

        $icon = $metricInfo[0];
        $color = $metricInfo[1];
        ?>

        <div class="col-6 col-xl-4 col-xxl-2">

            <div class="metric h-100 metric-<?= strtolower($key) ?>">

                <div class="metric-top">

                    <span class="metric-icon metric-icon-<?= e($color) ?>">
                        <i class="fa-solid <?= e($icon) ?>"></i>
                    </span>

                    <span class="metric-more">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </span>

                </div>

                <div class="text-secondary small">
                    <?= e($key) ?>
                </div>

                <div class="stat-number">
                    <?= (int) $value ?>
                </div>

                <div class="metric-foot">

                    <span class="metric-dot"></span>

                    <span>
                        Current total
                    </span>

                </div>

            </div>

        </div>

    <?php endforeach; ?>

</div>

<!-- =========================================================
     RECENT APPOINTMENTS AND QUICK ACTIONS
========================================================= -->

<div class="row g-4 mt-2 dashboard-lower">

    <!-- RECENT APPOINTMENTS -->
    <div class="col-xl-8">

        <div class="panel h-100 dashboard-panel">

            <div class="panel-heading">

                <div>

                    <span class="welcome-kicker">
                        LATEST ACTIVITY
                    </span>

                    <h3 class="section-title mb-0">
                        Recent appointments
                    </h3>

                    <p class="panel-subtitle">
                        The latest bookings from your patients
                    </p>

                </div>

                <a
                    href="appointments.php"
                    class="panel-link"
                >
                    View all
                    <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>

            </div>

            <div class="table-responsive mt-3">

                <table class="table align-middle">

                    <thead>

                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if ($recent): ?>

                            <?php foreach ($recent as $appointment): ?>

                                <tr>

                                    <!-- Patient -->
                                    <td>

                                        <div class="person-cell">

                                            <span class="person-avatar">
                                                <?= e(
                                                    strtoupper(
                                                        substr(
                                                            (string) $appointment['patient_name'],
                                                            0,
                                                            1
                                                        )
                                                    )
                                                ) ?>
                                            </span>

                                            <span>
                                                <?= e($appointment['patient_name']) ?>
                                            </span>

                                        </div>

                                    </td>

                                    <!-- Doctor -->
                                    <td>
                                        <?= e($appointment['doctor_name']) ?>
                                    </td>

                                    <!-- Appointment Date -->
                                    <td>

                                        <span class="appointment-date">

                                            <i class="fa-regular fa-calendar me-2"></i>

                                            <?= e($appointment['appointment_date']) ?>

                                        </span>

                                    </td>

                                    <!-- Appointment Status -->
                                    <td>

                                        <span class="status status-<?= e($appointment['status']) ?>">
                                            <?= e($appointment['status']) ?>
                                        </span>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="4">

                                    <div class="empty-state">

                                        <i class="fa-regular fa-calendar-xmark"></i>

                                        <strong>
                                            No appointments yet
                                        </strong>

                                        <span>
                                            New bookings will appear here.
                                        </span>

                                    </div>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- QUICK ACTIONS -->
    <div class="col-xl-4">

        <div class="panel h-100 dashboard-panel quick-panel">

            <div class="panel-heading">

                <div>

                    <span class="welcome-kicker">
                        SHORTCUTS
                    </span>

                    <h3 class="section-title mb-0">
                        Quick actions
                    </h3>

                    <p class="panel-subtitle">
                        Jump straight into management
                    </p>

                </div>

                <span class="quick-heading-icon">
                    <i class="fa-solid fa-bolt"></i>
                </span>

            </div>

            <div class="quick-actions mt-3">

                <!-- Doctors -->
                <a
                    href="doctors.php"
                    class="quick-action"
                >

                    <span class="quick-action-icon qa-blue">
                        <i class="fa-solid fa-user-doctor"></i>
                    </span>

                    <span>

                        <strong>
                            Manage doctors
                        </strong>

                        <small>
                            Profiles and availability
                        </small>

                    </span>

                    <i class="fa-solid fa-chevron-right quick-arrow"></i>

                </a>

                <!-- Appointments -->
                <a
                    href="appointments.php"
                    class="quick-action"
                >

                    <span class="quick-action-icon qa-aqua">
                        <i class="fa-solid fa-calendar-check"></i>
                    </span>

                    <span>

                        <strong>
                            Appointments
                        </strong>

                        <small>
                            Review and update bookings
                        </small>

                    </span>

                    <i class="fa-solid fa-chevron-right quick-arrow"></i>

                </a>

                <!-- Patients -->
                <a
                    href="patients.php"
                    class="quick-action"
                >

                    <span class="quick-action-icon qa-violet">
                        <i class="fa-solid fa-users"></i>
                    </span>

                    <span>

                        <strong>
                            Patient directory
                        </strong>

                        <small>
                            Browse registered patients
                        </small>

                    </span>

                    <i class="fa-solid fa-chevron-right quick-arrow"></i>

                </a>

                <!-- Messages -->
                <a
                    href="messages.php"
                    class="quick-action"
                >

                    <span class="quick-action-icon qa-amber">
                        <i class="fa-solid fa-envelope"></i>
                    </span>

                    <span>

                        <strong>
                            Messages
                        </strong>

                        <small>
                            Check patient enquiries
                        </small>

                    </span>

                    <i class="fa-solid fa-chevron-right quick-arrow"></i>

                </a>

            </div>

            <!-- Care Note -->
            <div class="care-note">

                <i class="fa-solid fa-heart-pulse"></i>

                <span>

                    <strong>
                        Every detail matters.
                    </strong>

                    <small>
                        Thanks for helping your clinic deliver better care.
                    </small>

                </span>

            </div>

        </div>

    </div>

</div>

<?php require __DIR__ . '/footer.php'; ?>
