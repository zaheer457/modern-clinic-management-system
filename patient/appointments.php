
<?php

require_once __DIR__ . '/../config/auth.php';

require_role('patient');

$title = 'My Appointments';
$base = '../';

$userId = (int) current_user()['id'];

/* =========================================================
   GET CURRENT PATIENT
========================================================= */

$p = db_one(
    "SELECT id
     FROM patients
     WHERE user_id = ?",
    "i",
    $userId
);

/* =========================================================
   CANCEL APPOINTMENT
========================================================= */

if (isset($_GET['cancel']) && $p) {

    $appointmentId = (int) $_GET['cancel'];

    if ($appointmentId > 0) {

        db_exec(
            "UPDATE appointments
             SET status = 'Cancelled'
             WHERE id = ?
               AND patient_id = ?
               AND status = 'Pending'
               AND appointment_date >= CURDATE()",
            "ii",
            $appointmentId,
            $p['id']
        );

        flash('success', 'If the appointment was eligible, it has been cancelled.');

    } else {

        flash('danger', 'Invalid appointment selected.');
    }

    redirect('appointments.php');
}

/* =========================================================
   FILTER APPOINTMENTS
========================================================= */

$filter = $_GET['status'] ?? 'All';

$allowedFilters = [
    'All',
    'Pending',
    'Confirmed',
    'Completed',
    'Cancelled'
];

if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'All';
}

/* =========================================================
   LOAD APPOINTMENTS
========================================================= */

$rows = [];

if ($p) {

    if ($filter === 'All') {

        $rows = db_all(
            "SELECT
                a.*,
                u.full_name AS doctor_name,
                dep.name AS department
             FROM appointments a
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users u ON u.id = d.user_id
             JOIN departments dep ON dep.id = a.department_id
             WHERE a.patient_id = ?
             ORDER BY
                a.appointment_date DESC,
                a.appointment_time DESC",
            "i",
            $p['id']
        );

    } else {

        $rows = db_all(
            "SELECT
                a.*,
                u.full_name AS doctor_name,
                dep.name AS department
             FROM appointments a
             JOIN doctors d ON d.id = a.doctor_id
             JOIN users u ON u.id = d.user_id
             JOIN departments dep ON dep.id = a.department_id
             WHERE a.patient_id = ?
               AND a.status = ?
             ORDER BY
                a.appointment_date DESC,
                a.appointment_time DESC",
            "is",
            $p['id'],
            $filter
        );
    }
}

/* =========================================================
   APPOINTMENT COUNTS
========================================================= */

$counts = [
    'All' => 0,
    'Pending' => 0,
    'Confirmed' => 0,
    'Completed' => 0,
    'Cancelled' => 0
];

if ($p) {

    $countRows = db_all(
        "SELECT status, COUNT(*) AS total
         FROM appointments
         WHERE patient_id = ?
         GROUP BY status",
        "i",
        $p['id']
    );

    foreach ($countRows as $countRow) {

        $status = $countRow['status'];
        $total = (int) $countRow['total'];

        $counts['All'] += $total;

        if (isset($counts[$status])) {
            $counts[$status] = $total;
        }
    }
}

/* =========================================================
   LOAD HEADER AFTER REQUEST PROCESSING
========================================================= */

require __DIR__ . '/../includes/header.php';

?>

<style>
.my-appointments-page {
    min-height: 75vh;
    padding: 34px 0 65px;
    background:
        radial-gradient(ellipse at 5% 0%, rgba(69,213,223,.12), transparent 32%),
        radial-gradient(ellipse at 96% 42%, rgba(21,151,212,.08), transparent 30%),
        #f4f9fc;
}

.appointments-shell {
    max-width: 1120px;
    margin: 0 auto;
}

.appointments-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}

.appointments-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 9px;
    color: #087fae;
    font-size: 10px;
    font-weight: 850;
    letter-spacing: 1.3px;
    text-transform: uppercase;
}

.appointments-heading h1 {
    margin: 0 0 8px;
    color: #08243a;
    font-size: clamp(28px, 3.5vw, 37px);
    font-weight: 850;
    letter-spacing: -1px;
}

.appointments-heading h1 span {
    color: #1597d4;
}

.appointments-heading p {
    margin: 0;
    color: #718797;
    font-size: 13px;
    line-height: 1.8;
}

