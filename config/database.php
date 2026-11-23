<?php
declare(strict_types=1);

session_start();
date_default_timezone_set('Asia/Makassar');

require_once __DIR__ . '/BaseModel.php';

final class Database
{
    private PDO $connection;

    public function __construct()
    {
        $dsn = 'mysql:host=127.0.0.1;port=3306;dbname=presensi_siswa;charset=utf8mb4';

        $this->connection = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Waktu database disamakan dengan zona waktu Samarinda.
        $this->connection->exec("SET time_zone = '+08:00'");
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}

class Kelas extends BaseModel
{
    private ?int $idKelas = null;
    private string $namaKelas = '';
    private string $jurusan = '';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getAll(): array
    {
        $sql = 'SELECT * FROM kelas ORDER BY nama_kelas';
        return $this->db->query($sql)->fetchAll();
    }

    public function getIdKelas(): ?int
    {
        return $this->idKelas;
    }

    public function setIdKelas(?int $id): void
    {
        $this->idKelas = $id;
    }

    public function getNamaKelas(): string
    {
        return $this->namaKelas;
    }

    public function setNamaKelas(string $nama): void
    {
        $this->namaKelas = trim($nama);
    }

    public function getJurusan(): string
    {
        return $this->jurusan;
    }

    public function setJurusan(string $jurusan): void
    {
        $this->jurusan = trim($jurusan);
    }
}

class Siswa extends BaseModel
{
    private string $nisn = '';
    private string $nama = '';
    private string $alamat = '';
    private int $idKelas = 0;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getAll(): array
    {
        $sql = 'SELECT s.nisn, s.nama, s.alamat, k.nama_kelas
                FROM siswa s
                INNER JOIN kelas k ON k.id_kelas = s.id_kelas
                ORDER BY s.nama';

        return $this->db->query($sql)->fetchAll();
    }

    public function getNisn(): string
    {
        return $this->nisn;
    }

    public function setNisn(string $nisn): void
    {
        $this->nisn = trim($nisn);
    }

    public function getNama(): string
    {
        return $this->nama;
    }

    public function setNama(string $nama): void
    {
        $this->nama = trim($nama);
    }

    public function getAlamat(): string
    {
        return $this->alamat;
    }

    public function setAlamat(string $alamat): void
    {
        $this->alamat = trim($alamat);
    }

    public function getIdKelas(): int
    {
        return $this->idKelas;
    }

    public function setIdKelas(int $id): void
    {
        $this->idKelas = $id;
    }
}

class Presensi extends BaseModel
{
    private ?int $id = null;
    private string $nisn = '';
    private ?string $tanggalWaktu = null;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function getAll(): array
    {
        $sql = 'SELECT p.id, p.nisn, p.tanggal_waktu, s.nama, k.nama_kelas
                FROM presensi p
                INNER JOIN siswa s ON s.nisn = p.nisn
                INNER JOIN kelas k ON k.id_kelas = s.id_kelas
                ORDER BY p.tanggal_waktu DESC';

        return $this->db->query($sql)->fetchAll();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getNisn(): string
    {
        return $this->nisn;
    }

    public function setNisn(string $nisn): void
    {
        $this->nisn = trim($nisn);
    }

    public function getTanggalWaktu(): ?string
    {
        return $this->tanggalWaktu;
    }

    public function setTanggalWaktu(?string $waktu): void
    {
        $this->tanggalWaktu = $waktu;
    }
}

class User extends BaseModel
{
    public function getAll(): array
    {
        $sql = 'SELECT id_user, username FROM users ORDER BY username';
        return $this->db->query($sql)->fetchAll();
    }

    public function login(string $username, string $password): ?array
    {
        $sql = 'SELECT id_user, username, password
                FROM users
                WHERE username = ?
                LIMIT 1';
        $query = $this->db->prepare($sql);
        $query->execute([$username]);
        $user = $query->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        return $user;
    }
}

final class PresensiApp
{
    private PDO $db;
    private Kelas $kelas;
    private Siswa $siswa;
    private Presensi $presensi;
    private User $user;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->kelas = new Kelas($db);
        $this->siswa = new Siswa($db);
        $this->presensi = new Presensi($db);
        $this->user = new User($db);
    }

    public function login(string $username, string $password): ?array
    {
        return $this->user->login($username, $password);
    }

    public function classes(): array
    {
        $sql = 'SELECT k.id_kelas AS id, k.nama_kelas, k.jurusan,
                       COUNT(s.nisn) AS jumlah_siswa
                FROM kelas k
                LEFT JOIN siswa s ON s.id_kelas = k.id_kelas
                GROUP BY k.id_kelas
                ORDER BY k.nama_kelas';

        return $this->db->query($sql)->fetchAll();
    }

    public function classById(int $id): ?array
    {
        $sql = 'SELECT id_kelas AS id, nama_kelas, jurusan
                FROM kelas
                WHERE id_kelas = ?';
        $query = $this->db->prepare($sql);
        $query->execute([$id]);

        return $query->fetch() ?: null;
    }

    public function saveClass(string $name, string $major = '', ?int $id = null): void
    {
        $name = trim($name);
        $major = trim($major);

        if ($name === '') {
            throw new RuntimeException('Nama kelas harus diisi.');
        }
        if (strlen($name) > 20) {
            throw new RuntimeException('Nama kelas maksimal 20 karakter.');
        }

        if ($major === '') {
            $major = preg_replace('/^(X|XI)\s+/i', '', $name) ?: $name;
        }
        if (strlen($major) > 50) {
            throw new RuntimeException('Nama jurusan maksimal 50 karakter.');
        }

        if ($id !== null) {
            $sql = 'UPDATE kelas SET nama_kelas = ?, jurusan = ? WHERE id_kelas = ?';
            $query = $this->db->prepare($sql);
            $query->execute([$name, $major, $id]);
            return;
        }

        $sql = 'INSERT INTO kelas (nama_kelas, jurusan) VALUES (?, ?)';
        $query = $this->db->prepare($sql);
        $query->execute([$name, $major]);
    }

