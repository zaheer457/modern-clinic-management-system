
<?php

$title = 'Services';

require __DIR__ . '/header.php';

/* =========================================================
   FETCH DEPARTMENTS
========================================================= */

$deps = db_all(
    "SELECT *
     FROM departments
     ORDER BY name"
);

/* =========================================================
   ADD SERVICE
========================================================= */

if (is_post()) {

    verify_csrf();

    $name = trim($_POST['name']);
    $dep  = (int) $_POST['department_id'];
    $desc = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $dur   = (int) $_POST['duration'];

    if ($name) {

        db_exec(
            "INSERT INTO services (
                department_id,
                name,
                description,
                price,
                duration
            ) VALUES (?, ?, ?, ?, ?)",

            'issdi',

            $dep,
            $name,
            $desc,
            $price,
            $dur
        );

        flash('success', 'Service added.');

        redirect('services.php');
    }
}

/* =========================================================
   DELETE SERVICE
========================================================= */

if (isset($_GET['delete'])) {

    db_exec(
        "DELETE FROM services WHERE id = ?",
        'i',
        (int) $_GET['delete']
    );

    flash('success', 'Service deleted.');

    redirect('services.php');
}

/* =========================================================
   FETCH SERVICES
========================================================= */

$rows = db_all(
    "SELECT
        s.*,
        d.name AS department
     FROM services s
     JOIN departments d ON d.id = s.department_id
     ORDER BY s.id DESC"
);

?>

<!-- =========================================================
     SERVICE MANAGEMENT
========================================================= -->

<div class="row g-4">

    <!-- ADD SERVICE FORM -->

    <div class="col-lg-4">

        <div class="panel p-4">

            <h5 class="mb-4">Add Service</h5>

            <form method="post">

                <?= csrf_field() ?>

                <!-- Service Name -->

                <input
                    type="text"
                    name="name"
                    class="form-control mb-2"
                    placeholder="Service name"
                    required
                >

                <!-- Department -->

                <select
                    name="department_id"
                    class="form-select mb-2"
                    required
                >
                    <?php foreach ($deps as $d): ?>

                        <option value="<?= (int) $d['id'] ?>">
                            <?= e($d['name']) ?>
                        </option>

                    <?php endforeach; ?>
                </select>

                <!-- Description -->

                <textarea
                    name="description"
                    class="form-control mb-2"
                    placeholder="Description"
                    rows="3"
                ></textarea>

                <!-- Price -->

                <input
                    type="number"
                    name="price"
                    step="0.01"
                    min="0"
                    class="form-control mb-2"
                    placeholder="Price"
                    required
                >

                <!-- Duration -->

                <input
                    type="number"
                    name="duration"
                    class="form-control mb-3"
                    value="30"
                    min="1"
                    placeholder="Duration in minutes"
                    required
                >

                <button
                    type="submit"
                    class="btn btn-primary rounded-pill"
                >
                    Add Service
                </button>

            </form>

        </div>

    </div>

    <!-- SERVICES TABLE -->

    <div class="col-lg-8">

        <div class="table-card">

            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Department</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($rows)): ?>

                            <?php foreach ($rows as $r): ?>

                                <tr>

                                    <!-- Service Name -->

                                    <td>
                                        <div class="fw-semibold">
                                            <?= e($r['name']) ?>
                                        </div>

                                        <?php if (!empty($r['description'])): ?>
                                            <small class="text-muted">
                                                <?= e($r['description']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Department -->

                                    <td>
                                        <?= e($r['department']) ?>
                                    </td>

                                    <!-- Price -->

                                    <td>
                                        Rs.
                                        <?= number_format(
                                            (float) $r['price']
                                        ) ?>
                                    </td>

                                    <!-- Delete Service -->

                                    <td>
                                        <a
                                            data-confirm="Delete service?"
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
                                    No services found.
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
