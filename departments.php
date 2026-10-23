
<?php

$title = 'Our Medical Departments';

require __DIR__ . '/includes/header.php';

$rows = db_all("
    SELECT
        dep.*,
        COUNT(d.id) AS doctors
    FROM departments dep
    LEFT JOIN doctors d
        ON d.department_id = dep.id
        AND d.status = 1
    WHERE dep.status = 1
    GROUP BY dep.id
    ORDER BY dep.name ASC
");

?>

<!-- =========================================
     PREMIUM DEPARTMENTS HERO
========================================= -->

<section class="page-hero departments-hero">
    <div class="container">

        <div class="departments-hero-panel">

            <div class="departments-hero-content">

                <div class="departments-hero-kicker">
                    <span class="departments-kicker-icon">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </span>
                    EXCEPTIONAL CARE, EVERY DAY
                </div>

                <h1 class="departments-hero-title">
                    Our Medical
                    <span>Departments</span>
                </h1>

                <p class="departments-hero-text">
                    Discover specialized healthcare services designed around
                    your needs. Our experienced medical professionals combine
                    modern medical practices with compassionate care to help
                    you and your family live healthier lives.
                </p>

                <div class="departments-hero-points">
                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        Specialized Care
                    </span>

                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        Experienced Doctors
                    </span>

                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        Patient-Focused Service
                    </span>
                </div>

            </div>

            <div class="departments-hero-visual">

                <div class="departments-hero-orbit departments-orbit-one"></div>
                <div class="departments-hero-orbit departments-orbit-two"></div>

                <div class="departments-hero-medical-icon">
                    <i class="fa-solid fa-hospital"></i>

                    <span class="departments-medical-plus">
                        <i class="fa-solid fa-plus"></i>
                    </span>
                </div>

                <div class="departments-hero-floating-card">
                    <span class="departments-floating-icon">
                        <i class="fa-solid fa-user-doctor"></i>
                    </span>

                    <div>
                        <strong>Expert Care</strong>
                        <span>Doctors you can trust</span>
                    </div>
                </div>

            </div>

        </div>

    </div>
</section>


<!-- =========================================
     DEPARTMENTS LISTING
========================================= -->

<section class="section departments-section">
    <div class="container">

        <div class="departments-section-heading">

            <div class="departments-heading-copy">
                <span class="section-kicker">
                    FIND THE RIGHT CARE
                </span>

                <h2 class="departments-section-title mt-2">
                    Explore Our Specialties
                </h2>

                <p class="departments-section-description">
                    From routine consultations to specialized treatments,
                    explore our departments and find the care you need.
                </p>
            </div>

            <div class="department-total">
                <div class="department-total-icon">
                    <i class="fa-solid fa-hospital"></i>
                </div>

                <div>
                    <strong><?= count($rows) ?></strong>
                    <span>Available Departments</span>
                </div>
            </div>

        </div>


        <?php if (!empty($rows)): ?>

            <div class="row g-4 departments-grid">

                <?php foreach ($rows as $r): ?>

                    <div class="col-md-6 col-lg-4">

                        <article class="dept-card department-modern-card h-100">

                            <div class="department-card-top">

                                <div class="icon-box department-icon">
                                    <i class="fa-solid <?= e($r['icon'] ?? 'fa-hospital') ?>"></i>
                                </div>

                                <span class="department-card-label">
                                    Medical Specialty
                                </span>

                            </div>

                            <h3 class="department-card-title">
                                <?= e($r['name']) ?>
                            </h3>

                            <p class="department-card-description">
                                <?= e($r['description'] ?? '') ?>
                            </p>

                            <div class="department-card-divider"></div>

                            <div class="department-card-footer">

                                <div class="department-doctor-count">

                                    <span class="doctor-count-icon">
                                        <i class="fa-solid fa-user-doctor"></i>
                                    </span>

                                    <div>
                                        <strong>
                                            <?= (int) $r['doctors'] ?>
                                        </strong>

                                        <span>
                                            <?= (int) $r['doctors'] === 1
                                                ? 'Available Doctor'
                                                : 'Available Doctors' ?>
                                        </span>
                                    </div>

                                </div>

                                <a
                                    href="department.php?id=<?= (int) $r['id'] ?>"
                                    class="department-explore-btn"
                                    aria-label="Explore <?= e($r['name']) ?>"
                                    title="Explore <?= e($r['name']) ?>"
                                >
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>

                            </div>

                        </article>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="departments-empty-state">

                <div class="department-empty-icon">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>

                <h3>Departments Coming Soon</h3>

                <p>
                    Our medical departments are being updated.
                    Please check back soon to explore our available services.
                </p>

            </div>

        <?php endif; ?>

    </div>
</section>


<!-- =========================================
     PATIENT SUPPORT SECTION
========================================= -->

<section class="department-support-section">
    <div class="container">

        <div class="department-support-card">

            <div class="department-support-icon">
                <i class="fa-solid fa-hand-holding-medical"></i>
            </div>

            <div class="department-support-content">

                <span class="section-kicker">
                    WE ARE HERE FOR YOU
                </span>

                <h2>Not Sure Which Department You Need?</h2>

                <p>
                    Explore our medical specialties and choose the department
                    that best matches your healthcare needs. Our goal is to
                    make your journey toward better health simple and
                    comfortable.
                </p>

            </div>

            <a href="doctors.php" class="department-support-btn">
                Meet Our Doctors
                <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>

        </div>

    </div>
</section>


<style>
    
/* ==========================================
   DEPARTMENTS PAGE — REFINED PREMIUM DESIGN
   Only visual styling changes
========================================== */

.departments-hero,
.departments-hero * {
    box-sizing: border-box;
}

.departments-hero {
    position: relative;
    overflow: hidden;
    margin-top: 0 !important;
    padding: 34px 0 42px !important;
    background: #f7fbfd !important;
    border-bottom: 1px solid #e3edf3;
}

/* ==========================================
   HERO CONTAINER
========================================== */

.departments-hero-panel {
    position: relative;
    isolation: isolate;
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(240px, .6fr);
    align-items: center;
    gap: 30px;
    overflow: hidden;
    padding: 45px 48px;
    border: 1px solid #dcebf3;
    border-radius: 26px;
    background:
        radial-gradient(circle at 88% 12%, rgba(69,213,223,.18), transparent 32%),
        linear-gradient(120deg, #fff 0%, #f0f9fd 54%, #e5f6fc 100%);
    box-shadow: 0 14px 40px rgba(22,65,90,.07);
}

.departments-hero-content {
    position: relative;
    z-index: 2;
    min-width: 0;
    max-width: 740px;
}

/* FIXED: EXCEPTIONAL CARE BADGE */

.departments-hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 15px 8px 8px;
    border: 1px solid #042a39;
    border-radius: 40px;
    background: #e8f6fc;
    color: #0c1012 !important;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.05px;
    box-shadow: 0 4px 12px rgba(21,151,212,.06);
}

.departments-kicker-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, #1597d4, #087caf);
    color: #04090e !important;
    font-size: 13px;
}

