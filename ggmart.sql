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
    kode_produk CHAR(15) PRIMARY KEY,
    id_kategori INT,
    nama_produk VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    harga_jual DECIMAL(12, 2) NOT NULL,
    satuan_dasar CHAR(10),
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
  kode_produk CHAR(15),
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
    kode_transaksi CHAR(15) PRIMARY KEY,
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
    kode_transaksi CHAR(15) NOT NULL,
    kode_produk CHAR(15),
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
