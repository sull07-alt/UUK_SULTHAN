CREATE DATABASE IF NOT EXISTS presensi_siswa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE presensi_siswa;

CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS kelas (
    id_kelas INT AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(20) NOT NULL UNIQUE,
    jurusan VARCHAR(50) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS siswa (
    nisn CHAR(10) PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    alamat VARCHAR(255) NOT NULL DEFAULT '',
    id_kelas INT NOT NULL,
    INDEX idx_siswa_nama (nama),
    CONSTRAINT fk_siswa_kelas
        FOREIGN KEY (id_kelas) REFERENCES kelas (id_kelas)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS presensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nisn CHAR(10) NOT NULL,
    tanggal_waktu DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_presensi_waktu (tanggal_waktu),
    INDEX idx_presensi_siswa_waktu (nisn, tanggal_waktu),
    CONSTRAINT fk_presensi_siswa
        FOREIGN KEY (nisn) REFERENCES siswa (nisn)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE = InnoDB;

-- Seeder kelas X dan XI untuk setiap jurusan.
INSERT INTO kelas (nama_kelas, jurusan) VALUES
    ('X PPLG', 'PPLG'),
    ('XI PPLG', 'PPLG'),
    ('X DKV', 'DKV'),
    ('XI DKV', 'DKV'),
    ('X TJKT', 'TJKT'),
    ('XI TJKT', 'TJKT'),
    ('X MPLB', 'MPLB'),
    ('XI MPLB', 'MPLB')
ON DUPLICATE KEY UPDATE jurusan = VALUES(jurusan);

-- Akun awal: admin / password. Password disimpan dalam bentuk hash.
INSERT INTO users (username, password)
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
)
ON DUPLICATE KEY UPDATE username = VALUES(username);