.departments-hero-title {
    margin: 23px 0 0 !important;
    color: #163047 !important;
    font-size: clamp(35px, 4.3vw, 52px);
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -1.5px;
    overflow-wrap: anywhere;
}

.departments-hero-title span {
    display: block;
    color: #087fb8 !important;
}

.departments-hero-text {
    max-width: 680px;
    margin: 20px 0 0 !important;
    color: #526a7c !important;
    font-size: 15px;
    line-height: 1.9;
}

/* FIXED: HERO FEATURE POINTS */

.departments-hero-points {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 20px;
    margin-top: 27px;
}

.departments-hero-points span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 12px;
    border: 1px solid #d8eaf2;
    border-radius: 10px;
    background: #fff;
    color: #24465c !important;
    font-size: 12px;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(22,48,71,.035);
}

.departments-hero-points i {
    color: #149b71 !important;
    font-size: 14px;
}

/* ==========================================
   HERO MEDICAL ILLUSTRATION
========================================== */

.departments-hero-visual {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 285px;
}

.departments-hero-orbit {
    position: absolute;
    border: 1px solid rgba(21,151,212,.18);
    border-radius: 50%;
}

.departments-orbit-one {
    width: 255px;
    height: 255px;
}

.departments-orbit-two {
    width: 195px;
    height: 195px;
    border-color: rgba(69,213,223,.34);
}

