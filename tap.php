<?php
require_once __DIR__ . '/config/database.php';
requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        checkCsrf();
        $nisn = trim((string) ($_POST['nisn'] ?? ''));
        $app->tap($nisn);
        setMessage('Presensi berhasil dicatat.');
        header('Location: tap.php');
        exit;
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    }
}

$title = 'Simulasi tap kartu';
require __DIR__ . '/layouts/header.php';
?>
<main class="content">
    <h1>Simulasi tap kartu</h1>
    <?php showMessage(); ?>

    <?php if ($error): ?>
        <div class="notice error"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="card narrow">
        <p>Tempelkan kartu RFID atau ketik NISN untuk mencatat presensi.</p>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

            <label>
                NISN pada kartu
                <input
                    name="nisn"
                    maxlength="10"
                    minlength="10"
                    pattern="[0-9]{10}"
                    inputmode="numeric"
                    autocomplete="off"
                    autofocus
                    required
                >
            </label>

            <button type="submit">Catat presensi</button>
        </form>
    </section>
</main>
<?php require __DIR__ . '/layouts/footer.php'; ?>
