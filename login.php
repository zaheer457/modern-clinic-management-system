<?php
$title = 'Login';
require __DIR__ . '/includes/header.php';

if (is_post()) {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? '';

    $u = db_one(
        "SELECT * FROM users WHERE email=? AND status=1",
        "s",
        $email
    );

    if ($u && password_ok($pw, $u['password'])) {
        session_regenerate_id(true);

        unset($u['password']);
        $_SESSION['user'] = $u;

        if ($u['role'] === 'admin') {
            redirect('admin/');
        }

        if ($u['role'] === 'patient') {
            redirect('index.php');
        }

        flash(
            'danger',
            'Doctor accounts use the doctor portal in the full deployment.'
        );

        redirect('index.php');
    }

    flash('danger', 'Invalid email or password.');
}
?>

<style>
/* ==========================================
   MODERN CLINIC — DARK PREMIUM LOGIN
========================================== */

html:has(.clinic-login-page),
body:has(.clinic-login-page) {
    min-height: 100%;
    background: #07121e !important;
}

body:has(.clinic-login-page) {
    margin: 0 !important;
    color: #edf6ff;
}

body:has(.clinic-login-page) .site-main {
    display: block !important;
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    background: #07121e !important;
}

body:has(.clinic-login-page) .footer,
body:has(.clinic-login-page) .footer-bottom {
    background: #07121e !important;
    color: #a9bdcd !important;
}

.clinic-login-page,
.clinic-login-page * {
    box-sizing: border-box;
}

.clinic-login-page {
    --cl-text: #f3f8ff;
    --cl-muted: #a4b7c9;
    --cl-cyan: #55dce5;
    --cl-border: rgba(133, 194, 224, .20);
    --cl-input: #091b2a;

    position: relative;
    isolation: isolate;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: calc(100vh - 76px);
    padding: 42px 24px;
    overflow: hidden;
    background:
        radial-gradient(ellipse at 8% 15%,
            rgba(29, 112, 190, .22), transparent 38%),
        radial-gradient(ellipse at 95% 85%,
            rgba(47, 201, 210, .12), transparent 35%),
        #07121e !important;
}

.clinic-login-page::before {
    content: "";
    position: absolute;
    z-index: -1;
    top: -190px;
    right: -100px;
    width: 340px;
    height: 340px;
    border: 1px solid rgba(85, 220, 229, .18);
    border-radius: 50%;
    box-shadow:
        0 0 0 38px rgba(85, 220, 229, .025),
        0 0 0 78px rgba(85, 220, 229, .018);
    pointer-events: none;
}

.clinic-login-container {
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
}

.clinic-login-layout {
    display: grid;
    grid-template-columns: 1fr .92fr;
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--cl-border);
    border-radius: 26px;
    background: #0a1827 !important;
    box-shadow: 0 28px 90px rgba(0, 0, 0, .42);
}

/* LEFT BRANDING */

