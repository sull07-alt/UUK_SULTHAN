<?php
require_once __DIR__ . '/config/database.php';
requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        checkCsrf();
        $app->deleteAttendance((int) ($_POST['id'] ?? 0));
        setMessage('Log presensi berhasil dihapus.');
        header('Location: rekap.php');
        exit;
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    }
}

$date = (string) ($_GET['tanggal'] ?? date('Y-m-d'));
$search = trim((string) ($_GET['q'] ?? ''));
$classId = !empty($_GET['id_kelas']) ? (int) $_GET['id_kelas'] : null;
$classes = $app->classes();
$rows = $app->attendances($date, $search, false, $classId);
$title = 'Rekap presensi';

require __DIR__ . '/layouts/header.php';
?>
<main class="content">
    <h1>Rekap presensi</h1>
    <?php showMessage(); ?>

    <?php if ($error): ?>
        <div class="notice error"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="card">
        <form class="filters" method="get">
            <label>
                Tanggal
                <input type="date" name="tanggal" value="<?= e($date) ?>">
            </label>

            <label>
                Kelas
                <select name="id_kelas">
                    <option value="">Semua kelas</option>
                    <?php foreach ($classes as $class): ?>
                        <option
                            value="<?= (int) $class['id'] ?>"
                            <?= $classId === (int) $class['id'] ? 'selected' : '' ?>
                        >
                            <?= e($class['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Cari nama/NISN
                <input name="q" value="<?= e($search) ?>" placeholder="Nama atau NISN">
            </label>

            <button type="submit">Filter</button>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>NISN</th>
                        <th>Nama siswa</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= e(date('d/m/Y H:i:s', strtotime($row['waktu_tap']))) ?></td>
                            <td><?= e($row['nisn']) ?></td>
                            <td><?= e($row['nama']) ?></td>
                            <td><?= e($row['nama_kelas']) ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Hapus log presensi ini?')">
                                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button class="link danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="5" class="empty">Tidak ada presensi yang cocok.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php require __DIR__ . '/layouts/footer.php'; ?>