    public function deleteClass(int $id): void
    {
        $query = $this->db->prepare('DELETE FROM kelas WHERE id_kelas = ?');
        $query->execute([$id]);
    }

    public function students(string $search = ''): array
    {
        $sql = 'SELECT s.nisn, s.nisn AS id, s.nama, s.alamat,
                       s.id_kelas AS classroom_id, k.nama_kelas
                FROM siswa s
                INNER JOIN kelas k ON k.id_kelas = s.id_kelas
                WHERE s.nisn LIKE ? OR s.nama LIKE ?
                ORDER BY s.nama';
        $term = '%' . $search . '%';
        $query = $this->db->prepare($sql);
        $query->execute([$term, $term]);

        return $query->fetchAll();
    }

    public function studentById(string $nisn): ?array
    {
        $query = $this->db->prepare('SELECT * FROM siswa WHERE nisn = ?');
        $query->execute([$nisn]);

        return $query->fetch() ?: null;
    }

    public function saveStudent(
        string $nisn,
        string $name,
        string $address,
        int $classId,
        ?string $oldNisn = null
    ): void {
        $nisn = trim($nisn);
        $name = trim($name);
        $address = trim($address);

        if (!preg_match('/^[0-9]{10}$/', $nisn)) {
            throw new RuntimeException('NISN harus terdiri dari tepat 10 digit angka.');
        }
        if ($name === '') {
            throw new RuntimeException('Nama siswa wajib diisi.');
        }
        if (mb_strlen($name) > 100) {
            throw new RuntimeException('Nama siswa maksimal 100 karakter.');
        }
        if (mb_strlen($address) > 255) {
            throw new RuntimeException('Alamat maksimal 255 karakter.');
        }
        if ($classId < 1) {
            throw new RuntimeException('Kelas wajib dipilih.');
        }

        if ($oldNisn !== null && $oldNisn !== '') {
            $sql = 'UPDATE siswa
                    SET nisn = ?, nama = ?, alamat = ?, id_kelas = ?
                    WHERE nisn = ?';
            $query = $this->db->prepare($sql);
            $query->execute([$nisn, $name, $address, $classId, $oldNisn]);
            return;
        }

        $sql = 'INSERT INTO siswa (nisn, nama, alamat, id_kelas)
                VALUES (?, ?, ?, ?)';
        $query = $this->db->prepare($sql);
        $query->execute([$nisn, $name, $address, $classId]);
    }

    public function deleteStudent(string $nisn): void
    {
        $query = $this->db->prepare('DELETE FROM siswa WHERE nisn = ?');
        $query->execute([$nisn]);
    }

    public function tap(string $nisn): void
    {
        $nisn = trim($nisn);
        if (!preg_match('/^[0-9]{10}$/', $nisn)) {
            throw new RuntimeException('NISN kartu harus terdiri dari 10 digit angka.');
        }

        $query = $this->db->prepare('SELECT nisn FROM siswa WHERE nisn = ?');
        $query->execute([$nisn]);
        if (!$query->fetchColumn()) {
            throw new RuntimeException('NISN tidak terdaftar. Periksa kembali kartu siswa.');
        }

        $query = $this->db->prepare('INSERT INTO presensi (nisn) VALUES (?)');
        $query->execute([$nisn]);
    }

    public function attendances(
        string $date = '',
        string $search = '',
        bool $today = false,
        ?int $classId = null
    ): array {
        $conditions = [];
        $values = [];

        if ($today) {
            $conditions[] = 'DATE(p.tanggal_waktu) = CURDATE()';
        } elseif ($date !== '') {
            $conditions[] = 'DATE(p.tanggal_waktu) = ?';
            $values[] = $date;
        }

        if ($classId !== null && $classId > 0) {
            $conditions[] = 'k.id_kelas = ?';
            $values[] = $classId;
        }

        if ($search !== '') {
            $conditions[] = '(s.nisn LIKE ? OR s.nama LIKE ?)';
            $term = '%' . $search . '%';
            $values[] = $term;
            $values[] = $term;
        }

        $sql = 'SELECT p.id, p.tanggal_waktu AS waktu_tap,
                       s.nisn, s.nama, k.nama_kelas
                FROM presensi p
                INNER JOIN siswa s ON s.nisn = p.nisn
                INNER JOIN kelas k ON k.id_kelas = s.id_kelas';

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY p.tanggal_waktu DESC LIMIT 500';
        $query = $this->db->prepare($sql);
        $query->execute($values);

        return $query->fetchAll();
    }

    public function deleteAttendance(int $id): void
    {
        $query = $this->db->prepare('DELETE FROM presensi WHERE id = ?');
        $query->execute([$id]);
    }
}

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function checkCsrf(): void
{
    $submittedToken = (string) ($_POST['csrf'] ?? '');
    if (!hash_equals(csrfToken(), $submittedToken)) {
        throw new RuntimeException('Form kedaluwarsa. Muat ulang lalu coba lagi.');
    }
}

function requireAdmin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function setMessage(string $message): void
{
    $_SESSION['message'] = $message;
}

function showMessage(): void
{
    if (empty($_SESSION['message'])) {
        return;
    }

    echo '<div class="notice">' . e($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}

$database = new Database();
$app = new PresensiApp($database->getConnection());
