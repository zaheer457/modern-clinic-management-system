
<?php

$title = 'Reviews';

require __DIR__ . '/header.php';

/* =========================================================
   UPDATE REVIEW STATUS
========================================================= */

if (isset($_GET['action'], $_GET['id'])) {

    $action = $_GET['action'];
    $id = (int) $_GET['id'];

    $allowedActions = [
        'Approved',
        'Rejected'
    ];

    if (in_array($action, $allowedActions, true)) {

        db_exec(
            "UPDATE reviews
             SET status = ?
             WHERE id = ?",
            'si',
            $action,
            $id
        );
    }

    redirect('reviews.php');
}

/* =========================================================
   FETCH REVIEWS
========================================================= */

$rows = db_all(
    "SELECT
        r.*,
        p.full_name AS patient_name,
        d.full_name AS doctor_name

     FROM reviews r

     JOIN patients pa
        ON pa.id = r.patient_id

     JOIN users p
        ON p.id = pa.user_id

     JOIN doctors dr
        ON dr.id = r.doctor_id

     JOIN users d
        ON d.id = dr.user_id

     ORDER BY r.created_at DESC"
);

?>

<!-- =========================================================
     REVIEW MANAGEMENT
========================================================= -->

<h3 class="section-title mb-4">
    Reviews
</h3>

<div class="table-card">

    <div class="table-responsive">

        <table class="table align-middle mb-0">

            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($rows)): ?>

                    <?php foreach ($rows as $r): ?>

                        <tr>

                            <!-- Patient Name -->
                            <td>
                                <?= e($r['patient_name']) ?>
                            </td>

                            <!-- Doctor Name -->
                            <td>
                                <?= e($r['doctor_name']) ?>
                            </td>

                            <!-- Star Rating -->
                            <td>
                                <span class="review-stars">
                                    <?= str_repeat('★', max(0, min(5, (int) $r['rating']))) ?>
                                </span>
                            </td>

                            <!-- Review Comment -->
                            <td>
                                <?= e($r['comment']) ?>
                            </td>

                            <!-- Review Status -->
                            <td>
                                <span class="status status-<?= e($r['status']) ?>">
                                    <?= e($r['status']) ?>
                                </span>
                            </td>

                            <!-- Review Actions -->
                            <td>

                                <?php if ($r['status'] === 'Pending'): ?>

                                    <a
                                        href="?action=Approved&id=<?= (int) $r['id'] ?>"
                                        class="btn btn-sm btn-outline-success"
                                    >
                                        Approve
                                    </a>

                                    <a
                                        href="?action=Rejected&id=<?= (int) $r['id'] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                    >
                                        Reject
                                    </a>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="text-center py-4">
                            No reviews found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require __DIR__ . '/footer.php'; ?>
