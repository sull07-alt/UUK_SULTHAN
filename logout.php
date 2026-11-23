<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$storedToken = (string) ($_SESSION['csrf'] ?? '');
$submittedToken = (string) ($_POST['csrf'] ?? '');

if ($storedToken === '' || !hash_equals($storedToken, $submittedToken)) {
    http_response_code(419);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
}

$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
