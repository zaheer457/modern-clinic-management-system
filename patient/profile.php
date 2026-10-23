
<?php
require_once __DIR__ . '/../config/auth.php';

require_role('patient');

$title = 'My Profile';
$base = '../';
$hideNavbar = false;

$userId = (int) current_user()['id'];

/* =========================================================
   GET PATIENT DETAILS
========================================================= */

$p = db_one(
    "SELECT
        p.*,
        u.full_name,
        u.email,
        u.phone
     FROM patients p
     JOIN users u ON u.id = p.user_id
     WHERE p.user_id = ?",
    "i",
    $userId
);

if (!$p) {
    http_response_code(404);
    exit('Patient profile not found.');
}

/* =========================================================
   APPOINTMENT STATISTICS
========================================================= */

$stats = db_one(
    "SELECT
        COUNT(*) AS total_appointments,

        COALESCE(
            SUM(
                CASE
                    WHEN appointment_date >= CURDATE()
                     AND status IN ('Pending', 'Confirmed')
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS upcoming_appointments,

        COALESCE(
            SUM(
                CASE
                    WHEN status = 'Completed'
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS completed_appointments

     FROM appointments
     WHERE patient_id = ?",
    "i",
    $p['id']
);

$totalAppointments = (int) ($stats['total_appointments'] ?? 0);
$upcomingAppointments = (int) ($stats['upcoming_appointments'] ?? 0);
$completedAppointments = (int) ($stats['completed_appointments'] ?? 0);

/* =========================================================
   RECENT APPOINTMENTS
========================================================= */

$recentAppointments = db_all(
    "SELECT
        a.id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        u.full_name AS doctor_name,
        dep.name AS department
     FROM appointments a
     JOIN doctors d ON d.id = a.doctor_id
     JOIN users u ON u.id = d.user_id
     JOIN departments dep ON dep.id = a.department_id
     WHERE a.patient_id = ?
     ORDER BY
        a.appointment_date DESC,
        a.appointment_time DESC
     LIMIT 3",
    "i",
    $p['id']
);

/* =========================================================
   PROFILE INITIAL
========================================================= */

$initial = strtoupper(
    substr(trim($p['full_name'] ?? 'P'), 0, 1)
);

require __DIR__ . '/../includes/header.php';
?>

<style>
    
/* =========================================================
   PATIENT PROFILE PAGE
========================================================= */

.patient-profile-page {
    min-height: 75vh;
    padding: 32px 0 65px;
    background:
        radial-gradient(ellipse at 5% 0%, rgba(69,213,223,.12), transparent 32%),
        radial-gradient(ellipse at 96% 42%, rgba(21,151,212,.08), transparent 30%),
        #f4f9fc;
}

.patient-profile-shell {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
}

/* =========================================================
   PAGE INTRO + BACK TO HOME
========================================================= */

.patient-profile-intro {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 25px;
    padding: 0 2px;
}

.patient-profile-heading {
    flex: 1;
    min-width: 0;
}

.patient-profile-kicker,
.patient-profile-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 9px;
    color: #087fae;
    font-size: 10px;
    font-weight: 850;
    letter-spacing: 1.35px;
    text-transform: uppercase;
}

.patient-profile-intro h1 {
    margin: 0 0 8px;
    color: #08243a;
    font-size: clamp(27px, 3.5vw, 35px);
    font-weight: 850;
    letter-spacing: -1px;
}

.patient-profile-intro h1 span {
    color: #1597d4;
}

.patient-profile-intro p {
    margin: 0;
    color: #698092;
    font-size: 13px;
    line-height: 1.8;
}

/* Back to Home button */

.patient-home-button-wrap {
    margin-top: 0;
}

.patient-home-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    flex-shrink: 0;
    min-height: 43px;
    padding: 11px 17px;
    border: 1px solid #cfe2ec;
    border-radius: 12px;
    background: linear-gradient(135deg, #ffffff, #eaf7fc);
    color: #087eb9;
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 5px 16px rgba(8, 36, 58, 0.07);
    transition: all 0.2s ease;
}

.patient-home-button:hover {
    background: linear-gradient(135deg, #087eb9, #1597d4);
    border-color: #1597d4;
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(21, 151, 212, 0.2);
}

/* =========================================================
   PROFILE BANNER
========================================================= */

.patient-profile-banner {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 21px;
    padding: 27px;
    border-radius: 22px;
    background:
        radial-gradient(circle at 90% 10%, rgba(69,213,223,.27), transparent 34%),
        linear-gradient(120deg, #08243a 0%, #104969 58%, #1597a8 100%);
    box-shadow: 0 16px 36px rgba(8,36,58,.15);
    color: #fff;
}

.patient-profile-banner::after {
    content: "";
    position: absolute;
    z-index: -1;
    width: 190px;
    height: 190px;
    top: -105px;
    right: -42px;
    border: 25px solid rgba(255,255,255,.055);
    border-radius: 50%;
}

.patient-avatar {
    display: flex;
    width: 82px;
    height: 82px;
    flex: 0 0 82px;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 3px solid rgba(255,255,255,.8);
    border-radius: 22px;
    background: linear-gradient(135deg, #45d5df, #1597d4);
    color: #fff;
    font-size: 31px;
    font-weight: 850;
    box-shadow: 0 9px 23px rgba(0,0,0,.2);
}

.patient-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.patient-profile-identity {
    min-width: 0;
    flex: 1;
}

.patient-profile-identity h2 {
    margin: 0 0 7px;
    color: #fff;
    font-size: clamp(19px, 2.5vw, 24px);
    font-weight: 850;
    overflow-wrap: anywhere;
}

.patient-profile-identity p {
    margin: 0;
    color: rgba(255,255,255,.78);
    font-size: 12px;
    overflow-wrap: anywhere;
}

.patient-role-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin-top: 10px;
    padding: 6px 10px;
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 30px;
    background: rgba(255,255,255,.09);
    color: #d8f8fc;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .75px;
    text-transform: uppercase;
}

.patient-edit-top {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 16px;
    border: 1px solid rgba(255,255,255,.3);
    border-radius: 11px;
    background: rgba(255,255,255,.12);
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
    white-space: nowrap;
    transition: .2s ease;
}

.patient-edit-top:hover {
    background: rgba(255,255,255,.22);
    color: #fff;
    transform: translateY(-2px);
}

/* =========================================================
   APPOINTMENT STATISTICS
========================================================= */

.patient-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin: 21px 0;
}

.patient-stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 19px;
    border: 1px solid #dce9f0;
    border-radius: 17px;
    background: linear-gradient(145deg, #edf7fd 0%, #e9f7f8 100%);
    box-shadow: 0 8px 24px rgba(8,36,58,.045);
}

.patient-stat-icon {
    display: grid;
    width: 47px;
    height: 47px;
    flex: 0 0 47px;
    place-items: center;
    border: 1px solid #cde8f3;
    border-radius: 14px;
    background: #dff2fc;
    color: #087fb8;
    font-size: 18px;
}

.patient-stat-icon.green {
    border-color: #cdeee0;
    background: #ddf7ea;
    color: #218a60;
}

.patient-stat-icon.cyan {
    border-color: #c9eeee;
    background: #dcf7f6;
    color: #118d98;
}

.patient-stat-number {
    display: block;
    color: #10364f;
    font-size: 26px;
    font-weight: 850;
    line-height: 1.1;
}

.patient-stat-label {
    display: block;
    margin-top: 5px;
    color: #708798;
    font-size: 11px;
    line-height: 1.5;
}

/* =========================================================
   APPOINTMENTS CARD
========================================================= */

.patient-profile-card {
    overflow: hidden;
    margin-top: 19px;
    border: 1px solid #dce9f0;
    border-radius: 18px;
    background: linear-gradient(145deg, #edf7fd 0%, #e9f7f8 100%);
    box-shadow: 0 11px 32px rgba(8,36,58,.055);
}

.patient-profile-card-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 19px 23px;
    border-bottom: 1px solid rgba(21,151,212,.13);
    background: linear-gradient(110deg, #dff3fd, #e0f7f6);
}

.patient-profile-heading-icon {
    display: grid;
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    place-items: center;
    border: 1px solid #cbe7f2;
    border-radius: 13px;
    background: linear-gradient(135deg, #d9f0ff, #d8f6f4);
    color: #087fb8;
    font-size: 16px;
}

.patient-profile-card-heading h2 {
    margin: 0 0 4px;
    color: #10364f;
    font-size: 16px;
    font-weight: 850;
}

.patient-profile-card-heading p {
    margin: 0;
    color: #708798;
    font-size: 11px;
    line-height: 1.65;
}

/* =========================================================
   APPOINTMENT TABLE
========================================================= */

.patient-appointment-table-wrap {
    overflow-x: auto;
    width: 100%;
}

.patient-appointment-table {
    width: 100%;
    min-width: 630px;
    margin: 0;
    border-collapse: collapse;
}

.patient-appointment-table th {
    padding: 13px 17px;
    background: rgba(21,151,212,.055);
    color: #698092;
    font-size: 10px;
    font-weight: 850;
    text-align: left;
    white-space: nowrap;
}

.patient-appointment-table td {
    padding: 15px 17px;
    border-top: 1px solid #deebf1;
    color: #29495e;
    font-size: 11px;
    vertical-align: middle;
}

.patient-appointment-table tbody tr:hover {
    background: rgba(255,255,255,.48);
}

.patient-doctor-name {
    color: #163c54;
    font-weight: 800;
}

.patient-table-subtitle {
    display: block;
    margin-top: 4px;
    color: #7b909e;
    font-size: 10px;
}

/* Appointment statuses */

.patient-status {
    display: inline-flex;
    align-items: center;
    padding: 6px 9px;
    border: 1px solid #d9e6ed;
    border-radius: 30px;
    background: #fff;
    color: #526b7b;
    font-size: 9px;
    font-weight: 800;
    white-space: nowrap;
}

.patient-status-pending {
    border-color: #f4dfae;
    background: #fff7e3;
    color: #926a16;
}

.patient-status-confirmed {
    border-color: #c5e4f5;
    background: #e6f5ff;
    color: #0878ae;
}

.patient-status-completed {
    border-color: #c8ead9;
    background: #e4f8ed;
    color: #218253;
}

.patient-status-cancelled {
    border-color: #f2d1d1;
    background: #fff0f0;
    color: #b04444;
}

/* Appointments footer */

.patient-appointments-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 17px 22px;
    border-top: 1px solid #dce9f0;
}

.patient-appointments-footer p {
    margin: 0;
    color: #708798;
    font-size: 11px;
    line-height: 1.7;
}

/* =========================================================
   BUTTONS
========================================================= */

.patient-profile-button {
    display: inline-flex;
    min-height: 41px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
    transition: transform .2s ease, box-shadow .2s ease;
}

.patient-view-all-btn {
    border: 0;
    background: linear-gradient(110deg, #087eb9, #1597d4 60%, #27b8c8);
    color: #fff;
    box-shadow: 0 7px 17px rgba(21,151,212,.17);
}

.patient-view-all-btn:hover {
    transform: translateY(-2px);
    color: #fff;
    box-shadow: 0 11px 23px rgba(21,151,212,.24);
}

/* =========================================================
   EMPTY STATE
========================================================= */

.patient-empty-state {
    padding: 30px 20px;
    text-align: center;
}

.patient-empty-icon {
    display: grid;
    width: 53px;
    height: 53px;
    margin: 0 auto 12px;
    place-items: center;
    border: 1px solid #cde8f3;
    border-radius: 17px;
    background: #e0f3fc;
    color: #087fb8;
    font-size: 20px;
}

.patient-empty-state h3 {
    margin: 0 0 7px;
    color: #163c54;
    font-size: 15px;
    font-weight: 850;
}

.patient-empty-state p {
    margin: 0 0 15px;
    color: #718797;
    font-size: 11px;
    line-height: 1.7;
}

/* =========================================================
   RESPONSIVE DESIGN
========================================================= */

@media (max-width: 767px) {
    .patient-profile-page {
        padding: 22px 0 42px;
    }

    .patient-profile-banner {
        gap: 14px;
        padding: 19px;
        border-radius: 16px;
        flex-wrap: wrap;
    }

    .patient-avatar {
        width: 64px;
        height: 64px;
        flex-basis: 64px;
        border-radius: 17px;
        font-size: 25px;
    }

    .patient-edit-top {
        width: 100%;
    }

    .patient-stat-grid {
        grid-template-columns: 1fr;
        gap: 10px;
        margin: 15px 0;
    }

    .patient-stat-card {
        padding: 15px;
    }

    .patient-stat-number {
        font-size: 23px;
    }

    .patient-profile-card {
        border-radius: 16px;
    }

    .patient-profile-card-heading {
        padding: 16px;
    }

    .patient-appointments-footer {
        align-items: stretch;
        flex-direction: column;
        padding: 15px;
    }

    .patient-appointments-footer .patient-view-all-btn {
        width: 100%;
    }
}

/* Smaller screens */

@media (max-width: 576px) {
    .patient-profile-intro {
        align-items: flex-start;
        gap: 15px;
    }

    .patient-home-button {
        min-height: 39px;
        padding: 9px 12px;
        font-size: 11px;
    }
}

/* Very small mobile screens */

@media (max-width: 420px) {
    .patient-profile-banner {
        padding: 16px;
    }

    .patient-profile-intro {
        flex-direction: column;
        align-items: flex-start;
        gap: 13px;
    }
}

</style>

<section class="patient-profile-page">
    <div class="container">
        <div class="patient-profile-shell">

            <!-- PAGE INTRO -->
            <div class="patient-profile-intro">
    <div class="patient-profile-heading">
        <span class="patient-profile-eyebrow">PATIENT PORTAL</span>
        <h1>My Profile</h1>
        <p>Manage your personal information and track your appointments.</p>
    </div>

    <a href="../index.php" class="patient-home-button">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Home</span>
    </a>
</div>

            <!-- PROFILE BANNER -->
            <div class="patient-profile-banner">

                <div class="patient-avatar">
                    <?php if (!empty($p['profile_image'])): ?>
                        <img
                            src="../<?= e($p['profile_image']) ?>"
                            alt="Patient profile picture"
                            onerror="this.style.display='none'; this.parentElement.classList.add('avatar-image-missing'); this.parentElement.textContent='<?= e($initial) ?>';"
                        >
                    <?php else: ?>
                        <?= e($initial) ?>
                    <?php endif; ?>
                </div>

                <div class="patient-profile-identity">
                    <h2><?= e($p['full_name']) ?></h2>
                    <p><?= e($p['email']) ?></p>

                    <span class="patient-role-pill">
                        <i class="fa-solid fa-circle-check"></i>
                        Patient Account
                    </span>
                </div>

                <a href="edit-profile.php" class="patient-edit-top">
                    <i class="fa-solid fa-user-pen"></i>
                    Update Profile
                </a>

            </div>

            <!-- APPOINTMENT STATISTICS -->
            <div class="patient-stat-grid">

                <div class="patient-stat-card">
                    <div class="patient-stat-icon">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>

                    <div>
                        <span class="patient-stat-number">
                            <?= $totalAppointments ?>
                        </span>

                        <span class="patient-stat-label">
                            Total Appointments
                        </span>
                    </div>
                </div>

                <div class="patient-stat-card">
                    <div class="patient-stat-icon cyan">
                        <i class="fa-solid fa-calendar-day"></i>
                    </div>

                    <div>
                        <span class="patient-stat-number">
                            <?= $upcomingAppointments ?>
                        </span>

                        <span class="patient-stat-label">
                            Upcoming Appointments
                        </span>
                    </div>
                </div>

                <div class="patient-stat-card">
                    <div class="patient-stat-icon green">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <div>
                        <span class="patient-stat-number">
                            <?= $completedAppointments ?>
                        </span>

                        <span class="patient-stat-label">
                            Completed Appointments
                        </span>
                    </div>
                </div>

            </div>

            <!-- RECENT APPOINTMENTS -->
            <div class="patient-profile-card">

                <div class="patient-profile-card-heading">
                    <div class="patient-profile-heading-icon">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>

                    <div>
                        <h2>Recent Appointments</h2>
                        <p>A quick overview of your latest appointments.</p>
                    </div>
                </div>

                <?php if (!empty($recentAppointments)): ?>

                    <div class="patient-appointment-table-wrap">
                        <table class="patient-appointment-table">
                            <thead>
                                <tr>
                                    <th>Appointment</th>
                                    <th>Doctor</th>
                                    <th>Date &amp; Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($recentAppointments as $a): ?>

                                    <?php
                                    $statusClass = strtolower(
                                        preg_replace(
                                            '/[^a-zA-Z0-9]+/',
                                            '-',
                                            $a['status']
                                        )
                                    );

                                    $appointmentDate = strtotime(
                                        $a['appointment_date']
                                    );

                                    $appointmentTime = strtotime(
                                        $a['appointment_time']
                                    );
                                    ?>

                                    <tr>
                                        <td>
                                            <strong>
                                                #<?= (int) $a['id'] ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <span class="patient-doctor-name">
                                                <?= e($a['doctor_name']) ?>
                                            </span>

                                            <span class="patient-table-subtitle">
                                                <?= e($a['department']) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <strong>
                                                <?= $appointmentDate
                                                    ? e(date('d M Y', $appointmentDate))
                                                    : e($a['appointment_date']) ?>
                                            </strong>

                                            <span class="patient-table-subtitle">
                                                <?= $appointmentTime
                                                    ? e(date('g:i A', $appointmentTime))
                                                    : e($a['appointment_time']) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="patient-status patient-status-<?= e($statusClass) ?>">
                                                <?= e($a['status']) ?>
                                            </span>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="patient-appointments-footer">
                        <p>
                            Showing your latest
                            <?= count($recentAppointments) ?>
                            appointment(s).
                        </p>

                        <a
                            href="appointments.php"
                            class="patient-profile-button patient-view-all-btn"
                        >
                            <i class="fa-solid fa-calendar-days"></i>
                            View All Appointments
                        </a>

                        <a href="../logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </a>
                    </div>

                <?php else: ?>

                    <div class="patient-empty-state">

                        <div class="patient-empty-icon">
                            <i class="fa-solid fa-calendar-plus"></i>
                        </div>

                        <h3>No Appointments Yet</h3>

                        <p>
                            You have not booked any appointments.
                            Book your first appointment to see it here.
                        </p>

                        <a
                            href="../appointment.php"
                            class="patient-profile-button patient-view-all-btn"
                        >
                            <i class="fa-solid fa-plus"></i>
                            Book an Appointment
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
