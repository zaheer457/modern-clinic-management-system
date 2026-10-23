
<?php
$title = 'Contact';
require __DIR__ . '/includes/header.php';

if (is_post()) {
    verify_csrf();

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $message) {
        db_exec(
            "INSERT INTO messages (name, email, phone, subject, message)
             VALUES (?, ?, ?, ?, ?)",
            'sssss',
            $name,
            $email,
            $phone,
            $subject,
            $message
        );

        flash('success', 'Thanks. Your message has been sent to Modern Clinic.');
        redirect('contact.php');
    }

    flash('danger', 'Please complete the required fields with a valid email.');
}
?>

<style>
/* CONTACT PAGE */
.contact-page {
    padding-top: 0;
    padding-bottom: 60px;
    background: #f7fbfd;
}

/* Centered hero container */
.contact-hero {
    position: relative;
    width: calc(100% - 48px);
    max-width: 1140px;
    margin: 0 auto 36px;
    padding: 38px 32px;
    overflow: hidden;
    box-sizing: border-box;
    border: 1px solid rgba(69, 213, 223, 0.25);
    border-radius: 24px;
    background: linear-gradient(
        125deg,
        #08243a 0%,
        #103e5a 56%,
        #126c89 100%
    );
    box-shadow: 0 14px 35px rgba(8, 36, 58, 0.12);
    color: #ffffff;
}

.contact-hero::before {
    content: "";
    position: absolute;
    width: 240px;
    height: 240px;
    top: -130px;
    right: -45px;
    border-radius: 50%;
    background: rgba(69, 213, 223, 0.12);
    pointer-events: none;
}

.contact-hero::after {
    content: "";
    position: absolute;
    width: 150px;
    height: 150px;
    bottom: -100px;
    left: 38%;
    border-radius: 50%;
    background: rgba(50, 182, 122, 0.12);
    pointer-events: none;
}

.contact-hero-content {
    position: relative;
    z-index: 1;
    max-width: 760px;
    margin: 0 auto;
    text-align: center;
}

.contact-hero .section-kicker {
    display: inline-block;
    margin-bottom: 8px;
    color: #45d5df;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.contact-hero h1 {
    margin: 0 0 12px;
    color: #ffffff;
    font-size: clamp(30px, 4vw, 43px);
    font-weight: 800;
    line-height: 1.2;
}

.contact-hero h1 span {
    color: #45d5df;
}

.contact-hero p {
    max-width: 600px;
    margin: 0 auto;
    color: rgba(255, 255, 255, 0.82);
    font-size: 15px;
    line-height: 1.8;
}

/* Main content spacing */
.contact-content {
    width: calc(100% - 48px);
    max-width: 1140px;
    margin: 0 auto;
}

.contact-grid {
    align-items: stretch;
}

/* Contact cards */
.contact-card {
    height: 100%;
    padding: 30px;
    border: 1px solid #e1edf3;
    border-radius: 22px;
    background: #ffffff;
    box-shadow: 0 10px 32px rgba(8, 36, 58, 0.055);
}

.contact-card h3 {
    margin: 0;
    color: #08243a;
    font-size: 22px;
    font-weight: 800;
}

.contact-card .contact-subtitle {
    margin: 9px 0 26px;
    color: #718394;
    font-size: 14px;
    line-height: 1.7;
}

/* Information rows */
.contact-info-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 23px;
}

.contact-info-item:last-child {
    margin-bottom: 0;
}

.contact-info-icon {
    display: flex;
    flex: 0 0 46px;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    border: 1px solid rgba(21, 151, 212, 0.12);
    border-radius: 14px;
    background: #eef8fc;
    color: #1597d4;
    font-size: 17px;
}

.contact-info-item h6 {
    margin: 1px 0 5px;
    color: #163047;
    font-size: 14px;
    font-weight: 800;
}

.contact-info-item p,
.contact-info-item a {
    margin: 0;
    color: #6b7d8d;
    font-size: 14px;
    line-height: 1.7;
    text-decoration: none;
    overflow-wrap: anywhere;
}

.contact-info-item a:hover {
    color: #1597d4;
}

/* Form */
.contact-form .form-label {
    margin-bottom: 8px;
    color: #163047;
    font-size: 13px;
    font-weight: 700;
}

.contact-form .form-control {
    min-height: 49px;
    padding: 12px 15px;
    border: 1px solid #dce8ef;
    border-radius: 12px;
    background: #f9fcfe;
    color: #163047;
    font-size: 14px;
    box-shadow: none;
    transition: border-color 0.2s ease, background 0.2s ease,
                box-shadow 0.2s ease;
}

.contact-form textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

