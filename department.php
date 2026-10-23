
<?php
require_once __DIR__ . '/config/functions.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(404);
    exit('Department not found.');
}

/* =========================
   DEPARTMENT INFORMATION
========================= */

$dep = db_one(
    "SELECT * FROM departments WHERE id = ? AND status = 1",
    "i",
    $id
);

if (!$dep) {
    http_response_code(404);
    exit('Department not found.');
}

$title = $dep['name'];

/* =========================
   DEPARTMENT DOCTORS
========================= */

$docs = db_all(
    "SELECT d.*, u.full_name
     FROM doctors d
     JOIN users u ON u.id = d.user_id
     WHERE d.department_id = ?
       AND d.status = 1
       AND u.status = 1
     ORDER BY u.full_name ASC",
    "i",
    $id
);

/* =========================
   DEPARTMENT SERVICES
========================= */

$services = db_all(
    "SELECT *
     FROM services
     WHERE department_id = ?
       AND status = 1
     ORDER BY name ASC",
    "i",
    $id
);

require __DIR__ . '/includes/header.php';
?>

<!-- ==================================
     DEPARTMENT HERO
================================== -->

<section class="page-hero department-detail-hero">

    <div class="container">

        <div class="department-hero-layout">

            <div class="department-hero-main">

                <span class="section-kicker">
                    <i class="fa-solid fa-heart-pulse me-2"></i>
                    SPECIALIZED HEALTHCARE
                </span>

                <h1 class="section-title department-detail-title">
                    <?= e($dep['name']) ?>
                </h1>

                <p class="department-detail-description">
                    <?= e($dep['description']) ?>
                </p>

                <p class="department-hero-extra">
                    Explore our medical professionals and available services.
                    Our goal is to make finding the right healthcare service
                    simple, comfortable and convenient for every patient.
                </p>

                <div class="department-hero-actions">

                    <a href="#department-doctors"
                       class="department-primary-btn">
                        <i class="fa-solid fa-user-doctor me-2"></i>
                        Meet Our Doctors
                    </a>

                    <a href="#department-services"
                       class="department-secondary-btn">
                        <i class="fa-solid fa-stethoscope me-2"></i>
                        Explore Services
                    </a>

                </div>

            </div>

            <div class="department-hero-visual">

                <div class="department-visual-orbit orbit-one"></div>
                <div class="department-visual-orbit orbit-two"></div>

                <div class="department-visual-icon">
                    <i class="fa-solid <?= e(!empty($dep['icon']) ? $dep['icon'] : 'fa-hospital') ?>"></i>
                </div>

                <div class="department-floating-card department-floating-top">
                    <span class="department-floating-icon">
                        <i class="fa-solid fa-user-doctor"></i>
                    </span>

                    <div>
                        <strong><?= count($docs) ?></strong>
                        <span>Available Doctors</span>
                    </div>
                </div>

                <div class="department-floating-card department-floating-bottom">
                    <span class="department-floating-icon service-floating-icon">
                        <i class="fa-solid fa-notes-medical"></i>
                    </span>

                    <div>
                        <strong><?= count($services) ?></strong>
                        <span>Listed Services</span>
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>


<!-- ==================================
     QUICK DEPARTMENT STATISTICS
================================== -->

<section class="department-stats-section">

    <div class="container">

        <div class="department-stats-grid">

            <div class="department-stat-item">
                <div class="department-stat-icon">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>

                <div>
                    <strong><?= count($docs) ?></strong>
                    <span>Department Doctors</span>
                </div>
            </div>

            <div class="department-stat-item">
                <div class="department-stat-icon">
                    <i class="fa-solid fa-stethoscope"></i>
                </div>

                <div>
                    <strong><?= count($services) ?></strong>
                    <span>Related Services</span>
                </div>
            </div>

            <div class="department-stat-item">
                <div class="department-stat-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>

                <div>
                    <strong>Online</strong>
                    <span>Appointment Requests</span>
                </div>
            </div>

        </div>

    </div>

</section>


<!-- ==================================
     DOCTORS SECTION
================================== -->

