
<?php
/*
|--------------------------------------------------------------------------
| Doctor Management
| Process delete requests BEFORE rendering the admin header.
|--------------------------------------------------------------------------
*/


require_once __DIR__ . '/../config/auth.php';

require_admin();

$title = 'Doctors';

/*
|--------------------------------------------------------------------------
| DELETE DOCTOR
|--------------------------------------------------------------------------
*/

if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && ($_POST['action'] ?? '') === 'delete'
) {
    verify_csrf();

    $id = (int) ($_POST['doctor_id'] ?? 0);

    if ($id <= 0) {
        flash('danger', 'Invalid doctor selected.');
        redirect('doctors.php');
    }

    global $db;
    $transactionStarted = false;

    try {
        $doctor = db_one(
            "SELECT id, user_id FROM doctors WHERE id = ? LIMIT 1",
            'i',
            $id
        );

        if (!$doctor) {
            flash('warning', 'Doctor was not found or was already deleted.');
            redirect('doctors.php');
        }

        $userId = (int) $doctor['user_id'];
        $appointmentCount = db_one(
            "SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ?",
            'i',
            $id
        );
        $hasAppointments = (int) ($appointmentCount['total'] ?? 0) > 0;

        $db->begin_transaction();
        $transactionStarted = true;

        /* Remove the doctor from future service/schedule listings first. */
        db_exec("DELETE FROM service_doctors WHERE doctor_id = ?", 'i', $id);
        db_exec("DELETE FROM schedules WHERE doctor_id = ?", 'i', $id);

        if ($hasAppointments) {
            /*
             * Keep the doctor row and user record for historical appointment
             * joins. Mark both inactive so the doctor disappears from public
             * lists, department counts, booking options and service availability.
             */
            db_exec("UPDATE doctors SET status = 0 WHERE id = ?", 'i', $id);
            db_exec("UPDATE users SET status = 0 WHERE id = ? AND role = 'doctor'", 'i', $userId);
            $db->commit();
            $transactionStarted = false;

            flash(
                'success',
                'Doctor removed from active departments and services. Existing appointments were preserved in history.'
            );
        } else {
            /* No appointments depend on this doctor, so full deletion is safe. */
            db_exec("DELETE FROM doctors WHERE id = ?", 'i', $id);
            db_exec("DELETE FROM users WHERE id = ? AND role = 'doctor'", 'i', $userId);
            $db->commit();
            $transactionStarted = false;

            flash('success', 'Doctor deleted successfully and removed from related services and department listings.');
        }
    } catch (Throwable $e) {
        if ($transactionStarted) {
            try {
                $db->rollback();
            } catch (Throwable $rollbackError) {
                error_log('Doctor deletion rollback failed: ' . $rollbackError->getMessage());
            }
        }

        error_log('Doctor deletion failed: ' . $e->getMessage());
        flash('danger', 'Doctor could not be removed. No changes were saved; check the database error log.');
    }

    // Redirect before any HTML is sent.
    redirect('doctors.php');
}

/*
|--------------------------------------------------------------------------
| FETCH DOCTORS
|--------------------------------------------------------------------------
*/

$rows = db_all(
    "SELECT
        d.*,
        u.full_name,
        u.email,
        dep.name AS department
     FROM doctors d
     JOIN users u ON u.id = d.user_id
     JOIN departments dep ON dep.id = d.department_id
     ORDER BY d.id DESC"
);

/*
|--------------------------------------------------------------------------
| RENDER ADMIN HEADER AFTER REQUEST PROCESSING
|--------------------------------------------------------------------------
*/

require __DIR__ . '/header.php';

?>

<div class="admin-doctors-page">
<!-- PAGE TITLE -->


<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

    <div>
        <h3 class="section-title mb-1">
            Doctor Management
        </h3>

        <p class="text-secondary mb-0">
            Manage doctors, specialties and department assignments.
        </p>
    </div>

    <a
        href="doctor-form.php"
        class="btn btn-primary rounded-pill"
    >
        <i class="fa-solid fa-plus me-2"></i>
        Add Doctor
    </a>

</div>

<!-- DOCTORS TABLE -->

<div class="table-card">

    <div class="table-responsive">

        <table class="table align-middle mb-0">

            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Specialty</th>
                    <th>Department</th>
                    <th>Experience</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($rows)): ?>

                    <?php foreach ($rows as $r): ?>

                        <tr>

                            <!-- DOCTOR -->

                            <td>
                                <strong>
                                    <?= e($r['full_name'] ?? '') ?>
                                </strong>

                                <div class="small text-secondary">
                                    <?= e($r['email'] ?? '') ?>
                                </div>
                            </td>

                            <!-- SPECIALTY -->

                            <td>
                                <?= e($r['specialty'] ?? '') ?>
                            </td>

                            <!-- DEPARTMENT -->

                            <td>
                                <?= e($r['department'] ?? '') ?>
                            </td>

                            <!-- EXPERIENCE -->

                            <td>
                                <?= (int) ($r['experience'] ?? 0) ?> yrs
                            </td>

                            <!-- STATUS -->

                            <td>
                                <?php if (!empty($r['status'])): ?>

                                    <span class="status status-Confirmed">
                                        Active
                                    </span>

                                <?php else: ?>

                                    <span class="status status-Rejected">
                                        Inactive
                                    </span>

                                <?php endif; ?>
                            </td>

                            <!-- ACTIONS -->

                            <td class="text-end">

                                <div class="d-inline-flex align-items-center gap-2 flex-wrap">

                                    <a
                                        href="doctor-form.php?id=<?= (int) $r['id'] ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        <i class="fa-solid fa-pen me-1"></i>
                                        Edit
                                    </a>

                                    <form
                                        method="post"
                                        action="doctors.php"
                                        class="d-inline"
                                        onsubmit="return confirm('Remove this doctor? If appointments exist, the doctor will be archived to preserve appointment history.');"
                                    >

                                        <?= csrf_field() ?>

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="doctor_id"
                                            value="<?= (int) $r['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                        >
                                            <i class="fa-solid fa-trash me-1"></i>
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="text-center py-5">

                            <i class="fa-solid fa-user-doctor fa-2x mb-3 d-block text-secondary"></i>

                            <strong>No doctors found.</strong>

                            <p class="text-secondary mb-0 mt-1">
                                Add a doctor to see them listed here.
                            </p>

                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

                </div> <!-- /.admin-doctors-page -->


<?php require __DIR__ . '/footer.php'; ?>
