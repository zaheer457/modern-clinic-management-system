
<?php
$title = 'About Us';
require __DIR__ . '/includes/header.php';
?>

<style>
/* =====================================================
   MODERN CLINIC — PREMIUM ABOUT US PAGE
===================================================== */

.about-page {
    overflow: hidden;
    background: #f7fbfd;
    color: #163047;
}

.about-hero {
    position: relative;
    overflow: hidden;
    padding: 76px 0 70px;
    background:
        radial-gradient(ellipse at 90% 15%, rgba(69,213,223,.20), transparent 34%),
        linear-gradient(125deg, #edf9fd, #ffffff 52%, #e9f8fc);
}

.about-hero::before {
    content: "";
    position: absolute;
    width: 330px;
    height: 330px;
    right: -120px;
    top: -175px;
    border: 45px solid rgba(21,151,212,.07);
    border-radius: 50%;
    pointer-events: none;
}

.about-hero-content {
    position: relative;
    z-index: 1;
    max-width: 760px;
}

.about-badge {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 9px 15px;
    border: 1px solid #cdebf5;
    border-radius: 50px;
    background: rgba(255,255,255,.85);
    color: #117fac;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.about-badge i {
    color: #1597d4;
}

.about-hero .section-title {
    max-width: 720px;
    margin: 22px 0 16px;
    color: #08243a;
    font-size: clamp(35px, 5vw, 56px);
    font-weight: 850;
    line-height: 1.13;
    letter-spacing: -1.8px;
}

.about-highlight {
    color: #1597d4;
}

.about-hero-description {
    max-width: 620px;
    margin: 0;
    color: #637d8d;
    font-size: 16px;
    line-height: 1.9;
}

.about-hero-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 13px;
    margin-top: 28px;
}

.about-btn-primary,
.about-btn-secondary {
    display: inline-flex;
    min-height: 48px;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 12px 21px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    transition: transform .2s ease, box-shadow .2s ease;
}