.departments-hero-medical-icon {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 145px;
    height: 145px;
    border: 1px solid rgba(255,255,255,.4);
    border-radius: 38px;
    background: linear-gradient(145deg, #1597d4, #087caf);
    color: #fff;
    font-size: 61px;
    box-shadow: 0 25px 55px rgba(21,120,175,.23);
    transform: rotate(-5deg);
}

.departments-medical-plus {
    position: absolute;
    top: -12px;
    right: -12px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 39px;
    height: 39px;
    border: 4px solid #f0f9fd;
    border-radius: 50%;
    background: #25a875;
    color: #fff;
    font-size: 15px;
    transform: rotate(5deg);
}

.departments-hero-floating-card {
    position: absolute;
    z-index: 2;
    right: -2px;
    bottom: 20px;
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 13px 15px;
    border: 1px solid #dceaf2;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 12px 32px rgba(22,48,71,.10);
}

.departments-floating-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 41px;
    height: 41px;
    flex-shrink: 0;
    border-radius: 12px;
    background: #e2f5fd;
    color: #087caf;
    font-size: 18px;
}

.departments-hero-floating-card strong,
.departments-hero-floating-card div span {
    display: block;
}

.departments-hero-floating-card strong {
    color: #163047 !important;
    font-size: 13px;
    font-weight: 800;
}

.departments-hero-floating-card div span {
    margin-top: 3px;
    color: #5d7384 !important;
    font-size: 10px;
}

/* ==========================================
   DEPARTMENTS SECTION
========================================== */

.departments-section {
    padding: 65px 0 70px;
    background: #fff;
}

.departments-section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
    margin-bottom: 35px;
}

.departments-heading-copy {
    min-width: 0;
}

.departments-section-title {
    color: #163047 !important;
    font-size: clamp(25px, 3vw, 34px);
    font-weight: 800;
    line-height: 1.35;
    letter-spacing: -.8px;
}

.departments-section-description {
    max-width: 650px;
    margin: 10px 0 0;
    color: #586f81 !important;
    font-size: 14px;
    line-height: 1.9;
}

/* FIXED: AVAILABLE DEPARTMENTS COUNT */

.department-total {
    display: flex;
    align-items: center;
    gap: 13px;
    flex-shrink: 0;
    padding: 16px 20px;
    border: 1px solid #cfe6f1;
    border-radius: 16px;
    background: linear-gradient(135deg, #edf8fd, #f7fcff);
    box-shadow: 0 7px 20px rgba(164, 173, 177, 0.05);
}

.department-total-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    flex-shrink: 0;
    border-radius: 13px;
    background: #d6effb;
    color: #087caf !important;
    font-size: 19px;
}

.department-total strong,
.department-total span {
    display: block;
}

.department-total strong {
    color: #d6effb;
    font-size: 25px;
    font-weight: 850;
    line-height: 1.3;
}

.department-total span {
    margin-top: 3px;
    color: #d6effb;
    font-size: 12px;
    font-weight: 650;
}

/* ==========================================
   DEPARTMENT CARDS — CLEANER LOOK
========================================== */

.departments-grid {
    align-items: stretch;
}

.department-modern-card {
    position: relative;
    display: flex;
    flex-direction: column;
    padding: 26px;
    border: 1px solid #e0eaf1;
    border-radius: 20px;
    background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
    box-shadow: 0 7px 24px rgba(215, 224, 232, 0.06);
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}

.department-modern-card:hover {
    transform: translateY(-5px);
    border-color: #a9dced;
    box-shadow: 0 17px 35px rgba(21,100,145,.12);
}

.department-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 23px;
}

.department-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 59px;
    height: 59px;
    flex-shrink: 0;
    border: 1px solid #d7edf7;
    border-radius: 17px;
    background: linear-gradient(135deg, #e1f4fc, #f0faff);
    color: #087fb8 !important;
    font-size: 24px;
    transition: background .25s ease, color .25s ease;
}