.clinic-login-visual {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
    padding: 42px 40px 28px;
    overflow: hidden;
    color: #fff !important;
    background:
        radial-gradient(circle at 80% 18%,
            rgba(66, 212, 226, .20), transparent 35%),
        linear-gradient(145deg, #102b45, #0a2339 60%, #08394c) !important;
}

.clinic-login-visual::before {
    content: "";
    position: absolute;
    top: 160px;
    right: -155px;
    width: 300px;
    height: 300px;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 50%;
    box-shadow:
        0 0 0 32px rgba(255,255,255,.025),
        0 0 0 68px rgba(255,255,255,.018);
    pointer-events: none;
}

.clinic-login-visual::after {
    content: "+";
    position: absolute;
    top: 32%;
    right: 25px;
    color: rgba(100, 231, 233, .12);
    font-size: 170px;
    font-weight: 200;
    line-height: 1;
    pointer-events: none;
}

.clinic-login-brand,
.clinic-login-message,
.clinic-login-bottom {
    position: relative;
    z-index: 1;
}

.clinic-login-brand {
    display: flex;
    align-items: center;
    gap: 14px;
}

.clinic-login-brand-icon {
    display: grid;
    place-items: center;
    flex: 0 0 54px;
    width: 54px;
    height: 54px;
    border-radius: 16px;
    color: #062030 !important;
    background: linear-gradient(140deg, #70e9e6, #45bfe1);
    box-shadow: 0 8px 25px rgba(69, 213, 223, .2);
    font-size: 24px;
}

.clinic-login-brand-name {
    margin: 0;
    color: #fff !important;
    font-size: 21px;
    font-weight: 800;
    letter-spacing: -.6px;
}

.clinic-login-brand-caption {
    margin: 5px 0 0;
    color: #a8c1d3 !important;
    font-size: 10px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}

.clinic-login-message {
    position: relative;
    max-width: 400px;
    margin: 45px 0;
}

.clinic-login-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 23px;
    padding: 10px 14px;
    border: 1px solid rgba(105,236,229,.26);
    border-radius: 40px;
    color: #8cece8 !important;
    background: rgba(69,213,223,.08);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.clinic-login-message h1 {
    margin: 0 0 19px;
    color: #f5f8ff !important;
    font-size: clamp(34px, 3.5vw, 48px);
    font-weight: 800;
    line-height: 1.18;
    letter-spacing: -1.8px;
}

.clinic-login-message h1 span {
    color: #65e1df !important;
}

.clinic-login-message > p {
    max-width: 350px;
    color: #b0c2d2 !important;
    font-size: 14px;
    line-height: 1.9;
}

.clinic-login-benefits {
    display: grid;
    gap: 14px;
    margin-top: 29px;
}

.clinic-login-benefit {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #e5f0fa !important;
    font-size: 12px;
}

.clinic-login-benefit-icon {
    display: grid;
    place-items: center;
    flex: 0 0 39px;
    width: 39px;
    height: 39px;
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 12px;
    color: #8cece8 !important;
    background: rgba(255,255,255,.055);
}

.clinic-login-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,.13);
    color: #b5c9d8 !important;
    font-size: 11px;
}

.clinic-login-bottom i {
    margin-right: 5px;
    color: #65e6b8;
}

/* RIGHT PANEL — FORCE DARK BACKGROUND */

