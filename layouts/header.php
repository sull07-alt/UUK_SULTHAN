<?php
$title = $title ?? 'Presensi Siswa';
$showNav = $showNav ?? true;
$bodyClass = $showNav ? 'admin-page' : 'login-page';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · Presensi Siswa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= e($bodyClass) ?>">
<?php if ($showNav): ?>
    <?php require __DIR__ . '/nav.php'; ?>
<?php endif; ?>
