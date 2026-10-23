
<?php
require_once __DIR__ . '/config/auth.php';

/* =========================================================
   GET DOCTOR DETAILS
========================================================= */

$id = (int) ($_GET['id'] ?? 0);

$d = db_one(
    "SELECT
        d.*,
        u.full_name,
        u.email,
        u.phone,
        dep.name AS department
     FROM doctors d
     JOIN users u ON u.id = d.user_id
     JOIN departments dep ON dep.id = d.department_id
     WHERE d.id = ?
       AND d.status = 1
       AND u.status = 1",
    "i",
    $id
);

if (!$d) {
    http_response_code(404);
    exit('Doctor not found.');
}

$title = $d['full_name'];

/* =========================================================
   GET DOCTOR SCHEDULE
========================================================= */

$sch = db_all(
    "SELECT *
     FROM schedules
     WHERE doctor_id = ?
       AND status = 1
     ORDER BY day_of_week, start_time",
    "i",
    $id
);

$days = [
    'Sunday', 'Monday', 'Tuesday', 'Wednesday',
    'Thursday', 'Friday', 'Saturday'
];

$photo = !empty($d['image'])
    ? $d['image']
    : 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=900&q=85';

$experience = max(0, (int) ($d['experience'] ?? 0));
$fee = (float) ($d['consultation_fee'] ?? 0);

$biography = trim((string) ($d['biography'] ?? ''));
$qualification = trim((string) ($d['qualification'] ?? ''));
$languages = trim((string) ($d['languages'] ?? ''));

$appointmentUrl = 'appointment.php?doctor=' . (int) $d['id'];

require __DIR__ . '/includes/header.php';
?>


<style>
/* =========================================================
   MODERN CLINIC — DOCTOR PROFILE
   Existing blue / cyan theme preserved
========================================================= */

.doctor-profile-page {
    --dp-navy: #08243a;
    --dp-blue: #1597d4;
    --dp-cyan: #45d5df;
    --dp-ink: #163047;
    --dp-muted: #6b7d8d;
    --dp-border: #e2edf3;
    --dp-soft: #f3f9fc;

    color: var(--dp-ink);
    padding-bottom: 45px;
}

.doctor-profile-page,
.doctor-profile-page * {
    box-sizing: border-box;
}

.doctor-profile-page a {
    text-decoration: none;
}

/* ==================== HERO ==================== */

.doctor-profile-hero {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    margin: 18px 18px 0;
    padding: 32px 0;
    border: 1px solid rgba(255, 255, 255, .16);
    border-radius: 25px;
    background:
        radial-gradient(
            circle at 90% 8%,
            rgba(69, 213, 223, .20),
            transparent 30%
        ),
        radial-gradient(
            circle at 0% 100%,
            rgba(21, 151, 212, .18),
            transparent 35%
        ),
        linear-gradient(
            125deg,
            #071e31 0%,
            #0b3651 58%,
            #10516b 100%
        );
    box-shadow: 0 12px 32px rgba(8, 36, 58, .10);
}

.doctor-profile-hero::before,
.doctor-profile-hero::after {
    content: "";
    position: absolute;
    z-index: -1;
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 50%;
    pointer-events: none;
}

.doctor-profile-hero::before {
    width: 330px;
    height: 330px;
    top: -170px;
    right: -100px;
}

.doctor-profile-hero::after {
    width: 240px;
    height: 240px;
    top: -120px;
    right: -55px;
}

.doctor-profile-hero > .container {
    position: relative;
    z-index: 1;
    padding-right: 28px;
    padding-left: 28px;
}

.doctor-profile-hero .row {
    align-items: center;
    row-gap: 28px;
}

/* Doctor photo — reduced height */

.doctor-profile-photo-wrap {
    position: relative;
    width: 100%;
    max-width: 285px;
    margin: 0 auto;
}

.doctor-profile-photo {
    display: block;
    width: 100%;
    height: 310px;
    object-fit: cover;
    object-position: center top;
    border: 4px solid rgba(255, 255, 255, .20);
    border-radius: 23px;
    background: #eaf4f8;
    box-shadow: 0 18px 42px rgba(0, 0, 0, .22);
}

.doctor-photo-caption {
    position: absolute;
    right: 13px;
    bottom: 13px;
    left: 13px;
    padding: 12px 14px;
    border: 1px solid rgba(255, 255, 255, .20);
    border-radius: 14px;
    background: rgba(5, 29, 46, .88);
    color: #fff;
    backdrop-filter: blur(10px);
}

