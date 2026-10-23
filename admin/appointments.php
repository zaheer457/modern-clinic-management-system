
<?php
/*
|--------------------------------------------------------------------------
| Appointment Management
| Process status updates before rendering the admin header.
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../config/auth.php';

require_admin();

$title = 'Appointments';

/*
|--------------------------------------------------------------------------
| UPDATE APPOINTMENT STATUS
|--------------------------------------------------------------------------
*/

if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
    && isset($_GET['action'], $_GET['id'])
) {
    $allowed = [
        'Confirmed',
        'Rejected',
        'Completed',
        'Cancelled'
    ];

    $action = (string) $_GET['action'];
    $id = (int) $_GET['id'];

    if ($id <= 0 || !in_array($action, $allowed, true)) {
        flash('danger', 'Invalid appointment action.');
        redirect('appointments.php');
    }

    try {
        $updated = db_exec(
            "UPDATE appointments SET status = ? WHERE id = ?",
            'si',
            $action,
            $id
        );

        flash('success', 'Appointment status updated.');
    } catch (Throwable $e) {
        error_log('Appointment status update failed: ' . $e->getMessage());

        flash('danger', 'Could not update appointment status.');
    }

    redirect('appointments.php');
}

/*
|--------------------------------------------------------------------------
| FETCH APPOINTMENTS
|--------------------------------------------------------------------------
*/

$rows = db_all(
    "SELECT
        a.*,
        p.full_name AS patient_name,
        d.full_name AS doctor_name,
        dep.name AS department
     FROM appointments a
     JOIN patients pa
        ON pa.id = a.patient_id
     JOIN users p
        ON p.id = pa.user_id
     JOIN doctors dr
        ON dr.id = a.doctor_id
     JOIN users d
        ON d.id = dr.user_id
     JOIN departments dep
        ON dep.id = a.department_id
     ORDER BY
        a.appointment_date DESC,
        a.appointment_time DESC"
);

/*
|--------------------------------------------------------------------------
| RENDER ADMIN HEADER AFTER REQUEST PROCESSING
|--------------------------------------------------------------------------
*/

require __DIR__ . '/header.php';

?>

<!-- =========================================================
     APPOINTMENT MANAGEMENT
========================================================= -->

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h3 class="section-title mb-1">
            Appointment Management
        </h3>

        <p class="text-secondary mb-0">
            Review appointments and manage their status.
        </p>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">

            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Date / Time</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($rows)): ?>

                    <?php foreach ($rows as $a): ?>
                        <tr>

                            <!-- Patient -->
                            <td>
                                <strong>
                                    <?= e($a['patient_name'] ?? '') ?>
                                </strong>
                            </td>

                            <!-- Doctor -->
                            <td>
                                <?= e($a['doctor_name'] ?? '') ?>
                            </td>

                            <!-- Date and time -->
                            <td>
                                <?= e($a['appointment_date'] ?? '') ?>

                                <br>

                                <small class="text-secondary">
                                    <?php
                                    $time = $a['appointment_time'] ?? '';
                                    $timestamp = $time !== '' ? strtotime($time) : false;

                                    echo $timestamp !== false
                                        ? e(date('g:i A', $timestamp))
                                        : '—';
                                    ?>
                                </small>
                            </td>

                            <!-- Appointment type -->
                            <td>
                                <?= e($a['appointment_type'] ?? '') ?>
                            </td>

                            <!-- Status -->
                            <td>
                                <?php
                                $status = (string) ($a['status'] ?? 'Pending');

                                $statusClass = match ($status) {
                                    'Confirmed' => 'status-Confirmed',
                                    'Completed' => 'status-Completed',
                                    'Rejected'  => 'status-Rejected',
                                    'Cancelled' => 'status-Cancelled',
                                    default     => 'status-Pending',
                                };
                                ?>

                                <span class="status <?= e($statusClass) ?>">
                                    <?= e($status) ?>
                                </span>
                            </td>

                            <!-- Actions -->
                            <td>
                                <div class="d-inline-flex align-items-center gap-2 flex-wrap">

                                    <?php if ($status === 'Pending'): ?>

                                        <a
                                            class="btn btn-sm btn-outline-success"
                                            href="appointments.php?action=Confirmed&amp;id=<?= (int) $a['id'] ?>"
                                            onclick="return confirm('Confirm this appointment?');"
                                        >
                                            <i class="fa-solid fa-check me-1"></i>
                                            Confirm
                                        </a>

                                        <a
                                            class="btn btn-sm btn-outline-danger"
                                            href="appointments.php?action=Rejected&amp;id=<?= (int) $a['id'] ?>"
                                            onclick="return confirm('Reject this appointment?');"
                                        >
                                            <i class="fa-solid fa-xmark me-1"></i>
                                            Reject
                                        </a>

                                    <?php elseif ($status === 'Confirmed'): ?>

                                        <a
                                            class="btn btn-sm btn-outline-primary"
                                            href="appointments.php?action=Completed&amp;id=<?= (int) $a['id'] ?>"
                                            onclick="return confirm('Mark this appointment as completed?');"
                                        >
                                            <i class="fa-solid fa-check-double me-1"></i>
                                            Complete
                                        </a>

                                        <a
                                            class="btn btn-sm btn-outline-danger"
                                            href="appointments.php?action=Cancelled&amp;id=<?= (int) $a['id'] ?>"
                                            onclick="return confirm('Cancel this appointment?');"
                                        >
                                            <i class="fa-solid fa-ban me-1"></i>
                                            Cancel
                                        </a>

                                    <?php else: ?>

                                        <span class="text-secondary small">
                                            No actions available
                                        </span>

                                    <?php endif; ?>

                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fa-solid fa-calendar-check fa-2x mb-3 d-block text-secondary"></i>

                            <strong>No appointments found.</strong>

                            <p class="text-secondary mb-0 mt-1">
                                Appointments will appear here when patients book them.
                            </p>
                        </td>
                    </tr>

                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
