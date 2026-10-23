
<?php

require_once __DIR__ . '/../config/auth.php';

require_admin();

/* =========================================================
   DOCTOR ID AND EXISTING DATA
========================================================= */

$id = (int) ($_GET['id'] ?? 0);

if ($id < 0) {
    $id = 0;
}

$row = null;

if ($id > 0) {
    $row = db_one(
        "SELECT
            d.*,
            u.full_name,
            u.email,
            u.phone
         FROM doctors d
         JOIN users u ON u.id = d.user_id
         WHERE d.id = ?",
        "i",
        $id
    );

    if (!$row) {
        flash('danger', 'Doctor not found.');
        redirect('doctors.php');
    }
}

$title = $id > 0 ? 'Edit Doctor' : 'Add Doctor';

/* =========================================================
   FORM DEFAULT VALUES
========================================================= */

$name = $row['full_name'] ?? '';
$email = $row['email'] ?? '';
$phone = $row['phone'] ?? '';
$dep = (int) ($row['department_id'] ?? 0);
$spec = $row['specialty'] ?? '';
$qual = $row['qualification'] ?? '';
$exp = (int) ($row['experience'] ?? 0);
$bio = $row['biography'] ?? '';
$fee = $row['consultation_fee'] ?? 0;
$langs = $row['languages'] ?? 'English, Urdu';

/* =========================================================
   HANDLE FORM SUBMISSION BEFORE HEADER OUTPUT
========================================================= */

if (is_post()) {

    verify_csrf();

    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dep = (int) ($_POST['department_id'] ?? 0);
    $spec = trim($_POST['specialty'] ?? '');
    $qual = trim($_POST['qualification'] ?? '');
    $exp = (int) ($_POST['experience'] ?? 0);
    $bio = trim($_POST['biography'] ?? '');
    $langs = trim($_POST['languages'] ?? '');

    $feeInput = trim($_POST['consultation_fee'] ?? '0');

    $fee = is_numeric($feeInput) ? (float) $feeInput : -1;

    $img = null;
    $transactionStarted = false;

    try {

        /* Validate required fields */

        if ($name === '' || $email === '' || $spec === '') {
            throw new RuntimeException(
                'Please complete all required fields.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'Please enter a valid email address.'
            );
        }

        if ($dep <= 0) {
            throw new RuntimeException(
                'Please select a department.'
            );
        }

        if ($exp < 0 || $fee < 0) {
            throw new RuntimeException(
                'Experience and consultation fee cannot be negative.'
            );
        }

        /* Verify department exists */

        $department = db_one(
            "SELECT id FROM departments WHERE id = ?",
            "i",
            $dep
        );

        if (!$department) {
            throw new RuntimeException(
                'The selected department does not exist.'
            );
        }

        /* =================================================
           CHECK EMAIL UNIQUENESS
        ================================================= */

        if ($id > 0) {

            $existingEmail = db_one(
                "SELECT id
                 FROM users
                 WHERE email = ?
                   AND id <> (
                       SELECT user_id
                       FROM doctors
                       WHERE id = ?
                   )
                 LIMIT 1",
                "si",
                $email,
                $id
            );

        } else {

            $existingEmail = db_one(
                "SELECT id
                 FROM users
                 WHERE email = ?
                 LIMIT 1",
                "s",
                $email
            );
        }

        if ($existingEmail) {
            throw new RuntimeException(
                'This email address is already registered. Please use another email.'
            );
        }

        /* Upload new image only if provided */

        $img = upload_image('image', 'doctors');

        global $db;

        if (!$db) {
            throw new RuntimeException(
                'Database connection is unavailable.'
            );
        }

        $db->begin_transaction();
        $transactionStarted = true;

        /* =================================================
           EDIT EXISTING DOCTOR
        ================================================= */

        if ($id > 0) {

            $doctor = db_one(
                "SELECT user_id
                 FROM doctors
                 WHERE id = ?",
                "i",
                $id
            );

            if (!$doctor) {
                throw new RuntimeException(
                    'Doctor not found.'
                );
            }

            $userId = (int) $doctor['user_id'];

            /* Update user account */

            db_exec(
                "UPDATE users
                 SET full_name = ?,
                     email = ?,
                     phone = ?
                 WHERE id = ?",
                "sssi",
                $name,
                $email,
                $phone,
                $userId
            );

            /* Update doctor details */

            if ($img) {

                db_exec(
                    "UPDATE doctors
                     SET department_id = ?,
                         qualification = ?,
                         specialty = ?,
                         experience = ?,
                         biography = ?,
                         consultation_fee = ?,
                         languages = ?,
                         image = ?
                     WHERE id = ?",
                    "issisdssi",
                    $dep,
                    $qual,
                    $spec,
                    $exp,
                    $bio,
                    $fee,
                    $langs,
                    $img,
                    $id
                );

            } else {

                db_exec(
                    "UPDATE doctors
                     SET department_id = ?,
                         qualification = ?,
                         specialty = ?,
                         experience = ?,
                         biography = ?,
                         consultation_fee = ?,
                         languages = ?
                     WHERE id = ?",
                    "issidsdi",
                    $dep,
                    $qual,
                    $spec,
                    $exp,
                    $bio,
                    $fee,
                    $langs,
                    $id
                );
            }

        } else {

            /* =============================================
               CREATE NEW DOCTOR ACCOUNT
            ============================================= */

            $hash = safe_password_hash('Doctor@123');

            db_exec(
                "INSERT INTO users
                    (full_name, email, phone, password, role)
                 VALUES (?, ?, ?, ?, 'doctor')",
                "ssss",
                $name,
                $email,
                $phone,
                $hash
            );

            $userId = (int) $db->insert_id;

            db_exec(
                "INSERT INTO doctors
                    (
                        user_id,
                        department_id,
                        qualification,
                        specialty,
                        experience,
                        biography,
                        consultation_fee,
                        languages,
                        image
                    )
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                "iissisdss",
                $userId,
                $dep,
                $qual,
                $spec,
                $exp,
                $bio,
                $fee,
                $langs,
                $img
            );
        }

        $db->commit();
        $transactionStarted = false;

        flash(
            'success',
            $id > 0
                ? 'Doctor details updated successfully.'
                : 'Doctor added successfully.'
        );

        redirect('doctors.php');

    } catch (Throwable $e) {

        if ($transactionStarted && isset($db) && $db) {
            try {
                $db->rollback();
            } catch (Throwable $rollbackError) {
                error_log(
                    'Doctor transaction rollback failed: '
                    . $rollbackError->getMessage()
                );
            }
        }

        error_log(
            'Doctor form save failed: '
            . $e->getMessage()
        );

        $message = $e instanceof RuntimeException
            ? $e->getMessage()
            : 'Could not save doctor. Please check the server error log.';

        flash('danger', $message);
    }
}

