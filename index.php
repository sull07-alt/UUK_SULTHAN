<?php
require_once __DIR__ . '/config/database.php';
requireAdmin();

$rows = $app->attendances('', '', true);
$title = 'Kehadiran hari ini';

require __DIR__ . '/layouts/header.php';
?>

<main class="content">
    <h1>Kehadiran hari ini</h1>
    <?php showMessage(); ?>

    <p>Jumlah tap hari ini: <strong><?= count($rows) ?></strong></p>

    <section class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>NISN</th>
                        <th>Nama siswa</th>
                        <th>Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= e(date('H:i:s', strtotime($row['waktu_tap']))) ?></td>
                            <td><?= e($row['nisn']) ?></td>
                            <td><?= e($row['nama']) ?></td>
                            <td><?= e($row['nama_kelas']) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="4" class="empty">Belum ada presensi hari ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
