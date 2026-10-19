<?php
function notificationSchema(mysqli $conn): void
{
  $conn->query("CREATE TABLE IF NOT EXISTS notifikasi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_riwayat INT NOT NULL,
    id_pengguna INT NOT NULL,
    peran VARCHAR(20) NOT NULL,
    jenis VARCHAR(50) NOT NULL,
    judul VARCHAR(150) NOT NULL,
    pesan VARCHAR(500) NOT NULL,
    tautan VARCHAR(250) NOT NULL,
    popup TINYINT NOT NULL DEFAULT 1,
    dibaca_pada DATETIME DEFAULT NULL,
    tanggal DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    UNIQUE KEY uq_notifikasi_penerima(id_riwayat,id_pengguna),
    KEY idx_notifikasi_inbox(id_pengguna,peran,dibaca_pada,id),
    KEY idx_notifikasi_terbaru(id_pengguna,peran,tanggal),
    FOREIGN KEY(id_riwayat) REFERENCES riwayat_pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY(id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $conn->query("CREATE TABLE IF NOT EXISTS notifikasi_kirim (
    id_notifikasi BIGINT UNSIGNED NOT NULL,
    perangkat CHAR(36) NOT NULL,
    tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(id_notifikasi,perangkat),
    FOREIGN KEY(id_notifikasi) REFERENCES notifikasi(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $fields=array_column($conn->query('SHOW COLUMNS FROM transaksi')->fetch_all(MYSQLI_ASSOC),'Field');
  if (!in_array('tanggal_cek_qris',$fields,true)) $conn->query('ALTER TABLE transaksi ADD tanggal_cek_qris DATETIME DEFAULT NULL');
}