<section class="section department-content-section"
         id="department-doctors">

    <div class="container">

        <div class="department-section-heading">

            <div>
                <span class="section-kicker">
                    MEET YOUR CARE TEAM
                </span>

                <h2 class="section-title mt-2">
                    Our Medical Specialists
                </h2>

                <p>
                    Discover the medical professionals working in
                    <?= e($dep['name']) ?> and explore their profiles.
                </p>
            </div>

            <div class="department-section-count">
                <i class="fa-solid fa-user-doctor"></i>
                <?= count($docs) ?> Doctors
            </div>

        </div>


        <div class="row g-4">

            <?php if (!empty($docs)): ?>

                <?php foreach ($docs as $d): ?>

                    <div class="col-md-6 col-lg-4">

                        <article class="doctor-card department-doctor-card">

                            <div class="doctor-card-top">

                                <div class="department-doctor-avatar">
                                    <i class="fa-solid fa-user-doctor"></i>
                                </div>

                                <span class="doctor-available-badge">
                                    <span></span>
                                    Active
                                </span>

                            </div>

                            <div class="department-doctor-info">

                                <span class="department-doctor-label">
                                    MEDICAL PROFESSIONAL
                                </span>

                                <h3>
                                    <?= e($d['full_name']) ?>
                                </h3>

                                <p class="department-doctor-specialty">
                                    <i class="fa-solid fa-stethoscope me-2"></i>
                                    <?= e($d['specialty'] ?? 'Medical Professional') ?>
                                </p>

                                <div class="department-doctor-divider"></div>

                                <div class="department-doctor-department">
                                    <i class="fa-solid fa-hospital"></i>
                                    <?= e($dep['name']) ?>
                                </div>

                                <a
                                    href="doctor-profile.php?id=<?= (int) $d['id'] ?>"
                                    class="department-doctor-btn"
                                >
                                    View Doctor Profile
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>

                            </div>

                        </article>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12">

                    <div class="department-empty-state">

                        <div class="department-empty-icon">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>

                        <h3>Doctors Coming Soon</h3>

                        <p>
                            There are currently no active doctors listed in
                            this department. Please check back later for
                            updates to our medical team.
                        </p>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>


<!-- ==================================
     SERVICES SECTION
================================== -->

<section class="section department-services-section"
         id="department-services">

    <div class="container">

        <div class="department-section-heading">

            <div>
                <span class="section-kicker">
                    HEALTHCARE SOLUTIONS
                </span>

                <h2 class="section-title mt-2">
                    Our Department Services
                </h2>

                <p>
                    Explore the services listed under
                    <?= e($dep['name']) ?> and choose the next step
                    in your healthcare journey.
                </p>
            </div>

            <div class="department-section-count service-count">
                <i class="fa-solid fa-notes-medical"></i>
                <?= count($services) ?> Services
            </div>

        </div>


        <div class="row g-4">

            <?php if (!empty($services)): ?>

                <?php foreach ($services as $s): ?>

                    <div class="col-md-6 col-lg-4">

                        <article class="service-card department-service-card">

                            <div class="department-service-icon">
                                <i class="fa-solid fa-heart-pulse"></i>
                            </div>

                            <h3>
                                <?= e($s['name']) ?>
                            </h3>

                            <p>
                                <?= e($s['description']) ?>
                            </p>

                            <div class="department-service-divider"></div>

                            <a
                                href="appointment.php?department=<?= (int) $dep['id'] ?>"
                                class="department-service-btn"
                            >
                                <i class="fa-solid fa-calendar-plus me-2"></i>
                                Book an Appointment
                                <i class="fa-solid fa-arrow-right ms-auto"></i>
                            </a>

                        </article>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12">

                    <div class="department-empty-state">

                        <div class="department-empty-icon">
                            <i class="fa-solid fa-stethoscope"></i>
                        </div>

                        <h3>Services Coming Soon</h3>

                        <p>
                            There are currently no services listed for this
                            department. Please check again later.
                        </p>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>


<!-- ==================================
     APPOINTMENT CALL TO ACTION
================================== -->

<section class="department-cta-section">

    <div class="container">

        <div class="department-cta-card">

            <div class="department-cta-icon">
                <i class="fa-solid fa-calendar-check"></i>
            </div>

            <div class="department-cta-content">

                <span>YOUR HEALTH MATTERS</span>

                <h2>Ready to Take the Next Step?</h2>

                <p>
                    Explore our department services and submit an appointment
                    request to begin your healthcare journey.
                </p>

            </div>

            <a
                href="appointment.php?department=<?= (int) $dep['id'] ?>"
                class="department-cta-btn"
            >
                Request an Appointment
                <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>

        </div>

    </div>