.appointments-back-btn,
.appointments-book-btn {
    display: inline-flex;
    min-height: 42px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 16px;
    border-radius: 11px;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
    transition: .2s ease;
}

.appointments-back-btn {
    border: 1px solid #d8e7ef;
    background: #fff;
    color: #315166;
}

.appointments-back-btn:hover {
    border-color: #1597d4;
    color: #087eb9;
    transform: translateY(-2px);
}

.appointments-book-btn {
    border: 0;
    background: linear-gradient(110deg, #087eb9, #1597d4 60%, #27b8c8);
    color: #fff;
    box-shadow: 0 7px 17px rgba(21,151,212,.17);
}

.appointments-book-btn:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 11px 23px rgba(21,151,212,.24);
}

.appointments-stat-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 13px;
    margin-bottom: 23px;
}

.appointments-stat-card {
    min-width: 0;
    padding: 17px;
    border: 1px solid #dce9f0;
    border-radius: 16px;
    background: linear-gradient(145deg, #edf7fd 0%, #e9f7f8 100%);
    box-shadow: 0 8px 24px rgba(8,36,58,.045);
    text-decoration: none;
    transition: .2s ease;
}

.appointments-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 13px 25px rgba(8,36,58,.08);
}

.appointments-stat-card.active {
    border-color: #1597d4;
    box-shadow: 0 0 0 2px rgba(21,151,212,.1);
}

.appointments-stat-icon {
    display: grid;
    width: 38px;
    height: 38px;
    margin-bottom: 13px;
    place-items: center;
    border: 1px solid #cde8f3;
    border-radius: 12px;
    background: #dff2fc;
    color: #087fb8;
    font-size: 15px;
}

.appointments-stat-icon.pending {
    border-color: #f4dfae;
    background: #fff5d9;
    color: #926a16;
}

.appointments-stat-icon.confirmed {
    border-color: #c5e4f5;
    background: #e2f3ff;
    color: #0878ae;
}

.appointments-stat-icon.completed {
    border-color: #c8ead9;
    background: #e2f7eb;
    color: #218253;
}

.appointments-stat-icon.cancelled {
    border-color: #f2d1d1;
    background: #fff0f0;
    color: #b04444;
}

.appointments-stat-number {
    display: block;
    color: #10364f;
    font-size: 25px;
    font-weight: 850;
    line-height: 1.1;
}

.appointments-stat-label {
    display: block;
    margin-top: 6px;
    color: #708798;
    font-size: 10px;
    font-weight: 700;
}

.appointments-panel {
    overflow: hidden;
    border: 1px solid #dce9f0;
    border-radius: 19px;
    background: linear-gradient(145deg, #edf7fd 0%, #e9f7f8 100%);
    box-shadow: 0 11px 32px rgba(8,36,58,.055);
}

.appointments-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    padding: 21px 23px;
    border-bottom: 1px solid rgba(21,151,212,.13);
    background: linear-gradient(110deg, #dff3fd, #e0f7f6);
}

.appointments-panel-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.appointments-panel-icon {
    display: grid;
    width: 43px;
    height: 43px;
    flex: 0 0 43px;
    place-items: center;
    border: 1px solid #cbe7f2;
    border-radius: 13px;
    background: linear-gradient(135deg, #d9f0ff, #d8f6f4);
    color: #087fb8;
    font-size: 16px;
}

.appointments-panel-title h2 {
    margin: 0 0 4px;
    color: #10364f;
    font-size: 16px;
    font-weight: 850;
}

.appointments-panel-title p {
    margin: 0;
    color: #708798;
    font-size: 11px;
}

.appointments-result-count {
    padding: 7px 11px;
    border: 1px solid #cce6ef;
    border-radius: 30px;
    background: rgba(255,255,255,.7);
    color: #27708e;
    font-size: 10px;
    font-weight: 800;
}

.appointments-filter-bar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    padding: 16px 22px;
    border-bottom: 1px solid #dce9f0;
}

.appointments-filter-label {
    margin-right: 5px;
    color: #698092;
    font-size: 10px;
    font-weight: 800;
}

.appointments-filter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 8px 12px;
    border: 1px solid #d8e6ee;
    border-radius: 30px;
    background: rgba(255,255,255,.75);
    color: #526b7b;
    font-size: 10px;
    font-weight: 800;
    text-decoration: none;
    transition: .2s ease;
}

