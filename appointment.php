
<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/functions.php';

require_login();

/*
 * Appointment booking is patient-only.
 * Keep role handling explicit so an admin/other account is not silently
 * sent to the public homepage when opening a booking URL.
 */
$currentUser = current_user();
$currentRole = $currentUser['role'] ?? '';

if ($currentRole === 'admin') {
    redirect('/clinic-management-system/admin/index.php');
}

if ($currentRole !== 'patient') {
    redirect('/clinic-management-system/login.php');
}

$title = 'Book Appointment';


/* =========================================================
   BOOKING CONTEXT
========================================================= */

$requestedDoctor = (int) (
    $_GET['doctor'] ?? $_POST['doctor_id'] ?? 0
);

$requestedDepartment = (int) (
    $_GET['department'] ?? $_POST['department_id'] ?? 0
);

$requestedService = (int) (
    $_GET['service'] ?? $_POST['service_id'] ?? 0
);

$lockedDoctor = (int) ($_GET['doctor'] ?? 0) > 0;

$lockedDepartment = !$lockedDoctor
    && $requestedDepartment > 0;

$errors = [];

$bookingSuccess = $_SESSION['appointment_booking_success'] ?? '';
unset($_SESSION['appointment_booking_success']);

/* =========================================================
   LOAD SELECTED SERVICE
========================================================= */

$selectedService = null;

if ($requestedService > 0) {
    $selectedService = db_one(
        "SELECT id, department_id, name, description, price, duration
         FROM services
         WHERE id = ?
           AND status = 1",
        "i",
        $requestedService
    );

    if (!$selectedService) {
        $errors[] = 'The selected service is unavailable. Please choose another service.';
    }
}

/* =========================================================
   LOAD ACTIVE DOCTORS
   Use assigned doctors; otherwise fall back to the
   selected service's department.
========================================================= */

$sql = "
    SELECT DISTINCT
        d.id,
        d.department_id,
        d.specialty,
        d.consultation_fee,
        u.full_name,
        dep.name AS department
    FROM doctors d
    JOIN users u ON u.id = d.user_id
    JOIN departments dep ON dep.id = d.department_id
";

$params = [];
$types = '';

if ($requestedService > 0 && $selectedService) {
    $sql .= "
        LEFT JOIN service_doctors sd
            ON sd.doctor_id = d.id
           AND sd.service_id = ?
    ";

    $params[] = $requestedService;
    $types .= 'i';
}

$sql .= "
    WHERE d.status = 1
      AND u.status = 1
";

if ($requestedService > 0) {
    if ($selectedService) {
        $sql .= "
            AND (
                sd.id IS NOT NULL
                OR (
                    NOT EXISTS (
                        SELECT 1
                        FROM service_doctors sx
                        WHERE sx.service_id = ?
                    )
                    AND d.department_id = ?
                )
            )
        ";

        $params[] = $requestedService;
        $types .= 'i';

        $params[] = (int) $selectedService['department_id'];
        $types .= 'i';
    } else {
        // Invalid or inactive service: show no doctors.
        $sql .= " AND 1 = 0";
    }
}

if ($lockedDoctor) {
    $sql .= " AND d.id = ?";
    $params[] = $requestedDoctor;
    $types .= 'i';
} elseif ($lockedDepartment) {
    $sql .= " AND d.department_id = ?";
    $params[] = $requestedDepartment;
    $types .= 'i';
}

$sql .= " ORDER BY u.full_name ASC";

$doctors = db_all($sql, $types, ...$params);

/* =========================================================
   LOAD SELECTED DOCTOR AND SCHEDULES
========================================================= */

$selectedDoctor = null;
$schedules = [];
$availableDays = [];

if ($requestedDoctor > 0) {
    foreach ($doctors as $doctorItem) {
        if ((int) $doctorItem['id'] === $requestedDoctor) {
            $selectedDoctor = $doctorItem;
            break;
        }
    }
} elseif ($lockedDepartment && count($doctors) === 1) {
    $selectedDoctor = $doctors[0];
}

