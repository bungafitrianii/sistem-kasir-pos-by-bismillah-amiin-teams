<?php
require_once __DIR__ . '/config.php';

// Sudah login? langsung ke dashboard.
if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

// Hash palsu: dipakai agar waktu proses sama baik email ada maupun tidak
// (mencegah orang menebak email mana yang terdaftar dari lama respon).
const HASH_PALSU = '$2y$10$usesomesillystringforsalt.abcdefghijklmnopqrstuvwxyz012';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_check()) {
        $error = 'Sesi tidak valid, silakan muat ulang halaman.';
    } elseif ($email === '' || $password === '') {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        $stmt = db()->prepare(
            'SELECT id, nama, email, password_hash, role FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $hash = $user['password_hash'] ?? HASH_PALSU;
        $ok   = password_verify($password, $hash) && $user;

        if ($ok) {
            // Hash lama diperbarui otomatis bila algoritma bawaan PHP berubah.
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $baru = password_hash($password, PASSWORD_DEFAULT);
                db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                    ->execute([$baru, $user['id']]);
            }
            session_regenerate_id(true);   // cegah session fixation
            $_SESSION['user'] = [
                'id'    => (int) $user['id'],
                'nama'  => $user['nama'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ];
            header('Location: dashboard.php');
            exit;
        }
        // Pesan sengaja sama untuk email salah maupun password salah.
        $error = 'Email atau kata sandi salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk - Amanah Store</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
       background:#fbf8df;font-family:system-ui,sans-serif;color:#222;padding:16px}
  .card{background:#fff;border-radius:24px;padding:28px;width:100%;max-width:380px;
        box-shadow:0 4px 20px rgba(0,0,0,.08)}
  h1{margin:0 0 4px;font-size:24px;text-align:center}
  p.sub{margin:0 0 20px;text-align:center;color:#666;font-size:14px}
  label{display:block;font-weight:600;font-size:14px;margin:14px 0 6px}
  input[type=email],input[type=password]{width:100%;padding:12px 14px;border:1px solid #cfcfa8;
        border-radius:12px;font-size:15px}
  button{width:100%;margin-top:20px;padding:14px;border:0;border-radius:14px;background:#5c5f00;
         color:#fff;font-size:16px;font-weight:700;cursor:pointer}
  .err{background:#fde8e8;color:#a40000;padding:10px 12px;border-radius:10px;font-size:14px;margin-bottom:6px}
</style>
</head>
<body>
<form class="card" method="post" action="login.php" autocomplete="on">
  <h1>Amanah Store</h1>
  <p class="sub">Selamat datang kembali!</p>
  <?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label for="email">Email</label>
  <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="pakhaji@amanahstore.id" required>
  <label for="password">Kata Sandi</label>
  <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
  <button type="submit">Masuk ke Dashboard</button>
</form>
</body>
</html>