.appointments-filter:hover {
    border-color: #1597d4;
    color: #087eb9;
}

.appointments-filter.active {
    border-color: #1597d4;
    background: #087fb8;
    color: #fff;
    box-shadow: 0 5px 12px rgba(21,151,212,.15);
}

.appointments-filter-count {
    opacity: .8;
    font-size: 9px;
}

.appointments-table-wrap {
    overflow-x: auto;
    width: 100%;
}

.appointments-table {
    width: 100%;
    min-width: 800px;
    margin: 0;
    border-collapse: collapse;
}

.appointments-table th {
    padding: 14px 17px;
    background: rgba(21,151,212,.045);
    color: #698092;
    font-size: 10px;
    font-weight: 850;
    text-align: left;
    white-space: nowrap;
}

.appointments-table td {
    padding: 17px;
    border-top: 1px solid #deebf1;
    color: #29495e;
    font-size: 11px;
    vertical-align: middle;
}

.appointments-table tbody tr {
    transition: background .2s ease;
}

.appointments-table tbody tr:hover {
    background: rgba(255,255,255,.48);
}

.appointment-id {
    color: #087fb8;
    font-weight: 850;
}

.appointment-doctor {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 170px;
}

.appointment-doctor-avatar {
    display: grid;
    width: 39px;
    height: 39px;
    flex: 0 0 39px;
    place-items: center;
    border: 1px solid #cde8f3;
    border-radius: 12px;
    background: linear-gradient(135deg, #d9f0ff, #d8f6f4);
    color: #087fb8;
    font-size: 14px;
}

.appointment-doctor strong,
.appointment-doctor small {
    display: block;
}

.appointment-doctor strong {
    max-width: 180px;
    color: #163c54;
    font-size: 11px;
    font-weight: 850;
    overflow-wrap: anywhere;
}

.appointment-doctor small {
    margin-top: 4px;
    color: #7b909e;
    font-size: 10px;
}

.appointment-date {
    color: #29495e;
    font-weight: 800;
    white-space: nowrap;
}

.appointment-time {
    display: block;
    margin-top: 5px;
    color: #7b909e;
    font-size: 10px;
    white-space: nowrap;
}

.appointment-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 10px;
    border: 1px solid #d9e6ed;
    border-radius: 30px;
    background: #fff;
    color: #526b7b;
    font-size: 9px;
    font-weight: 850;
    white-space: nowrap;
}

.appointment-status-pending {
    border-color: #f4dfae;
    background: #fff7e3;
    color: #926a16;
}

.appointment-status-confirmed {
    border-color: #c5e4f5;
    background: #e6f5ff;
    color: #0878ae;
}

.appointment-status-completed {
    border-color: #c8ead9;
    background: #e4f8ed;
    color: #218253;
}

.appointment-status-cancelled {
    border-color: #f2d1d1;
    background: #fff0f0;
    color: #b04444;
}

.appointment-cancel-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 11px;
    border: 1px solid #f0d0d0;
    border-radius: 9px;
    background: #fff5f5;
    color: #b04444;
    font-size: 10px;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
    transition: .2s ease;
}

.appointment-cancel-btn:hover {
    border-color: #d94b4b;
    background: #d94b4b;
    color: #fff;
}

.appointments-empty {
    padding: 48px 20px;
    text-align: center;
}

.appointments-empty-icon {
    display: grid;
    width: 64px;
    height: 64px;
    margin: 0 auto 15px;
    place-items: center;
    border: 1px solid #cde8f3;
    border-radius: 20px;
    background: #e0f3fc;
    color: #087fb8;
    font-size: 23px;
}

.appointments-empty h3 {
    margin: 0 0 8px;
    color: #163c54;
    font-size: 17px;
    font-weight: 850;
}

.appointments-empty p {
    max-width: 390px;
    margin: 0 auto 18px;
    color: #718797;
    font-size: 12px;
    line-height: 1.8;
}

.appointments-panel-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 16px 22px;
    border-top: 1px solid #dce9f0;
}

.appointments-panel-footer p {
    margin: 0;
    color: #708798;
    font-size: 10px;
    line-height: 1.7;
}

