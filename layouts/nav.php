<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<header class="topbar">
    <a class="brand-link" href="index.php" aria-label="Presensi siswa SMK TI Airlangga">
        <img src="assets/logo-airlangga.png" alt="SMK TI Airlangga Samarinda">
    </a>

    <button
        class="menu-toggle"
        type="button"
        aria-label="Buka menu"
        aria-controls="admin-nav"
        aria-expanded="false"
    >
        <span></span>
        <span></span>
        <span></span>
    </button>

    <nav class="admin-nav" id="admin-nav" aria-label="Navigasi utama">
        <a href="index.php" <?= $currentPage === 'index.php' ? 'aria-current="page"' : '' ?>>Hari ini</a>
        <a href="kelas.php" <?= $currentPage === 'kelas.php' ? 'aria-current="page"' : '' ?>>Kelas</a>
        <a href="siswa.php" <?= $currentPage === 'siswa.php' ? 'aria-current="page"' : '' ?>>Siswa</a>
        <a href="pencarian.php" <?= $currentPage === 'pencarian.php' ? 'aria-current="page"' : '' ?>>Pencarian</a>
        <a href="tap.php" <?= $currentPage === 'tap.php' ? 'aria-current="page"' : '' ?>>Tap kartu</a>
        <a href="rekap.php" <?= $currentPage === 'rekap.php' ? 'aria-current="page"' : '' ?>>Rekap</a>
        <button class="logout-button" type="button" data-open-logout>Keluar</button>
    </nav>
</header>

<dialog class="logout-dialog" id="logout-confirm" aria-labelledby="logout-title">
    <div class="dialog-icon" aria-hidden="true">↗</div>
    <h2 id="logout-title">Keluar dari dashboard?</h2>
    <p>Kamu perlu login kembali untuk mengakses data presensi.</p>
    <div class="dialog-actions">
        <button class="confirm-logout" type="submit" form="logout-form">Ya, keluar</button>
        <button class="cancel-logout" type="button" data-close-logout>Tidak</button>
    </div>
    <form id="logout-form" method="post" action="logout.php">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
    </form>
</dialog>

<script>
(() => {
    const toggle = document.querySelector('.menu-toggle');
    const menu = document.querySelector('#admin-nav');
    const dialog = document.querySelector('#logout-confirm');

    const closeMenu = () => {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Buka menu');
    };

    toggle.addEventListener('click', () => {
        const isOpen = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
    });

    menu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeMenu);
    });

    document.querySelector('[data-open-logout]').addEventListener('click', () => {
        closeMenu();
        dialog.showModal();
    });

    document.querySelector('[data-close-logout]').addEventListener('click', () => {
        dialog.close();
    });

    dialog.addEventListener('click', event => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 760) {
            closeMenu();
        }
    });
})();
</script>
