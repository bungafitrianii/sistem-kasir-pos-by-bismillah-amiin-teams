<?php
// ============================================================
// Membuat akun OWNER pertama. Jalankan SEKALI dari terminal:
//     php seed_owner.php
// Setelah selesai, HAPUS file ini dari server.
// ============================================================
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Hanya bisa dijalankan dari terminal.');
}
require_once __DIR__ . '/config.php';

$sudah = db()->query("SELECT COUNT(*) FROM users WHERE role = 'owner'")->fetchColumn();
if ($sudah > 0) {
    exit("Akun owner sudah ada. Tidak ada yang dibuat.\n");
}

function tanya(string $label): string
{
    echo $label . ': ';
    return trim((string) fgets(STDIN));
}

$nama     = tanya('Nama owner');
$email    = tanya('Email');
$password = tanya('Kata sandi (min. 8 karakter, terlihat saat diketik)');

if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    exit("Data tidak valid. Pastikan nama terisi, email benar, dan sandi minimal 8 karakter.\n");
}

db()->prepare('INSERT INTO users (nama, email, password_hash, role) VALUES (?, ?, ?, ?)')
    ->execute([$nama, $email, password_hash($password, PASSWORD_DEFAULT), 'owner']);

echo "Owner berhasil dibuat. Silakan login, lalu hapus file seed_owner.php.\n";
