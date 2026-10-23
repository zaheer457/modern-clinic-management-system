
<?php
$title = 'Our Services';

require __DIR__ . '/includes/header.php';

$rows = db_all("
    SELECT
        s.*,
        d.name AS department,
        (
            SELECT COUNT(*)
            FROM service_doctors sd
            JOIN doctors doc ON doc.id = sd.doctor_id
            JOIN users u ON u.id = doc.user_id
            WHERE sd.service_id = s.id
              AND doc.status = 1
              AND u.status = 1
        ) AS available_doctors
    FROM services s
    JOIN departments d ON d.id = s.department_id
    WHERE s.status = 1
    ORDER BY s.name ASC
");

$serviceIcons = [
    'cardiology' => 'fa-heart-pulse',
    'heart' => 'fa-heart-pulse',
    'dental' => 'fa-tooth',
    'dent' => 'fa-tooth',
    'eye' => 'fa-eye',
    'orthopedic' => 'fa-bone',
    'bone' => 'fa-bone',
    'skin' => 'fa-hand-sparkles',
    'dermatology' => 'fa-hand-sparkles',
    'pediatric' => 'fa-baby',
    'child' => 'fa-baby',
    'mental' => 'fa-brain',
    'neurology' => 'fa-brain',
    'laboratory' => 'fa-flask',
    'test' => 'fa-vial',
    'emergency' => 'fa-truck-medical',
    'general' => 'fa-stethoscope',
    'consultation' => 'fa-user-doctor',
];

function clinicServiceIcon($name, $icons)
{
    $name = strtolower((string) $name);

    foreach ($icons as $keyword => $icon) {
        if (strpos($name, $keyword) !== false) {
            return $icon;
        }
    }

    return 'fa-heart-pulse';
}
?>

<style>
/* ===== PREMIUM SERVICES PAGE ===== */

.services-page {
    padding-bottom: 90px;
    overflow: hidden;
}

.services-hero {
    position: relative;
    padding: 85px 0 75px;
    background:
        radial-gradient(ellipse at 85% 15%, rgba(69,213,223,.16), transparent 38%),
        radial-gradient(ellipse at 10% 90%, rgba(21,151,212,.12), transparent 38%),
        linear-gradient(135deg, #08243a 0%, #0a3048 55%, #10465a 100%);
    color: #fff;
    overflow: hidden;
}

.services-hero::before,
.services-hero::after {
    content: "";
    position: absolute;
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 50%;
    pointer-events: none;
}

.services-hero::before {
    width: 390px;
    height: 390px;
    right: -100px;
    top: -190px;
}

.services-hero::after {
    width: 270px;
    height: 270px;
    right: 15px;
    top: -130px;
}

.services-hero .container {
    position: relative;
    z-index: 1;
}

.services-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 9px 15px;
    border: 1px solid rgba(69,213,223,.27);
    border-radius: 50px;
    background: rgba(69,213,223,.09);
    color: #8ceaf0;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.services-hero h1 {
    max-width: 720px;
    margin: 23px 0 17px;
    font-size: clamp(36px, 5vw, 58px);
    line-height: 1.12;
    font-weight: 800;
    letter-spacing: -1.7px;
}

.services-hero h1 span {
    color: #65dce5;
}

.services-hero p {
    max-width: 620px;
    margin: 0;
    color: #c2d5e1;
    font-size: 16px;
    line-height: 1.9;
}

.services-hero-bottom {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 28px;
}

.services-hero-bottom span {
    border: 1px solid #dceef4;
    padding: 10px;
    border-radius: 14px ;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #eaf0f3;
    font-size: 13px;
    font-weight: 600;
}

.services-hero-bottom i {
    color: #65dce5;
}

.services-content {
    padding-top: 62px;
}

.services-heading {
    display: flex;
    align-items: end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 28px;
}

.services-heading h2 {
    color: #12344b;
    font-size: clamp(25px, 3vw, 34px);
    font-weight: 800;
    letter-spacing: -.7px;
    margin: 8px 0 7px;
}

.services-heading p {
    color: #718495;
    margin: 0;
    line-height: 1.7;
}

.services-count {
    padding: 11px 16px;
    border: 1px solid #dceef4;
    border-radius: 14px;
    background: #f2fbfd;
    color: #12688a;
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 23px;
}

.service-premium-card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    padding: 27px;
    border: 1px solid #e3edf2;
    border-radius: 23px;
    background: linear-gradient(155deg, #fff 0%, #fbfdff 100%);
    box-shadow: 0 8px 28px rgba(13,54,77,.045);
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    overflow: hidden;
}

.service-premium-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #1597d4, #45d5df, #32b67a);
    opacity: .8;
}