.department-modern-card:hover .department-icon {
    background: linear-gradient(135deg, #1597d4, #087caf);
    color: #fff !important;
}

.department-card-label {
    padding: 8px 11px;
    border: 1px solid #dcecf4;
    border-radius: 30px;
    background: #edf7fc;
    color: #cfdae0 !important;
    font-size: 10px;
    font-weight: 750;
    letter-spacing: .2px;
}

.department-card-title {
    margin: 0 0 12px;
    color: #163047 !important;
    font-size: 20px;
    font-weight: 800;
    line-height: 1.45;
    overflow-wrap: anywhere;
}

.department-card-description {
    flex-grow: 1;
    margin: 0;
    color: #586f81 !important;
    font-size: 13px;
    line-height: 1.9;
    overflow-wrap: anywhere;
}

.department-card-divider {
    height: 1px;
    margin: 24px 0 20px;
    background: #e3edf3;
}

.department-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.department-doctor-count {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}

.doctor-count-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    border: 1px solid #d2eee3;
    border-radius: 12px;
    background: #e6f7f0;
    color: #16885f !important;
    font-size: 16px;
}

.department-doctor-count strong,
.department-doctor-count div span {
    display: block;
}

.department-doctor-count strong {
    color: #f7fbff !important;
    font-size: 16px;
    font-weight: 850;
}

.department-doctor-count div span {
    margin-top: 3px;
    color: #f1f9ff !important;
    font-size: 11px;
    font-weight: 600;
}

.department-explore-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 43px;
    height: 43px;
    flex-shrink: 0;
    border: 1px solid #1597d4;
    border-radius: 13px;
    background: #1597d4;
    color: #fff !important;
    text-decoration: none;
    transition: background .2s ease, transform .2s ease, box-shadow .2s ease;
}

.department-explore-btn:hover {
    background: #087caf;
    border-color: #087caf;
    color: #fff !important;
    box-shadow: 0 5px 14px rgba(8,124,175,.2);
    transform: translateX(3px);
}

/* ==========================================
   EMPTY STATE
========================================== */

.departments-empty-state {
    padding: 55px 24px;
    border: 1px dashed #c9e0eb;
    border-radius: 22px;
    background: #f5fafc;
    text-align: center;
}

.department-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 70px;
    height: 70px;
    margin: 0 auto 20px;
    border-radius: 20px;
    background: #e2f4fc;
    color: #087caf;
    font-size: 28px;
}

.departments-empty-state h3 {
    color: #163047 !important;
    font-size: 22px;
    font-weight: 800;
}

.departments-empty-state p {
    max-width: 520px;
    margin: 12px auto 0;
    color: #586f81 !important;
    font-size: 13px;
    line-height: 1.9;
}

/* ==========================================
   PATIENT SUPPORT BANNER
========================================== */

.department-support-section {
    padding: 0 0 75px;
    background: #fff;
}

.department-support-card {
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 34px;
    border: 1px solid #d5e8f1;
    border-radius: 23px;
    background:
        radial-gradient(circle at 100% 0%, rgba(69,213,223,.13), transparent 38%),
        linear-gradient(135deg, #edf8fd, #fff);
    box-shadow: 0 9px 28px rgba(22,65,90,.045);
}

.department-support-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 65px;
    height: 65px;
    flex-shrink: 0;
    border: 1px solid #cce8f4;
    border-radius: 19px;
    background: #dff3fc;
    color: #087caf;
    font-size: 27px;
}

.department-support-content {
    flex: 1;
    min-width: 0;
}

.department-support-content h2 {
    margin: 9px 0 10px;
    color: #163047 !important;
    font-size: 23px;
    font-weight: 800;
    line-height: 1.4;
}

.department-support-content p {
    max-width: 680px;
    margin: 0;
    color: #586f81 !important;
    font-size: 13px;
    line-height: 1.9;
}

.department-support-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    padding: 14px 20px;
    border: 1px solid #1597d4;
    border-radius: 13px;
    background: linear-gradient(135deg, #1597d4, #087caf);
    color: #fff !important;
    font-size: 12px;
    font-weight: 750;
    text-decoration: none;
    box-shadow: 0 6px 15px rgba(21,151,212,.16);
    transition: background .2s ease, transform .2s ease;
}

