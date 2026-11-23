-- Database: `amanah_store`
-- Skema tabel untuk Sistem Kasir & Toko Kelontong Amanah Store

CREATE DATABASE IF NOT EXISTS `amanah_store` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `amanah_store`;

-- Tabel Pengguna (Owner & Kasir)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('owner', 'kasir') NOT NULL DEFAULT 'kasir',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contoh Akun Awal (Password default: 'password123')
-- Hash dihasilkan dengan password_hash('password123', PASSWORD_DEFAULT)
INSERT INTO `users` (`nama`, `email`, `password_hash`, `role`)
VALUES ('Pak Haji (Owner)', 'owner@amanahstore.id', '$2y$10$wB.5yK06Bgl49/1Rk1m3c.4rS/j0zK0mU4eX9k.9s2.x5bH8tDk6y', 'owner')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Tabel Data Barang / Produk Toko Kelontong
CREATE TABLE IF NOT EXISTS `barang` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contoh Data Awal Barang Toko Kelontong
INSERT INTO `barang` (`kode_barang`, `nama_barang`, `kategori`, `harga_beli`, `harga_jual`, `stok`, `satuan`) VALUES
('BRG-001', 'Beras Ramos Super 5kg', 'Sembako', 62000, 68000, 25, 'sak'),
('BRG-002', 'Minyak Goreng Bimoli 2 Liter', 'Sembako', 32000, 36000, 30, 'pouch'),
('BRG-003', 'Gula Pasir Gulaku 1kg', 'Sembako', 15500, 17500, 40, 'bks'),
('BRG-004', 'Telur Ayam Negeri 1kg', 'Sembako', 26000, 29000, 50, 'kg'),
('BRG-005', 'Indomie Goreng Spesial', 'Makanan', 2800, 3200, 120, 'bks'),
('BRG-006', 'Kopi Kapal Api Spesial Mix 10x24g', 'Minuman', 12500, 14500, 35, 'renceng'),
('BRG-007', 'Air Mineral Aqua 600ml', 'Minuman', 3000, 4000, 48, 'botol'),
('BRG-008', 'Sabun Cuci Piring Sunlight 650ml', 'Perlengkapan', 13000, 15000, 20, 'pouch'),
('BRG-009', 'Deterjen Rinso Molto 770g', 'Perlengkapan', 19000, 22000, 15, 'bks'),
('BRG-010', 'Pasta Gigi Pepsodent 190g', 'Perlengkapan', 11500, 13500, 18, 'pcs')
ON DUPLICATE KEY UPDATE `id`=`id`;

