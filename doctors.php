
<?php

require_once __DIR__ . '/config/auth.php';

$title = 'Our Doctors';

$q = trim($_GET['q'] ?? '');
$dept = (int) ($_GET['department'] ?? 0);

/* ACTIVE DEPARTMENTS */
$departments = db_all(
    "SELECT id, name
     FROM departments
     WHERE status = 1
     ORDER BY name"
);

/* DOCTORS WITH SEARCH AND FILTERS */
$sql = "
    SELECT
        d.*,
        u.full_name,
        dep.name AS department
    FROM doctors d
    JOIN users u ON u.id = d.user_id
    JOIN departments dep ON dep.id = d.department_id
    WHERE d.status = 1
      AND u.status = 1
";

$types = '';
$params = [];

if ($q !== '') {
    $sql .= " AND (u.full_name LIKE ? OR d.specialty LIKE ?)";
    $like = '%' . $q . '%';
    $types .= 'ss';
    $params[] = $like;
    $params[] = $like;
}

if ($dept > 0) {
    $sql .= " AND d.department_id = ?";
    $types .= 'i';
    $params[] = $dept;
}

$sql .= " ORDER BY u.full_name ASC";

$docs = db_all($sql, $types, ...$params);

require __DIR__ . '/includes/header.php';
?>

<style>
/* =========================================
   COMPACT MODERN CLINIC DOCTOR DIRECTORY
========================================= */

.doctors-page {
    --doc-navy: #08243a;
    --doc-blue: #1597d4;
    --doc-cyan: #45d5df;
    --doc-ink: #163047;
    --doc-muted: #718397;
    --doc-line: #e3edf3;
    padding-bottom: 50px;
    color: var(--doc-ink);
}

/* HERO */

.doctors-hero {
    position: relative;
    overflow: hidden;
    margin: 14px 16px 0;
    padding: 32px 0 28px;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 23px;
    background:
        radial-gradient(circle at 90% 8%, rgba(69,213,223,.19), transparent 30%),
        radial-gradient(circle at 4% 100%, rgba(21,151,212,.18), transparent 35%),
        linear-gradient(125deg, #071e31, #0b3651 58%, #10516b);
    box-shadow: 0 12px 30px rgba(8,36,58,.09);
}

.doctors-hero::before,
.doctors-hero::after {
    content: "";
    position: absolute;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 50%;
    pointer-events: none;
}

.doctors-hero::before {
    width: 270px;
    height: 270px;
    right: -90px;
    top: -170px;
}

.doctors-hero::after {
    width: 190px;
    height: 190px;
    right: -45px;
    top: -115px;
}

.doctors-hero .container {
    position: relative;
    z-index: 1;
}

.doctors-hero-content {
    max-width: 760px;
    margin: 0 auto 22px;
    text-align: center;
}

.doctors-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 12px;
    border: 1px solid rgba(126,235,239,.28);
    border-radius: 30px;
    background: rgba(69,213,223,.10);
    color: #9af2f1;
    font-size: .76rem;
    font-weight: 750;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.doctors-main-title {
    margin: 14px 0 10px;
    color: #fff;
    font-size: clamp(1.9rem, 3.5vw, 2.7rem);
    font-weight: 850;
    letter-spacing: -1px;
    line-height: 1.18;
}

.doctors-main-title span {
    color: #75e1e9;
}

.doctors-hero-description {
    max-width: 570px;
    margin: 0 auto;
    color: rgba(255,255,255,.76);
    font-size: .93rem;
    line-height: 1.75;
}

/* SEARCH PANEL */

.doctors-search-panel {
    display: grid;
    grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr) auto;
    align-items: end;
    gap: 12px;
    max-width: 960px;
    margin: 0 auto;
    padding: 15px;
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 17px;
    background: rgba(255,255,255,.09);
    box-shadow: 0 10px 25px rgba(0,0,0,.09);
    backdrop-filter: blur(12px);
}

.doctors-search-field {
    min-width: 0;
}

.doctors-search-field label {
    display: block;
    margin: 0 0 7px 3px;
    color: #dceff5;
    font-size: .79rem;
    font-weight: 650;
}

