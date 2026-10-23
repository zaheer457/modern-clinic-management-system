<?php
$title = 'Create Account';
require __DIR__ . '/includes/header.php';

if (is_post()) {
    verify_csrf();

    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = $_POST['date_of_birth'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $pw = $_POST['password'] ?? '';
    $cp = $_POST['confirm_password'] ?? '';

    if (
        !$name ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($pw) < 8 ||
        $pw !== $cp
    ) {
        flash(
            'danger',
            'Please provide valid details. Password must be at least 8 characters and match confirmation.'
        );
    } elseif (db_one("SELECT id FROM users WHERE email=?", "s", $email)) {
        flash('danger', 'An account with this email already exists.');
    } else {
        $hash = safe_password_hash($pw);

        global $db;
        $db->begin_transaction();

        try {
            $st = $db->prepare(
                "INSERT INTO users
                    (full_name, email, phone, password, role)
                 VALUES (?, ?, ?, ?, 'patient')"
            );

            $st->bind_param('ssss', $name, $email, $phone, $hash);
            $st->execute();

            $uid = $db->insert_id;
            $img = upload_image('profile_image', 'patients');

            $st = $db->prepare(
                "INSERT INTO patients
                    (user_id, date_of_birth, gender, address, profile_image)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $st->bind_param('issss', $uid, $dob, $gender, $address, $img);
            $st->execute();

            $db->commit();

            flash('success', 'Registration successful. Please login.');
            redirect('login.php');

        } catch (Throwable $e) {
            $db->rollback();
            flash('danger', 'Registration could not be completed.');
        }
    }
}
?>

<style>
/* ==========================================
   MODERN CLINIC — PREMIUM DARK REGISTER
   Background, container and visibility fixes
========================================== */

html:has(section.clinic-register-page),
body:has(section.clinic-register-page) {
    min-height: 100%;
    background: #07111f !important;
}

body:has(section.clinic-register-page) {
    margin: 0 !important;
    color: #edf6ff !important;
}

body:has(section.clinic-register-page) main.site-main.container.py-4,
body:has(section.clinic-register-page) .site-main {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    background: #07111f !important;
}

body:has(section.clinic-register-page) .footer,
body:has(section.clinic-register-page) .footer-bottom {
    background: #07111f !important;
    color: #a9bdcd !important;
}

.clinic-register-page,
.clinic-register-page * {
    box-sizing: border-box;
}

.clinic-register-page {
    --reg-text: #f3f8ff;
    --reg-muted: #a4b7c9;
    --reg-cyan: #55dce5;
    --reg-border: rgba(133, 194, 224, .20);
    --reg-input: #091b2b;

    position: relative;
    isolation: isolate;
    display: flex !important;
    align-items: center;
    justify-content: center;
    width: 100% !important;
    min-height: 100vh !important;
    margin: 0 !important;
    padding: 45px 24px !important;
    overflow: hidden;

    background:
        radial-gradient(
            ellipse at 12% 15%,
            rgba(21, 151, 212, .15),
            transparent 35%
        ),
        radial-gradient(
            ellipse at 88% 85%,
            rgba(69, 213, 223, .10),
            transparent 32%
        ),
        #07111f !important;
}

.clinic-register-page::before,
.clinic-register-page::after {
    content: "";
    position: absolute;
    z-index: -1;
    border-radius: 50%;
    pointer-events: none;
}

.clinic-register-page::before {
    width: 340px;
    height: 340px;
    top: -190px;
    right: -100px;
    border: 1px solid rgba(85, 220, 229, .18);
    box-shadow:
        0 0 0 38px rgba(85, 220, 229, .025),
        0 0 0 78px rgba(85, 220, 229, .018);
}

.clinic-register-page::after {
    width: 260px;
    height: 260px;
    bottom: -145px;
    left: -110px;
    background: rgba(69, 213, 223, .035);
}

.clinic-register-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
}

.clinic-register-layout {
    display: grid;
    grid-template-columns: .82fr 1.18fr;
    align-items: stretch;
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--reg-border);
    border-radius: 26px;
    background: #0b1927 !important;
    box-shadow: 0 28px 90px rgba(0, 0, 0, .42);
}

