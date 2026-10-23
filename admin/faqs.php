
<?php

$title = 'FAQs';

require __DIR__ . '/header.php';

/* =========================================================
   ADD NEW FAQ
========================================================= */

if (is_post()) {

    verify_csrf();

    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');

    if ($question !== '' && $answer !== '') {

        db_exec(
            "INSERT INTO faqs (question, answer)
             VALUES (?, ?)",
            'ss',
            $question,
            $answer
        );

        flash('success', 'FAQ added.');

        redirect('faqs.php');
    }
}

/* =========================================================
   DELETE FAQ
========================================================= */

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    db_exec(
        "DELETE FROM faqs WHERE id = ?",
        'i',
        $id
    );

    flash('success', 'FAQ deleted.');

    redirect('faqs.php');
}

/* =========================================================
   FETCH ALL FAQs
========================================================= */

$rows = db_all(
    "SELECT *
     FROM faqs
     ORDER BY id DESC"
);

?>

<!-- =========================================================
     FAQ MANAGEMENT
========================================================= -->

<div class="row g-4">

    <!-- ADD FAQ FORM -->
    <div class="col-lg-5">

        <div class="panel p-4">

            <h5 class="mb-3">
                Add FAQ
            </h5>

            <form method="post">

                <?= csrf_field() ?>

                <!-- Question -->
                <input
                    type="text"
                    name="question"
                    class="form-control mb-2"
                    placeholder="Enter Question"
                    required
                >

                <!-- Answer -->
                <textarea
                    name="answer"
                    class="form-control mb-3"
                    rows="5"
                    placeholder="Enter Answer"
                    required
                ></textarea>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="btn btn-primary rounded-pill"
                >
                    Add FAQ
                </button>

            </form>

        </div>

    </div>

    <!-- FAQ LIST -->
    <div class="col-lg-7">

        <div class="table-card">

            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Question & Answer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($rows)): ?>

                            <?php foreach ($rows as $r): ?>

                                <tr>

                                    <!-- FAQ Question and Answer -->
                                    <td>
                                        <strong>
                                            <?= e($r['question']) ?>
                                        </strong>

                                        <br>

                                        <small class="text-secondary">
                                            <?= e($r['answer']) ?>
                                        </small>
                                    </td>

                                    <!-- Delete Action -->
                                    <td>
                                        <a
                                            href="?delete=<?= (int) $r['id'] ?>"
                                            data-confirm="Delete FAQ?"
                                            class="btn btn-sm btn-outline-danger"
                                        >
                                            Delete
                                        </a>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="2" class="text-center py-4">
                                    No FAQs found.
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
