# Presensi Siswa SMK TI Airlangga Samarinda

Aplikasi presensi sederhana untuk mencatat tap kartu RFID. Setiap tap siswa yang valid menyimpan NISN dan waktu secara otomatis. Aplikasi dibuat dengan PHP native, PDO, OOP, dan MariaDB.

## Menjalankan aplikasi

1. Nyalakan Apache dan MySQL/MariaDB di Laragon.
2. Impor file `schema.sql` melalui phpMyAdmin jika membuat database baru.
3. Jika pengaturan MariaDB berbeda, sesuaikan DSN, username, dan password pada constructor `Database` di `config/database.php`.
4. Buka `http://localhost/UUK_SULTHAN/`.
5. Masuk menggunakan akun awal `admin` dengan sandi `password`.

Seeder membuat akun administrator dan delapan kelas X/XI untuk jurusan PPLG, DKV, TJKT, dan MPLB. Untuk database yang sudah berisi data, jangan impor ulang sebelum membuat cadangan.

## Fitur

- Login dan logout administrator.
- CRUD kelas dan siswa.
- Simulasi tap kartu menggunakan NISN.
- Rekap presensi dengan filter tanggal, kelas, dan pencarian nama/NISN.
- Pencarian siswa.
- Tampilan responsif untuk layar ponsel.

## Susunan file

- `assets/logo-airlangga.png` — logo sekolah.
- `config/BaseModel.php` — abstract class induk model.
- `config/database.php` — koneksi database, model OOP, validasi, dan fungsi umum.
- `layouts/` — header, navigasi, dan footer.
- `index.php`, `login.php`, `logout.php` — ringkasan dan autentikasi.
- `kelas.php`, `siswa.php`, `pencarian.php` — kelola dan cari data.
- `tap.php`, `rekap.php` — simulasi tap dan rekap presensi.
- `schema.sql` — struktur database dan seeder.
- `style.css` — tampilan aplikasi.
- `docs/` — ERD dan screenshot halaman aplikasi.

## ERD

![ERD Presensi Siswa](docs/erd-presensi.svg)

## Screenshot aplikasi

| Login | Dashboard |
|---|---|
| <img src="docs/login.png" alt="Halaman login" width="480"> | <img src="docs/dashboard.png" alt="Dashboard presensi hari ini" width="480"> |

| Kelola kelas | Kelola siswa |
|---|---|
| <img src="docs/kelas.png" alt="Halaman kelola kelas" width="480"> | <img src="docs/siswa.png" alt="Halaman kelola siswa" width="480"> |

| Pencarian siswa | Simulasi tap kartu |
|---|---|
| <img src="docs/pencarian-siswa.png" alt="Halaman pencarian siswa" width="480"> | <img src="docs/tap-kartu.png" alt="Halaman simulasi tap kartu" width="480"> |

| Presensi hari ini | Rekap presensi |
|---|---|
| <img src="docs/presensi-hari-ini.png" alt="Daftar presensi hari ini" width="480"> | <img src="docs/rekap-presensi.png" alt="Halaman rekap presensi" width="480"> |

| Konfirmasi hapus presensi | Presensi setelah log dihapus |
|---|---|
| <img src="docs/konfirmasi-hapus-presensi.png" alt="Dialog konfirmasi hapus log" width="480"> | <img src="docs/presensi-terhapus.png" alt="Rekap setelah log dihapus" width="480"> |

| Konfirmasi keluar | Pesan setelah kelas disimpan |
|---|---|
| <img src="docs/konfirmasi-keluar.png" alt="Dialog konfirmasi keluar" width="480"> | <img src="docs/kelas-tersimpan.png" alt="Pesan data kelas berhasil disimpan" width="480"> |

## Aturan data utama

- NISN harus berupa 10 digit angka dan disimpan sebagai teks.
- Nama siswa wajib diisi; alamat boleh kosong.
- Setiap tap valid menambah satu log presensi dengan waktu otomatis.
- Siswa yang tidak tap tidak mendapat baris presensi.
- Password disimpan menggunakan `password_hash()` dan diverifikasi menggunakan `password_verify()`.
- Query dengan input pengguna menggunakan prepared statement.
