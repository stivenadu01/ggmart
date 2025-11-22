-- DATABASE: ggmart
CREATE DATABASE IF NOT EXISTS ggmart;
USE ggmart;

-- TABLE: user
CREATE TABLE IF NOT EXISTS user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir', 'manager', 'user') DEFAULT 'user',
    tanggal_dibuat DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- TABLE: kategori
CREATE TABLE IF NOT EXISTS kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT
);

-- TABLE: produk
CREATE TABLE IF NOT EXISTS produk (
    kode_produk VARCHAR(15) PRIMARY KEY,
    id_kategori INT,
    nama_produk VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    harga_jual DECIMAL(12, 2) NOT NULL,
    satuan_dasar VARCHAR(10),
    is_lokal TINYINT(1) DEFAULT 0,
    stok INT DEFAULT 0,
    terjual INT DEFAULT 0,
    gambar VARCHAR(255),
    tanggal_dibuat DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_produk_kategori
        FOREIGN KEY (id_kategori)
        REFERENCES kategori(id_kategori)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS mutasi_stok (
  id_mutasi INT AUTO_INCREMENT PRIMARY KEY,
  kode_produk VARCHAR(15),
  nama_produk VARCHAR(150), -- kalau produk dihapus tetap ada nama
  type ENUM('masuk','keluar') NOT NULL,
  jumlah INT NOT NULL,
  total_pokok DECIMAL(12, 2),
  keterangan TEXT,
  harga_pokok DECIMAL(12, 2),
  sisa_stok INT,
  tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (kode_produk) REFERENCES produk(kode_produk)
    ON DELETE SET NULL
    ON UPDATE CASCADE
);

-- TABLE: transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    kode_transaksi VARCHAR(15) PRIMARY KEY,
    id_user INT,
    tanggal_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_harga DECIMAL(12, 2) NOT NULL,
    total_pokok DECIMAL(12, 2),
    status ENUM('selesai') DEFAULT 'selesai',
    metode_bayar ENUM('qris','tunai') DEFAULT 'tunai',
    CONSTRAINT fk_transaksi_user
        FOREIGN KEY (id_user)
        REFERENCES user(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- TABLE: detail_transaksi
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(15) NOT NULL,
    kode_produk VARCHAR(15),
    jumlah INT DEFAULT 1,
    harga_satuan DECIMAL(12, 2),
    harga_pokok DECIMAL(12, 2),
    subtotal DECIMAL(12, 2),
    subtotal_pokok DECIMAL(12, 2),

    CONSTRAINT fk_detail_transaksi
        FOREIGN KEY (kode_transaksi)
        REFERENCES transaksi(kode_transaksi)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT fk_detail_produk
        FOREIGN KEY (kode_produk)
        REFERENCES produk(kode_produk)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS setting (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,  
    value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS landing_hero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(50) NOT NULL,  
    subtitle VARCHAR(50) NOT NULL,
    cta_primary_text VARCHAR(50),
    cta_primary_url VARCHAR(100),
    cta_secondary_text VARCHAR(50),
    cta_secondary_url VARCHAR(100),
    `text` VARCHAR (100),
    urutan INT DEFAULT 0,
    image_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS gallery (
    id_galery INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS gallery_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_gallery INT NOT NULL,
    kode_produk VARCHAR(15),   -- relasi ke produk dipindahkan ke sini
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_gallery
        FOREIGN KEY (gallery_id) REFERENCES gallery(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_gallery_produk
        FOREIGN KEY (kode_produk) REFERENCES produk(kode_produk)
        ON DELETE SET NULL
);


-- index
ALTER TABLE produk
  ADD INDEX idx_kode_produk (kode_produk),
  ADD INDEX idx_nama_produk (nama_produk),
  ADD INDEX idx_id_kategori (id_kategori),
  ADD INDEX idx_tanggal_dibuat (tanggal_dibuat);

ALTER TABLE kategori ADD INDEX idx_id_kategori (id_kategori);

ALTER TABLE detail_transaksi
  ADD INDEX idx_kode_transaksi (kode_transaksi),
  ADD INDEX idx_kode_produk (kode_produk);

ALTER TABLE transaksi
  ADD INDEX idx_kode_transaksi (kode_transaksi),
  ADD INDEX idx_tanggal_transaksi (tanggal_transaksi),
  ADD INDEX idx_metode_bayar (metode_bayar),
  ADD INDEX idx_id_user (id_user);

ALTER TABLE user
  ADD INDEX idx_email (email),
  ADD INDEX idx_id_user (id_user);

ALTER TABLE mutasi_stok
  ADD INDEX idx_tanggal(tanggal);