</section>


<!-- ==================================
     BACK TO DEPARTMENTS
================================== -->

<div class="department-back-section">

    <div class="container">

        <a href="departments.php" class="department-back-btn">
            <i class="fa-solid fa-arrow-left me-2"></i>
            Back to All Departments
        </a>

    </div>

</div>


<!-- ==================================
     DEPARTMENT PAGE DESIGN
================================== -->

<style>

/* HERO */

.department-detail-hero {
    position: relative;
    overflow: hidden;
    padding: 36px 0 65px;
    background:
        radial-gradient(
            circle at 83% 35%,
            rgba(69, 213, 223, 0.15),
            transparent 29%
        ),
        linear-gradient(
            135deg,
            #f7fbfd 0%,
            #edf8fc 55%,
            #e6f5fb 100%
        );
    border-bottom: 1px solid #deedf4;
}

.department-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 11px;
    margin-bottom: 45px;
    color: #7a8d9b;
    font-size: 12px;
}

.department-breadcrumb a {
    color: #55758a;
    text-decoration: none;
    transition: color 0.2s ease;
}

.department-breadcrumb a:hover {
    color: #1597d4;
}

.department-breadcrumb i {
    font-size: 9px;
    color: #a8bac6;
}

.department-breadcrumb span {
    color: #1597d4;
    font-weight: 700;
}

.department-hero-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.5fr) minmax(270px, 0.8fr);
    align-items: center;
    gap: 45px;
}

.department-hero-main {
    max-width: 760px;
}

.department-detail-title {
    margin-top: 18px;
    margin-bottom: 20px;
    color: #163047;
    font-size: clamp(34px, 4.3vw, 52px);
    font-weight: 800;
    line-height: 1.17;
    letter-spacing: -1.5px;
    overflow-wrap: anywhere;
}

.department-detail-description {
    max-width: 680px;
    margin-bottom: 14px;
    color: #294b61;
    font-size: 17px;
    font-weight: 600;
    line-height: 1.85;
}

.department-hero-extra {
    max-width: 680px;
    color: #718392;
    font-size: 14px;
    line-height: 1.9;
}

.department-hero-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 13px;
    margin-top: 28px;
}

.department-primary-btn,
.department-secondary-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 48px;
    padding: 13px 19px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
}

.department-primary-btn {
    background: #1597d4;
    color: #ffffff;
    box-shadow: 0 7px 18px rgba(21, 151, 212, 0.17);
}

.department-primary-btn:hover {
    background: #087caf;
    color: #ffffff;
    transform: translateY(-2px);
}

.department-secondary-btn {
    background: #ffffff;
    border: 1px solid #d8e8f0;
    color: #31566e;
}

.department-secondary-btn:hover {
    border-color: #1597d4;
    color: #1597d4;
    transform: translateY(-2px);
}


/* HERO VISUAL */

.department-hero-visual {
    position: relative;
    min-height: 320px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.department-visual-orbit {
    position: absolute;
    border: 1px solid rgba(21, 151, 212, 0.16);
    border-radius: 50%;
}

.orbit-one {
    width: 260px;
    height: 260px;
}

.orbit-two {
    width: 205px;
    height: 205px;
}

.department-visual-icon {
    position: relative;
    z-index: 1;
    width: 155px;
    height: 155px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 42px;
    background: linear-gradient(145deg, #1597d4, #087caf);
    color: #ffffff;
    font-size: 66px;
    box-shadow:
        0 25px 55px rgba(21, 120, 175, 0.22),
        inset 0 1px 0 rgba(255,255,255,0.25);
    transform: rotate(-5deg);
}

.department-floating-card {
    position: absolute;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 13px 16px;
    background: rgba(255, 255, 255, 0.97);
    border: 1px solid #e1edf3;
    border-radius: 15px;
    box-shadow: 0 12px 35px rgba(22, 48, 71, 0.09);
}

.department-floating-top {
    top: 24px;
    right: -4px;
}

.department-floating-bottom {
    bottom: 22px;
    left: -6px;
}

.department-floating-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #e2f5fd;
    color: #1597d4;
    font-size: 17px;
}

.service-floating-icon {
    background: #e6f8f0;
    color: #29a879;
}

.department-floating-card strong,
.department-floating-card span:not(.department-floating-icon) {
    display: block;
}

.department-floating-card strong {
    color: #163047;
    font-size: 17px;
    font-weight: 800;
}

.department-floating-card div > span {
    margin-top: 2px;
    color: #7a8d9b;
    font-size: 10px;
}


/* STATISTICS */

.department-stats-section {
    position: relative;
    z-index: 2;
    padding: 0;
    background: #ffffff;
}

.department-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    padding: 35px;
    border-bottom: 1px solid #e7eff4;
}