.clinic-login-page .clinic-login-form-panel {
    display: flex !important;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
    padding: 48px 44px;
    border: 0 !important;
    color: var(--cl-text) !important;
    background:
        radial-gradient(ellipse at 100% 0%,
            rgba(41, 150, 195, .12), transparent 43%),
        linear-gradient(145deg, #101d30, #0a1726 75%) !important;
}

.clinic-login-form-heading {
    margin-bottom: 32px;
}

.clinic-login-form-heading .welcome-label {
    display: block;
    margin-bottom: 13px;
    color: #56dce4 !important;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
}

.clinic-login-form-heading h2 {
    margin: 0 0 12px;
    color: #f4f8ff !important;
    font-size: 35px;
    font-weight: 800;
    letter-spacing: -1px;
}

.clinic-login-form-heading p {
    margin: 0;
    color: #a6b8ca !important;
    font-size: 13px;
    line-height: 1.8;
}

/* FORM FIELDS */

.clinic-login-page .clinic-login-field {
    margin-bottom: 23px;
}

.clinic-login-page .clinic-login-field > label {
    display: block;
    margin: 0 0 10px;
    color: #d9e8f5 !important;
    font-size: 13px;
    font-weight: 700;
}

.clinic-login-input-wrap {
    position: relative !important;
    display: block !important;
    width: 100%;
    min-width: 0;
}

/* No permanent email or lock icons */

.clinic-login-page .clinic-login-input-wrap > .field-icon {
    display: none !important;
}

/* Reserve no space for the removed left-side icons */

.clinic-login-page .clinic-login-input-wrap input.clinic-login-input {
    display: block !important;
    width: 100% !important;
    min-width: 0;
    height: 54px !important;
    min-height: 54px !important;
    margin: 0 !important;
    padding: 14px 16px !important;
    border: 1px solid #2c455b !important;
    border-radius: 12px !important;
    outline: none !important;
    color: #f3f8ff !important;
    -webkit-text-fill-color: #f3f8ff !important;
    background: #091b2b !important;
    font-family: inherit !important;
    font-size: 14px !important;
    line-height: 1.5 !important;
    box-shadow: none !important;
    appearance: none;
    -webkit-appearance: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.clinic-login-page .clinic-login-input-wrap input.clinic-login-input::placeholder {
    color: #8299ad !important;
    -webkit-text-fill-color: #8299ad !important;
    opacity: 1 !important;
}

.clinic-login-page .clinic-login-input-wrap input.clinic-login-input:hover {
    border-color: #41647c !important;
}

.clinic-login-page .clinic-login-input-wrap input.clinic-login-input:focus {
    border-color: #48d4e0 !important;
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
    background: #0b2031 !important;
    box-shadow: 0 0 0 4px rgba(69,213,223,.10) !important;
}

.clinic-login-page .clinic-login-input-wrap input.clinic-login-input:-webkit-autofill,
.clinic-login-page .clinic-login-input-wrap input.clinic-login-input:-webkit-autofill:hover,
.clinic-login-page .clinic-login-input-wrap input.clinic-login-input:-webkit-autofill:focus {
    -webkit-text-fill-color: #f3f8ff !important;
    caret-color: #fff;
    -webkit-box-shadow: 0 0 0 1000px #091b2b inset !important;
}

/* Password eye button only */

.clinic-login-page .clinic-login-password-toggle {
    position: absolute !important;
    top: 50% !important;
    right: 10px !important;
    left: auto !important;
    z-index: 4;
    display: grid !important;
    place-items: center;
    width: 36px !important;
    height: 36px !important;
    margin: 0 !important;
    padding: 0 !important;
    transform: translateY(-50%) !important;
    border: 1px solid transparent !important;
    border-radius: 9px !important;
    color: #a8c2d2 !important;
    background: transparent !important;
    font-size: 15px;
    line-height: 1;
    cursor: pointer;
}

.clinic-login-page .clinic-login-password-toggle:hover {
    color: #63e2e4 !important;
    background: #163146 !important;
}

.clinic-login-page .clinic-login-password-toggle:focus-visible {
    outline: 2px solid #45d5df !important;
    outline-offset: 2px;
}

/* Only password field reserves space for the eye */

.clinic-login-page #login-password {
    padding-right: 54px !important;
}

/* OPTIONS */

.clinic-login-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin: 3px 0 26px;
}

.clinic-login-remember {
    display: flex !important;
    align-items: center;
    gap: 8px;
    margin: 0 !important;
    color: #b3c4d2 !important;
    font-size: 12px !important;
    cursor: pointer;
}

.clinic-login-remember input {
    width: 15px;
    height: 15px;
    margin: 0;
    accent-color: #42cedd;
    cursor: pointer;
}

.clinic-login-page .clinic-login-forgot {
    color: #63dce3 !important;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.clinic-login-page .clinic-login-forgot:hover {
    color: #a0f4ef !important;
    text-decoration: underline;
}

/* SUBMIT BUTTON */

.clinic-login-page .clinic-login-submit {
    display: flex !important;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100% !important;
    min-height: 54px;
    padding: 14px 20px;
    border: 1px solid rgba(112,237,237,.25) !important;
    border-radius: 12px !important;
    color: #061b2a !important;
    background: linear-gradient(110deg, #49cfe6, #48b6f2 52%, #8181ff) !important;
    box-shadow: 0 9px 25px rgba(9,154,195,.15);
    font-family: inherit;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    transition: transform .2s ease, box-shadow .2s ease;
}

.clinic-login-page .clinic-login-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 13px 30px rgba(9,154,195,.25);
}

.clinic-login-submit:focus-visible {
    outline: 3px solid rgba(69,213,223,.45);
    outline-offset: 3px;
}

