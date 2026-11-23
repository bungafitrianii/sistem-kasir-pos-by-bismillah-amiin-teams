<?php
require_once __DIR__ . '/backend/auth.php';
require_login();
$u = current_user();
$isOwner = ($u['role'] ?? '') === 'owner';

// Statistik singkat untuk dashboard
$totalBarang = 0;
$stokMenipis = 0;
try {
    $totalBarang = (int)db()->query("SELECT COUNT(*) FROM barang")->fetchColumn();
    $stokMenipis = (int)db()->query("SELECT COUNT(*) FROM barang WHERE stok <= 5")->fetchColumn();
} catch (Exception $e) {
    // Tabel barang belum di-query
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Kasir - Amanah Store</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #5d6300;
      --primary-dark: #464a00;
      --primary-light: #eff2af;
      --bg-base: #fcfae4;
      --bg-mid: #faf8de;
      --text-main: #1c1c0f;
      --text-muted: #6b6b55;
      --border: #e2e4cb;
      --card-bg: #ffffff;
      --danger: #d9383a;
      --danger-bg: #fde8e8;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: linear-gradient(180deg, #f2f4ba 0%, var(--bg-mid) 140px, var(--bg-base) 100%);
      color: var(--text-main);
      min-height: 100vh;
      padding: 30px 16px;
    }
    .wrapper {
      max-width: 680px;
      margin: 0 auto;
    }
    .card {
      background: var(--card-bg);
      border-radius: 22px;
      padding: 30px 24px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.06);
      border: 1px solid #ebeedd;
      margin-bottom: 20px;
    }
    .profile-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 1px solid #eef1df;
    }
    .profile-info h2 {
      font-size: 22px;
      font-weight: 800;
      color: var(--primary-dark);
    }
    .role-badge {
      display: inline-block;
      margin-top: 4px;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      background: var(--primary-light);
      color: var(--primary-dark);
    }
    .quick-stats {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 24px;
    }
    .stat-box {
      background: #fafaf0;
      padding: 14px 16px;
      border-radius: 14px;
      border: 1px solid var(--border);
    }
    .stat-box .title {
      font-size: 13px;
      color: var(--text-muted);
    }
    .stat-box .num {
      font-size: 20px;
      font-weight: 800;
      color: var(--primary-dark);
      margin-top: 4px;
    }
    .menu-grid {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .menu-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 20px;
      border-radius: 14px;
      background: #fafbf5;
      border: 1px solid var(--border);
      text-decoration: none;
      color: var(--text-main);
      transition: all 0.15s ease;
    }
    .menu-item:hover {
      background: var(--primary-light);
      border-color: #d1d882;
      transform: translateY(-2px);
    }
    .menu-title {
      font-size: 16px;
      font-weight: 700;
      color: var(--primary-dark);
    }
    .menu-desc {
      font-size: 13px;
      color: var(--text-muted);
      margin-top: 2px;
    }
    .logout-form {
      margin-top: 24px;
      border-top: 1px solid #eef1df;
      padding-top: 20px;
      text-align: right;
    }
    .btn-logout {
      padding: 10px 20px;
      border-radius: 10px;
      border: 1px solid #f8b4b4;
      background: var(--danger-bg);
      color: var(--danger);
      font-weight: 700;
      font-size: 14px;
      cursor: pointer;
    }
    .btn-logout:hover {
      background: var(--danger);
      color: #fff;
    }
  </style>
</head>
<body>

<div class="wrapper">
  <div class="card">
    <div class="profile-header">
      <div class="profile-info">
        <h2>Halo, <?= e($u['nama']) ?>! 👋</h2>
        <span class="role-badge">Peran: <?= e($u['role']) ?></span>
      </div>
      <div>
        <span style="font-size: 12px; font-weight: 600; color: #10b981;">● Online</span>
      </div>
    </div>

    <!-- Statistik Ringkas -->
    <div class="quick-stats">
      <div class="stat-box">
        <div class="title">Total Data Barang</div>
        <div class="num"><?= number_format($totalBarang, 0, ',', '.') ?> <span style="font-size:13px; font-weight:500;">Item</span></div>
      </div>
      <div class="stat-box">
        <div class="title">Stok Kritis (&le; 5)</div>
        <div class="num" style="color: <?= $stokMenipis > 0 ? 'var(--danger)' : 'var(--primary-dark)' ?>;">
          <?= number_format($stokMenipis, 0, ',', '.') ?> <span style="font-size:13px; font-weight:500;">Item</span>
        </div>
      </div>
    </div>

    <!-- Menu Navigasi Utama -->
    <h3 style="font-size: 15px; margin-bottom: 12px; color: var(--text-muted); font-weight: 700;">MENU OPERASIONAL TOKO</h3>
    <div class="menu-grid">
      <a href="barang.php" class="menu-item">
        <div>
          <div class="menu-title">📦 Kelola Data Barang (CRUD)</div>
          <div class="menu-desc">Lihat katalog, ubah harga jual/modal, perbarui stok, dan hapus barang.</div>
        </div>
        <span style="font-size: 18px; color: var(--primary);">&rarr;</span>
      </a>

      <?php if ($isOwner): ?>
        <a href="tambah_barang.php" class="menu-item">
          <div>
            <div class="menu-title">➕ Tambah Barang Baru</div>
            <div class="menu-desc">Daftarkan produk atau barcode baru ke dalam sistem kasir.</div>
          </div>
          <span style="font-size: 18px; color: var(--primary);">&rarr;</span>
        </a>

        <a href="backend/tambah_kasir.php" class="menu-item">
          <div>
            <div class="menu-title">👥 Kelola &amp; Tambah Kasir</div>
            <div class="menu-desc">Daftarkan akun staf kasir baru untuk melayani transaksi.</div>
          </div>
          <span style="font-size: 18px; color: var(--primary);">&rarr;</span>
        </a>
      <?php endif; ?>
    </div>

    <!-- Tombol Logout -->
    <form method="post" action="backend/logout.php" class="logout-form">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <button type="submit" class="btn-logout">🚪 Keluar dari Sistem</button>
    </form>
  </div>
</div>

</body>
</html>
