<?php
require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        checkCsrf();

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $admin = $app->login($username, $password);

        if (!$admin) {
            throw new RuntimeException('Nama pengguna atau sandi salah.');
        }

        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id_user'];
        $_SESSION['admin_name'] = $admin['username'];
        header('Location: index.php');
        exit;
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    }
}

$title = 'Login';
$showNav = false;
require __DIR__ . '/layouts/header.php';
?>
<main class="login card">
    <img class="login-logo" src="assets/logo-airlangga.png" alt="Logo SMK TI Airlangga Samarinda">
    <h1>Presensi Siswa</h1>
    <p>Masuk sebagai administrator</p>

    <?php if ($error): ?>
        <div class="notice error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

        <label>
            Nama pengguna
            <input name="username" required autocomplete="username">
        </label>

        <label>
            Sandi
            <input
                type="password"
                name="password"
                required
                autocomplete="current-password"
            >
        </label>

        <button type="submit">Masuk</button>
    </form>
</main>
<?php require __DIR__ . '/layouts/footer.php'; ?>
