
<?php
/*
|--------------------------------------------------------------------------
| Department Management
| Process requests before rendering the admin header.
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../config/auth.php';

require_admin();

$title = 'Departments';

/*
|--------------------------------------------------------------------------
| ADD NEW DEPARTMENT
|--------------------------------------------------------------------------
*/

if (
    is_post()
    && ($_POST['action'] ?? '') !== 'delete_department'
) {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? '');

    if ($name === '') {
        flash('danger', 'Please enter a department name.');
        redirect('departments.php');
    }

    try {
        db_exec(
            "INSERT INTO departments (name, description, icon)
             VALUES (?, ?, ?)",
            'sss',
            $name,
            $desc,
            $icon
        );

        flash('success', 'Department added successfully.');
    } catch (Throwable $e) {
        error_log('Department insert failed: ' . $e->getMessage());
        flash('danger', 'Department could not be added. Check whether the department name already exists.');
    }

    redirect('departments.php');
}

/*
|--------------------------------------------------------------------------
| DELETE DEPARTMENT
|--------------------------------------------------------------------------
*/

if (
    ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && ($_POST['action'] ?? '') === 'delete_department'
) {
    verify_csrf();

    $id = (int) ($_POST['department_id'] ?? 0);

    if ($id <= 0) {
        flash('danger', 'Invalid department selected.');
        redirect('departments.php');
    }

    try {
        /*
         * Check linked doctors.
         */
        $doctorCount = db_one(
            "SELECT COUNT(*) AS total
             FROM doctors
             WHERE department_id = ?",
            'i',
            $id
        );

        /*
         * Check linked appointments.
         */
        $appointmentCount = db_one(
            "SELECT COUNT(*) AS total
             FROM appointments
             WHERE department_id = ?",
            'i',
            $id
        );

        $doctors = (int) ($doctorCount['total'] ?? 0);
        $appointments = (int) ($appointmentCount['total'] ?? 0);

        if ($doctors > 0 || $appointments > 0) {
            $reasons = [];

            if ($doctors > 0) {
                $reasons[] = $doctors . ' doctor(s)';
            }

            if ($appointments > 0) {
                $reasons[] = $appointments . ' appointment(s)';
            }

            flash(
                'warning',
                'This department cannot be deleted because it is linked to ' .
                implode(' and ', $reasons) .
                '. Reassign the related records first.'
            );

            redirect('departments.php');
        }

        $deleted = db_exec(
            "DELETE FROM departments WHERE id = ?",
            'i',
            $id
        );

        if ($deleted > 0) {
            flash('success', 'Department deleted successfully.');
        } else {
            flash('warning', 'Department was not found or was already deleted.');
        }
    } catch (Throwable $e) {
        error_log('Department deletion failed: ' . $e->getMessage());

        flash(
            'danger',
            'Department could not be deleted because it may still be linked to other records.'
        );
    }

    redirect('departments.php');
}

/*
|--------------------------------------------------------------------------
| FETCH DEPARTMENTS AND DOCTOR COUNTS
|--------------------------------------------------------------------------
*/

$rows = db_all(
    "SELECT
        dep.*,
        COUNT(d.id) AS doctors
     FROM departments dep
     LEFT JOIN doctors d
        ON d.department_id = dep.id
     GROUP BY dep.id
     ORDER BY dep.name"
);

/*
|--------------------------------------------------------------------------
| RENDER ADMIN HEADER
|--------------------------------------------------------------------------
*/

require __DIR__ . '/header.php';

?>

<!-- =========================================================
     DEPARTMENT MANAGEMENT
========================================================= -->

<div class="row g-4">

    <!-- ADD DEPARTMENT FORM -->
    <div class="col-lg-4">

        <div class="panel p-4">

            <h5 class="mb-3">
                <i class="fa-solid fa-hospital me-2"></i>
                Add Department
            </h5>

            <form method="post" action="departments.php">

                <?= csrf_field() ?>

                <input
                    type="text"
                    name="name"
                    class="form-control mb-3"
                    placeholder="Department Name"
                    maxlength="150"
                    required
                >

                <input
                    type="text"
                    name="icon"
                    class="form-control mb-3"
                    placeholder="Font Awesome Class"
                    value="fa-heart-pulse"
                >

                <textarea
                    name="description"
                    class="form-control mb-3"
                    placeholder="Department Description"
                    rows="4"
                ></textarea>

                <button
                    type="submit"
                    class="btn btn-primary rounded-pill"
                >
                    <i class="fa-solid fa-plus me-2"></i>
                    Add Department
                </button>

            </form>

        </div>

    </div>

    <!-- DEPARTMENT LIST -->
    <div class="col-lg-8">

        <div class="table-card">

            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Department Name</th>
                            <th>Doctors</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($rows)): ?>

                            <?php foreach ($rows as $r): ?>

                                <tr>

                                    <!-- Department Name -->
                                    <td>
                                        <strong>
                                            <?= e($r['name'] ?? '') ?>
                                        </strong>

                                        <?php if (!empty($r['description'])): ?>
                                            <div class="small text-secondary mt-1">
                                                <?= e($r['description']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Number of Doctors -->
                                    <td>
                                        <span class="status status-Confirmed">
                                            <?= (int) ($r['doctors'] ?? 0) ?>
                                        </span>
                                    </td>

                                    <!-- Delete Action -->
                                    <td class="text-end">

                                        <form
                                            method="post"
                                            action="departments.php"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete this department? Departments linked to doctors or appointments cannot be deleted.');"
                                        >

                                            <?= csrf_field() ?>

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="delete_department"
                                            >

                                            <input
                                                type="hidden"
                                                name="department_id"
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

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="3" class="text-center py-5">

                                    <i class="fa-solid fa-hospital fa-2x mb-3 d-block text-secondary"></i>

                                    <strong>No departments found.</strong>

                                    <p class="text-secondary mb-0 mt-1">
                                        Add a department to see it listed here.
                                    </p>

                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php require __DIR__ . '/footer.php'; ?>