/* =========================================================
   LOAD DEPARTMENTS AND RENDER HEADER
========================================================= */

$deps = db_all(
    "SELECT id, name
     FROM departments
     ORDER BY name"
);

require __DIR__ . '/header.php';

?>

<!-- DOCTOR FORM -->

<div class="panel p-4">

    <form
        method="post"
        enctype="multipart/form-data"
    >

        <?= csrf_field() ?>

        <div class="row g-3">

            <!-- Full Name -->
            <div class="col-md-6">
                <label class="form-label">Full name *</label>

                <input
                    type="text"
                    name="full_name"
                    class="form-control"
                    value="<?= e($name) ?>"
                    required
                >
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <label class="form-label">Email *</label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="<?= e($email) ?>"
                    required
                >
            </div>

            <!-- Phone -->
            <div class="col-md-6">
                <label class="form-label">Phone</label>

                <input
                    type="text"
                    name="phone"
                    class="form-control"
                    value="<?= e($phone) ?>"
                >
            </div>

            <!-- Department -->
            <div class="col-md-6">
                <label class="form-label">Department *</label>

                <select
                    name="department_id"
                    class="form-select"
                    required
                >
                    <option value="">Select department</option>

                    <?php foreach ($deps as $department): ?>
                        <option
                            value="<?= (int) $department['id'] ?>"
                            <?= $dep === (int) $department['id']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= e($department['name']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <!-- Specialty -->
            <div class="col-md-6">
                <label class="form-label">Specialty *</label>

                <input
                    type="text"
                    name="specialty"
                    class="form-control"
                    value="<?= e($spec) ?>"
                    required
                >
            </div>

            <!-- Qualification -->
            <div class="col-md-6">
                <label class="form-label">Qualification</label>

                <input
                    type="text"
                    name="qualification"
                    class="form-control"
                    value="<?= e($qual) ?>"
                >
            </div>

            <!-- Experience -->
            <div class="col-md-4">
                <label class="form-label">Experience (years)</label>

                <input
                    type="number"
                    name="experience"
                    class="form-control"
                    value="<?= e((string) $exp) ?>"
                    min="0"
                >
            </div>

            <!-- Fee -->
            <div class="col-md-4">
                <label class="form-label">Consultation fee</label>

                <input
                    type="number"
                    name="consultation_fee"
                    class="form-control"
                    value="<?= e((string) $fee) ?>"
                    min="0"
                    step="0.01"
                >
            </div>

            <!-- Languages -->
            <div class="col-md-4">
                <label class="form-label">Languages</label>

                <input
                    type="text"
                    name="languages"
                    class="form-control"
                    value="<?= e($langs) ?>"
                >
            </div>

            <!-- Biography -->
            <div class="col-12">
                <label class="form-label">Biography</label>

                <textarea
                    name="biography"
                    class="form-control"
                    rows="5"
                ><?= e($bio) ?></textarea>
            </div>

            <!-- Profile Image -->
            <div class="col-12">
                <label class="form-label">Profile image</label>

                <input
                    type="file"
                    name="image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <?php if (!empty($row['image'])): ?>
                    <div class="mt-3">
                        <img
                            src="../<?= e($row['image']) ?>"
                            alt="Doctor profile"
                            style="
                                width: 90px;
                                height: 90px;
                                object-fit: cover;
                                border-radius: 16px;
                            "
                        >
                        <p class="small text-secondary mt-2 mb-0">
                            Choose a new image only if you want to replace the current one.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Buttons -->
            <div class="col-12">

                <button
                    type="submit"
                    class="btn btn-primary rounded-pill"
                >
                    <i class="fa-solid fa-floppy-disk me-2"></i>
                    Save Doctor
                </button>

                <a
                    href="doctors.php"
                    class="btn btn-light rounded-pill"
                >
                    Cancel
                </a>

            </div>

        </div>

    </form>

</div>

<?php require __DIR__ . '/footer.php'; ?>
