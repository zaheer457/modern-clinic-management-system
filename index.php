<?php
$title = 'Home';
require __DIR__ . '/includes/header.php';

$stats = [
    'Doctors'      => count_rows('doctors'),
    'Departments'  => count_rows('departments'),
    'Patients'     => count_rows('patients'),
    'Appointments' => count_rows('appointments')
];

$docs = db_all("
    SELECT d.*, u.full_name, dep.name department
    FROM doctors d
    JOIN users u ON u.id = d.user_id
    JOIN departments dep ON dep.id = d.department_id
    WHERE d.status = 1 AND u.status = 1
    ORDER BY d.id
    LIMIT 4
");

$depts = db_all("
    SELECT *
    FROM departments
    WHERE status = 1
    ORDER BY id
");

$reviews = db_all("
    SELECT r.*, p.full_name patient_name, u.full_name doctor_name
    FROM reviews r
    JOIN patients pa ON pa.id = r.patient_id
    JOIN users p ON p.id = pa.user_id
    JOIN doctors d ON d.id = r.doctor_id
    JOIN users u ON u.id = d.user_id
    WHERE r.status = 'Approved'
    ORDER BY r.id DESC
    LIMIT 3
");
?>

<!-- ================= HERO SECTION ================= -->

<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6">

                <span class="section-kicker">
                    Healthcare made easier
                </span>

                <h1 class="mt-3">
                    Care That Puts Your Health First.
                </h1>

                <p class="my-4">
                    Finding the right doctor should be simple.
                    Explore our medical team, learn about available
                    services, and find the care that suits your needs
                    through one convenient platform.
                </p>

                <div class="d-flex flex-wrap gap-3">

                    <a href="doctors.php"
                       class="btn btn-primary btn-lg rounded-pill px-4">
                        <i class="fa-solid fa-user-doctor me-2"></i>
                        Meet Our Doctors
                    </a>

                    <a href="departments.php"
                       class="btn btn-outline-dark btn-lg rounded-pill px-4">
                        Explore Departments
                    </a>

                </div>

            </div>

            <div class="col-lg-6">
                <div class="hero-art">

                    <div class="floating-card">
                        <strong>
                            <i class="fa-solid fa-heart-pulse text-success me-2"></i>
                            Your Health Matters
                        </strong>

                        <div class="small text-secondary mt-1">
                            Find information and care for your healthcare needs.
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>


<!-- ================= CLINIC STATISTICS ================= -->

<section class="section pt-5">
    <div class="container">

        <div class="row g-3">

            <?php foreach ($stats as $label => $value): ?>

                <div class="col-6 col-lg-3">
                    <div class="stat-card">

                        <div class="stat-number">
                            <?= (int) $value ?>
                        </div>

                        <div class="text-secondary">
                            <?= e($label) ?>
                        </div>

                    </div>
                </div>

            <?php endforeach; ?>

        </div>

    </div>
</section>


<!-- ================= FEATURED DOCTORS ================= -->

<section class="section bg-white">
    <div class="container">

        <div class="d-flex flex-wrap justify-content-between
                    align-items-end gap-3 mb-4">

            <div>
                <span class="section-kicker">
                    Get to know our medical team
                </span>

                <h2 class="section-title mt-2">
                    Meet Our Doctors
                </h2>

                <p class="text-secondary mb-0">
                    Explore doctor profiles, specialties, and experience
                    to help you find a suitable healthcare professional.
                </p>
            </div>

            <a href="doctors.php"
               class="btn btn-outline-primary rounded-pill">
                View All Doctors
                <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>

        </div>


        <div class="row g-4">

            <?php if (!empty($docs)): ?>

                <?php foreach ($docs as $d): ?>

                    <div class="col-6 col-lg-3">

                        <div class="doctor-card h-100">

                            <div
                                class="doctor-photo"
                                style="background-image:url('<?= e($d['image'] ?: 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=700&q=80') ?>')">
                            </div>

                            <div class="card-body">

                                <span class="pill">
                                    <?= e($d['department']) ?>
                                </span>

                                <h5 class="mt-3 mb-1">
                                    <?= e($d['full_name']) ?>
                                </h5>

                                <p class="text-secondary mb-3">
                                    <?= e($d['specialty']) ?>
                                </p>

                                <small class="text-secondary">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    <?= (int) $d['experience'] ?> years of experience
                                </small>

                                <div class="mt-3">
                                    <a
                                        href="doctor-profile.php?id=<?= (int) $d['id'] ?>"
                                        class="btn btn-sm btn-primary rounded-pill">
                                        View Profile
                                    </a>
                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12">
                    <div class="panel p-4 text-center">
                        <i class="fa-solid fa-user-doctor fs-2 text-secondary mb-3"></i>

                        <h5>Doctor profiles are being updated</h5>

                        <p class="text-secondary mb-0">
                            Please check back soon for information about our medical team.
                        </p>
                    </div>
                </div>

            <?php endif; ?>

        </div>

    </div>
</section>


<!-- ================= MEDICAL DEPARTMENTS ================= -->

<section class="section">
    <div class="container">

        <div class="text-center mb-5">

            <span class="section-kicker">
                Explore our areas of care
            </span>

            <h2 class="section-title mt-2">
                Medical Departments
            </h2>

            <p class="text-secondary mx-auto"
               style="max-width:650px">
                Learn about our medical departments and the services
                available to help you make informed healthcare decisions.
            </p>

        </div>


        <div class="row g-4">

            <?php if (!empty($depts)): ?>

                <?php foreach ($depts as $d): ?>

                    <div class="col-md-6 col-lg-4">

                        <a
                            class="text-decoration-none text-dark"
                            href="department.php?id=<?= (int) $d['id'] ?>">

                            <div class="dept-card p-4 h-100">

                                <div class="icon-box">
                                    <i class="fa-solid <?= e($d['icon']) ?>"></i>
                                </div>

                                <h5 class="mt-3">
                                    <?= e($d['name']) ?>
                                </h5>

                                <p class="text-secondary mb-0">
                                    <?= e($d['description']) ?>
                                </p>

                                <div class="mt-3 small fw-bold text-primary">
                                    Explore Department
                                    <i class="fa-solid fa-arrow-right ms-1"></i>
                                </div>

                            </div>

                        </a>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="col-12">
                    <div class="panel p-4 text-center">
                        <h5>Department information is being updated</h5>
                        <p class="text-secondary mb-0">
                            Please check back later for available departments.
                        </p>
                    </div>
                </div>

            <?php endif; ?>

        </div>

    </div>
</section>


<!-- ================= WHY CHOOSE US ================= -->

<section class="section bg-white">
    <div class="container">

        <div class="row align-items-center g-5">

            <div class="col-lg-6">

                <span class="section-kicker">
                    A simpler way to manage your care
                </span>

                <h2 class="section-title mt-2">
                    Healthcare, Made Easier.
                </h2>

                <p class="text-secondary">
                    We bring essential clinic information together in one
                    place. Explore medical services, review doctor profiles,
                    and manage your appointments without unnecessary steps.
                </p>

                <div class="row g-3 mt-3">

                    <?php
                    $features = [
                        'Doctor Profiles',
                        'Medical Departments',
                        'Patient Account Access',
                        'Online Appointment Booking'
                    ];
                    ?>

                    <?php foreach ($features as $feature): ?>

                        <div class="col-sm-6">

                            <div class="feature-card p-3 h-100">
                                <i class="fa-solid fa-circle-check text-success me-2"></i>
                                <?= e($feature) ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>


            <div class="col-lg-6">

                <div class="panel p-4">

                    <span class="section-kicker">
                        Getting started
                    </span>

                    <h3 class="h4 mt-2 mb-3">
                        How It Works
                    </h3>

                    <?php
                    $steps = [
                        [
                            'title' => 'Explore Doctors',
                            'description' => 'Browse available doctors and their specialties.'
                        ],
                        [
                            'title' => 'Review a Profile',
                            'description' => 'Learn about a doctor’s experience and department.'
                        ],
                        [
                            'title' => 'Choose an Appointment',
                            'description' => 'Select an available date and time when booking.'
                        ],
                        [
                            'title' => 'Check Your Booking',
                            'description' => 'Use your patient account to review appointment details and status.'
                        ]
                    ];
                    ?>

                    <?php foreach ($steps as $i => $step): ?>

                        <div class="d-flex gap-3 py-3 border-bottom">

                            <div
                                class="icon-box flex-shrink-0"
                                style="width:42px;height:42px">

                                <?= sprintf('%02d', $i + 1) ?>

                            </div>

                            <div>

                                <strong>
                                    <?= e($step['title']) ?>
                                </strong>

                                <div class="small text-secondary mt-1">
                                    <?= e($step['description']) ?>
                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>

    </div>
</section>


<!-- ================= END OF HOMEPAGE ================= -->

<?php require __DIR__ . '/includes/footer.php'; ?>