.service-premium-card:hover {
    transform: translateY(-7px);
    border-color: #bce8ef;
    box-shadow: 0 20px 42px rgba(13,75,101,.11);
}

.service-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}

.service-icon-premium {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 61px;
    height: 61px;
    flex: 0 0 61px;
    border: 1px solid #d8f1f5;
    border-radius: 19px;
    background: linear-gradient(145deg, #e9faff, #effcf8);
    color: #138bb7;
    font-size: 25px;
    transition: transform .25s ease;
}

.service-premium-card:hover .service-icon-premium {
    transform: scale(1.06) rotate(-4deg);
}

.service-department-tag {
    display: inline-flex;
    align-items: center;
    max-width: 58%;
    padding: 8px 11px;
    border-radius: 30px;
    background: #f0f7fa;
    color: #527183;
    font-size: 11px;
    font-weight: 800;
    line-height: 1.4;
    text-align: right;
}

.service-premium-card h3 {
    margin: 23px 0 11px;
    color: #12344b;
    font-size: 21px;
    line-height: 1.4;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.service-description {
    flex: 1;
    color: #708292;
    font-size: 13px;
    line-height: 1.85;
    margin: 0 0 23px;
    overflow-wrap: anywhere;
}

.service-details {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    padding: 15px 0;
    border-top: 1px solid #eaf1f4;
    border-bottom: 1px solid #eaf1f4;
}

.service-detail-item {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #536d7d;
    font-size: 12px;
    font-weight: 700;
}

.service-detail-item i {
    color: #1597b9;
}





.service-book-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    margin-top: 20px;
    padding: 13px 17px;
    border: 0;
    border-radius: 13px;
    background: linear-gradient(110deg, #117eaf, #159fd0);
    color: #fff !important;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none !important;
    box-shadow: 0 7px 17px rgba(21,151,212,.17);
    transition: all .22s ease;
}

.service-book-btn:hover {
    background: linear-gradient(110deg, #08658f, #108ab9);
    box-shadow: 0 10px 23px rgba(21,151,212,.27);
    transform: translateY(-1px);
}

.services-empty {
    padding: 55px 25px;
    border: 1px dashed #c9dfe8;
    border-radius: 22px;
    background: #f8fcfe;
    text-align: center;
}

.services-empty i {
    color: #1597d4;
    font-size: 36px;
}

.services-empty h3 {
    margin: 18px 0 8px;
    color: #12344b;
    font-size: 22px;
    font-weight: 800;
}

.services-empty p {
    color: #718495;
    margin: 0;
}

.services-bottom-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 22px;
    margin-top: 55px;
    padding: 30px 34px;
    border: 1px solid #d8edf2;
    border-radius: 22px;
    background:
        radial-gradient(ellipse at 100% 0%, rgba(69,213,223,.14), transparent 45%),
        linear-gradient(120deg, #f0faff, #f6fffb);
}

.services-bottom-cta h3 {
    margin: 0 0 8px;
    color: #12344b;
    font-size: 21px;
    font-weight: 800;
}

.services-bottom-cta p {
    margin: 0;
    color: #6b8290;
    font-size: 13px;
    line-height: 1.7;
}

.services-bottom-cta a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    flex-shrink: 0;
    padding: 13px 19px;
    border-radius: 12px;
    background: #082f49;
    color: #fff !important;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none !important;
    transition: background .2s ease, transform .2s ease;
}

.services-bottom-cta a:hover {
    background: #105d7d;
    transform: translateY(-2px);
}

@media (max-width: 991px) {
    .services-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .services-hero {
        padding: 68px 0 60px;
    }
}

@media (max-width: 575px) {
    .services-grid {
        grid-template-columns: 1fr;
        gap: 17px;
    }

    .services-hero {
        padding: 54px 0 48px;
    }

    .services-hero h1 {
        letter-spacing: -1px;
    }

    .service-premium-card {
        padding: 23px;
    }

    .services-bottom-cta {
        align-items: flex-start;
        flex-direction: column;
        padding: 24px;
    }

    .services-bottom-cta a {
        width: 100%;
    }
}

```css
/* ===== SERVICES PAGE FINAL SPACING + CARD WIDTH FIX ===== */

/* Remove unwanted gap above the hero */
.services-page {
    margin: 0 !important;
    padding: 0 0 55px !important;
}

.services-hero {
    margin: 0 !important;
    padding: 32px 0 35px !important;
}

/* Keep hero content aligned and compact */
.services-hero .container {
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}

.services-hero h1 {
    font-size: clamp(29px, 3.2vw, 42px) !important;
    line-height: 1.16 !important;
    margin: 15px 0 10px !important;
}

.services-hero p {
    max-width: 560px !important;
    font-size: 13px !important;
    line-height: 1.7 !important;
}

.services-hero-bottom {
    margin-top: 19px !important;
    gap: 10px 18px !important;
}

/* Narrower overall content area */
.services-content {
    padding-top: 32px !important;
}

.services-content > .container {
    max-width: 1100px !important;
}

/* Compact grid and cards */
.services-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 18px !important;
    max-width: 100% !important;
}

.service-premium-card {
    min-width: 0 !important;
    padding: 19px !important;
    border-radius: 17px !important;
}

.service-icon-premium {
    width: 45px !important;
    height: 45px !important;
    flex-basis: 45px !important;
    border-radius: 13px !important;
    font-size: 19px !important;
}

.service-department-tag {
    padding: 6px 8px !important;
    font-size: 10px !important;
}

.service-premium-card h3 {
    font-size: 17px !important;
    line-height: 1.4 !important;
    margin: 15px 0 8px !important;
}

.service-description {
    font-size: 12px !important;
    line-height: 1.65 !important;
    margin-bottom: 14px !important;
}

.service-details {
    gap: 8px 12px !important;
    padding: 10px 0 !important;
}

.service-detail-item {
    font-size: 11px !important;
}



.service-book-btn {
    padding: 10px 11px !important;
    margin-top: 14px !important;
    font-size: 11px !important;
}

/* Tablet */
@media (max-width: 991px) {
    .services-content > .container {
        max-width: 760px !important;
    }

    .services-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }

    .services-hero {
        padding: 28px 0 32px !important;
    }
}

/* Mobile */
@media (max-width: 575px) {
    .services-content > .container {
        max-width: 430px !important;
    }

    .services-grid {
        grid-template-columns: minmax(0, 1fr) !important;
        gap: 15px !important;
    }

    .service-premium-card {
        padding: 20px !important;
    }

    .services-hero {
        padding: 25px 0 28px !important;
    }

    .services-content {
        padding-top: 25px !important;
    }
}

```css
/* ===== SERVICES HERO: HEIGHT AND TOP GAP CORRECTION ===== */

/* Remove spacing around the services page */
body:has(.services-page) {
    margin-top: 0 !important;
}

.services-page {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* Restore a spacious, premium hero container */
.services-hero {
    min-height: 310px !important;
    display: flex !important;
    align-items: center !important;
    margin-top: 0 !important;
    padding: 52px 0 !important;
}

.services-hero .container {
    width: 100% !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
}

.services-hero h1 {
    font-size: clamp(34px, 4vw, 48px) !important;
    margin: 18px 0 14px !important;
    line-height: 1.18 !important;
}

.services-hero p {
    max-width: 650px !important;
    font-size: 14px !important;
    line-height: 1.85 !important;
}

.services-hero-bottom {
    margin-top: 24px !important;
}

/* Services section spacing */
.services-content {
    padding-top: 40px !important;
}

/* Keep the cards compact */
.services-content > .container {
    max-width: 1100px !important;
}

.services-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    gap: 18px !important;
}

.service-premium-card {
    padding: 20px !important;
}

@media (max-width: 767px) {
    .services-hero {
        min-height: auto !important;
        padding: 38px 0 !important;
    }

    .services-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}

@media (max-width: 575px) {
    .services-grid {
        grid-template-columns: 1fr !important;
    }
}

/* ===== SERVICES CARDS: CLEAN PREMIUM HEADER FIX ===== */

/* Remove the green-blue strip from the top of every card */
.services-grid .service-premium-card::before {
    display: none !important;
    content: none !important;
}

/* Keep card layout clean and consistent */
.services-grid .service-premium-card {
    position: relative !important;
    border: 1px solid #203b52 !important;
    border-radius: 20px !important;
    overflow: hidden !important;
    padding: 20px !important;
}

/* Give the icon and speciality a balanced header */
.services-grid .service-card-top {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 14px !important;
    width: 100% !important;
    min-height: 58px !important;
}

/* Speciality label: readable, properly sized and aligned */
.services-grid .service-department-tag {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex: 0 1 auto !important;
    width: auto !important;
    max-width: calc(100% - 60px) !important;
    min-height: 40px !important;
    padding: 9px 14px !important;
    border: 1px solid rgba(69, 213, 223, 0.22) !important;
    border-radius: 12px !important;
    background: #17354d !important;
    color: #b9eaff !important;
    font-size: 12px !important;
    font-weight: 750 !important;
    line-height: 1.4 !important;
    text-align: center !important;
    white-space: normal !important;
    overflow-wrap: anywhere !important;
}

/* Keep the icon aligned with the speciality label */
.services-grid .service-icon-premium {
    flex: 0 0 52px !important;
    width: 52px !important;
    height: 52px !important;
    border-radius: 15px !important;
    font-size: 21px !important;
}

/* Small screens */
@media (max-width: 575px) {
    .services-grid .service-premium-card {
        padding: 19px !important;
    }

    .services-grid .service-card-top {
        gap: 10px !important;
    }

    .services-grid .service-department-tag {
        max-width: calc(100% - 58px) !important;
        padding: 8px 11px !important;
        font-size: 12px !important;
    }
}

</style>

<section class="services-page">

    <section class="services-hero">
        <div class="container">
            <span class="services-eyebrow">
                <i class="fa-solid fa-shield-heart"></i>
                Care you can trust
            </span>

            <h1>
                Healthcare designed<br>
                around <span>your wellbeing.</span>
            </h1>

            <p>
                Discover our healthcare services, explore available specialists,
                and take the next step towards better health with care that puts
                you first.
            </p>

            <div class="services-hero-bottom">
                <span><i class="fa-solid fa-circle-check"></i> Professional care</span>
                <span><i class="fa-solid fa-user-doctor"></i> Specialist-led services</span>
                <span><i class="fa-solid fa-calendar-check"></i> Easy appointment booking</span>
            </div>
        </div>
    </section>

    <section class="services-content">
        <div class="container">

            <div class="services-heading">
                <div>
                    <span class="section-kicker">Explore our care</span>
                    <h2>Our Medical Services</h2>
                    <p>Find the service that matches your healthcare needs.</p>
                </div>

                <div class="services-count">
                    <i class="fa-solid fa-stethoscope me-2"></i>
                    <?= count($rows) ?> Services Available
                </div>
            </div>

            <?php if (!empty($rows)): ?>

                <div class="services-grid">

                    <?php foreach ($rows as $s): ?>
                        <?php
                            $serviceName = (string) ($s['name'] ?? '');
                            $departmentName = (string) ($s['department'] ?? '');
                            $description = trim((string) ($s['description'] ?? ''));
                            $doctorCount = (int) ($s['available_doctors'] ?? 0);
                            $icon = clinicServiceIcon(
                                $serviceName . ' ' . $departmentName,
                                $serviceIcons
                            );
                        ?>

                        <article class="service-premium-card">

                            <div class="service-card-top">
                                <div class="service-icon-premium">
                                    <i class="fa-solid <?= e($icon) ?>"></i>
                                </div>

                                <span class="service-department-tag">
                                    <?= e($departmentName) ?>
                                </span>
                            </div>

                            <h3><?= e($serviceName) ?></h3>

                            <p class="service-description">
                                <?= e(
                                    $description !== ''
                                        ? $description
                                        : 'Our healthcare team is here to help you explore the right care for your needs.'
                                ) ?>
                            </p>

                            <div class="service-details">
                                <span class="service-detail-item">
                                    <i class="fa-regular fa-clock"></i>
                                    <?= (int) ($s['duration'] ?? 30) ?> minutes
                                </span>

                                <?php if (isset($s['price']) && $s['price'] !== null && $s['price'] !== ''): ?>
                                    <span class="service-detail-item">
                                        <i class="fa-solid fa-wallet"></i>
                                        Rs. <?= number_format((float) $s['price'], 0) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            

                            <a
                                href="appointment.php?service=<?= (int) $s['id'] ?>"
                                class="service-book-btn"
                            >
                                Book This Service
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                        </article>
                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="services-empty">
                    <i class="fa-solid fa-heart-pulse"></i>
                    <h3>Services are being updated</h3>
                    <p>Please check back soon to explore our healthcare services.</p>
                </div>

            <?php endif; ?>

            <div class="services-bottom-cta">
                <div>
                    <h3>Not sure which service you need?</h3>
                    <p>Explore our doctors and find a specialist who can guide you.</p>
                </div>

                <a href="doctors.php">
                    <i class="fa-solid fa-user-doctor"></i>
                    Meet Our Doctors
                </a>
            </div>

        </div>
    </section>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

