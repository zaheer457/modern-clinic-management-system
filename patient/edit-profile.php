
<?php
require_once __DIR__ . '/../config/auth.php';

require_role('patient');

$userId = (int) current_user()['id'];

$errors = [];
$successMessage = '';

/* ---------------------------------------------------------
   LOAD PROFILE
--------------------------------------------------------- */

$patient = db_one(
    "SELECT
        u.id AS user_id,
        u.full_name,
        u.email,
        u.phone,
        p.id AS patient_id,
        p.date_of_birth,
        p.gender,
        p.address,
        p.profile_image
     FROM users u
     LEFT JOIN patients p ON p.user_id = u.id
     WHERE u.id = ?
     LIMIT 1",
    "i",
    $userId
);

if (!$patient) {
    exit('Patient profile not found. Please contact the administrator.');
}

/* ---------------------------------------------------------
   SAVE PROFILE
--------------------------------------------------------- */

if (is_post()) {
    verify_csrf();

    $fullName = trim($_POST['full_name'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $phone    = trim($_POST['phone'] ?? '');
    $dob      = trim($_POST['date_of_birth'] ?? '');
    $gender   = trim($_POST['gender'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }

    if ($gender !== '' && !in_array($gender, ['Male', 'Female', 'Other'], true)) {
        $errors[] = 'Please select a valid gender.';
    }

    if ($dob !== '') {
        $date = DateTime::createFromFormat('!Y-m-d', $dob);

        if (!$date || $date->format('Y-m-d') !== $dob) {
            $errors[] = 'Please select a valid date of birth.';
        } elseif ($dob > date('Y-m-d')) {
            $errors[] = 'Date of birth cannot be in the future.';
        }
    }

    // Check whether another account already uses this email.
    if (!$errors) {
        $existingEmail = db_one(
            "SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1",
            "si",
            $email,
            $userId
        );

        if ($existingEmail) {
            $errors[] = 'This email address is already registered to another account.';
        }
    }

    // Preserve submitted values if validation fails.
    $patient['full_name'] = $fullName;
    $patient['email'] = $email;
    $patient['phone'] = $phone;
    $patient['date_of_birth'] = $dob;
    $patient['gender'] = $gender;
    $patient['address'] = $address;

    if (!$errors) {
        $db = $GLOBALS['db'] ?? null;
        $transactionStarted = false;

        try {
            if (!($db instanceof mysqli)) {
                throw new RuntimeException(
                    'Database connection $db is not a valid MySQLi connection.'
                );
            }

            // Upload only when the user selects a new picture.
            $newImage = null;

            if (
                isset($_FILES['profile_image']) &&
                ($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
            ) {
                $newImage = upload_image('profile_image', 'patients');

                if (!$newImage) {
                    throw new RuntimeException(
                        'The selected profile picture could not be uploaded.'
                    );
                }
            }

            $db->begin_transaction();
            $transactionStarted = true;

            // Update name, email and phone.
            db_exec(
                "UPDATE users
                 SET full_name = ?, email = ?, phone = ?
                 WHERE id = ?",
                "sssi",
                $fullName,
                $email,
                $phone,
                $userId
            );

            // Ensure a patient record exists.
            if (!empty($patient['patient_id'])) {
                if ($newImage !== null) {
                    db_exec(
                        "UPDATE patients
                         SET date_of_birth = ?,
                             gender = ?,
                             address = ?,
                             profile_image = ?
                         WHERE user_id = ?",
                        "ssssi",
                        $dob !== '' ? $dob : null,
                        $gender !== '' ? $gender : null,
                        $address,
                        $newImage,
                        $userId
                    );
                } else {
                    db_exec(
                        "UPDATE patients
                         SET date_of_birth = ?,
                             gender = ?,
                             address = ?
                         WHERE user_id = ?",
                        "sssi",
                        $dob !== '' ? $dob : null,
                        $gender !== '' ? $gender : null,
                        $address,
                        $userId
                    );
                }
            } else {
                db_exec(
                    "INSERT INTO patients
                     (user_id, date_of_birth, gender, address, profile_image)
                     VALUES (?, ?, ?, ?, ?)",
                    "issss",
                    $userId,
                    $dob !== '' ? $dob : null,
                    $gender !== '' ? $gender : null,
                    $address,
                    $newImage
                );
            }

            $db->commit();
            $transactionStarted = false;

            // Update session data so the new name/email appears immediately.
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['full_name'] = $fullName;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['phone'] = $phone;
            }

            flash('success', 'Profile updated successfully.');
            redirect('profile.php');
            exit;

        } catch (Throwable $e) {
            if ($transactionStarted && $db instanceof mysqli) {
                try {
                    $db->rollback();
                } catch (Throwable $rollbackError) {
                    error_log('Profile rollback failed: ' . $rollbackError->getMessage());
                }
            }

            error_log('Patient profile update error: ' . $e->getMessage());

            $errors[] = 'Your profile could not be saved. Please check your details and try again.';
        }
    }
}

$title = 'Edit Profile';
$base = '../';
$hideNavbar = false;

$name = trim((string) ($patient['full_name'] ?? 'Patient'));
$initial = strtoupper(substr($name, 0, 1));
$image = trim((string) ($patient['profile_image'] ?? ''));

require __DIR__ . '/../includes/header.php';
?>

<style>


.edit-profile-page {
    min-height: 75vh;
    padding: 24px 16px 60px;
    background:
        radial-gradient(ellipse at top left, rgba(137,120,247,.12), transparent 45%),
        #f4f7ff;
}

.ep-wrap {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
}

.ep-heading {
    margin-bottom: 24px;
}

.ep-heading .eyebrow {
    margin-bottom: 8px;
    color: #4263eb;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.ep-heading h1 {
    margin: 0 0 8px;
    color: #202e58;
    font-size: clamp(28px, 4vw, 38px);
    font-weight: 800;
}

.ep-heading p {
    margin: 0;
    color: #6d7895;
    line-height: 1.7;
}

.ep-card {
    overflow: hidden;
    border: 1px solid #e0ebf2;
    border-radius: 22px;
    background: #fff;
    box-shadow: 0 16px 44px rgba(8,36,58,.08);
}

.ep-banner {
    display: flex;
    align-items: center;
    gap: 17px;
    padding: 25px 30px;
    color: #fff;
    background: linear-gradient(115deg, #202e58, #105579);
}

.ep-avatar {
    display: flex;
    width: 76px;
    height: 76px;
    flex: 0 0 76px;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border: 2px solid rgba(255,255,255,.4);
    border-radius: 19px;
    background: linear-gradient(135deg, #4263eb, #45d5df);
    color: #fff;
    font-size: 29px;
    font-weight: 800;
}

.ep-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ep-banner h2 {
    margin: 0 0 6px;
    font-size: 21px;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.ep-banner p {
    margin: 0;
    color: #d4edf8;
    font-size: 13px;
    overflow-wrap: anywhere;
}

.ep-form {
    padding: 30px;
}

.ep-section-title {
    margin: 0 0 22px;
    padding-bottom: 14px;
    border-bottom: 1px solid #eaf1f5;
    color: #202e58;
    font-size: 16px;
    font-weight: 800;
}

.ep-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 21px;
}

.ep-field {
    min-width: 0;
}

.ep-field.full {
    grid-column: 1 / -1;
}

.ep-field label {
    display: block;
    margin-bottom: 9px;
    color: #263655;
    font-size: 13px;
    font-weight: 750;
}

.ep-field input,
.ep-field select,
.ep-field textarea {
    box-sizing: border-box;
    width: 100%;
    border: 1px solid #dce8ef;
    border-radius: 11px;
    outline: none;
    background: #f9fcfe;
    color: #263655;
    font: inherit;
    font-size: 14px;
    transition: .2s;
}

.ep-field input,
.ep-field select {
    min-height: 48px;
    padding: 12px 14px;
}

.ep-field textarea {
    min-height: 105px;
    padding: 13px 14px;
    resize: vertical;
}

.ep-field input:focus,
.ep-field select:focus,
.ep-field textarea:focus {
    border-color: #4263eb;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(137,120,247,.12);
}

.ep-field input[type="file"] {
    padding: 10px;
    background: #fff;
}

.ep-hint {
    display: block;
    margin-top: 8px;
    color: #788c9b;
    font-size: 12px;
    line-height: 1.6;
}

.ep-alert {
    margin-bottom: 22px;
    padding: 14px 16px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.7;
}

.ep-alert.error {
    border: 1px solid #ffd2d7;
    background: #fff1f2;
    color: #9f2430;
}

.ep-actions {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 30px;
    padding-top: 24px;
    border-top: 1px solid #eaf1f5;
}

.ep-btn {
    display: inline-flex;
    min-height: 46px;
    align-items: center;
    justify-content: center;
    padding: 0 21px;
    border: 0;
    border-radius: 11px;
    cursor: pointer;
    font: inherit;
    font-size: 14px;
    font-weight: 750;
    text-decoration: none;
    transition: transform .2s;
}

.ep-btn:hover {
    transform: translateY(-1px);
}

.ep-btn-save {
    color: #fff;
    background: linear-gradient(110deg, #117fbd, #4263eb, #25b6cc);
    box-shadow: 0 8px 20px rgba(66,99,235,.22);
}

.ep-btn-cancel {
    color: #23445a;
    background: #edf1ff;
}

@media (max-width: 620px) {
    .edit-profile-page {
        padding: 18px 12px 40px;
    }

    .ep-banner {
        padding: 20px;
    }

    .ep-avatar {
        width: 62px;
        height: 62px;
        flex-basis: 62px;
    }

    .ep-form {
        padding: 22px 18px;
    }

    .ep-grid {
        grid-template-columns: 1fr;
        gap: 17px;
    }

    .ep-field.full {
        grid-column: auto;
    }

    .ep-actions {
        flex-direction: column-reverse;
    }

    .ep-btn {
        width: 100%;
        box-sizing: border-box;
    }
}


/* =========================================
   EDIT PROFILE — COMPACT PREMIUM DESIGN
========================================= */

.edit-profile-page {
    min-height: 100vh;
    padding: 32px 16px 48px;
    background:
        radial-gradient(
            circle at 12% 8%,
            rgba(21, 151, 212, 0.13),
            transparent 35%
        ),
        radial-gradient(
            circle at 90% 85%,
            rgba(69, 213, 223, 0.10),
            transparent 32%
        ),
        #f4f7ff;
}

.ep-wrap {
    width: 100%;
    max-width: 760px;
    margin: 0 auto;
}

.ep-heading {
    margin-bottom: 20px;
}

.ep-heading .eyebrow {
    display: inline-block;
    margin-bottom: 8px;
    padding: 7px 12px;
    border: 1px solid rgba(21, 151, 212, 0.18);
    border-radius: 30px;
    background: rgba(21, 151, 212, 0.08);
    letter-spacing: 1.5px;
}

.ep-heading h1 {
    font-size: clamp(27px, 4vw, 34px);
    letter-spacing: -0.8px;
}

.ep-heading p {
    font-size: 14px;
}

/* Main profile container */
.ep-card {
    border: 1px solid rgba(21, 151, 212, 0.15);
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.97);
    box-shadow:
        0 18px 45px rgba(8, 36, 58, 0.09),
        0 3px 10px rgba(8, 36, 58, 0.03);
    transition: box-shadow 0.25s ease;
}

.ep-card:hover {
    box-shadow: 0 22px 50px rgba(8, 36, 58, 0.12);
}

/* Compact gradient profile banner */
.ep-banner {
    gap: 15px;
    padding: 21px 25px;
    background:
        radial-gradient(
            circle at 90% 10%,
            rgba(69, 213, 223, 0.19),
            transparent 35%
        ),
        linear-gradient(120deg, #202e58, #105579 70%, #117f9b);
}

.ep-avatar {
    width: 66px;
    height: 66px;
    flex-basis: 66px;
    border-radius: 17px;
    box-shadow: 0 5px 18px rgba(0, 0, 0, 0.13);
}

.ep-banner h2 {
    font-size: 19px;
}

.ep-banner p {
    font-size: 12px;
}

/* Less empty space inside the form */
.ep-form {
    padding: 24px 25px;
}

.ep-section-title {
    margin-bottom: 19px;
    padding-bottom: 12px;
    font-size: 15px;
}

.ep-grid {
    gap: 17px 19px;
}

.ep-field label {
    margin-bottom: 7px;
    font-size: 12px;
}

.ep-field input,
.ep-field select {
    min-height: 44px;
    padding: 10px 12px;
    border-radius: 10px;
    font-size: 13px;
}

.ep-field textarea {
    min-height: 85px;
    padding: 11px 12px;
    border-radius: 10px;
    font-size: 13px;
}

.ep-field input[type="file"] {
    min-height: 44px;
    padding: 8px;
}

.ep-field input:focus,
.ep-field select:focus,
.ep-field textarea:focus {
    border-color: #4263eb;
    box-shadow: 0 0 0 3px rgba(21, 151, 212, 0.12);
}

.ep-hint {
    margin-top: 6px;
    font-size: 11px;
}

/* Neater action buttons */
.ep-actions {
    gap: 10px;
    margin-top: 23px;
    padding-top: 19px;
}

.ep-btn {
    min-height: 43px;
    padding: 0 19px;
    border-radius: 10px;
    font-size: 13px;
}

.ep-btn-save {
    background: linear-gradient(110deg, #117fbd, #4263eb, #25b6cc);
    box-shadow: 0 5px 15px rgba(21, 151, 212, 0.19);
}

.ep-btn-save:hover {
    box-shadow: 0 8px 20px rgba(21, 151, 212, 0.28);
}

.ep-btn-cancel {
    border: 1px solid #dce8ef;
    background: #f3f8fb;
}

@media (max-width: 620px) {
    .edit-profile-page {
        padding: 24px 12px 35px;
    }

    .ep-heading {
        margin-bottom: 17px;
    }

    .ep-banner {
        padding: 18px;
    }

    .ep-form {
        padding: 21px 17px;
    }

    .ep-grid {
        gap: 16px;
    }

    .ep-actions {
        gap: 10px;
    }
}


</style>

<section class="edit-profile-page">
    <div class="ep-wrap">

        <div class="ep-heading">
            <div class="eyebrow">Patient Account</div>
            <h1>Edit Your Profile</h1>
            <p>Update your contact details and personal information below.</p>
        </div>

        <section class="ep-card">

            <div class="ep-banner">
                <div class="ep-avatar">
                    <?php if ($image !== ''): ?>
    <img
        src="../<?= e(ltrim($image, '/')) ?>"
        alt="Profile picture"
        onerror="this.remove(); this.parentElement.textContent='<?= e($initial) ?>';"
    >
<?php else: ?>
    <?= e($initial) ?>
<?php endif; ?>
                </div>

                <div>
                    <h2><?= e($name) ?></h2>
                    <p><?= e($patient['email'] ?? '') ?></p>
                </div>
            </div>

            <form class="ep-form" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <?php if ($errors): ?>
                    <div class="ep-alert error" role="alert">
                        <strong>Your changes were not saved:</strong>
                        <ul style="margin:8px 0 0;padding-left:20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <h3 class="ep-section-title">Personal Information</h3>

                <div class="ep-grid">

                    <div class="ep-field">
                        <label for="full_name">Full Name *</label>
                        <input
                            id="full_name"
                            name="full_name"
                            type="text"
                            maxlength="150"
                            value="<?= e($patient['full_name'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="ep-field">
                        <label for="email">Email Address *</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            maxlength="190"
                            value="<?= e($patient['email'] ?? '') ?>"
                            autocomplete="email"
                            required
                        >
                        <small class="ep-hint">
                            Use an email address that belongs to you. An address
                            already registered to another account cannot be used.
                        </small>
                    </div>

                    <div class="ep-field">
                        <label for="phone">Phone Number *</label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            maxlength="30"
                            value="<?= e($patient['phone'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="ep-field">
                        <label for="date_of_birth">Date of Birth</label>
                        <input
                            id="date_of_birth"
                            name="date_of_birth"
                            type="date"
                            max="<?= date('Y-m-d') ?>"
                            value="<?= e($patient['date_of_birth'] ?? '') ?>"
                        >
                    </div>

                    <div class="ep-field">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">Select gender</option>
                            <?php foreach (['Male', 'Female', 'Other'] as $option): ?>
                                <option
                                    value="<?= e($option) ?>"
                                    <?= ($patient['gender'] ?? '') === $option ? 'selected' : '' ?>
                                >
                                    <?= e($option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ep-field full">
                        <label for="address">Address</label>
                        <textarea
                            id="address"
                            name="address"
                            maxlength="1000"
                            placeholder="Enter your residential address"
                        ><?= e($patient['address'] ?? '') ?></textarea>
                    </div>

                    <div class="ep-field full">
                        <label for="profile_image">Profile Picture</label>
                        <input
                            id="profile_image"
                            name="profile_image"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >
                        <small class="ep-hint">
                            Select a new image only if you want to replace your current picture.
                        </small>
                    </div>

                </div>

                <div class="ep-actions">
                    <a class="ep-btn ep-btn-cancel" href="profile.php">
                        Cancel
                    </a>

                    <button class="ep-btn ep-btn-save" type="submit">
                        Save Changes
                    </button>
                </div>
            </form>
        </section>
    </div>
</section>

<script>
(function () {
    const input = document.getElementById('profile_image');
    const avatar = document.querySelector('.ep-avatar');
    if (!input || !avatar) return;

    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;

        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            this.value = '';
            alert('Please choose a JPG, PNG, or WEBP image.');
            return;
        }
        if (file.size > 3 * 1024 * 1024) {
            this.value = '';
            alert('Profile pictures must be 3 MB or smaller.');
            return;
        }

        const previewUrl = URL.createObjectURL(file);
        let preview = avatar.querySelector('img');
        if (!preview) {
            avatar.textContent = '';
            preview = document.createElement('img');
            preview.alt = 'Selected profile picture preview';
            avatar.appendChild(preview);
        }
        preview.onload = function () { URL.revokeObjectURL(previewUrl); };
        preview.src = previewUrl;
    });
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