.doctors-search-field .form-control,
.doctors-search-field .form-select {
    width: 100%;
    min-height: 43px;
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 10px;
    background-color: #fff;
    color: #163047;
    box-shadow: none;
    font-size: .88rem;
}

.doctors-search-field .form-control:focus,
.doctors-search-field .form-select:focus {
    border-color: #45d5df;
    box-shadow: 0 0 0 3px rgba(69,213,223,.17);
}

.doctors-search-actions {
    display: flex;
    gap: 7px;
}

.doctors-search-button,
.doctors-reset-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 43px;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: .84rem;
    font-weight: 750;
    text-decoration: none;
    white-space: nowrap;
    transition: .2s ease;
}

.doctors-search-button {
    border: 0;
    background: linear-gradient(110deg, #45d5df, #36b6d8);
    color: #06283b;
}

.doctors-search-button:hover {
    background: #7ce8ec;
    color: #06283b;
}

.doctors-reset-button {
    border: 1px solid rgba(255,255,255,.24);
    background: rgba(255,255,255,.07);
    color: #fff;
}

.doctors-reset-button:hover {
    background: rgba(255,255,255,.16);
    color: #fff;
}

/* DIRECTORY HEADING */

.doctors-directory {
    padding-top: 26px;
}

.doctors-directory-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 18px;
}

.doctors-directory-heading h2 {
    margin: 0 0 5px;
    color: var(--doc-navy);
    font-size: 1.4rem;
    font-weight: 850;
    letter-spacing: -.3px;
}

.doctors-directory-heading p {
    margin: 0;
    color: var(--doc-muted);
    font-size: .88rem;
    line-height: 1.6;
}

.doctors-result-count {
    flex-shrink: 0;
    padding: 8px 12px;
    border: 1px solid #dcecf3;
    border-radius: 30px;
    background: #f0f9fc;
    color: #147fa7;
    font-size: .8rem;
    font-weight: 750;
}

/* COMPACT GRID */

.doctors-grid {
    --bs-gutter-x: 1.15rem;
    --bs-gutter-y: 1.15rem;
}

.doctors-grid > [class*="col-"] {
    display: flex;
}

/* COMPACT CARD */

.doctor-directory-card {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--doc-line);
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 5px 20px rgba(8,36,58,.045);
    transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
}

.doctor-directory-card:hover {
    transform: translateY(-3px);
    border-color: #b8e5eb;
    box-shadow: 0 12px 28px rgba(8,36,58,.09);
}

/* SMALLER THUMBNAIL */

.doctor-directory-image-wrap {
    position: relative;
    height: 165px;
    flex-shrink: 0;
    overflow: hidden;
    background: radial-gradient(circle at top, #dff4f8, #eaf3f8);
}

.doctor-directory-image {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 25%;
    transition: transform .35s ease;
}

.doctor-directory-card:hover .doctor-directory-image {
    transform: scale(1.035);
}

/* CARD BODY */

.doctor-directory-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-width: 0;
    padding: 12px;
}

.doctor-directory-specialty {
    margin: 0 0 5px;
    color: #168eb8;
    font-size: .75rem;
    font-weight: 750;
    line-height: 1.45;
    overflow-wrap: anywhere;
}

.doctor-directory-name {
    margin: 0 0 12px;
    color: var(--doc-navy);
    font-size: .98rem;
    font-weight: 850;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

/* HIDE DETAILS SHOWN ON THE PROFILE PAGE */

.doctor-directory-qualification,
.doctor-directory-meta {
    display: none !important;
}

/* COMPACT BUTTONS — ALWAYS TWO COLUMNS */

.doctors-page .doctor-directory-actions {
    display: grid !important;
    grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr) !important;
    align-items: stretch !important;
    gap: 6px !important;
    width: 100% !important;
    min-width: 0 !important;
    margin-top: auto !important;
    padding-top: 0 !important;
}

