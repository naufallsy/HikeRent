-- ============================================================
--  HikeRent - Penyewaan Alat Hiking
--  Database : db_hikerent   |   Import lewat phpMyAdmin
--  Akun uji : admin@hikerent.com / password  (semua akun contoh: password)
-- ============================================================
CREATE DATABASE IF NOT EXISTS db_hikerent CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_hikerent;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS pengembalian, pembayaran, detail_transaksi, transaksi, keranjang, alat, kategori, users;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. USERS (admin & pelanggan)
CREATE TABLE users (
  id_user    INT AUTO_INCREMENT PRIMARY KEY,
  nama       VARCHAR(100) NOT NULL,
  email      VARCHAR(100) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  no_telp    VARCHAR(20) DEFAULT NULL,
  alamat     TEXT DEFAULT NULL,
  role       ENUM('admin','pelanggan') NOT NULL DEFAULT 'pelanggan',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. KATEGORI
CREATE TABLE kategori (
  id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(80) NOT NULL UNIQUE,
  deskripsi     VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- 3. ALAT
CREATE TABLE alat (
  id_alat     INT AUTO_INCREMENT PRIMARY KEY,
  id_kategori INT NOT NULL,
  nama_alat   VARCHAR(120) NOT NULL,
  deskripsi   TEXT,
  harga_sewa  INT UNSIGNED NOT NULL,
  stok        INT UNSIGNED NOT NULL DEFAULT 0,
  foto        VARCHAR(255) DEFAULT NULL,
  status      ENUM('tersedia','tidak_tersedia') NOT NULL DEFAULT 'tersedia',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_alat_kategori FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. KERANJANG
CREATE TABLE keranjang (
  id_keranjang INT AUTO_INCREMENT PRIMARY KEY,
  id_user      INT NOT NULL,
  id_alat      INT NOT NULL,
  jumlah       INT UNSIGNED NOT NULL DEFAULT 1,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_alat (id_user, id_alat),
  CONSTRAINT fk_keranjang_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
  CONSTRAINT fk_keranjang_alat FOREIGN KEY (id_alat) REFERENCES alat(id_alat) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. TRANSAKSI
CREATE TABLE transaksi (
  id_transaksi   INT AUTO_INCREMENT PRIMARY KEY,
  kode_transaksi VARCHAR(20) NOT NULL UNIQUE,
  id_user        INT NOT NULL,
  nama_penyewa   VARCHAR(100) NOT NULL,
  no_telp        VARCHAR(20) NOT NULL,
  alamat         TEXT NOT NULL,
  tgl_ambil      DATE NOT NULL,
  tgl_kembali    DATE NOT NULL,
  durasi         INT UNSIGNED NOT NULL,
  total_biaya    INT UNSIGNED NOT NULL,
  status         ENUM('menunggu','dikonfirmasi','diambil','selesai','ditolak','dibatalkan') NOT NULL DEFAULT 'menunggu',
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_transaksi_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 6. DETAIL TRANSAKSI
CREATE TABLE detail_transaksi (
  id_detail    INT AUTO_INCREMENT PRIMARY KEY,
  id_transaksi INT NOT NULL,
  id_alat      INT NOT NULL,
  jumlah       INT UNSIGNED NOT NULL,
  harga_satuan INT UNSIGNED NOT NULL,
  subtotal     INT UNSIGNED NOT NULL,
  CONSTRAINT fk_detail_transaksi FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
  CONSTRAINT fk_detail_alat      FOREIGN KEY (id_alat) REFERENCES alat(id_alat) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. PEMBAYARAN
CREATE TABLE pembayaran (
  id_pembayaran  INT AUTO_INCREMENT PRIMARY KEY,
  id_transaksi   INT NOT NULL UNIQUE,
  metode         VARCHAR(50) NOT NULL,
  jumlah         INT UNSIGNED NOT NULL,
  bukti          VARCHAR(255) DEFAULT NULL,
  status         ENUM('belum_bayar','menunggu_konfirmasi','berhasil','ditolak') NOT NULL DEFAULT 'belum_bayar',
  catatan        VARCHAR(255) DEFAULT NULL,
  tgl_upload     DATETIME DEFAULT NULL,
  tgl_verifikasi DATETIME DEFAULT NULL,
  CONSTRAINT fk_pembayaran_transaksi FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. PENGEMBALIAN
CREATE TABLE pengembalian (
  id_pengembalian    INT AUTO_INCREMENT PRIMARY KEY,
  id_transaksi       INT NOT NULL UNIQUE,
  tgl_dikembalikan   DATE NOT NULL,
  kondisi            ENUM('baik','rusak_ringan','rusak_berat','hilang') NOT NULL DEFAULT 'baik',
  hari_terlambat     INT UNSIGNED NOT NULL DEFAULT 0,
  denda_keterlambatan INT UNSIGNED NOT NULL DEFAULT 0,
  denda_kerusakan    INT UNSIGNED NOT NULL DEFAULT 0,
  total_denda        INT UNSIGNED NOT NULL DEFAULT 0,
  catatan            VARCHAR(255) DEFAULT NULL,
  id_admin           INT DEFAULT NULL,
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pengembalian_transaksi FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
  CONSTRAINT fk_pengembalian_admin     FOREIGN KEY (id_admin) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
--  DATA AWAL  (password semua akun = "password")
-- ============================================================
INSERT INTO users (id_user, nama, email, password, no_telp, alamat, role, created_at) VALUES
(1,'Administrator HikeRent','admin@hikerent.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','081234567890','Jl. Raya Dieng No. 10, Wonosobo','admin','2026-09-01 08:00:00'),
(2,'Budi Santoso','budi@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','081311112222','Jl. Merdeka No. 5, Wonosobo','pelanggan','2026-09-10 09:30:00'),
(3,'Siti Aminah','siti@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','082133334444','Jl. Kauman No. 12, Wonosobo','pelanggan','2026-09-20 14:15:00'),
(4,'Rizky Pratama','rizky@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','085255556666','Jl. Veteran No. 3, Temanggung','pelanggan','2026-10-05 16:45:00');

INSERT INTO kategori (id_kategori, nama_kategori, deskripsi) VALUES
(1,'Tenda','Tenda camping berbagai kapasitas'),
(2,'Carrier & Tas','Tas gunung untuk pendakian'),
(3,'Sleeping Bag','Kantong tidur hangat untuk di gunung'),
(4,'Matras','Alas tidur dan matras camping'),
(5,'Sepatu','Sepatu hiking dan trekking'),
(6,'Trekking Pole','Tongkat bantu pendakian'),
(7,'Alat Masak','Kompor dan peralatan masak outdoor'),
(8,'Penerangan','Headlamp dan lampu camping'),
(9,'Jaket & Pakaian','Jaket gunung dan pakaian outdoor');

INSERT INTO alat (id_alat, id_kategori, nama_alat, deskripsi, harga_sewa, stok, foto, status) VALUES
(1,1,'Tenda Dome 4 Orang','Tenda double layer kapasitas 4 orang, tahan angin dan hujan. Sudah termasuk flysheet, pasak, dan frame.',75000,5,'tenda-dome.svg','tersedia'),
(2,1,'Tenda Ultralight 2 Orang','Tenda ringan 2 orang dengan berat sekitar 2 kg, cocok untuk pendakian tektok maupun camp.',55000,6,'tenda-ultralight.svg','tersedia'),
(3,2,'Carrier 60L','Tas gunung 60 liter dengan sistem punggung ergonomis, rain cover, dan banyak kantong.',40000,8,'carrier-60l.svg','tersedia'),
(4,2,'Carrier 45L','Tas gunung 45 liter untuk pendakian 2 hari 1 malam. Ringan dan nyaman.',35000,6,'carrier-45l.svg','tersedia'),
(5,3,'Sleeping Bag Polar','Sleeping bag bahan polar hangat, nyaman untuk suhu hingga 10 derajat Celsius.',25000,10,'sleeping-bag.svg','tersedia'),
(6,5,'Sepatu Hiking Waterproof','Sepatu hiking anti air dengan sol anti selip. Tersedia ukuran 39-44 (sebutkan ukuran saat mengambil).',45000,7,'sepatu.svg','tersedia'),
(7,6,'Trekking Pole Carbon','Sepasang trekking pole carbon ringan, panjang dapat diatur 65-135 cm.',20000,12,'trekking-pole.svg','tersedia'),
(8,7,'Kompor Portable Windproof','Kompor gas portable dengan pelindung angin, mudah dibawa dan hemat gas.',15000,10,'kompor.svg','tersedia'),
(9,7,'Nesting Set Masak','Satu set nesting berisi panci, wajan, dan piring lipat untuk 2-3 orang.',20000,8,'nesting.svg','tersedia'),
(10,8,'Headlamp 300 Lumen','Headlamp LED 300 lumen dengan 3 mode cahaya, tahan percikan air.',15000,12,'headlamp.svg','tersedia'),
(11,4,'Matras Gulung Aluminium Foil','Matras gulung ringan dengan lapisan aluminium foil penahan dingin.',10000,15,'matras.svg','tersedia'),
(12,9,'Jaket Gunung Windproof','Jaket gunung tahan angin dan gerimis, tersedia ukuran M, L, dan XL.',35000,9,'jaket.svg','tersedia');

-- Transaksi contoh (supaya dashboard & laporan sudah berisi data)
INSERT INTO transaksi (id_transaksi, kode_transaksi, id_user, nama_penyewa, no_telp, alamat, tgl_ambil, tgl_kembali, durasi, total_biaya, status, created_at) VALUES
(1,'HR260928-A1B2C',2,'Budi Santoso','081311112222','Jl. Merdeka No. 5, Wonosobo','2026-10-03','2026-10-05',2,250000,'selesai','2026-09-28 10:00:00'),
(2,'HR261014-D3E4F',3,'Siti Aminah','082133334444','Jl. Kauman No. 12, Wonosobo','2026-10-18','2026-10-21',3,180000,'selesai','2026-10-14 11:20:00'),
(3,'HR261101-G5H6I',4,'Rizky Pratama','085255556666','Jl. Veteran No. 3, Temanggung','2026-11-05','2026-11-07',2,170000,'selesai','2026-11-01 09:10:00'),
(4,'HR261117-J7K8L',2,'Budi Santoso','081311112222','Jl. Merdeka No. 5, Wonosobo','2026-11-20','2026-11-24',4,380000,'diambil','2026-11-17 13:00:00'),
(5,'HR261119-M9N0P',3,'Siti Aminah','082133334444','Jl. Kauman No. 12, Wonosobo','2026-11-28','2026-11-30',2,340000,'dikonfirmasi','2026-11-19 15:30:00'),
(6,'HR261121-Q1R2S',4,'Rizky Pratama','085255556666','Jl. Veteran No. 3, Temanggung','2026-12-05','2026-12-07',2,140000,'menunggu','2026-11-21 20:05:00');

INSERT INTO detail_transaksi (id_transaksi, id_alat, jumlah, harga_satuan, subtotal) VALUES
(1,1,1,75000,150000),(1,5,2,25000,100000),
(2,3,1,40000,120000),(2,7,1,20000,60000),
(3,2,1,55000,110000),(3,10,2,15000,60000),
(4,6,1,45000,180000),(4,12,1,35000,140000),(4,8,1,15000,60000),
(5,1,2,75000,300000),(5,11,2,10000,40000),
(6,4,1,35000,70000),(6,9,1,20000,40000),(6,8,1,15000,30000);

INSERT INTO pembayaran (id_transaksi, metode, jumlah, bukti, status, catatan, tgl_upload, tgl_verifikasi) VALUES
(1,'Transfer BCA',250000,'contoh-bukti.svg','berhasil',NULL,'2026-09-28 10:30:00','2026-09-28 11:00:00'),
(2,'QRIS',180000,'contoh-bukti.svg','berhasil',NULL,'2026-10-14 12:00:00','2026-10-14 13:00:00'),
(3,'Transfer Mandiri',170000,'contoh-bukti.svg','berhasil',NULL,'2026-11-01 09:30:00','2026-11-01 10:15:00'),
(4,'DANA',380000,'contoh-bukti.svg','berhasil',NULL,'2026-11-17 13:20:00','2026-11-17 14:00:00'),
(5,'Transfer BCA',340000,'contoh-bukti.svg','berhasil',NULL,'2026-11-19 16:00:00','2026-11-19 17:00:00'),
(6,'QRIS',140000,'contoh-bukti.svg','menunggu_konfirmasi',NULL,'2026-11-21 20:15:00',NULL);

INSERT INTO pengembalian (id_transaksi, tgl_dikembalikan, kondisi, hari_terlambat, denda_keterlambatan, denda_kerusakan, total_denda, catatan, id_admin, created_at) VALUES
(1,'2026-10-05','baik',0,0,0,0,'Semua alat kembali lengkap',1,'2026-10-05 16:00:00'),
(2,'2026-10-22','baik',1,30000,0,30000,'Terlambat 1 hari',1,'2026-10-22 10:00:00'),
(3,'2026-11-07','baik',0,0,0,0,'Kondisi baik',1,'2026-11-07 15:00:00');