.contact-form .form-control::placeholder {
    color: #9aabb8;
}

.contact-form .form-control:focus {
    border-color: #1597d4;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(21, 151, 212, 0.10);
}

.contact-submit {
    min-height: 48px;
    padding: 12px 24px;
    border: none;
    border-radius: 999px;
    background: linear-gradient(120deg, #1597d4, #0879b5);
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 7px 18px rgba(21, 151, 212, 0.20);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.contact-submit:hover {
    transform: translateY(-2px);
    color: #ffffff;
    box-shadow: 0 10px 22px rgba(21, 151, 212, 0.27);
}

.contact-note {
    margin-top: 17px;
    color: #81919e;
    font-size: 12px;
    line-height: 1.7;
}

@media (max-width: 767px) {
    .contact-page {
        padding-bottom: 35px;
    }

    .contact-hero {
        width: calc(100% - 24px);
        margin: 0 auto 24px;
        padding: 30px 18px;
        border-radius: 20px;
    }

    .contact-content {
        width: calc(100% - 24px);
    }

    .contact-card {
        padding: 22px 18px;
        border-radius: 18px;
    }

    .contact-card h3 {
        font-size: 20px;
    }

    .contact-info-item {
        gap: 12px;
    }

    .contact-info-icon {
        flex-basis: 42px;
        width: 42px;
        height: 42px;
    }
}
</style>

<section class="contact-page">

    <section class="contact-hero">
        <div class="contact-hero-content">
            <span class="section-kicker">We're Here to Help</span>

            <h1>Contact <span>Us</span></h1>

            <p>
                Have a question or need assistance? Our team is here to help
                with appointments, healthcare inquiries and general support.
            </p>
        </div>
    </section>

    <section class="contact-content">
        <div class="row g-4 contact-grid">

            <!-- Clinic information -->
            <div class="col-lg-5">
                <div class="contact-card">
                    <h3>Get in Touch</h3>

                    <p class="contact-subtitle">
                        Reach out to our team. We'd be happy to assist you.
                    </p>

                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <h6>Our Location</h6>
                            <p>Karachi, Pakistan</p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <h6>Phone Number</h6>
                            <p>+92 300 1234567</p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <h6>Email Address</h6>
                            <p>
                                <a href="mailto:care@modernclinic.com">
                                    care@modernclinic.com
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <i class="fa-regular fa-clock"></i>
                        </div>
                        <div>
                            <h6>Opening Hours</h6>
                            <p>Monday – Saturday</p>
                            <p>9:00 AM – 8:00 PM</p>
                        </div>
                    </div>

                    <div class="contact-note">
                        <i class="fa-solid fa-shield-heart me-1"></i>
                        Your healthcare questions matter to us. Send us a
                        message and our team will assist you.
                    </div>
                </div>
            </div>

            <!-- Contact form -->
            <div class="col-lg-7">
                <div class="contact-card">
                    <h3>Send Us a Message</h3>

                    <p class="contact-subtitle">
                        Fill in the details below. Fields marked with * are required.
                    </p>

                    <form method="post" class="contact-form">
                        <?= csrf_field() ?>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="contact-name" class="form-label">
                                    Full Name *
                                </label>
                                <input
                                    id="contact-name"
                                    type="text"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter your full name"
                                    maxlength="150"
                                    autocomplete="name"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="contact-email" class="form-label">
                                    Email Address *
                                </label>
                                <input
                                    id="contact-email"
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="you@example.com"
                                    maxlength="190"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="contact-phone" class="form-label">
                                    Phone Number
                                </label>
                                <input
                                    id="contact-phone"
                                    type="tel"
                                    name="phone"
                                    class="form-control"
                                    placeholder="+92 3XX XXXXXXX"
                                    maxlength="30"
                                    autocomplete="tel"
                                >
                            </div>

                            <div class="col-md-6">
                                <label for="contact-subject" class="form-label">
                                    Subject
                                </label>
                                <input
                                    id="contact-subject"
                                    type="text"
                                    name="subject"
                                    class="form-control"
                                    placeholder="How can we help?"
                                    maxlength="200"
                                >
                            </div>

                            <div class="col-12">
                                <label for="contact-message" class="form-label">
                                    Your Message *
                                </label>
                                <textarea
                                    id="contact-message"
                                    name="message"
                                    class="form-control"
                                    rows="5"
                                    placeholder="Write your message here..."
                                    required
                                ></textarea>
                            </div>

                            <div class="col-12 pt-1">
                                <button type="submit" class="btn contact-submit">
                                    <i class="fa-regular fa-paper-plane me-2"></i>
                                    Send Message
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