.department-stat-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 8px 15px;
    border-right: 1px solid #e7eff4;
}

.department-stat-item:last-child {
    border-right: none;
}

.department-stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 15px;
    background: #e9f7fd;
    color: #1597d4;
    font-size: 20px;
}

.department-stat-item strong,
.department-stat-item span {
    display: block;
}

.department-stat-item strong {
    color: #163047;
    font-size: 19px;
    font-weight: 800;
}

.department-stat-item span {
    margin-top: 3px;
    color: #7a8d9b;
    font-size: 12px;
}


/* SECTION HEADINGS */

.department-content-section {
    padding-top: 75px;
    padding-bottom: 70px;
}

.department-services-section {
    padding-top: 70px;
    padding-bottom: 75px;
    background: #f6fafc;
    border-top: 1px solid #eaf1f5;
    border-bottom: 1px solid #eaf1f5;
}

.department-section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 22px;
    margin-bottom: 35px;
}

.department-section-heading .section-title {
    color: #163047;
    font-size: clamp(26px, 3vw, 34px);
    font-weight: 800;
    letter-spacing: -0.8px;
}

.department-section-heading p {
    max-width: 650px;
    margin-top: 12px;
    margin-bottom: 0;
    color: #748695;
    font-size: 14px;
    line-height: 1.85;
}

.department-section-count {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    flex-shrink: 0;
    padding: 11px 15px;
    border: 1px solid #dcecf4;
    border-radius: 12px;
    background: #f2f9fd;
    color: #267ba3;
    font-size: 12px;
    font-weight: 750;
}

.department-section-count i {
    font-size: 15px;
}

.service-count {
    background: #ffffff;
}


/* DOCTOR CARDS */

.department-doctor-card {
    height: 100%;
    overflow: hidden;
    padding: 0;
    border: 1px solid #e3edf3;
    border-radius: 20px;
    background: #ffffff;
    box-shadow: 0 8px 25px rgba(22, 48, 71, 0.045);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.department-doctor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 18px 38px rgba(22, 80, 115, 0.10);
}