.about-btn-primary {
    border: 1px solid #1597d4;
    background: linear-gradient(110deg, #1597d4, #087caf);
    color: #fff !important;
    box-shadow: 0 9px 22px rgba(21,151,212,.18);
}

.about-btn-secondary {
    border: 1px solid #d5e7ef;
    background: #fff;
    color: #163f58 !important;
}

.about-btn-primary:hover,
.about-btn-secondary:hover {
    transform: translateY(-2px);
}

.about-btn-primary:hover {
    box-shadow: 0 13px 27px rgba(21,151,212,.24);
}

.about-btn-secondary:hover {
    background: #f0f9fc;
}

/* Hero visual */
.about-visual {
    position: relative;
    min-height: 355px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.about-visual-main {
    position: relative;
    display: flex;
    width: 100%;
    max-width: 440px;
    min-height: 310px;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.8);
    border-radius: 30px;
    background:
        radial-gradient(circle at 70% 25%, rgba(69,213,223,.3), transparent 30%),
        linear-gradient(145deg, #0b344f, #105b78 60%, #1597a8);
    box-shadow: 0 25px 60px rgba(8,36,58,.18);
}

.about-visual-main::before,
.about-visual-main::after {
    content: "";
    position: absolute;
    width: 240px;
    height: 240px;
    border: 1px solid rgba(255,255,255,.13);
    border-radius: 50%;
}

.about-visual-main::before {
    right: -70px;
    top: -80px;
}

.about-visual-main::after {
    left: -110px;
    bottom: -140px;
    width: 310px;
    height: 310px;
}

.about-medical-symbol {
    position: relative;
    z-index: 1;
    display: flex;
    width: 142px;
    height: 142px;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255,255,255,.3);
    border-radius: 38px;
    background: rgba(255,255,255,.13);
    color: #fff;
    font-size: 72px;
    box-shadow: 0 15px 35px rgba(0,0,0,.10);
    backdrop-filter: blur(10px);
}

.about-visual-label {
    position: absolute;
    z-index: 2;
    right: 22px;
    bottom: 22px;
    left: 22px;
    padding: 16px 18px;
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 15px;
    background: rgba(5,35,54,.55);
    color: #fff;
    backdrop-filter: blur(10px);
}

.about-visual-label strong {
    display: block;
    margin-bottom: 4px;
    font-size: 15px;
}

.about-visual-label span {
    color: rgba(255,255,255,.78);
    font-size: 12px;
}

/* Intro and mission */
.about-section {
    padding: 76px 0;
}

.about-section-heading {
    max-width: 680px;
    margin: 0 auto 38px;
    text-align: center;
}

.about-eyebrow {
    display: inline-block;
    margin-bottom: 12px;
    color: #1597d4;
    font-size: 11px;
    font-weight: 850;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.about-section-heading h2 {
    margin: 0 0 13px;
    color: #08243a;
    font-size: clamp(27px, 3.5vw, 38px);
    font-weight: 850;
    letter-spacing: -1px;
}

.about-section-heading p {
    margin: 0;
    color: #6b7d8d;
    font-size: 14px;
    line-height: 1.9;
}

.about-info-card {
    height: 100%;
    padding: 28px;
    border: 1px solid #e0edf3;
    border-radius: 21px;
    background: #fff;
    box-shadow: 0 10px 32px rgba(8,36,58,.035);
    transition: transform .25s ease, box-shadow .25s ease;
}

.about-info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 17px 38px rgba(8,36,58,.08);
}

.about-card-icon {
    display: flex;
    width: 53px;
    height: 53px;
    align-items: center;
    justify-content: center;
    margin-bottom: 21px;
    border: 1px solid #d9eff8;
    border-radius: 15px;
    background: linear-gradient(135deg, #e5f7fd, #f0fbfd);
    color: #1288bd;
    font-size: 21px;
}

.about-info-card h3 {
    margin: 0 0 11px;
    color: #12334b;
    font-size: 19px;
    font-weight: 850;
}

.about-info-card p {
    margin: 0;
    color: #6b7d8d;
    font-size: 13px;
    line-height: 1.9;
}

/* Values */
.about-values-section {
    padding: 72px 0;
    background: #edf7fb;
}

.about-value-card {
    display: flex;
    height: 100%;
    align-items: flex-start;
    gap: 14px;
    padding: 21px;
    border: 1px solid #dcecf3;
    border-radius: 17px;
    background: rgba(255,255,255,.92);
    transition: transform .2s ease, border-color .2s ease;
}

.about-value-card:hover {
    transform: translateY(-3px);
    border-color: #9edbed;
}

.about-value-icon {
    display: flex;
    width: 43px;
    height: 43px;
    flex: 0 0 43px;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #e3f5fb;
    color: #1388bb;
    font-size: 17px;
}

.about-value-card h3 {
    margin: 2px 0 6px;
    color: #163047;
    font-size: 14px;
    font-weight: 850;
}

.about-value-card p {
    margin: 0;
    color: #728493;
    font-size: 12px;
    line-height: 1.75;
}

/* Final CTA */
.about-cta {
    position: relative;
    overflow: hidden;
    margin: 70px 0;
    padding: 43px 40px;
    border-radius: 25px;
    background:
        radial-gradient(circle at 90% 10%, rgba(69,213,223,.22), transparent 32%),
        linear-gradient(115deg, #08243a, #104969 65%, #147d99);
    box-shadow: 0 20px 48px rgba(8,36,58,.13);
}

.about-cta h2 {
    margin: 0 0 12px;
    color: #fff;
    font-size: clamp(25px, 3vw, 34px);
    font-weight: 850;
    letter-spacing: -.7px;
}

.about-cta p {
    max-width: 590px;
    margin: 0;
    color: rgba(255,255,255,.78);
    font-size: 13px;
    line-height: 1.9;
}

.about-cta .about-btn-primary {
    flex-shrink: 0;
    border-color: #fff;
    background: #fff;
    color: #0b5275 !important;
    box-shadow: none;
}

.about-cta .about-btn-primary:hover {
    background: #e9f8fc;
}

@media (max-width: 991px) {
    .about-hero {
        padding: 55px 0;
    }

    .about-visual {
        min-height: 320px;
    }

    .about-section,
    .about-values-section {
        padding: 55px 0;
    }

    .about-cta {
        margin: 48px 0;
        padding: 32px 27px;
    }
}

@media (max-width: 767px) {
    .about-hero {
        padding: 42px 0 34px;
    }

    .about-hero .section-title {
        font-size: 36px;
        letter-spacing: -1px;
    }

    .about-hero-description {
        font-size: 14px;
    }

    .about-visual {
        min-height: 285px;
    }

    .about-visual-main {
        min-height: 275px;
        border-radius: 23px;
    }

    .about-medical-symbol {
        width: 112px;
        height: 112px;
        border-radius: 30px;
        font-size: 56px;
    }

    .about-section,
    .about-values-section {
        padding: 45px 0;
    }

    .about-section-heading {
        margin-bottom: 27px;
    }

    .about-info-card {
        padding: 23px;
        border-radius: 17px;
    }

    .about-cta {
        margin: 35px 0;
        padding: 28px 22px;
        border-radius: 20px;
    }

    .about-cta .d-flex {
        align-items: flex-start !important;
        flex-direction: column;
    }
}

@media (prefers-reduced-motion: reduce) {
    .about-info-card,
    .about-value-card,
    .about-btn-primary,
    .about-btn-secondary {
        transition: none;
    }
}

```css
/* =====================================================
   ABOUT US — PREMIUM BUTTONS AND CARD REDESIGN
===================================================== */

/* Better spacing between the two buttons */
.about-hero-actions {
    gap: 12px;
    margin-top: 30px;
}

.about-btn-primary,
.about-btn-doctors {
    display: inline-flex;
    min-height: 49px;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 19px;
    border-radius: 13px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    transition: all .22s ease;
}

.about-btn-primary {
    border: 1px solid transparent;
    background: linear-gradient(115deg, #1597d4, #087caf);
    color: #fff !important;
    box-shadow: 0 8px 20px rgba(21,151,212,.22);
}

.about-btn-primary:hover {
    transform: translateY(-3px);
    background: linear-gradient(115deg, #087caf, #075e89);
    box-shadow: 0 13px 25px rgba(21,151,212,.28);
}

.about-btn-doctors {
    border: 1px solid #b8e3f0;
    background: linear-gradient(135deg, #f0fbfe, #e0f5fb);
    color: #0d648a !important;
    box-shadow: 0 6px 16px rgba(8,85,120,.06);
}

.about-btn-doctors:hover {
    transform: translateY(-3px);
    border-color: #76cde3;
    background: linear-gradient(135deg, #e0f7fc, #cceff8);
    box-shadow: 0 11px 23px rgba(8,85,120,.11);
}

.about-btn-arrow {
    margin-left: 3px;
    font-size: 11px;
    transition: transform .2s ease;
}

.about-btn-primary:hover .about-btn-arrow,
.about-btn-doctors:hover .about-btn-arrow {
    transform: translateX(3px);
}

/* Mission and vision — soft tinted surfaces, not plain white */
.about-info-card {
    position: relative;
    overflow: hidden;
    padding: 29px;
    border: 1px solid #d8eaf2;
    border-radius: 21px;
    background: linear-gradient(145deg, #f0faff 0%, #e5f5fb 100%);
    box-shadow: 0 10px 28px rgba(8,36,58,.045);
}

.about-info-card::after {
    content: "";
    position: absolute;
    top: -48px;
    right: -48px;
    width: 130px;
    height: 130px;
    border: 22px solid rgba(21,151,212,.055);
    border-radius: 50%;
    pointer-events: none;
}

.about-info-card:hover {
    transform: translateY(-5px);
    border-color: #b3dce9;
    background: linear-gradient(145deg, #e9f8fd, #dff2f9);
    box-shadow: 0 16px 35px rgba(8,36,58,.08);
}

.about-card-icon {
    position: relative;
    z-index: 1;
    width: 54px;
    height: 54px;
    border: 1px solid #c6e8f3;
    border-radius: 16px;
    background: linear-gradient(135deg, #d5f1fa, #c5eaf6);
    color: #087eae;
    box-shadow: 0 5px 13px rgba(21,151,212,.08);
}

/* Core values — subtle blue/cyan gradient cards */
.about-values-section {
    background:
        radial-gradient(ellipse at 0% 0%, rgba(69,213,223,.10), transparent 35%),
        linear-gradient(135deg, #edf7fb, #e5f4fa);
}

.about-value-card {
    position: relative;
    overflow: hidden;
    gap: 13px;
    padding: 20px 17px;
    border: 1px solid #d4e8f1;
    border-radius: 17px;
    background: linear-gradient(145deg, #f8fcfe, #e9f6fb);
    box-shadow: 0 7px 20px rgba(8,36,58,.035);
    transition: transform .22s ease, box-shadow .22s ease,
                border-color .22s ease;
}

.about-value-card:hover {
    transform: translateY(-4px);
    border-color: #9dd9e9;
    background: linear-gradient(145deg, #effaff, #def3fa);
    box-shadow: 0 12px 25px rgba(8,36,58,.075);
}

.about-value-icon {
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    border: 1px solid #d1ebf4;
    border-radius: 13px;
    background: linear-gradient(135deg, #dff5fb, #ccecf7);
    color: #087eae;
}

.about-value-card h3 {
    color: #123b54;
}

.about-value-card p {
    color: #617d8d;
}

/* Make section headings more refined */
.about-section-heading h2 {
    letter-spacing: -.9px;
}

.about-eyebrow {
    padding: 7px 12px;
    border: 1px solid #ccebf4;
    border-radius: 30px;
    background: #e8f7fc;
}

/* Mobile button layout */
@media (max-width: 480px) {
    .about-hero-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .about-btn-primary,
    .about-btn-doctors {
        width: 100%;
        min-height: 47px;
    }

    .about-info-card {
        padding: 23px;
    }

    .about-value-card {
        padding: 17px 14px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .about-btn-primary,
    .about-btn-doctors,
    .about-info-card,
    .about-value-card,
    .about-btn-arrow {
        transition: none;
    }
}

</style>

<div class="about-page">

    <!-- HERO -->
    <section class="about-hero">
        <div class="container">
            <div class="row align-items-center g-4 g-lg-5">

                <div class="col-lg-7">
                    <div class="about-hero-content">

                        <span class="about-badge">
                            <i class="fa-solid fa-heart-pulse"></i>
                            About Modern Clinic
                        </span>

                        <h1 class="section-title">
                            Better healthcare,
                            <span class="about-highlight">made human.</span>
                        </h1>

                        <p class="about-hero-description">
                            Your health deserves attention, understanding and care.
                            Modern Clinic brings professional healthcare and a simpler
                            appointment experience together, keeping patients at the
                            heart of everything we do.
                        </p>

                        
<div class="about-hero-actions">
    <a href="services.php" class="about-btn-primary">
        <i class="fa-solid fa-stethoscope"></i>
        Explore Services
        <i class="fa-solid fa-arrow-right about-btn-arrow"></i>
    </a>

    <a href="doctors.php" class="about-btn-doctors">
        <i class="fa-solid fa-user-doctor"></i>
        Explore Doctors
        <i class="fa-solid fa-arrow-right about-btn-arrow"></i>
    </a>
</div>

                        

                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="about-visual">
                        <div class="about-visual-main">

                            <div class="about-medical-symbol">
                                <i class="fa-solid fa-heart-pulse"></i>
                            </div>

                            <div class="about-visual-label">
                                <strong>Care that puts you first</strong>
                                <span>Professional care. A more personal experience.</span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- MISSION AND VISION -->
    <section class="about-section">
        <div class="container">

            <div class="about-section-heading">
                <span class="about-eyebrow">Who We Are</span>
                <h2>Healthcare with a human touch</h2>
                <p>
                    We believe a better healthcare experience starts with listening,
                    clear communication and respect for every patient's individual needs.
                </p>
            </div>

            <div class="row g-4">

                <div class="col-md-6">
                    <div class="about-info-card">
                        <div class="about-card-icon">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>

                        <h3>Our Mission</h3>

                        <p>
                            To make quality healthcare more approachable and coordinated
                            by connecting patients with medical professionals and making
                            everyday healthcare access simpler.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="about-info-card">
                        <div class="about-card-icon">
                            <i class="fa-solid fa-eye"></i>
                        </div>

                        <h3>Our Vision</h3>

                        <p>
                            To create a trusted healthcare experience where patients feel
                            informed, respected and supported throughout their journey
                            towards better health.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- CORE VALUES -->
    <section class="about-values-section">
        <div class="container">

            <div class="about-section-heading">
                <span class="about-eyebrow">What Guides Us</span>
                <h2>The values behind our care</h2>
                <p>
                    Every patient interaction should reflect the principles
                    that make healthcare more thoughtful and dependable.
                </p>
            </div>

            <div class="row g-3">

                <div class="col-sm-6 col-lg-3">
                    <div class="about-value-card">
                        <div class="about-value-icon">
                            <i class="fa-solid fa-shield-heart"></i>
                        </div>
                        <div>
                            <h3>Trust</h3>
                            <p>Respect, transparency and patient privacy matter.</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="about-value-card">
                        <div class="about-value-icon">
                            <i class="fa-solid fa-hand-holding-heart"></i>
                        </div>
                        <div>
                            <h3>Compassion</h3>
                            <p>Every person deserves kindness and understanding.</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="about-value-card">
                        <div class="about-value-icon">
                            <i class="fa-solid fa-award"></i>
                        </div>
                        <div>
                            <h3>Excellence</h3>
                            <p>We value professionalism and continuous improvement.</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="about-value-card">
                        <div class="about-value-icon">
                            <i class="fa-solid fa-handshake-angle"></i>
                        </div>
                        <div>
                            <h3>Accessibility</h3>
                            <p>We aim to make arranging care easier for patients.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

