<?php
// ============================================================
// Konfigurasi & fungsi bantu. Di-include oleh semua halaman.
// ============================================================

// --- Sesuaikan dengan server MySQL Anda ---
// Disarankan buat user MySQL khusus aplikasi (bukan root) yang hanya
// punya akses ke database amanah_store.
// Baca file .env jika ada di laptop
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim(trim($val), " '\";");
        }
    }
}

// Gunakan dari .env, atau default jika tidak ada
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_PORT', (int)($_ENV['DB_PORT'] ?? 3306));
define('DB_NAME', $_ENV['DB_NAME'] ?? 'amanah_store');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? ''); // Di GitHub selalu kosong & aman!
    
// --- Sesi: cookie tidak bisa dibaca JavaScript & aman dari CSRF lintas situs ---
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),   // otomatis aktif jika pakai HTTPS
    ]);
    session_start();
}

// Koneksi database (PDO). Satu koneksi dipakai ulang.
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,  // prepared statement asli
        ]);
    }
    return $pdo;
}

// Escape output HTML agar aman dari XSS.
function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// Token CSRF: dipasang di setiap form POST, diperiksa saat form dikirim.
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): bool
{
    $kirim = $_POST['csrf'] ?? '';
    return is_string($kirim) && hash_equals($_SESSION['csrf'] ?? '', $kirim);
}