.doctor-photo-caption small {
    display: block;
    margin-bottom: 4px;
    color: #9ce8ee;
    font-size: .70rem;
    font-weight: 700;
    letter-spacing: 1px;
}

.doctor-photo-caption strong {
    display: block;
    font-size: .9rem;
    line-height: 1.45;
    overflow-wrap: anywhere;
}

/* Doctor introduction */

.doctor-profile-content {
    min-width: 0;
    padding: 0 5px;
    color: #fff;
}

.doctor-department-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    max-width: 100%;
    padding: 8px 13px;
    border: 1px solid rgba(126, 235, 239, .28);
    border-radius: 30px;
    background: rgba(69, 213, 223, .11);
    color: #a4f0f1;
    font-size: .80rem;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.doctor-profile-name {
    margin: 17px 0 8px;
    color: #fff;
    font-size: clamp(1.9rem, 3.4vw, 2.85rem);
    font-weight: 800;
    line-height: 1.18;
    letter-spacing: -.8px;
    overflow-wrap: anywhere;
}

.doctor-profile-specialty {
    margin-bottom: 14px;
    color: #80e6ed;
    font-size: 1.04rem;
    font-weight: 650;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.doctor-profile-intro {
    margin: 0;
    color: rgba(255, 255, 255, .77);
    font-size: .93rem;
    line-height: 1.85;
    overflow-wrap: anywhere;
}

/* Stats */

.doctor-hero-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin: 22px 0;
}

.doctor-hero-stat {
    min-width: 0;
    padding: 14px 12px;
    border: 1px solid rgba(255, 255, 255, .13);
    border-radius: 15px;
    background: rgba(255, 255, 255, .07);
}

.doctor-hero-stat .stat-label {
    display: block;
    margin-bottom: 7px;
    color: rgba(255, 255, 255, .66);
    font-size: .72rem;
    line-height: 1.5;
}

.doctor-hero-stat .stat-value {
    display: block;
    color: #fff;
    font-size: .91rem;
    font-weight: 750;
    line-height: 1.55;
    overflow-wrap: anywhere;
}

.doctor-hero-stat .stat-value.fee {
    color: #8cf0e8;
    font-size: 1.04rem;
}

/* Appointment button */

.doctor-book-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 47px;
    padding: 12px 22px;
    border: 1px solid transparent;
    border-radius: 13px;
    background: linear-gradient(110deg, #45d5df, #36b6d8);
    box-shadow: 0 8px 20px rgba(39, 192, 213, .17);
    color: #06283b;
    font-size: .9rem;
    font-weight: 800;
    text-align: center;
    transition: transform .2s ease, box-shadow .2s ease;
}

.doctor-book-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(39, 192, 213, .28);
    color: #06283b;
}

.doctor-hero-note {
    display: block;
    margin-top: 10px;
    color: rgba(255, 255, 255, .62);
    font-size: .78rem;
    line-height: 1.65;
}

/* ==================== MAIN CARD ==================== */

.doctor-profile-main {
    padding-top: 25px;
}

.doctor-combined-card {
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr);
    gap: 30px;
    padding: 28px;
    border: 1px solid var(--dp-border);
    border-radius: 23px;
    background: #fff;
    box-shadow: 0 9px 30px rgba(8, 36, 58, .055);
}

.doctor-combined-about,
.doctor-combined-availability {
    min-width: 0;
}

.doctor-combined-divider {
    display: none;
}

/* Section headings */

.doctor-section-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.doctor-heading-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 43px;
    width: 43px;
    height: 43px;
    border: 1px solid #d9eff5;
    border-radius: 13px;
    background: #eaf8fb;
    color: #128dbd;
    font-size: 1.12rem;
}

.doctor-section-heading h2 {
    margin: 0 0 4px;
    color: var(--dp-navy);
    font-size: 1.16rem;
    font-weight: 800;
    line-height: 1.4;
}

.doctor-section-heading p {
    margin: 0;
    color: var(--dp-muted);
    font-size: .79rem;
    line-height: 1.55;
}

/* About */

.doctor-about-text {
    color: #607487;
    font-size: .91rem;
    line-height: 1.9;
    overflow-wrap: anywhere;
}

.doctor-qualification-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-top: 22px;
    padding: 16px;
    border: 1px solid #d9eef4;
    border-radius: 15px;
    background: #f2fafc;
}

.qualification-check {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 32px;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: #d9f5f4;
    color: #0a8e9d;
    font-size: 1rem;
    font-weight: 800;
}