.doctor-card-top {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 135px;
    background:
        radial-gradient(circle at 50% 0%, rgba(69,213,223,0.19), transparent 65%),
        linear-gradient(135deg, #eaf7fc, #f4fbfe);
    border-bottom: 1px solid #e8f1f5;
}

.department-doctor-avatar {
    width: 78px;
    height: 78px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 5px solid rgba(255,255,255,0.85);
    border-radius: 24px;
    background: linear-gradient(145deg, #1597d4, #087caf);
    color: #ffffff;
    font-size: 31px;
    box-shadow: 0 8px 20px rgba(21, 120, 175, 0.18);
}

.doctor-available-badge {
    position: absolute;
    top: 14px;
    right: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 10px;
    border-radius: 30px;
    background: #ffffff;
    color: #318967;
    font-size: 10px;
    font-weight: 750;
}

.doctor-available-badge span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #32b67a;
}

.department-doctor-info {
    display: flex;
    flex-direction: column;
    padding: 24px;
}

.department-doctor-label {
    color: #1597d4;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
}

.department-doctor-info h3 {
    margin: 10px 0 8px;
    color: #163047;
    font-size: 19px;
    font-weight: 800;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.department-doctor-specialty {
    margin: 0;
    color: #738797;
    font-size: 13px;
    line-height: 1.7;
}

.department-doctor-specialty i {
    color: #1597d4;
}

.department-doctor-divider,
.department-service-divider {
    height: 1px;
    margin: 19px 0;
    background: #eaf0f4;
}

.department-doctor-department {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin-bottom: 21px;
    color: #557387;
    font-size: 12px;
    line-height: 1.7;
}

.department-doctor-department i {
    margin-top: 3px;
    color: #1597d4;
}

.department-doctor-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: auto;
    padding: 13px 15px;
    border-radius: 11px;
    background: #1597d4;
    color: #ffffff;
    font-size: 12px;
    font-weight: 750;
    text-decoration: none;
    transition: background 0.2s ease;
}

.department-doctor-btn:hover {
    background: #087caf;
    color: #ffffff;
}


/* SERVICE CARDS */

.department-service-card {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 27px;
    border: 1px solid #e3edf3;
    border-radius: 19px;
    background: #ffffff;
    box-shadow: 0 7px 22px rgba(22, 48, 71, 0.035);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.department-service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 30px rgba(22, 80, 115, 0.08);
}

.department-service-icon {
    width: 53px;
    height: 53px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 23px;
    border-radius: 16px;
    background: #e6f6fc;
    color: #1597d4;
    font-size: 22px;
}



.department-service-card h3 {
    margin-bottom: 12px;
    color: #163047;
    font-size: 19px;
    font-weight: 800;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.department-service-card p {
    flex-grow: 1;
    margin-bottom: 0;
    color: #738594;
    font-size: 13px;
    line-height: 1.9;
    overflow-wrap: anywhere;
}

.department-service-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: auto;
    padding: 13px 14px;
    border: 1px solid #d4eaf4;
    border-radius: 11px;
    background: #f4fbfe;
    color: #137eaf;
    font-size: 11px;
    font-weight: 750;
    text-decoration: none;
    transition: all 0.2s ease;
}

.department-service-btn:hover {
    border-color: #1597d4;
    background: #1597d4;
    color: #ffffff;
}


/* EMPTY STATES */

.department-empty-state {
    padding: 50px 24px;
    border: 1px dashed #d6e6ef;
    border-radius: 20px;
    background: #f8fbfd;
    text-align: center;
}

.department-empty-icon {
    width: 65px;
    height: 65px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    border-radius: 20px;
    background: #e4f4fb;
    color: #1597d4;
    font-size: 26px;
}

.department-empty-state h3 {
    margin-bottom: 12px;
    color: #163047;
    font-size: 22px;
    font-weight: 800;
}

.department-empty-state p {
    max-width: 530px;
    margin: 0 auto;
    color: #738594;
    font-size: 13px;
    line-height: 1.9;
}


/* APPOINTMENT CTA */

.department-cta-section {
    padding: 65px 0;
    background: #ffffff;
}

.department-cta-card {
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 34px;
    border: 1px solid #dcecf4;
    border-radius: 22px;
    background:
        radial-gradient(circle at 100% 0%, rgba(69,213,223,0.13), transparent 38%),
        linear-gradient(135deg, #f1f9fd, #ffffff);
}

.department-cta-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 66px;
    height: 66px;
    flex-shrink: 0;
    border-radius: 20px;
    background: #dff3fc;
    color: #1597d4;
    font-size: 27px;
}

.department-cta-content {
    flex: 1;
    min-width: 0;
}

.department-cta-content > span {
    color: #1597d4;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.2px;
}

.department-cta-content h2 {
    margin: 9px 0;
    color: #163047;
    font-size: 24px;
    font-weight: 800;
    line-height: 1.4;
}

.department-cta-content p {
    margin: 0;
    color: #738594;
    font-size: 13px;
    line-height: 1.8;
}

.department-cta-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    padding: 15px 20px;
    border-radius: 12px;
    background: #1597d4;
    color: #ffffff;
    font-size: 12px;
    font-weight: 750;
    text-decoration: none;
    transition: background 0.2s ease, transform 0.2s ease;
}

.department-cta-btn:hover {
    background: #087caf;
    color: #ffffff;
    transform: translateY(-2px);
}


/* BACK BUTTON */

.department-back-section {
    padding: 0 0 45px;
    text-align: center;
    background: #ffffff;
}

.department-back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 13px 20px;
    border: 1px solid #dbe8ef;
    border-radius: 12px;
    background: #ffffff;
    color: #45677d;
    font-size: 12px;
    font-weight: 750;
    text-decoration: none;
    transition: all 0.2s ease;
}

