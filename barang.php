<?php
require_once __DIR__ . '/backend/auth.php';
require_login();

$u = current_user();
$isOwner = ($u['role'] ?? '') === 'owner';

// Pastikan tabel barang sudah ada di database
try {
    db()->exec("CREATE TABLE IF NOT EXISTS `barang` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `kode_barang` VARCHAR(50) NOT NULL UNIQUE,
      `nama_barang` VARCHAR(150) NOT NULL,
      `kategori` VARCHAR(50) NOT NULL DEFAULT 'Umum',
      `harga_beli` DECIMAL(12, 2) NOT NULL DEFAULT 0,
      `harga_jual` DECIMAL(12, 2) NOT NULL DEFAULT 0,
      `stok` INT NOT NULL DEFAULT 0,
      `satuan` VARCHAR(20) NOT NULL DEFAULT 'pcs',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
} catch (Exception $e) {
    // Abaikan jika terjadi error izin skema
}

// Parameter pencarian & filter
$cari = trim($_GET['cari'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');

$sql = "SELECT * FROM barang WHERE 1=1";
$params = [];

if ($cari !== '') {
    $sql .= " AND (kode_barang LIKE ? OR nama_barang LIKE ?)";
    $params[] = '%' . $cari . '%';
    $params[] = '%' . $cari . '%';
}

if ($kategori !== '') {
    $sql .= " AND kategori = ?";
    $params[] = $kategori;
}

$sql .= " ORDER BY id DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$daftar_barang = $stmt->fetchAll();

// Ambil daftar kategori unik untuk dropdown filter
$katStmt = db()->query("SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
$semua_kategori = $katStmt->fetchAll(PDO::FETCH_COLUMN);

// Statistik Ringkas
$statTotalItem = count($daftar_barang);
$statTotalStok = 0;
$statTotalAset = 0;
$statStokKritis = 0;

foreach ($daftar_barang as $b) {
    $statTotalStok += (int)$b['stok'];
    $statTotalAset += ((float)$b['harga_beli'] * (int)$b['stok']);
    if ((int)$b['stok'] <= 5) {
        $statStokKritis++;
    }
}

// Pesan notifikasi
$pesan = '';
$tipe_pesan = 'ok';
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] === 'berhasil_tambah') {
        $pesan = 'Barang baru berhasil ditambahkan!';
    } elseif ($_GET['pesan'] === 'berhasil_ubah') {
        $pesan = 'Data barang berhasil diperbarui!';
    } elseif ($_GET['pesan'] === 'berhasil_hapus') {
        $pesan = 'Data barang berhasil dihapus!';
    }
}
if (isset($_GET['error'])) {
    $tipe_pesan = 'err';
    if ($_GET['error'] === 'tidak_ditemukan') {
        $pesan = 'Data barang tidak ditemukan.';
    } elseif ($_GET['error'] === 'akses_ditolak') {
        $pesan = 'Aksi ditolak: hanya Owner yang berhak melakukan tindakan tersebut.';
    } else {
        $pesan = 'Terjadi kesalahan sistem.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Katalog Data Barang - Amanah Store</title>
  
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
      --warning: #b78103;
      --warning-bg: #fef7db;
      --success: #1b873f;
      --success-bg: #e6f6e6;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: linear-gradient(180deg, #f2f4ba 0%, var(--bg-mid) 140px, var(--bg-base) 100%);
      color: var(--text-main);
      min-height: 100vh;
      padding: 24px 16px;
    }
    .container {
      max-width: 1200px;
      margin: 0 auto;
    }
    header.top-nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 20px;
    }
    .brand-title {
      font-size: 24px;
      font-weight: 800;
      color: var(--primary-dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .badge-role {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      padding: 4px 10px;
      border-radius: 20px;
      background: var(--primary-light);
      color: var(--primary-dark);
    }
    .nav-actions {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 10px 18px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 14px;
      text-decoration: none;
      cursor: pointer;
      border: none;
      transition: all 0.15s ease;
    }
    .btn-primary {
      background: var(--primary);
      color: #fff;
    }
    .btn-primary:hover {
      background: var(--primary-dark);
    }
    .btn-secondary {
      background: #fff;
      color: var(--text-main);
      border: 1px solid var(--border);
    }
    .btn-secondary:hover {
      background: #f4f4ea;
    }
    .btn-sm {
      padding: 6px 12px;
      font-size: 13px;
      border-radius: 8px;
    }
    .btn-danger-sm {
      background: var(--danger-bg);
      color: var(--danger);
      border: 1px solid #f8b4b4;
    }
    .btn-danger-sm:hover {
      background: var(--danger);
      color: #fff;
    }
    .btn-edit-sm {
      background: var(--primary-light);
      color: var(--primary-dark);
    }
    .btn-edit-sm:hover {
      background: #e0e58c;
    }

    /* Grid Statistik */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 14px;
      margin-bottom: 20px;
    }
    .stat-card {
      background: var(--card-bg);
      padding: 16px;
      border-radius: 16px;
      border: 1px solid var(--border);
      box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .stat-label {
      font-size: 13px;
      color: var(--text-muted);
      margin-bottom: 4px;
    }
    .stat-value {
      font-size: 22px;
      font-weight: 800;
      color: var(--primary-dark);
    }

    /* Filter & Search Bar */
    .filter-card {
      background: var(--card-bg);
      padding: 16px;
      border-radius: 16px;
      border: 1px solid var(--border);
      margin-bottom: 20px;
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: center;
      justify-content: space-between;
    }
    .search-group {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      flex: 1;
    }
    .search-input, .filter-select {
      padding: 10px 14px;
      border: 1px solid var(--border);
      border-radius: 10px;
      font-size: 14px;
      font-family: inherit;
    }
    .search-input {
      min-width: 240px;
      flex: 1;
    }
    .search-input:focus, .filter-select:focus {
      outline: 2px solid var(--primary);
    }

    /* Alert */
    .alert {
      padding: 12px 16px;
      border-radius: 12px;
      margin-bottom: 16px;
      font-size: 14px;
      font-weight: 600;
    }
    .alert-ok { background: var(--success-bg); color: var(--success); border: 1px solid #b8e6b8; }
    .alert-err { background: var(--danger-bg); color: var(--danger); border: 1px solid #f8b4b4; }

    /* Table Container */
    .table-card {
      background: var(--card-bg);
      border-radius: 18px;
      border: 1px solid var(--border);
      box-shadow: 0 4px 14px rgba(0,0,0,0.04);
      overflow: hidden;
    }
    .table-responsive {
      width: 100%;
      overflow-x: auto;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 14px;
    }
    thead th {
      background: #faf9ee;
      padding: 14px 16px;
      font-weight: 700;
      color: var(--text-muted);
      border-bottom: 1px solid var(--border);
      white-space: nowrap;
    }
    tbody td {
      padding: 14px 16px;
      border-bottom: 1px solid #f2f3e8;
      vertical-align: middle;
    }
    tbody tr:hover {
      background: #fafae8;
    }

    /* Badges */
    .badge-stock {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-weight: 700;
      font-size: 12px;
    }
    .stock-safe { background: var(--success-bg); color: var(--success); }
    .stock-warn { background: var(--warning-bg); color: var(--warning); }
    .stock-danger { background: var(--danger-bg); color: var(--danger); }
    
    .badge-cat {
      background: #f1f2e2;
      color: #55573e;
      padding: 3px 8px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
    }

    .code-pill {
      font-family: monospace;
      font-weight: 700;
      background: #edf1c4;
      color: #3b3e00;
      padding: 2px 6px;
      border-radius: 4px;
    }

    .action-group {
      display: flex;
      gap: 6px;
      align-items: center;
    }

    .empty-state {
      text-align: center;
      padding: 48px 16px;
      color: var(--text-muted);
    }
  </style>
</head>
<body>
  <div class="container">
    
    <!-- Top Bar -->
    <header class="top-nav">
      <div>
        <h1 class="brand-title">
          <span>📦 Data Barang &amp; Stok</span>
          <span class="badge-role"><?= e($u['role']) ?>: <?= e($u['nama']) ?></span>
        </h1>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
          Kelola inventaris, harga modal, harga jual, dan ketersediaan stok toko kelontong.
        </p>
      </div>
      <div class="nav-actions">
        <a href="dashboard.php" class="btn btn-secondary">&larr; Dashboard</a>
        <?php if ($isOwner): ?>
          <a href="tambah_barang.php" class="btn btn-primary">+ Tambah Barang</a>
        <?php endif; ?>
      </div>
    </header>

    <!-- Notifikasi -->
    <?php if ($pesan): ?>
      <div class="alert <?= $tipe_pesan === 'ok' ? 'alert-ok' : 'alert-err' ?>">
        <?= e($pesan) ?>
      </div>
    <?php endif; ?>

    <!-- Statistik Ringkas -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Total Jenis Barang</div>
        <div class="stat-value"><?= number_format($statTotalItem, 0, ',', '.') ?> <span style="font-size:14px; font-weight:500;">Item</span></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Total Kuantitas Fisik</div>
        <div class="stat-value"><?= number_format($statTotalStok, 0, ',', '.') ?> <span style="font-size:14px; font-weight:500;">Unit</span></div>
      </div>
      <?php if ($isOwner): ?>
      <div class="stat-card">
        <div class="stat-label">Estimasi Nilai Aset Modal</div>
        <div class="stat-value" style="font-size:18px;">Rp <?= number_format($statTotalAset, 0, ',', '.') ?></div>
      </div>
      <?php endif; ?>
      <div class="stat-card">
        <div class="stat-label">Stok Kritis (&le; 5)</div>
        <div class="stat-value" style="color: <?= $statStokKritis > 0 ? 'var(--danger)' : 'var(--success)' ?>;">
          <?= number_format($statStokKritis, 0, ',', '.') ?> <span style="font-size:14px; font-weight:500;">Item</span>
        </div>
      </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="filter-card">
      <form method="get" action="barang.php" class="search-group">
        <input 
          type="text" 
          name="cari" 
          class="search-input" 
          placeholder="Cari kode atau nama barang..." 
          value="<?= e($cari) ?>"
        >
        <select name="kategori" class="filter-select">
          <option value="">-- Semua Kategori --</option>
          <?php foreach ($semua_kategori as $kat): ?>
            <option value="<?= e($kat) ?>" <?= $kategori === $kat ? 'selected' : '' ?>>
              <?= e($kat) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if ($cari !== '' || $kategori !== ''): ?>
          <a href="barang.php" class="btn btn-secondary" style="color: var(--danger);">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Tabel Data Barang -->
    <div class="table-card">
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th style="width: 50px;">No</th>
              <th>Kode</th>
              <th>Nama Barang</th>
              <th>Kategori</th>
              <?php if ($isOwner): ?>
                <th style="text-align: right;">Harga Beli</th>
              <?php endif; ?>
              <th style="text-align: right;">Harga Jual</th>
              <?php if ($isOwner): ?>
                <th style="text-align: right;">Margin</th>
              <?php endif; ?>
              <th style="text-align: center;">Stok</th>
              <th>Satuan</th>
              <?php if ($isOwner): ?>
                <th style="text-align: center; width: 140px;">Aksi</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($daftar_barang)): ?>
              <tr>
                <td colspan="<?= $isOwner ? '10' : '7' ?>" class="empty-state">
                  Belum ada data barang yang ditemukan. 
                  <?php if ($isOwner): ?>
                    <br><br>
                    <a href="tambah_barang.php" class="btn btn-primary btn-sm">+ Tambah Barang Pertama</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php else: ?>
              <?php $no = 1; foreach ($daftar_barang as $row): 
                $stok = (int)$row['stok'];
                $stkClass = $stok <= 5 ? 'stock-danger' : ($stok <= 15 ? 'stock-warn' : 'stock-safe');
                $margin = (float)$row['harga_jual'] - (float)$row['harga_beli'];
              ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><span class="code-pill"><?= e($row['kode_barang']) ?></span></td>
                  <td><b><?= e($row['nama_barang']) ?></b></td>
                  <td><span class="badge-cat"><?= e($row['kategori']) ?></span></td>
                  <?php if ($isOwner): ?>
                    <td style="text-align: right; color: var(--text-muted);">
                      Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?>
                    </td>
                  <?php endif; ?>
                  <td style="text-align: right; font-weight: 700; color: var(--primary-dark);">
                    Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?>
                  </td>
                  <?php if ($isOwner): ?>
                    <td style="text-align: right; font-size: 13px; color: <?= $margin >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                      +Rp <?= number_format($margin, 0, ',', '.') ?>
                    </td>
                  <?php endif; ?>
                  <td style="text-align: center;">
                    <span class="badge-stock <?= $stkClass ?>">
                      <?= number_format($stok, 0, ',', '.') ?>
                    </span>
                  </td>
                  <td><?= e($row['satuan']) ?></td>
                  <?php if ($isOwner): ?>
                    <td>
                      <div class="action-group" style="justify-content: center;">
                        <a href="edit_barang.php?id=<?= (int)$row['id'] ?>" class="btn btn-edit-sm btn-sm">Edit</a>
                        <form method="post" action="backend/hapus_barang.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang \'<?= addslashes(e($row['nama_barang'])) ?>\'?');" style="margin:0;">
                          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                          <button type="submit" class="btn btn-danger-sm btn-sm">Hapus</button>
                        </form>
                      </div>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</body>
</html>