.clinic-login-register {
    margin: 26px 0 0;
    color: #a5b8c9 !important;
    font-size: 12px;
    text-align: center;
}

.clinic-login-page .clinic-login-register a {
    color: #63dce3 !important;
    font-weight: 800;
    text-decoration: none;
}

.clinic-login-page .clinic-login-register a:hover {
    color: #a0f4ef !important;
    text-decoration: underline;
}

.clinic-login-security {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 27px;
    color: #93aabd !important;
    font-size: 11px;
}

.clinic-login-security i {
    color: #56d9a5;
}

/* RESPONSIVE */

@media (max-width: 900px) {
    .clinic-login-layout {
        grid-template-columns: 1fr .95fr;
    }

    .clinic-login-visual {
        padding: 34px 27px 25px;
    }

    .clinic-login-page .clinic-login-form-panel {
        padding: 38px 28px;
    }

    .clinic-login-message h1 {
        font-size: 36px;
    }
}

@media (max-width: 680px) {
    .clinic-login-page {
        min-height: auto;
        padding: 22px 14px 35px;
    }

    .clinic-login-layout {
        grid-template-columns: minmax(0, 1fr);
        border-radius: 20px;
    }

    .clinic-login-visual {
        padding: 27px 24px 22px;
    }

    .clinic-login-message {
        margin: 30px 0 25px;
    }

    .clinic-login-message h1 {
        font-size: 35px;
    }

    .clinic-login-benefits {
        gap: 11px;
        margin-top: 22px;
    }

    .clinic-login-bottom {
        margin-top: 25px;
    }

    .clinic-login-page .clinic-login-form-panel {
        padding: 34px 24px;
    }

    .clinic-login-form-heading {
        margin-bottom: 26px;
    }

    .clinic-login-form-heading h2 {
        font-size: 31px;
    }
}