.doctor-qualification-box h3 {
    margin: 2px 0 6px;
    color: var(--dp-navy);
    font-size: .9rem;
    font-weight: 800;
    line-height: 1.5;
}

.doctor-qualification-box p {
    margin: 0;
    color: #607589;
    font-size: .86rem;
    line-height: 1.75;
    overflow-wrap: anywhere;
}

/* ==================== AVAILABILITY ==================== */

.doctor-schedule-panel {
    padding: 3px 15px;
    border: 1px solid var(--dp-border);
    border-radius: 16px;
    background: #fbfdfe;
    overflow: hidden;
}

.doctor-schedule-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 0;
}

.doctor-schedule-row + .doctor-schedule-row {
    border-top: 1px solid #e7eff4;
}

.doctor-schedule-row-day {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 0;
}

.doctor-schedule-day-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 33px;
    width: 33px;
    height: 33px;
    border-radius: 10px;
    background: #e9f7fb;
    color: #168db9;
    font-size: .95rem;
}

.doctor-schedule-row-day strong {
    color: var(--dp-navy);
    font-size: .84rem;
    font-weight: 750;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.doctor-schedule-row-hours {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 3px;
    min-width: 0;
    text-align: right;
}

.schedule-hours-label {
    color: #168a9e;
    font-size: .70rem;
    font-weight: 750;
}

.schedule-hours-value {
    color: #526f84;
    font-size: .80rem;
    line-height: 1.55;
    overflow-wrap: anywhere;
}

.schedule-break {
    color: #718397;
    font-size: .70rem;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

/* No schedule */

.doctor-no-schedule {
    padding: 24px 17px;
    border: 1px dashed #c8dce6;
    border-radius: 15px;
    background: var(--dp-soft);
    color: var(--dp-muted);
    text-align: center;
}

.no-schedule-icon {
    margin-bottom: 9px;
    color: #1796bd;
    font-size: 1.7rem;
}

.doctor-no-schedule p {
    margin: 0;
    font-size: .86rem;
    line-height: 1.8;
}

/* Bottom booking button */

.doctor-combined-book {
    display: flex;
    width: 100%;
    min-height: 45px;
    margin-top: 15px;
    padding: 11px 16px;
    border-radius: 12px;
}

/* ==================== RESPONSIVE ==================== */

@media (max-width: 991.98px) {
    .doctor-profile-hero {
        padding: 27px 0;
    }

    .doctor-profile-photo-wrap {
        max-width: 260px;
    }

    .doctor-profile-photo {
        height: 285px;
    }

    .doctor-profile-name {
        font-size: clamp(1.8rem, 3.5vw, 2.4rem);
    }

    .doctor-hero-stats {
        gap: 8px;
    }

    .doctor-hero-stat {
        padding: 12px 9px;
    }

    .doctor-hero-stat .stat-label {
        font-size: .68rem;
    }

    .doctor-hero-stat .stat-value {
        font-size: .83rem;
    }

    .doctor-combined-card {
        gap: 22px;
        padding: 23px;
    }
}

@media (max-width: 767.98px) {
    .doctor-profile-hero {
        margin: 12px 10px 0;
        padding: 24px 0 27px;
        border-radius: 21px;
    }

    .doctor-profile-hero > .container {
        padding-right: 18px;
        padding-left: 18px;
    }

    .doctor-profile-photo-wrap {
        max-width: 245px;
    }

    .doctor-profile-photo {
        height: 265px;
    }

    .doctor-profile-content {
        padding: 0;
    }

    .doctor-profile-name {
        font-size: 2rem;
    }

    .doctor-profile-main {
        padding-top: 18px;
    }

    .doctor-combined-card {
        grid-template-columns: minmax(0, 1fr);
        gap: 23px;
        padding: 22px;
        border-radius: 20px;
    }

    .doctor-combined-divider {
        display: block;
        height: 1px;
        background: var(--dp-border);
    }

    .doctor-hero-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 575.98px) {
    .doctor-profile-page {
        padding-bottom: 28px;
    }

    .doctor-profile-hero {
        margin: 10px 8px 0;
        padding: 21px 0 23px;
    }

    .doctor-profile-hero > .container {
        padding-right: 15px;
        padding-left: 15px;
    }

    .doctor-profile-photo-wrap {
        max-width: 225px;
    }

    .doctor-profile-photo {
        height: 245px;
        border-radius: 20px;
    }

    .doctor-profile-name {
        margin-top: 14px;
        font-size: 1.8rem;
        letter-spacing: -.5px;
    }

    .doctor-profile-specialty {
        font-size: .95rem;
    }

    .doctor-profile-intro {
        font-size: .87rem;
        line-height: 1.8;
    }

    .doctor-hero-stats {
        gap: 7px;
        margin: 17px 0;
    }

    .doctor-hero-stat {
        padding: 11px 8px;
        border-radius: 12px;
    }

    .doctor-hero-stat .stat-label {
        margin-bottom: 5px;
        font-size: .65rem;
    }

    .doctor-hero-stat .stat-value {
        font-size: .75rem;
    }

    .doctor-hero-stat .stat-value.fee {
        font-size: .85rem;
    }

    .doctor-book-button {
        width: 100%;
        min-height: 45px;
        padding: 11px 12px;
        font-size: .85rem;
    }

    .doctor-hero-note {
        font-size: .74rem;
    }

    .doctor-profile-main {
        padding-top: 15px;
    }

    .doctor-combined-card {
        gap: 20px;
        padding: 18px 15px;
        border-radius: 18px;
    }

    .doctor-section-heading {
        gap: 10px;
        margin-bottom: 16px;
    }

    .doctor-heading-icon {
        flex-basis: 39px;
        width: 39px;
        height: 39px;
        border-radius: 11px;
        font-size: 1rem;
    }

    .doctor-section-heading h2 {
        font-size: 1.05rem;
    }

    .doctor-section-heading p {
        font-size: .74rem;
    }

    .doctor-about-text {
        font-size: .86rem;
        line-height: 1.85;
    }

    .doctor-qualification-box {
        gap: 10px;
        margin-top: 17px;
        padding: 13px 11px;
    }

    .doctor-qualification-box h3 {
        font-size: .84rem;
    }

    .doctor-qualification-box p {
        font-size: .81rem;
    }

    .doctor-schedule-panel {
        padding: 3px 10px;
    }

    .doctor-schedule-row {
        gap: 7px;
        padding: 12px 0;
    }

    .doctor-schedule-row-day {
        gap: 6px;
    }

    .doctor-schedule-day-icon {
        flex-basis: 29px;
        width: 29px;
        height: 29px;
        font-size: .82rem;
    }

    .doctor-schedule-row-day strong {
        font-size: .77rem;
    }

    .doctor-schedule-row-hours {
        max-width: 58%;
    }

    .schedule-hours-label {
        font-size: .66rem;
    }

    .schedule-hours-value {
        font-size: .73rem;
    }

    .schedule-break {
        font-size: .65rem;
    }

    .doctor-no-schedule {
        padding: 20px 12px;
    }
}
</style>


<section class="doctor-profile-page">

    <!-- DOCTOR HERO -->
    <section class="doctor-profile-hero">
        <div class="container">
            <div class="row g-4 g-lg-5">

                <!-- PHOTO -->
                <div class="col-lg-4 col-md-5">
                    <div class="doctor-profile-photo-wrap">

                        <img
                            src="<?= e($photo) ?>"
                            alt="<?= e($d['full_name']) ?>"
                            class="doctor-profile-photo"
                            onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=900&q=85';"
                        >

                        <div class="doctor-photo-caption">
                            <small>MEDICAL PROFESSIONAL</small>
                            <strong>
                                <?= e($d['department']) ?> Department
                            </strong>
                        </div>

                    </div>
                </div>

                <!-- DOCTOR DETAILS -->
                <div class="col-lg-8 col-md-7">
                    <div class="doctor-profile-content">

                        <span class="doctor-department-pill">
                            <span aria-hidden="true">✚</span>
                            <?= e($d['department']) ?>
                        </span>

                        <h1 class="doctor-profile-name">
                            <?= e($d['full_name']) ?>
                        </h1>

                        <div class="doctor-profile-specialty">
                            <?= e($d['specialty'] ?: 'Medical Specialist') ?>
                        </div>

                        <p class="doctor-profile-intro">
                            <?php if ($biography !== ''): ?>
                                <?= e($biography) ?>
                            <?php else: ?>
                                View this doctor's professional information,
                                consultation details and available appointment
                                schedule.
                            <?php endif; ?>
                        </p>

                        <div class="doctor-hero-stats">

                            <div class="doctor-hero-stat">
                                <span class="stat-label">
                                    Experience
                                </span>
                                <span class="stat-value">
                                    <?= $experience ?>
                                    <?= $experience === 1 ? 'Year' : 'Years' ?>
                                </span>
                            </div>

                            <div class="doctor-hero-stat">
                                <span class="stat-label">
                                    Consultation Fee
                                </span>
                                <span class="stat-value fee">
                                    Rs. <?= number_format($fee, 0) ?>
                                </span>
                            </div>

                            <div class="doctor-hero-stat">
                                <span class="stat-label">
                                    Languages
                                </span>
                                <span class="stat-value">
                                    <?= e($languages !== '' ? $languages : 'English, Urdu') ?>
                                </span>
                            </div>

                        </div>

                        

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ABOUT + QUALIFICATIONS + AVAILABILITY -->
    <section class="doctor-profile-main">
        <div class="container">

            <div class="doctor-combined-card">

                <!-- ABOUT AND QUALIFICATIONS -->
                <div class="doctor-combined-about">

                    <div class="doctor-section-heading">
                        <div class="doctor-heading-icon" aria-hidden="true">
                            ✚
                        </div>
                        <div>
                            <h2>About the Doctor</h2>
                            <p>Professional background and medical expertise</p>
                        </div>
                    </div>

                    <div class="doctor-about-text">
                        <?php if ($biography !== ''): ?>
                            <?= nl2br(e($biography)) ?>
                        <?php else: ?>
                            Professional biography has not been added yet.
                            Please contact the clinic for further information.
                        <?php endif; ?>
                    </div>

                    <div class="doctor-qualification-box">
                        <div class="qualification-check" aria-hidden="true">
                            ✓
                        </div>

                        <div>
                            <h3>Qualifications &amp; Education</h3>
                            <p>
                                <?= e(
                                    $qualification !== ''
                                        ? $qualification
                                        : 'Qualification details have not been added yet.'
                                ) ?>
                            </p>
                        </div>
                    </div>

                </div>

                <div class="doctor-combined-divider"></div>

                <!-- CLINIC AVAILABILITY -->
                <div class="doctor-combined-availability">

                    <div class="doctor-section-heading">
                        <div class="doctor-heading-icon" aria-hidden="true">
                            ◷
                        </div>
                        <div>
                            <h2>Clinic Availability</h2>
                            <p>Working days and consultation hours</p>
                        </div>
                    </div>

                    <?php if ($sch): ?>

                        <div class="doctor-schedule-panel">

                            <?php foreach ($sch as $s): ?>
                                <?php
                                $dayIndex = (int) $s['day_of_week'];
                                $dayName = $days[$dayIndex] ?? 'Scheduled Day';

                                $start = date(
                                    'g:i A',
                                    strtotime($s['start_time'])
                                );

                                $end = date(
                                    'g:i A',
                                    strtotime($s['end_time'])
                                );

                                $hasBreak = !empty($s['break_start'])
                                    && !empty($s['break_end']);

                                $breakStart = $hasBreak
                                    ? date('g:i A', strtotime($s['break_start']))
                                    : '';

                                $breakEnd = $hasBreak
                                    ? date('g:i A', strtotime($s['break_end']))
                                    : '';
                                ?>

                                <div class="doctor-schedule-row">

                                    <div class="doctor-schedule-row-day">
                                        <span
                                            class="doctor-schedule-day-icon"
                                            aria-hidden="true"
                                        >◷</span>

                                        <strong>
                                            <?= e($dayName) ?>
                                        </strong>
                                    </div>

                                    <div class="doctor-schedule-row-hours">

                                        <span class="schedule-hours-label">
                                            Consultation
                                        </span>

                                        <span class="schedule-hours-value">
                                            <?= e($start) ?>
                                            –
                                            <?= e($end) ?>
                                        </span>

                                        <?php if ($hasBreak): ?>
                                            <span class="schedule-break">
                                                Break:
                                                <?= e($breakStart) ?>
                                                –
                                                <?= e($breakEnd) ?>
                                            </span>
                                        <?php endif; ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="doctor-no-schedule">
                            <div class="no-schedule-icon" aria-hidden="true">
                                ◷
                            </div>

                            <p>
                                The doctor's working schedule has not been
                                added yet. Please contact the clinic to
                                confirm availability.
                            </p>
                        </div>

                    <?php endif; ?>

                    <a
                        href="<?= e($appointmentUrl) ?>"
                        class="doctor-book-button doctor-combined-book"
                    >
                        Book Appointment
                        <span aria-hidden="true">→</span>
                    </a>

                </div>

            </div>

        </div>
    </section>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