@media (max-width: 1000px) {
    .appointments-stat-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .my-appointments-page {
        padding: 23px 0 42px;
    }

    .appointments-heading {
        align-items: stretch;
    }

    .appointments-heading-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .appointments-heading-actions > a {
        width: 100%;
        padding: 10px 8px;
        font-size: 10px;
    }

    .appointments-stat-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .appointments-stat-card {
        padding: 14px;
    }

    .appointments-stat-number {
        font-size: 22px;
    }

    .appointments-stat-label {
        line-height: 1.6;
    }

    .appointments-panel {
        border-radius: 15px;
    }

    .appointments-panel-header {
        padding: 16px;
    }

    .appointments-filter-bar {
        padding: 13px;
        gap: 7px;
    }

    .appointments-filter-label {
        width: 100%;
    }

    .appointments-filter {
        padding: 8px 10px;
        font-size: 9px;
    }

    .appointments-panel-footer {
        padding: 14px;
    }
}
</style>

<section class="my-appointments-page">

    <div class="container">
        <div class="appointments-shell">

            <!-- PAGE HEADER -->

            <div class="appointments-heading">

                <div>
                    <div class="appointments-kicker">
                        <i class="fa-solid fa-heart-pulse"></i>
                        Patient Portal
                    </div>

                    <h1>My <span>Appointments</span></h1>

                    <p>
                        Manage your appointments and review your
                        healthcare visit history.
                    </p>
                </div>

                <div class="appointments-heading-actions"
                     style="display:flex;gap:9px;flex-wrap:wrap">

                    <a
                        href="profile.php"
                        class="appointments-back-btn"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        My Profile
                    </a>

                    <a
                        href="../appointment.php"
                        class="appointments-book-btn"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Book Appointment
                    </a>

                </div>

            </div>

            <!-- STATUS SUMMARY -->

            <div class="appointments-stat-grid">

                <?php
                $statCards = [
                    [
                        'label' => 'All Appointments',
                        'key' => 'All',
                        'icon' => 'fa-calendar-days',
                        'class' => ''
                    ],
                    [
                        'label' => 'Pending',
                        'key' => 'Pending',
                        'icon' => 'fa-clock',
                        'class' => 'pending'
                    ],
                    [
                        'label' => 'Confirmed',
                        'key' => 'Confirmed',
                        'icon' => 'fa-calendar-check',
                        'class' => 'confirmed'
                    ],
                    [
                        'label' => 'Completed',
                        'key' => 'Completed',
                        'icon' => 'fa-circle-check',
                        'class' => 'completed'
                    ],
                    [
                        'label' => 'Cancelled',
                        'key' => 'Cancelled',
                        'icon' => 'fa-calendar-xmark',
                        'class' => 'cancelled'
                    ]
                ];
                ?>

                <?php foreach ($statCards as $card): ?>

                    <a
                        href="appointments.php?status=<?= urlencode($card['key']) ?>"
                        class="appointments-stat-card <?= $filter === $card['key'] ? 'active' : '' ?>"
                    >

                        <div class="appointments-stat-icon <?= e($card['class']) ?>">
                            <i class="fa-solid <?= e($card['icon']) ?>"></i>
                        </div>

                        <span class="appointments-stat-number">
                            <?= (int) $counts[$card['key']] ?>
                        </span>

                        <span class="appointments-stat-label">
                            <?= e($card['label']) ?>
                        </span>

                    </a>

                <?php endforeach; ?>

            </div>

            <!-- APPOINTMENTS TABLE -->

            <div class="appointments-panel">

                <div class="appointments-panel-header">

                    <div class="appointments-panel-title">

                        <div class="appointments-panel-icon">
                            <i class="fa-solid fa-list-check"></i>
                        </div>

                        <div>
                            <h2>
                                <?= $filter === 'All'
                                    ? 'Appointment History'
                                    : e($filter) . ' Appointments' ?>
                            </h2>

                            <p>
                                Your appointments, organised by date.
                            </p>
                        </div>

                    </div>

                    <span class="appointments-result-count">
                        <?= count($rows) ?>
                        record<?= count($rows) === 1 ? '' : 's' ?>
                    </span>

                </div>

                <!-- QUICK FILTERS -->

                <div class="appointments-filter-bar">

                    <span class="appointments-filter-label">
                        Filter by status
                    </span>

                    <?php foreach ($allowedFilters as $statusFilter): ?>

                        <a
                            href="appointments.php?status=<?= urlencode($statusFilter) ?>"
                            class="appointments-filter <?= $filter === $statusFilter ? 'active' : '' ?>"
                        >
                            <?= e($statusFilter) ?>

                            <span class="appointments-filter-count">
                                <?= (int) $counts[$statusFilter] ?>
                            </span>
                        </a>

                    <?php endforeach; ?>

                </div>

                <?php if (!empty($rows)): ?>

                    <div class="appointments-table-wrap">

                        <table class="appointments-table">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Doctor</th>
                                    <th>Department</th>
                                    <th>Date &amp; Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($rows as $a): ?>

                                    <?php
                                    $statusClass = strtolower(
                                        preg_replace(
                                            '/[^a-zA-Z0-9]+/',
                                            '-',
                                            $a['status']
                                        )
                                    );

                                    $dateTimestamp = strtotime(
                                        $a['appointment_date']
                                    );

                                    $timeTimestamp = strtotime(
                                        $a['appointment_time']
                                    );

                                    $canCancel =
                                        $a['status'] === 'Pending'
                                        && $a['appointment_date'] >= date('Y-m-d');
                                    ?>

                                    <tr>

                                        <td>
                                            <span class="appointment-id">
                                                #<?= (int) $a['id'] ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="appointment-doctor">

                                                <div class="appointment-doctor-avatar">
                                                    <i class="fa-solid fa-user-doctor"></i>
                                                </div>

                                                <div>
                                                    <strong>
                                                        <?= e($a['doctor_name']) ?>
                                                    </strong>

                                                    <small>
                                                        Appointment #<?= (int) $a['id'] ?>
                                                    </small>
                                                </div>

                                            </div>
                                        </td>

                                        <td>
                                            <?= e($a['department']) ?>
                                        </td>

                                        <td>
                                            <span class="appointment-date">
                                                <?= $dateTimestamp
                                                    ? e(date('d M Y', $dateTimestamp))
                                                    : e($a['appointment_date']) ?>
                                            </span>

                                            <span class="appointment-time">
                                                <i class="fa-regular fa-clock"></i>
                                                <?= $timeTimestamp
                                                    ? e(date('g:i A', $timeTimestamp))
                                                    : e($a['appointment_time']) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="appointment-status appointment-status-<?= e($statusClass) ?>">
                                                <?= e($a['status']) ?>
                                            </span>
                                        </td>

                                        <td>

                                            <?php if ($canCancel): ?>

                                                <a
                                                    data-confirm="Are you sure you want to cancel this appointment?"
                                                    href="appointments.php?cancel=<?= (int) $a['id'] ?>"
                                                    class="appointment-cancel-btn"
                                                >
                                                    <i class="fa-solid fa-xmark"></i>
                                                    Cancel
                                                </a>

                                            <?php else: ?>

                                                <span class="text-secondary"
                                                      style="font-size:10px">
                                                    —
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                    <div class="appointments-panel-footer">

                        <p>
                            Showing <?= count($rows) ?> appointment(s).
                            Your records are private to your account.
                        </p>

                        <?php if ($filter !== 'All'): ?>

                            <a
                                href="appointments.php"
                                class="appointments-back-btn"
                            >
                                <i class="fa-solid fa-rotate-left"></i>
                                Clear Filter
                            </a>

                        <?php endif; ?>

                    </div>

                <?php else: ?>

                    <div class="appointments-empty">

                        <div class="appointments-empty-icon">
                            <i class="fa-solid fa-calendar-xmark"></i>
                        </div>

                        <h3>
                            <?= $filter === 'All'
                                ? 'No Appointments Yet'
                                : 'No ' . e($filter) . ' Appointments' ?>
                        </h3>

                        <p>
                            <?php if ($filter === 'All'): ?>
                                You have not booked an appointment yet.
                                Book an appointment to get started with your care.
                            <?php else: ?>
                                No appointments match this status right now.
                                You can choose another filter to view your records.
                            <?php endif; ?>
                        </p>

                        <?php if ($filter === 'All'): ?>

                            <a
                                href="../appointment.php"
                                class="appointments-book-btn"
                            >
                                <i class="fa-solid fa-plus"></i>
                                Book Your First Appointment
                            </a>

                        <?php else: ?>

                            <a
                                href="appointments.php"
                                class="appointments-book-btn"
                            >
                                <i class="fa-solid fa-list"></i>
                                View All Appointments
                            </a>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </div>

        </div>
    </div>

</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
