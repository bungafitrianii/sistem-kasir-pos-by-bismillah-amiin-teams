<?php
require_once __DIR__ . '/backend/auth.php';
require_role('owner'); // Khusus Owner yang berhak mengedit barang

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: barang.php?error=tidak_ditemukan');
    exit;
}

$stmt = db()->prepare('SELECT * FROM barang WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$barang = $stmt->fetch();

if (!$barang) {
    header('Location: barang.php?error=tidak_ditemukan');
    exit;
}

$error = '';

$kode_barang = $barang['kode_barang'];
$nama_barang = $barang['nama_barang'];
$kategori    = $barang['kategori'];
$satuan      = $barang['satuan'];
$harga_beli  = $barang['harga_beli'];
$harga_jual  = $barang['harga_jual'];
$stok        = $barang['stok'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_barang = trim($_POST['kode_barang'] ?? '');
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori    = trim($_POST['kategori'] ?? 'Umum');
    $satuan      = trim($_POST['satuan'] ?? 'pcs');
    $harga_beli  = trim($_POST['harga_beli'] ?? '0');
    $harga_jual  = trim($_POST['harga_jual'] ?? '0');
    $stok        = trim($_POST['stok'] ?? '0');

    $harga_beli_clean = (float)str_replace(['.', ','], ['', '.'], $harga_beli);
    $harga_jual_clean = (float)str_replace(['.', ','], ['', '.'], $harga_jual);
    $stok_clean       = (int)$stok;

    if (!csrf_check()) {
        $error = 'Sesi tidak valid, silakan muat ulang halaman.';
    } elseif ($kode_barang === '' || $nama_barang === '') {
        $error = 'Kode barang dan nama barang wajib diisi.';
    } elseif ($harga_beli_clean < 0 || $harga_jual_clean < 0 || $stok_clean < 0) {
        $error = 'Harga dan stok tidak boleh bernilai negatif.';
    } else {
        try {
            // Cek apakah kode barang sudah dipakai oleh ID lain
            $cekKode = db()->prepare('SELECT id FROM barang WHERE kode_barang = ? AND id != ? LIMIT 1');
            $cekKode->execute([$kode_barang, $id]);
            if ($cekKode->fetch()) {
                $error = 'Kode barang "' . e($kode_barang) . '" sudah digunakan oleh barang lain.';
            } else {
                $upStmt = db()->prepare(
                    'UPDATE barang 
                     SET kode_barang = ?, nama_barang = ?, kategori = ?, harga_beli = ?, harga_jual = ?, stok = ?, satuan = ?
                     WHERE id = ?'
                );
                $upStmt->execute([
                    $kode_barang, 
                    $nama_barang, 
                    $kategori, 
                    $harga_beli_clean, 
                    $harga_jual_clean, 
                    $stok_clean, 
                    $satuan, 
                    $id
                ]);

                header('Location: barang.php?pesan=berhasil_ubah');
                exit;
            }
        } catch (PDOException $ex) {
            $error = 'Gagal memperbarui barang: ' . $ex->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Data Barang - Amanah Store</title>
  
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
      --border: #d5d7c8;
      --card-bg: #ffffff;
      --danger: #a40000;
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
    .form-wrapper {
      max-width: 560px;
      margin: 0 auto;
      background: var(--card-bg);
      border-radius: 20px;
      padding: 28px 24px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.06);
      border: 1px solid #ebeedd;
    }
    .header-box {
      margin-bottom: 22px;
      border-bottom: 1px solid #ecefe0;
      padding-bottom: 14px;
    }
    .header-box h2 {
      font-size: 20px;
      font-weight: 800;
      color: var(--primary-dark);
    }
    .header-box p {
      font-size: 13px;
      color: var(--text-muted);
      margin-top: 4px;
    }
    .form-group {
      margin-bottom: 16px;
    }
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }
    label {
      display: block;
      font-weight: 700;
      font-size: 13px;
      margin-bottom: 6px;
      color: var(--text-main);
    }
    input, select {
      width: 100%;
      padding: 11px 14px;
      border: 1px solid var(--border);
      border-radius: 10px;
      font-size: 14px;
      font-family: inherit;
      background: #fafbf5;
    }
    input:focus, select:focus {
      outline: 2px solid var(--primary);
      background: #fff;
    }
    .btn-submit {
      width: 100%;
      padding: 13px;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 12px;
      font-weight: 800;
      font-size: 15px;
      cursor: pointer;
      margin-top: 10px;
      transition: background 0.15s ease;
    }
    .btn-submit:hover {
      background: var(--primary-dark);
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 18px;
      font-size: 14px;
      color: var(--primary);
      font-weight: 700;
      text-decoration: none;
    }
    .back-link:hover {
      text-decoration: underline;
    }
    .err-box {
      background: var(--danger-bg);
      color: var(--danger);
      padding: 12px 14px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 16px;
      border: 1px solid #f6bebe;
    }
  </style>
</head>
<body>

  <div class="form-wrapper">
    <div class="header-box">
      <h2>Edit Data Barang</h2>
      <p>ID Barang: #<?= (int)$barang['id'] ?> &bull; Terakhir diubah: <?= e($barang['updated_at']) ?></p>
    </div>

    <?php if ($error): ?>
      <div class="err-box"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="edit_barang.php?id=<?= (int)$barang['id'] ?>">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <div class="form-group">
        <label>Kode Barang / Barcode</label>
        <input type="text" name="kode_barang" value="<?= e($kode_barang) ?>" required>
      </div>

      <div class="form-group">
        <label>Nama Barang / Produk</label>
        <input type="text" name="nama_barang" value="<?= e($nama_barang) ?>" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Kategori</label>
          <input list="list-kategori" name="kategori" value="<?= e($kategori) ?>">
          <datalist id="list-kategori">
            <option value="Sembako">
            <option value="Makanan">
            <option value="Minuman">
            <option value="Bumbu Dapur">
            <option value="Perlengkapan">
            <option value="Kebutuhan Mandi">
            <option value="Obat-obatan">
            <option value="Rokok">
            <option value="Lainnya">
          </datalist>
        </div>
        <div class="form-group">
          <label>Satuan</label>
          <input list="list-satuan" name="satuan" value="<?= e($satuan) ?>">
          <datalist id="list-satuan">
            <option value="pcs">
            <option value="bks">
            <option value="kg">
            <option value="sak">
            <option value="botol">
            <option value="renceng">
            <option value="dus">
            <option value="pouch">
          </datalist>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Harga Beli / Modal (Rp)</label>
          <input type="number" step="100" min="0" name="harga_beli" value="<?= (int)$harga_beli ?>" required>
        </div>
        <div class="form-group">
          <label>Harga Jual (Rp)</label>
          <input type="number" step="100" min="0" name="harga_jual" value="<?= (int)$harga_jual ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label>Stok Tersedia</label>
        <input type="number" min="0" name="stok" value="<?= (int)$stok ?>" required>
      </div>

      <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
      <a href="barang.php" class="back-link">&larr; Batal &amp; Kembali ke Katalog</a>
    </form>
  </div>

</body>
</html>
