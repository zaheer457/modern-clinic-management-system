
<?php

$title = 'Patients';

require __DIR__ . '/header.php';

/* =========================================================
   FETCH ALL PATIENTS
========================================================= */

$rows = db_all(
    "SELECT
        p.*,
        u.full_name,
        u.email,
        u.phone,
        u.status

     FROM patients p

     JOIN users u
        ON u.id = p.user_id

     ORDER BY p.id DESC"
);

?>

<!-- =========================================================
     PATIENT MANAGEMENT
========================================================= -->

<h3 class="section-title mb-4">
    Patient Management
</h3>

<div class="table-card">

    <div class="table-responsive">

        <table class="table align-middle mb-0">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Registered</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($rows)): ?>

                    <?php foreach ($rows as $r): ?>

                        <tr>

                            <!-- Patient ID -->
                            <td>
                                #<?= (int) $r['id'] ?>
                            </td>

                            <!-- Patient Name -->
                            <td>
                                <strong>
                                    <?= e($r['full_name']) ?>
                                </strong>
                            </td>

                            <!-- Email -->
                            <td>
                                <?= e($r['email']) ?>
                            </td>

                            <!-- Phone -->
                            <td>
                                <?= e($r['phone']) ?>
                            </td>

                            <!-- Gender -->
                            <td>
                                <?= e($r['gender']) ?>
                            </td>

                            <!-- Registration Date -->
                            <td>
                                <?= e(
                                    date(
                                        'd M Y',
                                        strtotime($r['created_at'])
                                    )
                                ) ?>
                            </td>

                            <!-- Account Status -->
                            <td>

                                <?php if ($r['status']): ?>

                                    <span class="status status-Confirmed">
                                        Active
                                    </span>

                                <?php else: ?>

                                    <span class="status status-Rejected">
                                        Inactive
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="text-center py-4">
                            No patients found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require __DIR__ . '/footer.php'; ?>