.department-back-btn:hover {
    border-color: #1597d4;
    color: #1597d4;
}


/* RESPONSIVE */

@media (max-width: 991px) {
    .department-hero-layout {
        grid-template-columns: minmax(0, 1fr) minmax(220px, 0.75fr);
        gap: 20px;
    }

    .department-hero-visual {
        transform: scale(0.9);
    }

    .department-floating-top {
        right: -10px;
    }

    .department-floating-bottom {
        left: -10px;
    }

    .department-cta-card {
        flex-wrap: wrap;
    }

    .department-cta-content {
        flex-basis: calc(100% - 95px);
    }

    .department-cta-btn {
        margin-left: 90px;
    }
}

@media (max-width: 767px) {
    .department-detail-hero {
        padding: 25px 0 45px;
    }

    .department-breadcrumb {
        margin-bottom: 32px;
        gap: 8px;
        font-size: 11px;
    }

    .department-hero-layout {
        grid-template-columns: minmax(0, 1fr);
        gap: 20px;
    }

    .department-detail-title {
        font-size: 36px;
        letter-spacing: -1px;
    }

    .department-detail-description {
        font-size: 15px;
    }

    .department-hero-extra {
        font-size: 13px;
    }

    .department-hero-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .department-primary-btn,
    .department-secondary-btn {
        width: 100%;
    }

    .department-hero-visual {
        min-height: 280px;
        margin: 0 auto;
        width: 100%;
        max-width: 360px;
        transform: none;
    }

    .orbit-one {
        width: 230px;
        height: 230px;
    }

    .orbit-two {
        width: 185px;
        height: 185px;
    }

    .department-visual-icon {
        width: 125px;
        height: 125px;
        border-radius: 34px;
        font-size: 52px;
    }

    .department-floating-top {
        top: 15px;
        right: 0;
    }

    .department-floating-bottom {
        bottom: 12px;
        left: 0;
    }

    .department-floating-card {
        padding: 10px 12px;
    }

    .department-floating-icon {
        width: 35px;
        height: 35px;
    }

    .department-stats-grid {
        grid-template-columns: minmax(0, 1fr);
        padding: 15px 0;
    }

    .department-stat-item {
        justify-content: flex-start;
        padding: 15px 5px;
        border-right: none;
        border-bottom: 1px solid #e7eff4;
    }

    .department-stat-item:last-child {
        border-bottom: none;
    }

    .department-content-section,
    .department-services-section {
        padding-top: 48px;
        padding-bottom: 48px;
    }

    .department-section-heading {
        align-items: flex-start;
        flex-direction: column;
        gap: 18px;
        margin-bottom: 27px;
    }

    .department-section-heading .section-title {
        font-size: 28px;
    }

    .department-doctor-info,
    .department-service-card {
        padding: 22px;
    }

    .department-cta-section {
        padding: 45px 0;
    }

    .department-cta-card {
        align-items: flex-start;
        flex-direction: column;
        padding: 25px;
        gap: 18px;
    }

    .department-cta-content {
        flex-basis: auto;
        width: 100%;
    }

    .department-cta-content h2 {
        font-size: 22px;
    }

    .department-cta-btn {
        width: 100%;
        margin-left: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .department-primary-btn,
    .department-secondary-btn,
    .department-doctor-card,
    .department-doctor-btn,
    .department-service-card,
    .department-service-btn,
    .department-cta-btn,
    .department-back-btn {
        transition: none;
    }

    .department-doctor-card:hover,
    .department-service-card:hover,
    .department-primary-btn:hover,
    .department-secondary-btn:hover,
    .department-cta-btn:hover {
        transform: none;
    }
}


/* =====================================
   PREMIUM DEPARTMENT SERVICE CARDS
===================================== */

.department-service-card {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    padding: 28px !important;
    border: 1px solid #e0ebf2 !important;
    border-radius: 22px !important;
    background: linear-gradient(145deg, #ffffff 0%, #f8fcfe 100%) !important;
    box-shadow: 0 8px 25px rgba(22, 48, 71, 0.055) !important;
    transition: transform 0.28s ease,
                box-shadow 0.28s ease,
                border-color 0.28s ease !important;
}

/* Decorative top accent */
.department-service-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #1597d4, #45d5df);
    opacity: 0.8;
}

/* Soft decorative glow */
.department-service-card::after {
    content: "";
    position: absolute;
    z-index: -1;
    top: -65px;
    right: -65px;
    width: 155px;
    height: 155px;
    border-radius: 50%;
    background: rgba(69, 213, 223, 0.09);
    transition: transform 0.3s ease;
}

.department-service-card:hover {
    transform: translateY(-7px) !important;
    border-color: #a8dced !important;
    box-shadow: 0 20px 42px rgba(21, 112, 155, 0.12) !important;
}

.department-service-card:hover::after {
    transform: scale(1.25);
}

/* Service icon */
.department-service-icon {
    position: relative;
    z-index: 1;
    width: 64px !important;
    height: 64px !important;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px !important;
    border: 1px solid #d7eff9;
    border-radius: 19px !important;
    background: linear-gradient(145deg, #e5f7fe, #d8f1fa) !important;
    color: #1189c2 !important;
    font-size: 25px !important;
    box-shadow: 0 7px 16px rgba(21, 151, 212, 0.07);
    transition: transform 0.28s ease,
                background 0.28s ease,
                color 0.28s ease;
}

.department-service-card:hover .department-service-icon {
    transform: translateY(-3px) rotate(-4deg);
    background: linear-gradient(145deg, #1597d4, #087caf) !important;
    color: #ffffff !important;
}

/* Service name */
.department-service-card h3 {
    position: relative;
    z-index: 1;
    margin-bottom: 13px !important;
    color: #163047 !important;
    font-size: 20px !important;
    font-weight: 800 !important;
    line-height: 1.5 !important;
    letter-spacing: -0.35px;
    overflow-wrap: anywhere;
}

/* Service description */
.department-service-card p {
    position: relative;
    z-index: 1;
    flex-grow: 1;
    margin-bottom: 0 !important;
    color: #6b7d8d !important;
    font-size: 13px !important;
    line-height: 1.95 !important;
    overflow-wrap: anywhere;
}

/* Divider */
.department-service-divider {
    position: relative;
    z-index: 1;
    height: 1px !important;
    margin: 23px 0 18px !important;
    background: linear-gradient(
        90deg,
        #cde8f3,
        #eaf2f6,
        transparent
    ) !important;
}

/* Appointment button */
.department-service-btn {
    position: relative;
    z-index: 1;
    display: flex !important;
    align-items: center;
    gap: 8px !important;
    width: 100%;
    min-height: 48px;
    margin-top: auto;
    padding: 13px 15px !important;
    border: 1px solid #cde7f3 !important;
    border-radius: 13px !important;
    background: #edf8fd !important;
    color: #baced7 !important;
    font-size: 12px !important;
    font-weight: 750 !important;
    text-decoration: none !important;
    transition: background 0.25s ease,
                color 0.25s ease,
                border-color 0.25s ease,
                box-shadow 0.25s ease !important;
}

.department-service-btn:hover {
    border-color: #1597d4 !important;
    background: linear-gradient(110deg, #1597d4, #087caf) !important;
    color: #ffffff !important;
    box-shadow: 0 8px 18px rgba(21, 151, 212, 0.18);
}

/* Keep arrow at the far right */
.department-service-btn .fa-arrow-right {
    margin-left: auto !important;
    transition: transform 0.25s ease;
}

.department-service-btn:hover .fa-arrow-right {
    transform: translateX(4px);
}

/* Responsive spacing */
@media (max-width: 767px) {
    .department-service-card {
        padding: 23px !important;
        border-radius: 19px !important;
    }

    .department-service-icon {
        width: 57px !important;
        height: 57px !important;
        border-radius: 16px !important;
    }

    .department-service-card h3 {
        font-size: 19px !important;
    }
}

@media (prefers-reduced-motion: reduce) {
    .department-service-card,
    .department-service-card::after,
    .department-service-icon,
    .department-service-btn,
    .department-service-btn .fa-arrow-right {
        transition: none !important;
    }

    .department-service-card:hover {
        transform: none !important;
    }
}


</style>

<?php require __DIR__ . '/includes/footer.php'; ?>

