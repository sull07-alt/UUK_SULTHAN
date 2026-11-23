<?php
require_once __DIR__ . '/config/database.php';
requireAdmin();

$search = trim((string) ($_GET['q'] ?? ''));
$students = $app->students($search);
$title = 'Pencarian siswa';

require __DIR__ . '/layouts/header.php';
?>
<main class="content">
    <h1>Pencarian siswa</h1>
    <?php showMessage(); ?>

    <section class="card">
        <form class="filters" method="get">
            <label>
                Cari nama atau NISN
                <input name="q" value="<?= e($search) ?>" placeholder="Ketik nama/NISN">
            </label>
            <button type="submit">Cari</button>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NISN</th>
                        <th>Nama siswa</th>
                        <th>Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= e($student['nisn']) ?></td>
                            <td><?= e($student['nama']) ?></td>
                            <td><?= e($student['nama_kelas']) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$students): ?>
                        <tr>
                            <td colspan="3" class="empty">Siswa tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php require __DIR__ . '/layouts/footer.php'; ?>
