<?php
require_once __DIR__ . '/config/database.php';
requireAdmin();

$error = '';
$fieldErrors = [];
$edit = !empty($_GET['edit']) ? $app->classById((int) $_GET['edit']) : null;
$form = $edit ?: ['nama_kelas' => '', 'jurusan' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        checkCsrf();

        if (($_POST['action'] ?? '') === 'delete') {
            $app->deleteClass((int) ($_POST['id'] ?? 0));
            setMessage('Kelas berhasil dihapus.');
        } else {
            $form = $_POST;
            $className = trim((string) ($_POST['nama_kelas'] ?? ''));
            $major = trim((string) ($_POST['jurusan'] ?? ''));
            $classId = !empty($_POST['id']) ? (int) $_POST['id'] : null;

            $app->saveClass($className, $major, $classId);
            setMessage('Data kelas berhasil disimpan.');
        }

        header('Location: kelas.php');
        exit;
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000' && ($_POST['action'] ?? '') !== 'delete') {
            $fieldErrors['nama_kelas'] = 'Nama kelas sudah digunakan.';
        } else {
            $error = 'Kelas masih memiliki siswa, jadi tidak bisa dihapus.';
        }
    } catch (RuntimeException $exception) {
        $form = $_POST;
        $field = strpos($exception->getMessage(), 'jurusan') !== false
            ? 'jurusan'
            : 'nama_kelas';
        $fieldErrors[$field] = $exception->getMessage();
    }
}

$classes = $app->classes();
$title = 'Kelola kelas';
require __DIR__ . '/layouts/header.php';
?>

<main class="content">
    <h1>Kelola kelas</h1>
    <?php showMessage(); ?>

    <?php if ($error): ?>
        <div class="notice error"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="card">
        <h2><?= $edit ? 'Ubah kelas' : 'Tambah kelas' ?></h2>

        <form class="form-row" method="post">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">

            <label>
                Nama kelas
                <input
                    name="nama_kelas"
                    value="<?= e($form['nama_kelas'] ?? '') ?>"
                    placeholder="Contoh: X PPLG"
                    maxlength="20"
                    required
                    aria-describedby="err-kelas"
                >
                <?php if (!empty($fieldErrors['nama_kelas'])): ?>
                    <small class="field-error" id="err-kelas">
                        <?= e($fieldErrors['nama_kelas']) ?>
                    </small>
                <?php endif; ?>
            </label>

            <label>
                Jurusan
                <input
                    name="jurusan"
                    value="<?= e($form['jurusan'] ?? '') ?>"
                    placeholder="Contoh: PPLG"
                    maxlength="50"
                    required
                    aria-describedby="err-jurusan"
                >
                <?php if (!empty($fieldErrors['jurusan'])): ?>
                    <small class="field-error" id="err-jurusan">
                        <?= e($fieldErrors['jurusan']) ?>
                    </small>
                <?php endif; ?>
            </label>

            <button type="submit">Simpan</button>
            <?php if ($edit): ?>
                <a class="button secondary" href="kelas.php">Batal</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="card">
        <h2>Daftar kelas</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama kelas</th>
                        <th>Jurusan</th>
                        <th>Jumlah siswa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?= e($class['nama_kelas']) ?></td>
                            <td><?= e($class['jurusan']) ?></td>
                            <td><?= (int) $class['jumlah_siswa'] ?></td>
                            <td class="actions">
                                <a href="kelas.php?edit=<?= (int) $class['id'] ?>">Ubah</a>
                                <form method="post" onsubmit="return confirm('Hapus kelas ini?')">
                                    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $class['id'] ?>">
                                    <button class="link danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$classes): ?>
                        <tr>
                            <td colspan="4" class="empty">Belum ada kelas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require __DIR__ . '/layouts/footer.php'; ?>
