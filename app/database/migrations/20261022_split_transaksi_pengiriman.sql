-- Migrasi pemisahan transaksi dan pengiriman.
-- WAJIB backup database terlebih dahulu. Jalankan satu kali pada database lama.
-- Jika tabel pengiriman sudah ada, pastikan data dan skema sebelum menjalankan ulang.

CREATE TABLE IF NOT EXISTS `pengiriman` (
  `id_pengiriman` int(11) NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(15) NOT NULL,
  `metode_pengiriman` enum('ambil','kirim') NOT NULL DEFAULT 'ambil',
  `kelurahan` varchar(100) DEFAULT NULL,
  `nama_penerima` varchar(150) DEFAULT NULL,
  `no_hp_penerima` varchar(25) DEFAULT NULL,
  `alamat_pengiriman` text DEFAULT NULL,
  `ongkir` decimal(12,2) NOT NULL DEFAULT 0.00,
  `kecamatan` varchar(100) DEFAULT NULL,
  `siap_diambil` tinyint(1) NOT NULL DEFAULT 0,
  `tanggal_kirim` datetime DEFAULT NULL,
  PRIMARY KEY (`id_pengiriman`),
  UNIQUE KEY `uq_pengiriman_kode_transaksi` (`kode_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pengiriman`
(`kode_transaksi`,`metode_pengiriman`,`kelurahan`,`nama_penerima`,`no_hp_penerima`,`alamat_pengiriman`,`ongkir`,`kecamatan`,`siap_diambil`,`tanggal_kirim`)
SELECT `kode_transaksi`,
 CASE WHEN `metode_pengiriman` IN ('ambil','kirim') THEN `metode_pengiriman` ELSE 'ambil' END,
 `kelurahan`,`nama_penerima`,`no_hp_penerima`,`alamat_pengiriman`,`ongkir`,`kecamatan`,`siap_diambil`,`tanggal_kirim`
FROM `transaksi`
ON DUPLICATE KEY UPDATE
 `metode_pengiriman`=VALUES(`metode_pengiriman`), `kelurahan`=VALUES(`kelurahan`),
 `nama_penerima`=VALUES(`nama_penerima`), `no_hp_penerima`=VALUES(`no_hp_penerima`),
 `alamat_pengiriman`=VALUES(`alamat_pengiriman`), `ongkir`=VALUES(`ongkir`),
 `kecamatan`=VALUES(`kecamatan`), `siap_diambil`=VALUES(`siap_diambil`), `tanggal_kirim`=VALUES(`tanggal_kirim`);

ALTER TABLE `transaksi`
 MODIFY `status_bayar` enum('belum_bayar','menunggu_verifikasi','lunas','ditolak','kedaluwarsa','perlu_refund','dikembalikan') NOT NULL DEFAULT 'belum_bayar',
 MODIFY `sumber_transaksi` enum('online','kasir') NOT NULL DEFAULT 'online',
 DROP COLUMN `metode_pengiriman`,
 DROP COLUMN `kelurahan`,
 DROP COLUMN `nama_penerima`,
 DROP COLUMN `no_hp_penerima`,
 DROP COLUMN `alamat_pengiriman`,
 DROP COLUMN `ongkir`,
 DROP COLUMN `kecamatan`,
 DROP COLUMN `siap_diambil`,
 DROP COLUMN `tanggal_kirim`;

ALTER TABLE `pengiriman`
 ADD CONSTRAINT `fk_pengiriman_transaksi` FOREIGN KEY (`kode_transaksi`) REFERENCES `transaksi` (`kode_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE;
