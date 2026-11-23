<?php
require_once __DIR__ . '/config/database.php';
requireAdmin();

$error = '';
$fieldErrors = [];
$edit = !empty($_GET['edit']) ? $app->studentById((string) $_GET['edit']) : null;
$form = $edit ?: [
    'nisn' => '',
    'nama' => '',
    'alamat' => '',
    'id_kelas' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        checkCsrf();

        if (($_POST['action'] ?? '') === 'delete') {
            $app->deleteStudent((string) ($_POST['nisn'] ?? ''));
            setMessage('Siswa berhasil dihapus.');
            header('Location: siswa.php');
            exit;
        }

        $form = $_POST;
        $nisn = trim((string) ($_POST['nisn'] ?? ''));
        $name = trim((string) ($_POST['nama'] ?? ''));
        $address = trim((string) ($_POST['alamat'] ?? ''));
        $classId = (int) ($_POST['id_kelas'] ?? 0);

        if (!preg_match('/^[0-9]{10}$/', $nisn)) {
            $fieldErrors['nisn'] = 'NISN harus tepat 10 digit angka.';
        }
        if ($name === '') {
            $fieldErrors['nama'] = 'Nama siswa wajib diisi.';
        } elseif (mb_strlen($name) > 100) {
            $fieldErrors['nama'] = 'Nama siswa maksimal 100 karakter.';
        }
        if (mb_strlen($address) > 255) {
            $fieldErrors['alamat'] = 'Alamat maksimal 255 karakter.';
        }
        if ($classId < 1) {
            $fieldErrors['id_kelas'] = 'Pilih kelas siswa.';
        }

        if (!$fieldErrors) {
            $oldNisn = trim((string) ($_POST['old_nisn'] ?? ''));
            $oldNisn = $oldNisn !== '' ? $oldNisn : null;

            $app->saveStudent($nisn, $name, $address, $classId, $oldNisn);
            setMessage('Data siswa berhasil disimpan.');
            header('Location: siswa.php');
            exit;
        }
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            $fieldErrors['nisn'] = 'NISN sudah terdaftar.';
        } else {
            $error = 'Data siswa belum dapat disimpan.';
        }
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    }
}

$classes = $app->classes();
$students = $app->students();
$title = 'Kelola siswa';
require __DIR__ . '/layouts/header.php';
?>

<main class="content">
    <h1>Kelola siswa</h1>
    <?php showMessage(); ?>

    <?php if ($error): ?>
        <div class="notice error"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="card">
        <h2><?= $edit ? 'Ubah siswa' : 'Tambah siswa' ?></h2>

        <?php if (!$classes): ?>
            <p class="empty">Buat kelas terlebih dahulu sebelum menambah siswa.</p>
        <?php else: ?>
            <form class="form-row" method="post">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="old_nisn" value="<?= e($edit['nisn'] ?? '') ?>">

                <label>
                    NISN
                    <input
                        name="nisn"
                        value="<?= e($form['nisn'] ?? '') ?>"
                        maxlength="10"
                        minlength="10"
                        pattern="[0-9]{10}"
                        inputmode="numeric"
                        required
                        aria-describedby="err-nisn"
                    >
                    <?php if (!empty($fieldErrors['nisn'])): ?>
                        <small class="field-error" id="err-nisn">
                            <?= e($fieldErrors['nisn']) ?>
                        </small>
                    <?php endif; ?>
                </label>

                <label>
                    Nama siswa
                    <input
                        name="nama"
                        value="<?= e($form['nama'] ?? '') ?>"
                        maxlength="100"
                        required
                        aria-describedby="err-nama"
                    >
                    <?php if (!empty($fieldErrors['nama'])): ?>
                        <small class="field-error" id="err-nama">
                            <?= e($fieldErrors['nama']) ?>
                        </small>
                    <?php endif; ?>
                </label>

                <label>
                    Alamat (boleh kosong)
                    <input
                        name="alamat"
                        value="<?= e($form['alamat'] ?? '') ?>"
                        maxlength="255"
                        aria-describedby="err-alamat"
                    >
                    <?php if (!empty($fieldErrors['alamat'])): ?>
                        <small class="field-error" id="err-alamat">
                            <?= e($fieldErrors['alamat']) ?>
                        </small>
                    <?php endif; ?>
                </label>

                <label>
                    Kelas
                    <select name="id_kelas" required aria-describedby="err-kelas">
                        <option value="">Pilih kelas</option>
                        <?php foreach ($classes as $class): ?>
                            <?php
                            $selectedClass = (int) ($form['id_kelas'] ?? $form['classroom_id'] ?? 0);
                            ?>
                            <option
                                value="<?= (int) $class['id'] ?>"
                                <?= $selectedClass === (int) $class['id'] ? 'selected' : '' ?>
                            >
                                <?= e($class['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($fieldErrors['id_kelas'])): ?>
                        <small class="field-error" id="err-kelas">
                            <?= e($fieldErrors['id_kelas']) ?>
                        </small>
                    <?php endif; ?>
                </label>

                <button type="submit">Simpan</button>
                <?php if ($edit): ?>
                    <a class="button secondary" href="siswa.php">Batal</a>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Daftar siswa</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NISN</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= e($student['nisn']) ?></td>
                            <td><?= e($student['nama']) ?></td>
                            <td><?= e($student['alamat']) ?></td>
                            <td><?= e($student['nama_kelas']) ?></td>
                            <td class="actions">
                                <a href="siswa.php?edit=<?= rawurlencode($student['nisn']) ?>">Ubah</a>
                                <form method="post" onsubmit="return confirm('Hapus data siswa ini?')">
                                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="nisn" value="<?= e($student['nisn']) ?>">
                                    <button class="link danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$students): ?>
                        <tr>
                            <td colspan="5" class="empty">Belum ada data siswa.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