@media (max-width: 380px) {
    .clinic-login-visual,
    .clinic-login-page .clinic-login-form-panel {
        padding-right: 18px;
        padding-left: 18px;
    }

    .clinic-login-message h1 {
        font-size: 30px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .clinic-login-page *,
    .clinic-login-page *::before,
    .clinic-login-page *::after {
        transition-duration: .01ms !important;
    }
}

/* LOGIN PAGE — FIX OUTER WHITE BACKGROUND */
body:has(section.clinic-login-page) {
    background: #07111f !important;
}

body:has(section.clinic-login-page) main.site-main.container.py-4 {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    background: #07111f !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

body:has(section.clinic-login-page) section.clinic-login-page {
    display: flex !important;
    width: 100% !important;
    min-height: 100vh !important;
    margin: 0 !important;
    padding: 45px 24px !important;
    background:
        radial-gradient(
            ellipse at 12% 15%,
            rgba(21,151,212,.15),
            transparent 35%
        ),
        radial-gradient(
            ellipse at 88% 85%,
            rgba(69,213,223,.10),
            transparent 32%
        ),
        #07111f !important;
}

body:has(section.clinic-login-page) .clinic-login-layout,
body:has(section.clinic-login-page) .clinic-login-form-panel {
    background-color: #0b1927 !important;
}

@media (max-width: 680px) {
    body:has(section.clinic-login-page) section.clinic-login-page {
        padding: 24px 14px 35px !important;
    }
}

</style>

<section class="clinic-login-page">
    <div class="clinic-login-container">
        <div class="clinic-login-layout">

            <!-- LEFT BRANDING -->
            <section class="clinic-login-visual">
                <div class="clinic-login-brand">
                    <div class="clinic-login-brand-icon">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    </div>

                    <div>
                        <p class="clinic-login-brand-name">Modern Clinic</p>
                        <p class="clinic-login-brand-caption">
                            Healthcare Management
                        </p>
                    </div>
                </div>

                <div class="clinic-login-message">
                    <div class="clinic-login-eyebrow">
                        <i class="fa-solid fa-heart-pulse"
                           aria-hidden="true"></i>
                        Your health, our priority
                    </div>

                    <h1>
                        Better care.<br>
                        <span>Better living.</span>
                    </h1>

                    <p>
                        Your personal healthcare space is just one sign-in
                        away. Manage appointments and stay connected with
                        your clinic in one convenient place.
                    </p>

                    <div class="clinic-login-benefits">
                        <div class="clinic-login-benefit">
                            <span class="clinic-login-benefit-icon">
                                <i class="fa-regular fa-calendar-check"
                                   aria-hidden="true"></i>
                            </span>
                            <span>Manage your appointments easily</span>
                        </div>

                        <div class="clinic-login-benefit">
                            <span class="clinic-login-benefit-icon">
                                <i class="fa-solid fa-user-doctor"
                                   aria-hidden="true"></i>
                            </span>
                            <span>Stay connected with your care team</span>
                        </div>

                        <div class="clinic-login-benefit">
                            <span class="clinic-login-benefit-icon">
                                <i class="fa-solid fa-shield-heart"
                                   aria-hidden="true"></i>
                            </span>
                            <span>A convenient patient portal</span>
                        </div>
                    </div>
                </div>

                <div class="clinic-login-bottom">
                    <span>
                        <i class="fa-solid fa-circle-check"
                           aria-hidden="true"></i>
                        Care that puts you first
                    </span>
                    <span>Modern Clinic</span>
                </div>
            </section>

            <!-- RIGHT LOGIN FORM -->
            <section class="clinic-login-form-panel">
                <div class="clinic-login-form-heading">
                    <span class="welcome-label">WELCOME BACK</span>
                    <h2>Sign in</h2>
                    <p>
                        Enter your account details to access your dashboard.
                    </p>
                </div>

                <form method="post" action="login.php">
                    <?= csrf_field() ?>

                    <div class="clinic-login-field">
                        <label for="login-email">Email address</label>

                        <div class="clinic-login-input-wrap">
                            <input
                                type="email"
                                id="login-email"
                                name="email"
                                class="clinic-login-input"
                                placeholder="you@example.com"
                                autocomplete="username"
                                maxlength="254"
                                value="<?= e($_POST['email'] ?? '') ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="clinic-login-field">
                        <label for="login-password">Password</label>

                        <div class="clinic-login-input-wrap">
                            <input
                                type="password"
                                id="login-password"
                                name="password"
                                class="clinic-login-input"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                class="clinic-login-password-toggle"
                                id="toggle-login-password"
                                aria-label="Show password"
                                aria-pressed="false"
                            >
                                <i class="fa-regular fa-eye"
                                   aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="clinic-login-options">
                        <label class="clinic-login-remember">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                            >
                            <span>Remember me</span>
                        </label>

                        <a href="forgot-password.php"
                           class="clinic-login-forgot">
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="clinic-login-submit">
                        <span>Sign In to Your Account</span>
                        <i class="fa-solid fa-arrow-right"
                           aria-hidden="true"></i>
                    </button>
                </form>

                <p class="clinic-login-register">
                    New to Modern Clinic?
                    <a href="register.php">Create an account</a>
                </p>

                <div class="clinic-login-security">
                    <i class="fa-solid fa-shield-halved"
                       aria-hidden="true"></i>
                    <span>Secure sign-in to your clinic account</span>
                </div>
            </section>

        </div>
    </div>
</section>

<script>
(function () {
    const toggle = document.getElementById('toggle-login-password');
    const password = document.getElementById('login-password');

    if (!toggle || !password) return;

    toggle.addEventListener('click', function () {
        const show = password.type === 'password';

        password.type = show ? 'text' : 'password';

        toggle.innerHTML = show
            ? '<i class="fa-regular fa-eye-slash" aria-hidden="true"></i>'
            : '<i class="fa-regular fa-eye" aria-hidden="true"></i>';

        toggle.setAttribute(
            'aria-label',
            show ? 'Hide password' : 'Show password'
        );

        toggle.setAttribute('aria-pressed', String(show));
    });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
