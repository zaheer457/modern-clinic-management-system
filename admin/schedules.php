
<?php

$title = 'Schedules';

require __DIR__ . '/header.php';

/* =========================================================
   FETCH DOCTORS
========================================================= */

$docs = db_all(
    "SELECT
        d.id,
        u.full_name
     FROM doctors d
     JOIN users u ON u.id = d.user_id
     ORDER BY u.full_name"
);

/* =========================================================
   ADD SCHEDULE
========================================================= */

if (is_post()) {

    verify_csrf();

    db_exec(
        "INSERT INTO schedules (
            doctor_id,
            day_of_week,
            start_time,
            end_time,
            break_start,
            break_end,
            max_appointments
        ) VALUES (?, ?, ?, ?, ?, ?, ?)",

        'iissssi',

        (int) $_POST['doctor_id'],
        (int) $_POST['day_of_week'],
        $_POST['start_time'],
        $_POST['end_time'],
        $_POST['break_start'] ?: null,
        $_POST['break_end'] ?: null,
        (int) $_POST['max_appointments']
    );

    flash('success', 'Schedule added.');

    redirect('schedules.php');
}

/* =========================================================
   DELETE SCHEDULE
========================================================= */

if (isset($_GET['delete'])) {

    db_exec(
        "DELETE FROM schedules WHERE id = ?",
        'i',
        (int) $_GET['delete']
    );

    flash('success', 'Schedule deleted.');

    redirect('schedules.php');
}

/* =========================================================
   FETCH SCHEDULES
========================================================= */

$rows = db_all(
    "SELECT
        s.*,
        u.full_name
     FROM schedules s
     JOIN doctors d ON d.id = s.doctor_id
     JOIN users u ON u.id = d.user_id
     ORDER BY s.doctor_id, s.day_of_week"
);

/* =========================================================
   DAYS OF THE WEEK
========================================================= */

$days = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday'
];

?>

<!-- =========================================================
     SCHEDULE MANAGEMENT
========================================================= -->

<div class="row g-4">

    <!-- ADD SCHEDULE FORM -->

    <div class="col-lg-4">

        <div class="panel p-4">

            <h5 class="mb-4">Add Schedule</h5>

            <form method="post">

                <?= csrf_field() ?>

                <!-- Select Doctor -->

                <select
                    name="doctor_id"
                    class="form-select mb-2"
                    required
                >
                    <?php foreach ($docs as $d): ?>

                        <option value="<?= (int) $d['id'] ?>">
                            <?= e($d['full_name']) ?>
                        </option>

                    <?php endforeach; ?>
                </select>

                <!-- Select Day -->

                <select
                    name="day_of_week"
                    class="form-select mb-2"
                    required
                >
                    <?php foreach ($days as $i => $day): ?>

                        <option value="<?= $i ?>">
                            <?= e($day) ?>
                        </option>

                    <?php endforeach; ?>
                </select>

                <!-- Working Hours -->

                <label class="form-label">
                    Start Time
                </label>

                <input
                    type="time"
                    name="start_time"
                    class="form-control mb-2"
                    required
                >

                <label class="form-label">
                    End Time
                </label>

                <input
                    type="time"
                    name="end_time"
                    class="form-control mb-2"
                    required
                >

                <!-- Break Timings -->

                <label class="form-label">
                    Break Start
                </label>

                <input
                    type="time"
                    name="break_start"
                    class="form-control mb-2"
                >

                <label class="form-label">
                    Break End
                </label>

                <input
                    type="time"
                    name="break_end"
                    class="form-control mb-2"
                >

                <!-- Appointment Capacity -->

                <label class="form-label">
                    Maximum Appointments
                </label>

                <input
                    type="number"
                    name="max_appointments"
                    class="form-control mb-3"
                    value="12"
                    min="1"
                    required
                >

                <button
                    type="submit"
                    class="btn btn-primary rounded-pill"
                >
                    Add Schedule
                </button>

            </form>

        </div>

    </div>

    <!-- SCHEDULES TABLE -->

    <div class="col-lg-8">

        <div class="table-card">

            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Day</th>
                            <th>Hours</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($rows)): ?>

                            <?php foreach ($rows as $r): ?>

                                <tr>

                                    <!-- Doctor Name -->

                                    <td>
                                        <?= e($r['full_name']) ?>
                                    </td>

                                    <!-- Day -->

                                    <td>
                                        <?= e(
                                            $days[(int) $r['day_of_week']]
                                            ?? 'Unknown'
                                        ) ?>
                                    </td>

                                    <!-- Working Hours -->

                                    <td>
                                        <?= e(
                                            date(
                                                'g:i A',
                                                strtotime($r['start_time'])
                                            )
                                        ) ?>

                                        –

                                        <?= e(
                                            date(
                                                'g:i A',
                                                strtotime($r['end_time'])
                                            )
                                        ) ?>
                                    </td>

                                    <!-- Delete Schedule -->

                                    <td>
                                        <a
                                            data-confirm="Delete schedule?"
                                            class="btn btn-sm btn-outline-danger"
                                            href="?delete=<?= (int) $r['id'] ?>"
                                        >
                                            Delete
                                        </a>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td
                                    colspan="4"
                                    class="text-center py-4"
                                >
                                    No schedules found.
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