/* LEFT BRAND PANEL */

.clinic-register-visual {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
    padding: 38px 32px 27px;
    overflow: hidden;
    color: #fff !important;
    background:
        radial-gradient(
            circle at 85% 25%,
            rgba(69, 213, 223, .20),
            transparent 32%
        ),
        linear-gradient(
            145deg,
            #102b45 0%,
            #0a2339 60%,
            #08394c 100%
        ) !important;
}

.clinic-register-visual::before {
    content: "";
    position: absolute;
    width: 300px;
    height: 300px;
    right: -160px;
    top: 190px;
    border: 1px solid rgba(255, 255, 255, .12);
    border-radius: 50%;
    box-shadow:
        0 0 0 35px rgba(255, 255, 255, .025),
        0 0 0 70px rgba(255, 255, 255, .018);
    pointer-events: none;
}

.clinic-register-brand,
.clinic-register-message,
.clinic-register-footer {
    position: relative;
    z-index: 1;
}

.clinic-register-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.clinic-register-brand-icon {
    display: grid;
    place-items: center;
    flex: 0 0 46px;
    width: 46px;
    height: 46px;
    border-radius: 14px;
    color: #062030 !important;
    background: linear-gradient(140deg, #70e9e6, #45bfe1) !important;
    box-shadow: 0 8px 25px rgba(69, 213, 223, .20);
    font-size: 22px;
}

.clinic-register-brand-name {
    margin: 0;
    color: #fff !important;
    font-size: 17px;
    font-weight: 800;
    letter-spacing: -.4px;
}

.clinic-register-brand-caption {
    margin: 3px 0 0;
    color: #a8c1d3 !important;
    font-size: 10px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.clinic-register-message {
    margin: 40px 0;
}

.clinic-register-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 12px;
    margin-bottom: 23px;
    border: 1px solid rgba(69, 213, 223, .28);
    border-radius: 50px;
    color: #8ff0ef !important;
    background: rgba(69, 213, 223, .09) !important;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.clinic-register-message h1 {
    margin: 0 0 18px;
    color: #f5f8ff !important;
    font-size: clamp(30px, 3.4vw, 42px);
    line-height: 1.2;
    letter-spacing: -1.5px;
    font-weight: 800;
}

.clinic-register-message h1 span {
    color: #65e1df !important;
}

.clinic-register-message > p {
    color: #b0c2d2 !important;
    font-size: 13px;
    line-height: 1.9;
}

.clinic-register-features {
    display: grid;
    gap: 18px;
    margin-top: 30px;
}

.clinic-register-feature {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.clinic-register-feature-icon {
    display: grid;
    place-items: center;
    flex: 0 0 36px;
    width: 36px;
    height: 36px;
    border-radius: 11px;
    color: #75e5e5 !important;
    background: rgba(255, 255, 255, .09) !important;
}

.clinic-register-feature strong {
    display: block;
    margin: 0 0 4px;
    color: #fff !important;
    font-size: 12px;
}

.clinic-register-feature small {
    display: block;
    color: #b0c2d2 !important;
    font-size: 11px;
    line-height: 1.6;
}

.clinic-register-footer {
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, .13);
    color: #b5c9d8 !important;
    font-size: 11px;
}

.clinic-register-footer i {
    margin-right: 6px;
    color: #68e0c0 !important;
}

/* RIGHT FORM PANEL — FIX WHITE CONTAINER */

body:has(section.clinic-register-page) .clinic-register-form-panel {
    display: flex !important;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
    padding: 38px 43px;
    border: 0 !important;
    color: #f3f8ff !important;
    background:
        radial-gradient(
            ellipse at 100% 0%,
            rgba(41, 150, 195, .12),
            transparent 43%
        ),
        linear-gradient(145deg, #101d30, #0a1726 75%) !important;
}

.clinic-register-heading {
    margin-bottom: 25px;
}

.clinic-register-heading .eyebrow {
    display: block;
    margin-bottom: 10px;
    color: #56dce4 !important;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.8px;
    text-transform: uppercase;
}

.clinic-register-heading h2 {
    margin: 0 0 8px;
    color: #f4f8ff !important;
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -1px;
}

.clinic-register-heading p {
    margin: 0;
    color: #a6b8ca !important;
    font-size: 12px;
    line-height: 1.7;
}

/* FORM SPACING */

.clinic-register-page .clinic-register-field {
    min-width: 0;
    margin-bottom: 17px;
}

.clinic-register-page .clinic-register-field label {
    display: block;
    margin-bottom: 8px;
    color: #d9e8f5 !important;
    font-size: 11px;
    font-weight: 700;
}

.clinic-register-page .clinic-register-field label span {
    color: #a4b7c9 !important;
}

/* INPUT WRAPPERS — CLEAN FIELDS WITHOUT PERMANENT FIELD ICONS */

.clinic-register-input-wrap {
    position: relative !important;
    display: block !important;
    width: 100%;
    min-width: 0;
}

/* The field icons (user, email, phone, calendar, lock and shield) were
   permanently occupying input space. Hide only these decorative icons;
   keep the password visibility eye button working. */
.clinic-register-page .clinic-register-input-wrap > .field-icon {
    display: none !important;
}

/* PERMANENTLY VISIBLE INPUT TEXT */

.clinic-register-page .clinic-register-input-wrap input.clinic-register-input,
.clinic-register-page input.clinic-register-input,
.clinic-register-page select.clinic-register-select,
.clinic-register-page .clinic-register-select {
    display: block !important;
    width: 100% !important;
    min-width: 0;
    height: 46px !important;
    min-height: 46px !important;
    margin: 0 !important;
    padding: 11px 13px !important;
    border: 1px solid #2c455b !important;
    border-radius: 11px !important;
    outline: none !important;
    color: #f3f8ff !important;
    -webkit-text-fill-color: #f3f8ff !important;
    background-color: #091b2b !important;
    font-family: inherit !important;
    font-size: 12px !important;
    line-height: 1.5 !important;
    box-shadow: none !important;
    opacity: 1 !important;
    appearance: auto;
    -webkit-appearance: auto;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.clinic-register-page .clinic-register-input-wrap input.clinic-register-input::placeholder,
.clinic-register-page input.clinic-register-input::placeholder {
    color: #8299ad !important;
    -webkit-text-fill-color: #8299ad !important;
    opacity: 1 !important;
}

.clinic-register-page .clinic-register-input-wrap input.clinic-register-input:hover,
.clinic-register-page select.clinic-register-select:hover {
    border-color: #41647c !important;
}

.clinic-register-page .clinic-register-input-wrap input.clinic-register-input:focus,
.clinic-register-page select.clinic-register-select:focus {
    border-color: #48d4e0 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
    background-color: #0b2031 !important;
    box-shadow: 0 0 0 4px rgba(69, 213, 223, .10) !important;
}

/* DATE INPUT */

.clinic-register-page input[type="date"].clinic-register-input {
    color-scheme: dark;
}

.clinic-register-page input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(.85);
    opacity: 1;
    cursor: pointer;
}

/* SELECT OPTIONS */

.clinic-register-page select.clinic-register-select {
    padding-left: 12px !important;
    color-scheme: dark;
    cursor: pointer;
}

.clinic-register-page select.clinic-register-select option {
    color: #f3f8ff !important;
    background: #091b2b !important;
}

/* AUTOFILL FIX */

.clinic-register-page input.clinic-register-input:-webkit-autofill,
.clinic-register-page input.clinic-register-input:-webkit-autofill:hover,
.clinic-register-page input.clinic-register-input:-webkit-autofill:focus,
.clinic-register-page input.clinic-register-input:-webkit-autofill:active {
    -webkit-text-fill-color: #f3f8ff !important;
    caret-color: #fff !important;
    -webkit-box-shadow: 0 0 0 1000px #091b2b inset !important;
    box-shadow: 0 0 0 1000px #091b2b inset !important;
    transition: background-color 9999s ease-out;
}

/* PASSWORD EYE */

.clinic-register-page .clinic-register-password-input {
    padding-right: 45px !important;
}

.clinic-register-page .clinic-register-password-toggle {
    position: absolute !important;
    top: 50% !important;
    right: 8px !important;
    left: auto !important;
    z-index: 4;
    display: grid !important;
    place-items: center;
    width: 30px !important;
    height: 30px !important;
    margin: 0 !important;
    padding: 0 !important;
    transform: translateY(-50%) !important;
    border: 1px solid transparent !important;
    border-radius: 8px !important;
    color: #a8c2d2 !important;
    background: transparent !important;
    font-size: 14px;
    line-height: 1;
    cursor: pointer;
}

.clinic-register-page .clinic-register-password-toggle:hover {
    color: #63e2e4 !important;
    background: #163146 !important;
}

.clinic-register-page .clinic-register-password-toggle:focus-visible {
    outline: 2px solid #45d5df !important;
    outline-offset: 2px;
}

/* UPLOAD FIELD */

.clinic-register-page .clinic-register-upload {
    position: relative;
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
    min-height: 64px;
    padding: 10px 13px;
    border: 1px dashed #426178 !important;
    border-radius: 12px;
    background: #091b2b !important;
    transition: border-color .2s ease, background .2s ease;
}

.clinic-register-page .clinic-register-upload:hover {
    border-color: #48d4e0 !important;
    background: #0b2031 !important;
}

.clinic-register-upload-icon {
    display: grid;
    place-items: center;
    flex: 0 0 39px;
    width: 39px;
    height: 39px;
    border-radius: 11px;
    color: #63dce3 !important;
    background: #15354a !important;
    font-size: 16px;
}

.clinic-register-upload-copy {
    flex: 1;
    min-width: 0;
}

.clinic-register-upload-copy strong {
    display: block;
    color: #eaf5ff !important;
    font-size: 11px;
}

.clinic-register-upload-copy small {
    display: block;
    margin-top: 4px;
    color: #a4b7c9 !important;
    font-size: 10px;
}

.clinic-register-page .clinic-register-upload input[type="file"] {
    width: 100%;
    max-width: 175px;
    min-width: 0;
    color: #d2e2ef !important;
    background: transparent !important;
    font-family: inherit;
    font-size: 10px;
}

.clinic-register-page .clinic-register-upload input[type="file"]::file-selector-button {
    padding: 6px 8px;
    margin-right: 7px;
    border: 1px solid #34556b;
    border-radius: 7px;
    color: #e5f4ff;
    background: #163146;
    cursor: pointer;
}

/* PASSWORD HINT */

.clinic-register-password-hint {
    display: block;
    margin-top: 6px;
    color: #a4b7c9 !important;
    font-size: 10px;
}

/* SUBMIT BUTTON */

.clinic-register-page .clinic-register-submit {
    display: flex !important;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100% !important;
    min-height: 51px;
    margin-top: 5px;
    padding: 13px 18px;
    border: 1px solid rgba(112, 237, 237, .25) !important;
    border-radius: 12px !important;
    color: #061b2a !important;
    background: linear-gradient(
        110deg,
        #49cfe6,
        #48b6f2 52%,
        #8181ff
    ) !important;
    box-shadow: 0 9px 22px rgba(21, 151, 212, .22);
    font-family: inherit;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    transition: transform .2s ease, box-shadow .2s ease;
}

.clinic-register-page .clinic-register-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 13px 28px rgba(21, 151, 212, .29);
}

.clinic-register-page .clinic-register-submit:active {
    transform: translateY(0);
}

.clinic-register-page .clinic-register-submit:focus-visible {
    outline: 3px solid rgba(69, 213, 223, .45);
    outline-offset: 3px;
}

/* LOGIN LINK AND SECURITY */

.clinic-register-login {
    margin: 22px 0 0;
    color: #a5b8c9 !important;
    font-size: 12px;
    text-align: center;
}

.clinic-register-page .clinic-register-login a {
    color: #63dce3 !important;
    font-weight: 800;
    text-decoration: none;
}

.clinic-register-page .clinic-register-login a:hover {
    color: #a0f4ef !important;
    text-decoration: underline;
}

.clinic-register-security {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 7px;
    margin-top: 23px;
    color: #a4b7c9 !important;
    font-size: 10px;
}

.clinic-register-security i {
    color: #56d9a5 !important;
}

/* RESPONSIVE */

@media (max-width: 900px) {
    .clinic-register-layout {
        grid-template-columns: .75fr 1.25fr;
    }

    .clinic-register-visual {
        padding: 32px 24px 24px;
    }

    body:has(section.clinic-register-page) .clinic-register-form-panel {
        padding: 32px 27px;
    }
}

@media (max-width: 680px) {
    .clinic-register-page {
        min-height: auto !important;
        padding: 24px 14px 35px !important;
    }

    .clinic-register-layout {
        grid-template-columns: minmax(0, 1fr);
        border-radius: 20px;
    }

    .clinic-register-visual {
        padding: 25px 24px 22px;
    }

    .clinic-register-message {
        margin: 30px 0 26px;
    }

    .clinic-register-message h1 {
        font-size: 34px;
    }

    .clinic-register-message > p {
        font-size: 12px;
    }

    .clinic-register-features {
        gap: 13px;
        margin-top: 22px;
    }

    .clinic-register-footer {
        margin-top: 26px;
    }

    body:has(section.clinic-register-page) .clinic-register-form-panel {
        padding: 30px 23px;
    }

    .clinic-register-heading h2 {
        font-size: 27px;
    }
}

@media (max-width: 390px) {
    .clinic-register-page {
        padding-right: 9px !important;
        padding-left: 9px !important;
    }

    .clinic-register-visual,
    body:has(section.clinic-register-page) .clinic-register-form-panel {
        padding-right: 18px;
        padding-left: 18px;
    }

    .clinic-register-message h1 {
        font-size: 30px;
    }

    .clinic-register-page .clinic-register-upload {
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .clinic-register-page .clinic-register-upload input[type="file"] {
        max-width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .clinic-register-page *,
    .clinic-register-page *::before,
    .clinic-register-page *::after {
        transition-duration: .01ms !important;
    }
}
</style>

<section class="clinic-register-page">
    <div class="clinic-register-container">
        <div class="clinic-register-layout">

            <!-- LEFT BRANDING -->
            <section class="clinic-register-visual">
                <div class="clinic-register-brand">
                    <div class="clinic-register-brand-icon">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </div>

                    <div>
                        <p class="clinic-register-brand-name">Modern Clinic</p>
                        <p class="clinic-register-brand-caption">
                            Healthcare Management
                        </p>
                    </div>
                </div>

                <div class="clinic-register-message">
                    <div class="clinic-register-eyebrow">
                        <i class="fa-solid fa-heart-pulse"
                           aria-hidden="true"></i>
                        Your health journey starts here
                    </div>

                    <h1>
                        Your care.<br>
                        <span>Your comfort.</span>
                    </h1>

                    <p>
                        Create your patient account and make managing your
                        healthcare simpler. Keep your details organized
                        and access your clinic portal whenever you need it.
                    </p>

                    <div class="clinic-register-features">
                        <div class="clinic-register-feature">
                            <span class="clinic-register-feature-icon">
                                <i class="fa-regular fa-calendar-check"
                                   aria-hidden="true"></i>
                            </span>
                            <div>
                                <strong>Convenient appointments</strong>
                                <small>Keep your clinic visits organized.</small>
                            </div>
                        </div>

                        <div class="clinic-register-feature">
                            <span class="clinic-register-feature-icon">
                                <i class="fa-solid fa-user-shield"
                                   aria-hidden="true"></i>
                            </span>
                            <div>
                                <strong>Your personal account</strong>
                                <small>Manage your patient information.</small>
                            </div>
                        </div>

                        <div class="clinic-register-feature">
                            <span class="clinic-register-feature-icon">
                                <i class="fa-solid fa-heart"
                                   aria-hidden="true"></i>
                            </span>
                            <div>
                                <strong>Care made more convenient</strong>
                                <small>Your healthcare portal in one place.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="clinic-register-footer">
                    <i class="fa-solid fa-circle-check"
                       aria-hidden="true"></i>
                    A better way to manage your healthcare
                </div>
            </section>

            <!-- RIGHT REGISTRATION FORM -->
            <section class="clinic-register-form-panel">
                <div class="clinic-register-heading">
                    <span class="eyebrow">PATIENT REGISTRATION</span>
                    <h2>Create account</h2>
                    <p>
                        Fill in your details to get started with Modern Clinic.
                    </p>
                </div>

                <form method="post" action="register.php"
                      enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="row g-3">

                        <div class="col-12 clinic-register-field">
                            <label for="reg-name">Full name *</label>
                            <div class="clinic-register-input-wrap">
                                <i class="fa-regular fa-user field-icon"
                                   aria-hidden="true"></i>
                                <input
                                    type="text"
                                    id="reg-name"
                                    name="full_name"
                                    class="clinic-register-input"
                                    placeholder="Enter your full name"
                                    autocomplete="name"
                                    maxlength="150"
                                    value="<?= e($_POST['full_name'] ?? '') ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-md-6 clinic-register-field">
                            <label for="reg-email">Email address *</label>
                            <div class="clinic-register-input-wrap">
                                <i class="fa-regular fa-envelope field-icon"
                                   aria-hidden="true"></i>
                                <input
                                    type="email"
                                    id="reg-email"
                                    name="email"
                                    class="clinic-register-input"
                                    placeholder="you@example.com"
                                    autocomplete="email"
                                    maxlength="254"
                                    value="<?= e($_POST['email'] ?? '') ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-md-6 clinic-register-field">
                            <label for="reg-phone">Phone number</label>
                            <div class="clinic-register-input-wrap">
                                <i class="fa-solid fa-phone field-icon"
                                   aria-hidden="true"></i>
                                <input
                                    type="tel"
                                    id="reg-phone"
                                    name="phone"
                                    class="clinic-register-input"
                                    placeholder="Enter phone number"
                                    autocomplete="tel"
                                    value="<?= e($_POST['phone'] ?? '') ?>"
                                >
                            </div>
                        </div>

                        <div class="col-md-6 clinic-register-field">
                            <label for="reg-dob">Date of birth</label>
                            <div class="clinic-register-input-wrap">
                                <i class="fa-regular fa-calendar field-icon"
                                   aria-hidden="true"></i>
                                <input
                                    type="date"
                                    id="reg-dob"
                                    name="date_of_birth"
                                    class="clinic-register-input"
                                    value="<?= e($_POST['date_of_birth'] ?? '') ?>"
                                >
                            </div>
                        </div>

                        <div class="col-md-6 clinic-register-field">
                            <label for="reg-gender">Gender</label>
                            <select
                                id="reg-gender"
                                name="gender"
                                class="clinic-register-select"
                            >
                                <option value="">Select gender</option>
                                <option value="Male"
                                    <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>
                                    Male
                                </option>
                                <option value="Female"
                                    <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>
                                    Female
                                </option>
                                <option value="Other"
                                    <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>
                                    Other
                                </option>
                            </select>
                        </div>

                        <div class="col-12 clinic-register-field">
                            <label for="reg-address">Address</label>
                            <div class="clinic-register-input-wrap">
                                <i class="fa-solid fa-location-dot field-icon"
                                   aria-hidden="true"></i>
                                <input
                                    type="text"
                                    id="reg-address"
                                    name="address"
                                    class="clinic-register-input"
                                    placeholder="Your residential address"
                                    autocomplete="street-address"
                                    value="<?= e($_POST['address'] ?? '') ?>"
                                >
                            </div>
                        </div>

                        <div class="col-12 clinic-register-field">
                            <label for="reg-profile-image">
                                Profile picture
                                <span style="font-weight:400;">
                                    (optional)
                                </span>
                            </label>

                            <div class="clinic-register-upload">
                                <div class="clinic-register-upload-icon">
                                    <i class="fa-solid fa-cloud-arrow-up"
                                       aria-hidden="true"></i>
                                </div>

                                <div class="clinic-register-upload-copy">
                                    <strong>Choose a profile picture</strong>
                                    <small>JPG, PNG or WebP, if supported</small>
                                </div>

                                <input
                                    type="file"
                                    id="reg-profile-image"
                                    name="profile_image"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                >
                            </div>
                        </div>

                        <div class="col-md-6 clinic-register-field">
                            <label for="reg-password">Password *</label>
                            <div class="clinic-register-input-wrap">
                                <i class="fa-solid fa-lock field-icon"
                                   aria-hidden="true"></i>
                                <input
                                    type="password"
                                    id="reg-password"
                                    name="password"
                                    class="clinic-register-input clinic-register-password-input"
                                    placeholder="Minimum 8 characters"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >
                                <button
                                    type="button"
                                    class="clinic-register-password-toggle"
                                    data-password-target="reg-password"
                                    aria-label="Show password"
                                    aria-pressed="false"
                                >
                                    <i class="fa-regular fa-eye"
                                       aria-hidden="true"></i>
                                </button>
                            </div>
                            <small class="clinic-register-password-hint">
                                At least 8 characters.
                            </small>
                        </div>

                        <div class="col-md-6 clinic-register-field">
                            <label for="reg-confirm-password">
                                Confirm password *
                            </label>
                            <div class="clinic-register-input-wrap">
                                <i class="fa-solid fa-shield-halved field-icon"
                                   aria-hidden="true"></i>
                                <input
                                    type="password"
                                    id="reg-confirm-password"
                                    name="confirm_password"
                                    class="clinic-register-input clinic-register-password-input"
                                    placeholder="Re-enter password"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >
                                <button
                                    type="button"
                                    class="clinic-register-password-toggle"
                                    data-password-target="reg-confirm-password"
                                    aria-label="Show password"
                                    aria-pressed="false"
                                >
                                    <i class="fa-regular fa-eye"
                                       aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit"
                                    class="clinic-register-submit">
                                <span>Create Patient Account</span>
                                <i class="fa-solid fa-arrow-right"
                                   aria-hidden="true"></i>
                            </button>
                        </div>

                    </div>
                </form>

                <p class="clinic-register-login">
                    Already have an account?
                    <a href="login.php">Sign in</a>
                </p>

                <div class="clinic-register-security">
                    <i class="fa-solid fa-shield-halved"
                       aria-hidden="true"></i>
                    <span>Your account details are handled securely</span>
                </div>
            </section>

        </div>
    </div>
</section>

<script>
(function () {
    const toggles = document.querySelectorAll(
        '.clinic-register-password-toggle'
    );

    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const targetId = toggle.getAttribute('data-password-target');
            const input = document.getElementById(targetId);

            if (!input) return;

            const showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';

            toggle.innerHTML = showPassword
                ? '<i class="fa-regular fa-eye-slash" aria-hidden="true"></i>'
                : '<i class="fa-regular fa-eye" aria-hidden="true"></i>';

            toggle.setAttribute(
                'aria-label',
                showPassword ? 'Hide password' : 'Show password'
            );

            toggle.setAttribute(
                'aria-pressed',
                showPassword ? 'true' : 'false'
            );
        });
    });

    const password = document.getElementById('reg-password');
    const confirmPassword = document.getElementById('reg-confirm-password');
    const form = confirmPassword ? confirmPassword.closest('form') : null;

    if (form && password && confirmPassword) {
        form.addEventListener('submit', function (event) {
            if (password.value !== confirmPassword.value) {
                event.preventDefault();
                confirmPassword.setCustomValidity(
                    'Passwords do not match.'
                );
                confirmPassword.reportValidity();
            } else {
                confirmPassword.setCustomValidity('');
            }
        });

        confirmPassword.addEventListener('input', function () {
            confirmPassword.setCustomValidity('');
        });

        password.addEventListener('input', function () {
            confirmPassword.setCustomValidity('');
        });
    }
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