.doctors-page .doctor-directory-actions > a.btn {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    min-height: 32px !important;
    margin: 0 !important;
    padding: 6px 3px !important;

    border-radius: 8px !important;
    font-family: inherit !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
    letter-spacing: 0 !important;
    text-align: center !important;
    text-decoration: none !important;
    text-transform: none !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    box-sizing: border-box !important;

    box-shadow: none !important;
    transition: background .2s ease, border-color .2s ease !important;
}

.doctors-page .doctor-directory-actions > a.doctor-profile-link {
    border: 1px solid #c9e6ee !important;
    background: #f1fafc !important;
    color: #117fa7 !important;
}

.doctors-page .doctor-directory-actions > a.doctor-profile-link:hover {
    border-color: #1597d4 !important;
    background: #e2f5fa !important;
    color: #08678a !important;
}

.doctors-page .doctor-directory-actions > a.doctor-appointment-link {
    border: 1px solid #1597d4 !important;
    background: linear-gradient(110deg, #1597d4, #1386b8) !important;
    color: #fff !important;
}

.doctors-page .doctor-directory-actions > a.doctor-appointment-link:hover {
    border-color: #087eae !important;
    background: #087eae !important;
    color: #fff !important;
}

/* EMPTY STATE */

.doctors-empty-state {
    padding: 42px 20px;
    border: 1px dashed #cbdfe8;
    border-radius: 20px;
    background: #f8fcfd;
    text-align: center;
}

.doctors-empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    margin: 0 auto 14px;
    border-radius: 17px;
    background: #e3f6fa;
    color: #138cb7;
    font-size: 1.5rem;
}

.doctors-empty-state h3 {
    margin-bottom: 8px;
    color: var(--doc-navy);
    font-size: 1.15rem;
    font-weight: 800;
}

.doctors-empty-state p {
    margin: 0 0 18px;
    color: var(--doc-muted);
    line-height: 1.7;
}

/* TABLET */

@media (max-width: 991.98px) {
    .doctors-hero {
        padding: 29px 0 25px;
    }

    .doctors-search-panel {
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    }

    .doctors-search-actions {
        grid-column: 1 / -1;
    }

    .doctors-search-button,
    .doctors-reset-button {
        flex: 1;
    }

    .doctor-directory-image-wrap {
        height: 165px;
    }
}

/* MOBILE */

@media (max-width: 575.98px) {
    .doctors-hero {
        margin: 10px 10px 0;
        padding: 25px 0 20px;
        border-radius: 19px;
    }

    .doctors-hero-content {
        margin-bottom: 18px;
    }

    .doctors-main-title {
        font-size: 1.9rem;
    }

    .doctors-hero-description {
        padding: 0 8px;
        font-size: .88rem;
    }

    .doctors-search-panel {
        grid-template-columns: minmax(0, 1fr);
        gap: 11px;
        padding: 13px;
    }

    .doctors-search-actions {
        grid-column: auto;
    }

    .doctors-directory {
        padding-top: 22px;
    }

    .doctors-directory-heading {
        align-items: flex-start;
        flex-direction: column;
        gap: 10px;
    }

    .doctors-directory-heading h2 {
        font-size: 1.3rem;
    }

    .doctors-grid {
        --bs-gutter-x: .8rem;
        --bs-gutter-y: .8rem;
    }

    .doctor-directory-image-wrap {
        height: 150px;
    }

    .doctor-directory-body {
        padding: 10px;
    }

    .doctor-directory-specialty {
        font-size: .72rem;
    }

    .doctor-directory-name {
        margin-bottom: 10px;
        font-size: .91rem;
    }

    .doctors-page .doctor-directory-actions {
        gap: 5px !important;
    }

    .doctors-page .doctor-directory-actions > a.btn {
        min-height: 31px !important;
        padding: 5px 2px !important;
        font-size: 9px !important;
    }
}
</style>