.department-support-btn:hover {
    background: #086d9d;
    border-color: #086d9d;
    color: #fff !important;
    transform: translateY(-2px);
}

/* ==========================================
   TABLET
========================================== */

@media (max-width: 991px) {
    .departments-hero-panel {
        grid-template-columns: minmax(0, 1fr) minmax(200px, .65fr);
        gap: 15px;
        padding: 35px 30px;
    }

    .departments-hero-visual {
        transform: scale(.9);
    }

    .departments-hero-floating-card {
        right: -5px;
    }

    .department-support-card {
        flex-wrap: wrap;
    }

    .department-support-content {
        flex-basis: calc(100% - 100px);
    }

    .department-support-btn {
        margin-left: 89px;
    }
}

/* ==========================================
   MOBILE
========================================== */

@media (max-width: 767px) {
    .departments-hero {
        padding: 22px 0 32px !important;
    }

    .departments-hero-panel {
        grid-template-columns: minmax(0, 1fr);
        gap: 8px;
        padding: 27px 22px 18px;
        border-radius: 21px;
    }

    .departments-hero-kicker {
        gap: 8px;
        font-size: 9px;
        letter-spacing: .6px;
    }

    .departments-hero-title {
        margin-top: 20px !important;
        font-size: clamp(32px, 8vw, 42px);
        letter-spacing: -1px;
    }

    .departments-hero-text {
        margin-top: 16px !important;
        font-size: 13px;
        line-height: 1.9;
    }

    .departments-hero-points {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        margin-top: 21px;
    }

    .departments-hero-points span {
        max-width: 100%;
    }

    .departments-hero-visual {
        min-height: 245px;
        width: 100%;
        max-width: 340px;
        margin: 0 auto;
        transform: none;
    }

    .departments-orbit-one {
        width: 220px;
        height: 220px;
    }

    .departments-orbit-two {
        width: 170px;
        height: 170px;
    }

    .departments-hero-medical-icon {
        width: 120px;
        height: 120px;
        border-radius: 31px;
        font-size: 49px;
    }

    .departments-hero-floating-card {
        right: 0;
        bottom: 7px;
        padding: 10px 12px;
    }

    .departments-floating-icon {
        width: 35px;
        height: 35px;
    }

    .departments-section {
        padding: 45px 0 50px;
    }

    .departments-section-heading {
        align-items: flex-start;
        flex-direction: column;
        gap: 20px;
    }

    .departments-section-title {
        font-size: 27px;
    }

    .departments-section-description {
        font-size: 13px;
    }

    .department-total {
        width: 100%;
    }

    .department-modern-card {
        padding: 23px;
        border-radius: 18px;
    }

    .department-support-section {
        padding-bottom: 45px;
    }

    .department-support-card {
        align-items: flex-start;
        flex-direction: column;
        gap: 18px;
        padding: 25px;
        border-radius: 20px;
    }

    .department-support-content {
        flex-basis: auto;
        width: 100%;
    }

    .department-support-content h2 {
        font-size: 21px;
    }

    .department-support-btn {
        width: 100%;
        margin-left: 0;
    }
}

/* SMALL MOBILE */

@media (max-width: 575px) {
    .departments-hero-panel {
        padding: 23px 17px 15px;
    }

    .departments-hero-kicker {
        font-size: 8px;
        letter-spacing: .45px;
    }

    .department-modern-card {
        padding: 20px;
    }

    .department-card-top {
        gap: 8px;
    }

    .department-card-label {
        padding: 7px 9px;
        font-size: 9px;
    }

    .department-card-title {
        font-size: 19px;
    }

    .department-support-card {
        padding: 21px;
    }
}

/* ACCESSIBILITY */

@media (prefers-reduced-motion: reduce) {
    .department-modern-card,
    .department-explore-btn,
    .department-support-btn,
    .department-icon {
        transition: none !important;
    }

    .department-modern-card:hover,
    .department-explore-btn:hover,
    .department-support-btn:hover {
        transform: none !important;
    }
}






</style>



<?php require __DIR__ . '/includes/footer.php'; ?>