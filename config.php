<?php
// ============================================================
// Konfigurasi & fungsi bantu. Di-include oleh semua halaman.
// ============================================================

// --- Sesuaikan dengan server MySQL Anda ---
// Disarankan buat user MySQL khusus aplikasi (bukan root) yang hanya
// punya akses ke database amanah_store.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'amanah_store';
const DB_USER = 'root';
const DB_PASS = '';

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
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
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