if ($selectedDoctor) {
    $schedules = db_all(
        "SELECT id, day_of_week, start_time, end_time,
                break_start, break_end, max_appointments
         FROM schedules
         WHERE doctor_id = ?
           AND status = 1
         ORDER BY day_of_week, start_time",
        "i",
        (int) $selectedDoctor['id']
    );

    foreach ($schedules as $scheduleItem) {
        $availableDays[(int) $scheduleItem['day_of_week']] = true;
    }
}

/* =========================================================
   PROCESS APPOINTMENT SUBMISSION
========================================================= */

if (is_post()) {
    verify_csrf();

    $doctorId = (int) ($_POST['doctor_id'] ?? 0);
    $scheduleId = (int) ($_POST['schedule_id'] ?? 0);
    $date = trim($_POST['appointment_date'] ?? '');
    $type = trim($_POST['appointment_type'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    $validTypes = [
        'In-person consultation',
        'Online consultation'
    ];

    $doctor = db_one(
        "SELECT d.id, d.department_id, d.consultation_fee
         FROM doctors d
         JOIN users u ON u.id = d.user_id
         WHERE d.id = ?
           AND d.status = 1
           AND u.status = 1",
        "i",
        $doctorId
    );

    $patient = db_one(
        "SELECT id FROM patients WHERE user_id = ?",
        "i",
        (int) current_user()['id']
    );

    $schedule = null;

    $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

    $dateValid = $dateObject
        && $dateObject->format('Y-m-d') === $date
        && $date >= date('Y-m-d');

    if ($lockedDoctor && $doctorId !== $requestedDoctor) {
        $errors[] = 'Please use the doctor selected on the profile.';
    }

    if (
        $lockedDepartment &&
        (!$doctor || (int) $doctor['department_id'] !== $requestedDepartment)
    ) {
        $errors[] = 'Please choose a doctor from the selected department.';
    }

    if (!$doctor || !$patient) {
        $errors[] = 'Your patient account or selected doctor could not be found.';
    }

    if (!$dateValid) {
        $errors[] = 'Please select a valid date that is not in the past.';
    }

    if (!in_array($type, $validTypes, true)) {
        $errors[] = 'Please select a valid appointment type.';
    }

    if (mb_strlen($reason) > 500) {
        $errors[] = 'The reason must not exceed 500 characters.';
    }

    if ($scheduleId <= 0) {
        $errors[] = 'Please select an available shift.';
    }

    if (!$errors && $doctor && $dateValid) {
        $dayOfWeek = (int) $dateObject->format('w');

        $schedule = db_one(
            "SELECT *
             FROM schedules
             WHERE id = ?
               AND doctor_id = ?
               AND day_of_week = ?
               AND status = 1",
            "iii",
            $scheduleId,
            $doctorId,
            $dayOfWeek
        );

        if (!$schedule) {
            $errors[] = 'This doctor is not scheduled on the selected day and time.';
        }
    }

    if (!$errors && $schedule) {
        $start = $schedule['start_time'];
        $end = $schedule['end_time'];
        $slotTime = $start;

        if (
            !empty($schedule['break_start']) &&
            !empty($schedule['break_end']) &&
            $slotTime >= $schedule['break_start'] &&
            $slotTime < $schedule['break_end']
        ) {
            $errors[] = 'The selected time falls within the doctor’s break.';
        }

        $existing = db_one(
            "SELECT COUNT(*) AS total
             FROM appointments
             WHERE doctor_id = ?
               AND appointment_date = ?
               AND appointment_time >= ?
               AND appointment_time < ?
               AND status NOT IN ('Cancelled', 'Rejected')",
            "isss",
            $doctorId,
            $date,
            $start,
            $end
        );

        if (
            !$errors &&
            (int) ($existing['total'] ?? 0) >=
            (int) ($schedule['max_appointments'] ?? 20)
        ) {
            $errors[] = 'This shift has reached its appointment limit.';
        }

        $duplicate = db_one(
            "SELECT id
             FROM appointments
             WHERE doctor_id = ?
               AND appointment_date = ?
               AND appointment_time = ?
               AND status NOT IN ('Cancelled', 'Rejected')
             LIMIT 1",
            "iss",
            $doctorId,
            $date,
            $slotTime
        );

        if (!$errors && $duplicate) {
            $errors[] = 'That appointment time is already booked.';
        }

        if (!$errors) {
            try {
                db_exec(
                    "INSERT INTO appointments
                    (
                        patient_id,
                        doctor_id,
                        department_id,
                        appointment_date,
                        appointment_time,
                        appointment_type,
                        reason
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)",
                    "iiissss",
                    (int) $patient['id'],
                    $doctorId,
                    (int) $doctor['department_id'],
                    $date,
                    $slotTime,
                    $type,
                    $reason
                );

                $_SESSION['appointment_booking_success'] =
                    'Your appointment request has been submitted successfully.';

                redirect('appointment.php?booking=success');
            } catch (Throwable $e) {
                $errors[] = 'The appointment could not be saved. Please try again.';
            }
        }
    }

    /* Preserve doctor selection after validation errors. */
    if ($doctor) {
        foreach ($doctors as $item) {
            if ((int) $item['id'] === $doctorId) {
                $selectedDoctor = $item;
                break;
            }
        }

        if ($selectedDoctor) {
            $schedules = db_all(
                "SELECT id, day_of_week, start_time, end_time,
                        break_start, break_end, max_appointments
                 FROM schedules
                 WHERE doctor_id = ?
                   AND status = 1
                 ORDER BY day_of_week, start_time",
                "i",
                $doctorId
            );

            $availableDays = [];

            foreach ($schedules as $scheduleItem) {
                $availableDays[(int) $scheduleItem['day_of_week']] = true;
            }
        }
    }
}

/* =========================================================
   DISPLAY DATA
========================================================= */

$dayNames = [
    0 => 'Sunday',
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday'
];

$doctorScheduleData = [];

foreach ($doctors as $doc) {
    $rows = db_all(
        "SELECT id, day_of_week, start_time, end_time
         FROM schedules
         WHERE doctor_id = ?
           AND status = 1
         ORDER BY day_of_week, start_time",
        "i",
        (int) $doc['id']
    );

    $formattedSchedules = [];

    foreach ($rows as $row) {
        $dayNumber = (int) $row['day_of_week'];

        $formattedSchedules[] = [
            'id' => (int) $row['id'],
            'day' => $dayNumber,
            'day_name' => $dayNames[$dayNumber] ?? 'Day',
            'start' => $row['start_time'],
            'end' => $row['end_time']
        ];
    }

    $doctorScheduleData[] = [
        'id' => (int) $doc['id'],
        'fee' => (float) $doc['consultation_fee'],
        'schedules' => $formattedSchedules
    ];
}

require __DIR__ . '/includes/header.php';
?>

<style>
/* =========================================================
   MODERN CLINIC - PREMIUM APPOINTMENT PAGE
========================================================= */

.appointment-hero {
    position: relative;
    overflow: hidden;
    padding: 48px 0 42px;
    background:
        radial-gradient(ellipse at 85% 10%, rgba(69,213,223,.18), transparent 35%),
        linear-gradient(120deg, #edf9fd 0%, #f7fbfd 58%, #e8f8fb 100%);
}

.appointment-hero::after {
    content: "";
    position: absolute;
    right: -65px;
    bottom: -145px;
    width: 300px;
    height: 300px;
    border: 45px solid rgba(21,151,212,.055);
    border-radius: 50%;
    pointer-events: none;
}

.appointment-hero-content {
    position: relative;
    z-index: 1;
    max-width: 760px;
}

.appointment-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 13px;
    border: 1px solid #ccebf5;
    border-radius: 30px;
    background: rgba(255,255,255,.8);
    color: #117fac;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.appointment-hero h1 {
    margin: 19px 0 12px;
    color: #08243a;
    font-size: clamp(30px, 5vw, 46px);
    font-weight: 850;
    line-height: 1.17;
    letter-spacing: -1.4px;
}

.appointment-hero p {
    max-width: 610px;
    margin: 0;
    color: #637d8d;
    font-size: 15px;
    line-height: 1.9;
}

.appointment-page {
    padding: 36px 0 82px;
    background:
        radial-gradient(ellipse at 5% 8%, rgba(69,213,223,.08), transparent 30%),
        radial-gradient(ellipse at 95% 40%, rgba(21,151,212,.06), transparent 30%),
        #f7fbfd;
}

.appointment-shell {
    max-width: 940px;
    margin: 0 auto;
    overflow: hidden;
    border: 1px solid #deebf2;
    border-radius: 25px;
    background: #fff;
    box-shadow: 0 20px 65px rgba(8,36,58,.085);
}

.appointment-heading {
    position: relative;
    overflow: hidden;
    padding: 30px 35px;
    background: linear-gradient(115deg, #08243a 0%, #104969 65%, #147d99 100%);
    color: #fff;
}

.appointment-heading::after {
    content: "";
    position: absolute;
    top: -95px;
    right: -35px;
    width: 230px;
    height: 230px;
    border: 32px solid rgba(255,255,255,.055);
    border-radius: 50%;
    pointer-events: none;
}

.appointment-heading h2 {
    position: relative;
    z-index: 1;
    margin: 0 0 9px;
    font-size: clamp(22px, 3vw, 28px);
    font-weight: 800;
    letter-spacing: -.6px;
}

.appointment-heading h2 i {
    margin-right: 9px;
    color: #6de2e7;
}

.appointment-heading p {
    position: relative;
    z-index: 1;
    max-width: 620px;
    margin: 0;
    color: rgba(255,255,255,.78);
    font-size: 13px;
    line-height: 1.8;
}

.appointment-form {
    padding: 34px;
}

.appointment-form .form-label {
    display: block;
    margin-bottom: 9px;
    color: #163047;
    font-size: 13px;
    font-weight: 750;
}

.appointment-form .form-control,
.appointment-form .form-select {
    width: 100%;
    min-height: 52px;
    padding: 12px 14px;
    border: 1px solid #dce8ef;
    border-radius: 13px;
    background-color: #f9fcfe;
    color: #163047;
    font-size: 14px;
    box-shadow: none;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

.appointment-form textarea.form-control {
    min-height: 125px;
    padding-top: 14px;
    resize: vertical;
}

.appointment-form .form-control:focus,
.appointment-form .form-select:focus {
    border-color: #1597d4;
    background-color: #fff;
    box-shadow: 0 0 0 4px rgba(21,151,212,.11);
}

.appointment-form .form-control:disabled,
.appointment-form .form-select:disabled {
    background: #edf3f6;
    color: #8294a1;
    cursor: not-allowed;
    opacity: 1;
}

.appointment-fee {
    display: flex;
    min-height: 52px;
    align-items: center;
    gap: 11px;
    padding: 0 15px;
    border: 1px solid #cfeaf4;
    border-radius: 13px;
    background: linear-gradient(120deg,#edf9fd,#f5fcfe);
    color: #117eac;
    font-size: 14px;
    font-weight: 800;
}

.appointment-fee i {
    display: flex;
    width: 31px;
    height: 31px;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #d9f1fa;
    font-size: 14px;
}

.appointment-help {
    display: block;
    margin-top: 8px;
    color: #7a8d9b;
    font-size: 11px;
    line-height: 1.7;
}

.appointment-submit {
    display: flex;
    min-height: 54px;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 14px 20px;
    border: 0;
    border-radius: 14px !important;
    background: linear-gradient(110deg,#1597d4,#087caf) !important;
    color: #fff !important;
    font-size: 14px;
    font-weight: 800;
    box-shadow: 0 10px 22px rgba(21,151,212,.20);
    transition: transform .2s ease, box-shadow .2s ease;
}

.appointment-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 28px rgba(21,151,212,.28);
}

.appointment-submit:disabled {
    cursor: wait;
    opacity: .8;
    transform: none;
}





.appointment-error {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin: 20px 30px 0;
    padding: 14px 16px;
    border: 1px solid #f4caca;
    border-radius: 13px;
    background: #fff4f4;
    color: #a52c35;
    font-size: 13px;
    line-height: 1.7;
}

.appointment-error i {
    margin-top: 3px;
}

.appointment-empty {
    padding: 35px 25px;
    text-align: center;
}

.appointment-empty-icon {
    display: flex;
    width: 65px;
    height: 65px;
    align-items: center;
    justify-content: center;
    margin: 0 auto 17px;
    border-radius: 20px;
    background: #eaf7fc;
    color: #1597d4;
    font-size: 24px;
}

.appointment-empty h3 {
    color: #163047;
    font-size: 20px;
    font-weight: 800;
}

.appointment-empty p {
    margin-bottom: 0;
    color: #6b7d8d;
    font-size: 13px;
    line-height: 1.8;
}

/* SUCCESS POPUP */

.booking-success-overlay {
    position: fixed;
    z-index: 99999;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(5,24,39,.68);
    backdrop-filter: blur(7px);
    animation: bookingFadeIn .22s ease both;
}

.booking-success-modal {
    width: 100%;
    max-width: 445px;
    padding: 35px 30px 30px;
    border: 1px solid #e1f0e9;
    border-radius: 25px;
    background: #fff;
    text-align: center;
    box-shadow: 0 25px 90px rgba(0,0,0,.22);
    animation: bookingPopIn .32s ease both;
}

.booking-success-icon {
    display: flex;
    width: 77px;
    height: 77px;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    border: 8px solid #f1fbf5;
    border-radius: 50%;
    background: #dff7e9;
    color: #23975b;
    font-size: 30px;
}

.booking-success-modal h2 {
    margin: 0 0 11px;
    color: #163047;
    font-size: 25px;
    font-weight: 850;
    letter-spacing: -.5px;
}

.booking-success-modal p {
    margin: 0 0 24px;
    color: #6b7d8d;
    font-size: 13px;
    line-height: 1.85;
}

.booking-success-actions {
    display: grid;
    gap: 10px;
}

.booking-success-actions a,
.booking-success-actions button {
    display: flex;
    min-height: 49px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 750;
    text-decoration: none;
    cursor: pointer;
}

.booking-success-primary {
    border: 0;
    background: linear-gradient(110deg,#1597d4,#087caf);
    color: #fff !important;
    box-shadow: 0 8px 18px rgba(21,151,212,.17);
}

.booking-success-secondary {
    border: 1px solid #dce8ef;
    background: #f7fbfd;
    color: #315268;
}

.booking-success-primary:hover {
    filter: brightness(.96);
}

.booking-success-secondary:hover {
    background: #edf6fa;
}

@keyframes bookingFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes bookingPopIn {
    from { opacity: 0; transform: translateY(14px) scale(.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@media (max-width: 767px) {
    .appointment-hero {
        padding: 34px 0 30px;
    }

    .appointment-hero h1 {
        letter-spacing: -.8px;
    }

    .appointment-page {
        padding: 24px 0 50px;
    }

    .appointment-shell {
        border-radius: 19px;
    }

    .appointment-heading {
        padding: 25px 22px;
    }

    .appointment-form {
        padding: 24px 20px;
    }

    .appointment-error {
        margin: 17px 17px 0;
    }

    .booking-success-modal {
        padding: 30px 22px 24px;
        border-radius: 21px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .booking-success-overlay,
    .booking-success-modal {
        animation: none;
    }

    .appointment-submit {
        transition: none;
    }
}

/* =========================================================
COMPACT APPOINTMENT FORM — PREMIUM REFINEMENT
========================================================= */

/* Reduce overall page spacing */
.appointment-hero {
padding: 36px 0 30px;
}

.appointment-hero h1 {
margin: 13px 0 8px;
font-size: clamp(28px, 4vw, 39px);
}

.appointment-hero p {
font-size: 14px;
line-height: 1.75;
}

.appointment-page {
padding: 26px 20px 60px;
}

/* Make the form narrower and more elegant */
.appointment-shell {
max-width: 790px;
border-radius: 20px;
box-shadow: 0 14px 42px rgba(8, 36, 58, .075);
}

/* Compact header */
.appointment-heading {
padding: 23px 28px;
}

.appointment-heading h2 {
margin-bottom: 6px;
font-size: 24px;
}

.appointment-heading p {
font-size: 12px;
line-height: 1.65;
}

/* Reduce inner form spacing */
.appointment-form {
padding: 26px 28px;
}

.appointment-form .form-label {
margin-bottom: 6px;
font-size: 12px;
font-weight: 750;
}

/* Smaller inputs and dropdowns */
.appointment-form .form-control,
.appointment-form .form-select {
min-height: 44px;
padding: 9px 12px;
border-radius: 10px;
font-size: 13px;
}

/* Keep the reason field comfortable without being oversized */
.appointment-form textarea.form-control {
min-height: 88px;
padding-top: 11px;
}

/* Subtle focus effect */
.appointment-form .form-control:focus,
.appointment-form .form-select:focus {
border-color: #1597d4;
background-color: #fff;
box-shadow: 0 0 0 3px rgba(21, 151, 212, .09);
}

/* Compact consultation fee panel */
.appointment-fee {
min-height: 44px;
gap: 9px;
padding: 0 12px;
border-radius: 10px;
font-size: 13px;
}

.appointment-fee i {
width: 27px;
height: 27px;
border-radius: 8px;
font-size: 12px;
}

.appointment-help {
margin-top: 5px;
font-size: 10px;
}

/* Neater booking button */
.appointment-submit {
min-height: 46px;
padding: 11px 18px;
border-radius: 11px !important;
font-size: 13px;
}


/* Keep error messages proportionate */
.appointment-error {
margin: 16px 25px 0;
padding: 11px 13px;
border-radius: 10px;
font-size: 12px;
}

/* Smaller empty-state area */
.appointment-empty {
padding: 27px 20px;
}

.appointment-empty-icon {
width: 54px;
height: 54px;
margin-bottom: 13px;
border-radius: 16px;
font-size: 21px;
}

.appointment-empty h3 {
font-size: 18px;
}

/* Responsive layout */
@media (max-width: 767px) {
.appointment-hero {
padding: 30px 0 25px;
}

```
.appointment-page {
    padding: 20px 12px 40px;
}

.appointment-shell {
    border-radius: 16px;
}

.appointment-heading {
    padding: 21px 19px;
}

.appointment-heading h2 {
    font-size: 21px;
}

.appointment-form {
    padding: 21px 17px;
}

.appointment-form .form-control,
.appointment-form .form-select {
    min-height: 43px;
}

.appointment-error {
    margin: 14px 14px 0;
}
```

}

</style>

<!-- PAGE HERO -->

<section class="appointment-hero">
    <div class="container">
        <div class="appointment-hero-content">
            <span class="appointment-kicker">
                <i class="fa-solid fa-heart-pulse"></i>
                Patient Booking
            </span>

            <h1>Book Your Appointment</h1>

            <p>
                Quality healthcare starts with the right appointment.
                Select your preferred doctor, choose an available day
                and shift, and submit your consultation request.
            </p>
        </div>
    </div>
</section>

<!-- BOOKING FORM -->

<section class="appointment-page">
    <div class="container">
        <div class="appointment-shell">

            
<div class="appointment-heading">
    <h2>
        <i class="fa-regular fa-calendar-check"></i>
        Book Your Consultation
    </h2>

    <p>
        Choose your preferred doctor, select an available time,
        and take the next step towards better health.
    </p>
</div>

            <?php foreach ($errors as $error): ?>
                <div class="appointment-error" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= e($error) ?></span>
                </div>
            <?php endforeach; ?>

            <?php if (empty($doctors)): ?>

                <div class="appointment-empty">
                    <div class="appointment-empty-icon">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>

                    <h3>No Doctors Available</h3>

                    <p>
                        No active doctors are available for this selection.
                        Please try another department or check back later.
                    </p>
                </div>

            <?php else: ?>

                <form method="post" id="booking" class="appointment-form">
                    <?= csrf_field() ?>

                    <?php if ($lockedDepartment): ?>
                        <input type="hidden" name="department_id"
                               value="<?= $requestedDepartment ?>">
                    <?php endif; ?>

                    <div class="row g-4">

                        <div class="col-md-6">
                            <label class="form-label" for="doctor">
                                <i class="fa-solid fa-user-doctor me-1"></i>
                                Choose Doctor
                            </label>

                            <select name="doctor_id" id="doctor"
                                    class="form-select" required
                                    <?= $lockedDoctor ? 'disabled' : '' ?>>
                                <option value="">Select a doctor</option>

                                <?php foreach ($doctors as $doc): ?>
                                    <option
                                        value="<?= (int) $doc['id'] ?>"
                                        <?= ((int) ($selectedDoctor['id'] ?? 0)
                                            === (int) $doc['id'])
                                            ? 'selected' : '' ?>
                                    >
                                        <?= e($doc['full_name']) ?>
                                        — <?= e($doc['specialty']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if ($lockedDoctor && $selectedDoctor): ?>
                                <input type="hidden" name="doctor_id"
                                       value="<?= (int) $selectedDoctor['id'] ?>">
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fa-solid fa-wallet me-1"></i>
                                Consultation Fee
                            </label>

                            <div class="appointment-fee" id="fee">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <span>
                                    <?php if ($selectedDoctor): ?>
                                        Rs. <?= number_format(
                                            (float) $selectedDoctor['consultation_fee'],
                                            2
                                        ) ?>
                                    <?php else: ?>
                                        Select a doctor to view the fee
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="day">
                                <i class="fa-regular fa-calendar me-1"></i>
                                Available Day
                            </label>

                            <select id="day" class="form-select" required
                                    <?= !$selectedDoctor ? 'disabled' : '' ?>>
                                <option value="">Choose a day</option>

                                <?php foreach ($dayNames as $number => $day): ?>
                                    <?php if (isset($availableDays[$number])): ?>
                                        <option value="<?= $number ?>">
                                            <?= e($day) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="date">
                                <i class="fa-regular fa-calendar-days me-1"></i>
                                Appointment Date
                            </label>

                            <input type="date"
                                   name="appointment_date"
                                   id="date"
                                   min="<?= date('Y-m-d') ?>"
                                   class="form-control"
                                   required
                                   disabled>

                            <small class="appointment-help">
                                Choose an available day first.
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="schedule">
                                <i class="fa-regular fa-clock me-1"></i>
                                Available Shift
                            </label>

                            <select name="schedule_id"
                                    id="schedule"
                                    class="form-select"
                                    required
                                    disabled>
                                <option value="">Choose a day first</option>
                            </select>

                            <small class="appointment-help">
                                Shift timings depend on the selected doctor.
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="appointmentType">
                                <i class="fa-solid fa-stethoscope me-1"></i>
                                Consultation Type
                            </label>

                            <select name="appointment_type"
                                    id="appointmentType"
                                    class="form-select"
                                    required>
                                <option value="In-person consultation">
                                    In-person consultation
                                </option>
                                <option value="Online consultation">
                                    Online consultation
                                </option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="reason">
                                <i class="fa-regular fa-message me-1"></i>
                                Reason for Visit
                                <span class="text-secondary fw-normal">
                                    (Optional)
                                </span>
                            </label>

                            <textarea name="reason"
                                      id="reason"
                                      class="form-control"
                                      rows="4"
                                      maxlength="500"
                                      placeholder="Briefly describe your concern or reason for visiting..."></textarea>

                            <small class="appointment-help">
                                Maximum 500 characters. Do not use this form
                                for medical emergencies.
                            </small>
                        </div>

                        <div class="col-12 pt-1">
                            <button type="submit"
                                    id="submitBooking"
                                    class="btn appointment-submit">
                                <i class="fa-solid fa-calendar-check"></i>
                                <span>Submit Appointment Request</span>
                            </button>

                            
                        </div>

                    </div>
                </form>

            <?php endif; ?>
        </div>
    </div>
</section>

<!-- SUCCESS POPUP -->

<?php if ($bookingSuccess !== ''): ?>
<div class="booking-success-overlay"
     id="bookingSuccessPopup"
     role="dialog"
     aria-modal="true"
     aria-labelledby="bookingSuccessTitle">

    <div class="booking-success-modal">

        <div class="booking-success-icon">
            <i class="fa-solid fa-check"></i>
        </div>

        <h2 id="bookingSuccessTitle">Appointment Submitted!</h2>

        <p>
            <?= e($bookingSuccess) ?>
            You can now view your appointment request and check its status
            from your patient appointments page.
        </p>

        <div class="booking-success-actions">
            <a href="patient/appointments.php"
               class="booking-success-primary">
                <i class="fa-solid fa-calendar-check"></i>
                View My Appointments
            </a>

            <button type="button"
                    class="booking-success-secondary"
                    id="closeBookingPopup">
                Continue on This Page
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    'use strict';

    const doctorSelect = document.getElementById('doctor');
    const daySelect = document.getElementById('day');
    const dateInput = document.getElementById('date');
    const scheduleSelect = document.getElementById('schedule');
    const feeBox = document.getElementById('fee');
    const bookingForm = document.getElementById('booking');
    const submitButton = document.getElementById('submitBooking');

    if (
        !doctorSelect ||
        !daySelect ||
        !dateInput ||
        !scheduleSelect ||
        !feeBox
    ) {
        return;
    }

    const doctorData = <?= json_encode(
        $doctorScheduleData,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    const lockedDoctor = <?= $lockedDoctor ? 'true' : 'false' ?>;

    function currentDoctor() {
        return doctorData.find(
            item => item.id === Number(doctorSelect.value)
        );
    }

    function resetDateAndShift() {
        dateInput.value = '';
        dateInput.disabled = true;

        scheduleSelect.innerHTML =
            '<option value="">Choose a day first</option>';

        scheduleSelect.disabled = true;
    }

    function updateDoctor() {
        const doctor = currentDoctor();

        daySelect.innerHTML = '<option value="">Choose a day</option>';
        resetDateAndShift();

        if (!doctor) {
            daySelect.disabled = true;

            const feeText = feeBox.querySelector('span');
            if (feeText) {
                feeText.textContent = 'Select a doctor to view the fee';
            }

            return;
        }

        const feeText = feeBox.querySelector('span');

        if (feeText) {
            feeText.textContent = 'Rs. ' +
                doctor.fee.toLocaleString('en-PK', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
        }

        const days = [...new Set(
            doctor.schedules.map(schedule => schedule.day)
        )].sort((a, b) => a - b);

        days.forEach(day => {
            const schedule = doctor.schedules.find(
                item => item.day === day
            );

            const option = document.createElement('option');
            option.value = String(day);
            option.textContent = schedule.day_name;
            daySelect.appendChild(option);
        });

        daySelect.disabled = days.length === 0;

        if (days.length === 0) {
            daySelect.innerHTML =
                '<option value="">No schedule configured</option>';
        }
    }

    function updateDay() {
        resetDateAndShift();

        const doctor = currentDoctor();

        if (!doctor || daySelect.value === '') {
            return;
        }

        const selectedDay = Number(daySelect.value);

        const shifts = doctor.schedules.filter(
            schedule => schedule.day === selectedDay
        );

        scheduleSelect.innerHTML =
            '<option value="">Choose a shift</option>';

        shifts.forEach(shift => {
            const option = document.createElement('option');

            option.value = String(shift.id);
            option.textContent =
                shift.start.substring(0, 5) + ' – ' +
                shift.end.substring(0, 5);

            scheduleSelect.appendChild(option);
        });

        scheduleSelect.disabled = shifts.length === 0;
        dateInput.disabled = false;

        dateInput.onchange = function () {
            if (!dateInput.value) return;

            const chosenDate = new Date(
                dateInput.value + 'T12:00:00'
            );

            if (chosenDate.getDay() !== selectedDay) {
                const selectedDayName =
                    daySelect.options[daySelect.selectedIndex].text;

                alert('Please choose a ' + selectedDayName + ' date.');
                dateInput.value = '';
            }
        };
    }

    if (!lockedDoctor) {
        doctorSelect.addEventListener('change', updateDoctor);
    }

    daySelect.addEventListener('change', updateDay);

    if (bookingForm && submitButton) {
        bookingForm.addEventListener('submit', function () {
            if (!bookingForm.checkValidity()) return;

            submitButton.disabled = true;

            const label = submitButton.querySelector('span');

            if (label) {
                label.textContent = 'Submitting Appointment...';
            }
        });
    }

    updateDoctor();
})();

/* Confirmation popup interactions */
document.addEventListener('DOMContentLoaded', function () {
    const popup = document.getElementById('bookingSuccessPopup');
    const closeButton = document.getElementById('closeBookingPopup');

    if (!popup) return;

    if (closeButton) {
        closeButton.addEventListener('click', function () {
            popup.remove();
        });
    }

    popup.addEventListener('click', function (event) {
        if (event.target === popup) {
            popup.remove();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && popup.isConnected) {
            popup.remove();
        }
    });

    if (closeButton) {
        closeButton.focus();
    }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>