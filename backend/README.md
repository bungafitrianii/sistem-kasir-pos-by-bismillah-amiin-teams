# Backend Login Amanah Store (PHP)

PHP polos + PDO, tanpa framework. Cukup PHP 8+ dan MySQL.

## Isi
| File | Fungsi |
|---|---|
| config.php | Setelan database, sesi, koneksi, token CSRF |
| auth.php | require_login() dan require_role() untuk melindungi halaman |
| login.php | Form dan proses login |
| logout.php | Keluar (POST + CSRF) |
| dashboard.php | Placeholder, hanya bisa dibuka setelah login |
| tambah_kasir.php | Tambah kasir, khusus owner |
| seed_owner.php | Membuat owner pertama (CLI, sekali pakai) |

## Cara menjalankan
1. Pasang XAMPP atau Laragon, aktifkan Apache dan MySQL.
2. Pastikan database `amanah_store` sudah ada (impor database.sql dan data_barang.sql).
3. Salin folder ini ke `htdocs` (XAMPP) atau `www` (Laragon).
4. Buka `config.php`, sesuaikan DB_USER dan DB_PASS.
5. Di terminal, dari folder ini: `php seed_owner.php` lalu isi nama, email, sandi.
6. Buka `http://localhost/amanah-backend/login.php` dan masuk.
7. Hapus `seed_owner.php` setelah dipakai.