<section class="doctors-page">

    <!-- HERO AND SEARCH -->
    <section class="doctors-hero">
        <div class="container">

            <div class="doctors-hero-content">
                <span class="doctors-kicker">
                    <span aria-hidden="true">✚</span>
                    Our Medical Team
                </span>

                <h1 class="doctors-main-title">
                    Meet Our <span>Doctors</span>
                </h1>

                <p class="doctors-hero-description">
                    Find the right specialist for your healthcare needs.
                    Explore our doctors and book your consultation with confidence.
                </p>
            </div>

            <form method="GET" action="doctors.php" class="doctors-search-panel">

                <div class="doctors-search-field">
                    <label for="doctor-search">Search Doctors</label>

                    <input
                        id="doctor-search"
                        type="search"
                        name="q"
                        class="form-control"
                        placeholder="Enter doctor name or specialty"
                        value="<?= e($q) ?>"
                    >
                </div>

                <div class="doctors-search-field">
                    <label for="doctor-department">Medical Department</label>

                    <select
                        id="doctor-department"
                        name="department"
                        class="form-select"
                    >
                        <option value="0">All Departments</option>

                        <?php foreach ($departments as $x): ?>
                            <option
                                value="<?= (int) $x['id'] ?>"
                                <?= $dept === (int) $x['id'] ? 'selected' : '' ?>
                            >
                                <?= e($x['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="doctors-search-actions">
                    <button type="submit" class="doctors-search-button">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>
                        Search
                    </button>

                    <?php if ($q !== '' || $dept > 0): ?>
                        <a href="doctors.php" class="doctors-reset-button">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    </section>

    <!-- DOCTOR DIRECTORY -->
    <section class="doctors-directory">
        <div class="container">

            <div class="doctors-directory-heading">
                <div>
                    <h2>
                        <?= ($q !== '' || $dept > 0)
                            ? 'Search Results'
                            : 'Our Healthcare Specialists' ?>
                    </h2>

                    <p>
                        <?= ($q !== '' || $dept > 0)
                            ? 'Doctors matching your selected filters.'
                            : 'Explore our medical team and find a specialist for your needs.' ?>
                    </p>
                </div>

                <div class="doctors-result-count">
                    <?= count($docs) ?>
                    <?= count($docs) === 1 ? 'Doctor' : 'Doctors' ?>
                </div>
            </div>

            <?php if ($docs): ?>

                <div class="row g-4 doctors-grid">

                    <?php foreach ($docs as $d): ?>

                        <?php
                        $image = trim((string) ($d['image'] ?? ''));

                        if ($image === '') {
                            $image = 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=700&q=80';
                        }

                        $doctorName = trim((string) $d['full_name']);
                        $specialty = trim((string) ($d['specialty'] ?? ''));
                        ?>

                        <div class="col-6 col-lg-3">
                            <article class="doctor-directory-card">

                                <div class="doctor-directory-image-wrap">
                                    <img
                                        src="<?= e($image) ?>"
                                        alt="<?= e($doctorName) ?>"
                                        class="doctor-directory-image"
                                        loading="lazy"
                                        onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=700&q=80';"
                                    >
                                </div>

                                <div class="doctor-directory-body">

                                    <div class="doctor-directory-specialty">
                                        <?= e($specialty !== '' ? $specialty : 'Medical Specialist') ?>
                                    </div>

                                    <h3 class="doctor-directory-name">
                                        <?= e($doctorName) ?>
                                    </h3>

                                    <div class="doctor-directory-actions">

                                        <a
                                            href="doctor-profile.php?id=<?= (int) $d['id'] ?>"
                                            class="btn doctor-profile-link"
                                        >View Profile</a>

                                        <a
                                            href="appointment.php?doctor=<?= (int) $d['id'] ?>"
                                            class="btn doctor-appointment-link"
                                        >Book Appointment</a>

                                    </div>

                                </div>
                            </article>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="doctors-empty-state">
                    <div class="doctors-empty-icon" aria-hidden="true">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>

                    <h3>No Doctors Found</h3>

                    <p>
                        We couldn't find any doctors matching your search.
                        Try another name or choose a different department.
                    </p>

                    <a href="doctors.php" class="btn btn-primary rounded-pill px-4">
                        View All Doctors
                    </a>
                </div>

            <?php endif; ?>

        </div>
    </section>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
