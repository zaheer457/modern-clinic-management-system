
<?php

$title = 'Messages';

require __DIR__ . '/header.php';

/* =========================================================
   MARK MESSAGE AS READ
========================================================= */

if (isset($_GET['read'])) {

    $id = (int) $_GET['read'];

    db_exec(
        "UPDATE messages
         SET is_read = 1
         WHERE id = ?",
        'i',
        $id
    );

}

/* =========================================================
   DELETE MESSAGE
========================================================= */

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    db_exec(
        "DELETE FROM messages
         WHERE id = ?",
        'i',
        $id
    );

    flash('success', 'Message deleted.');

    redirect('messages.php');
}

/* =========================================================
   FETCH CONTACT MESSAGES
========================================================= */

$rows = db_all(
    "SELECT *
     FROM messages
     ORDER BY created_at DESC"
);

?>

<!-- =========================================================
     CONTACT MESSAGE MANAGEMENT
========================================================= -->

<h3 class="section-title mb-4">
    Contact Messages
</h3>

<div class="table-card">

    <div class="table-responsive">

        <table class="table align-middle mb-0">

            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($rows)): ?>

                    <?php foreach ($rows as $r): ?>

                        <tr>

                            <!-- Sender Details -->
                            <td>
                                <strong>
                                    <?= e($r['name']) ?>
                                </strong>

                                <br>

                                <small class="text-secondary">
                                    <?= e($r['email']) ?>
                                </small>
                            </td>

                            <!-- Subject -->
                            <td>
                                <?= e($r['subject']) ?>
                            </td>

                            <!-- Message -->
                            <td>
                                <?= e($r['message']) ?>
                            </td>

                            <!-- Date -->
                            <td>
                                <?= e(
                                    date(
                                        'd M Y',
                                        strtotime($r['created_at'])
                                    )
                                ) ?>
                            </td>

                            <!-- Actions -->
                            <td>

                                <?php if (!$r['is_read']): ?>

                                    <a
                                        href="?read=<?= (int) $r['id'] ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Read
                                    </a>

                                <?php endif; ?>

                                <a
                                    href="?delete=<?= (int) $r['id'] ?>"
                                    data-confirm="Delete message?"
                                    class="btn btn-sm btn-outline-danger"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5" class="text-center py-4">
                            No contact messages found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require __DIR__ . '/footer.php'; ?>
