<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Patient Dashboard
|--------------------------------------------------------------------------
| Load authentication/functions before using require_role()
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../config/auth.php';

require_role('patient');

$title = 'Patient Dashboard';
$base = '../';

require __DIR__ . '/../includes/header.php';


/*
|--------------------------------------------------------------------------
| Get logged-in patient's record
|--------------------------------------------------------------------------
*/

$p = db_one(
    "SELECT 
        p.*,
        u.full_name,
        u.email,
        u.phone
     FROM patients p
     JOIN users u ON u.id = p.user_id
     WHERE p.user_id = ?",
    "i",
    current_user()['id']
);


/*
|--------------------------------------------------------------------------
| Safety check
|--------------------------------------------------------------------------
*/

if (!$p) {
    http_response_code(404);
    exit('Patient profile not found.');
}


/*
|--------------------------------------------------------------------------
| Get patient's upcoming/recent appointments
|--------------------------------------------------------------------------
*/

$up = db_all(
    "SELECT 
        a.*,
        u.full_name AS doctor_name,
        dep.name AS department
     FROM appointments a
     JOIN doctors d ON d.id = a.doctor_id
     JOIN users u ON u.id = d.user_id
     JOIN departments dep ON dep.id = a.department_id
     WHERE a.patient_id = ?
     ORDER BY a.appointment_date, a.appointment_time
     LIMIT 5",
    "i",
    $p['id']
);

?>

<section class="page-hero">
    <div class="container">

        <span class="section-kicker">
            Patient portal
        </span>

        <h1 class="section-title mt-2">
            Welcome, <?= e(current_user()['full_name']) ?>
        </h1>

        <p class="text-secondary">
            Manage your appointments and profile from one place.
        </p>

    </div>
</section>


<section class="section pt-5">

    <div class="container">

        <!-- Dashboard Metrics -->

        <div class="row g-4">

            <div class="col-md-4">

                <div class="metric">

                    <div class="text-secondary">
                        Upcoming appointments
                    </div>

                    <div class="stat-number">
                        <?= count($up) ?>
                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="metric">

                    <div class="text-secondary">
                        Email
                    </div>

                    <strong>
                        <?= e($p['email']) ?>
                    </strong>

                </div>

            </div>


            <div class="col-md-4">

                <div class="metric">

                    <a
                        href="profile.php"
                        class="btn btn-primary rounded-pill"
                    >
                        Edit Profile
                    </a>

                </div>

            </div>

        </div>


        <!-- Appointments -->

        <div class="table-card mt-5">

            <div class="p-4">

                <div class="d-flex justify-content-between align-items-center">

                    <h4 class="section-title mb-0">
                        My appointments
                    </h4>

                    <a
                        href="../appointment.php"
                        class="btn btn-primary rounded-pill"
                    >
                        Book new
                    </a>

                </div>

            </div>


            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead>

                        <tr>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($up as $a): ?>

                            <tr>

                                <td>
                                    <?= e($a['doctor_name']) ?>
                                </td>

                                <td>
                                    <?= e($a['appointment_date']) ?>
                                </td>

                                <td>
                                    <?= date(
                                        'g:i A',
                                        strtotime($a['appointment_time'])
                                    ) ?>
                                </td>

                                <td>

                                    <span
                                        class="status status-<?= e($a['status']) ?>"
                                    >
                                        <?= e($a['status']) ?>
                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>


                        <?php if (!$up): ?>

                            <tr>

                                <td
                                    colspan="4"
                                    class="text-center py-4 text-secondary"
                                >
                                    No appointments yet.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</section>


<?php require __DIR__ . '/../includes/footer.php'; ?>