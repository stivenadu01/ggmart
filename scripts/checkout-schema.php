<?php
// Dipanggil setelah bootstrap dan backup oleh migrate.php atau upgrade-checkout.php.
$conn=db();
$conn->query("ALTER TABLE transaksi MODIFY status ENUM('pending','diproses','dikirim','dibatalkan','selesai') DEFAULT 'pending'");
$existing=array_column($conn->query('SHOW COLUMNS FROM transaksi')->fetch_all(MYSQLI_ASSOC),'Field');
foreach (['kecamatan'=>'VARCHAR(100) DEFAULT NULL','sumber_transaksi'=>"VARCHAR(10) NOT NULL DEFAULT 'online'",'siap_diambil'=>'TINYINT NOT NULL DEFAULT 0','tanggal_kirim'=>'DATETIME DEFAULT NULL'] as $name=>$definition) {
  if (!in_array($name,$existing,true)) $conn->query("ALTER TABLE transaksi ADD `$name` $definition");
}
if (!in_array('sumber_transaksi',$existing,true)) $conn->query("UPDATE transaksi t JOIN pengguna u ON u.id_pengguna=t.id_pengguna SET t.sumber_transaksi='kasir' WHERE u.role IN ('admin','kasir')");
$zoneColumns=array_column($conn->query('SHOW COLUMNS FROM zona_pengiriman')->fetch_all(MYSQLI_ASSOC),'Field');
if (!in_array('kecamatan',$zoneColumns,true)) $conn->query("ALTER TABLE zona_pengiriman ADD kecamatan VARCHAR(100) NOT NULL DEFAULT ''");
foreach (require __DIR__ . '/data/kecamatan.php' as $district=>$names) {
  foreach ($names as $name) $conn->execute_query("UPDATE zona_pengiriman SET kecamatan=? WHERE kelurahan=? AND kecamatan=''",[$district,$name]);
}
$conn->query("UPDATE transaksi t JOIN zona_pengiriman z ON z.kelurahan=t.kelurahan SET t.kecamatan=z.kecamatan WHERE t.kecamatan IS NULL AND t.metode_pengiriman='kirim'");
require_once __DIR__ . '/history-schema.php';
unifyHistorySchema($conn);
// Salin jejak yang diketahui waktunya; jangan mengarang tahapan pesanan lama.
$conn->query("INSERT IGNORE INTO riwayat_pesanan(kode_transaksi,peristiwa,status_bayar,catatan,kunci_peristiwa,tanggal)
  SELECT t.kode_transaksi,'checkout','belum_bayar','Pesanan dibuat.',CONCAT('checkout:',t.kode_transaksi),t.tanggal_transaksi FROM transaksi t
  WHERE NOT EXISTS(SELECT 1 FROM riwayat_pesanan r WHERE r.kode_transaksi=t.kode_transaksi AND r.peristiwa='checkout')");
foreach (['tanggal_kirim'=>['dikirim','Barang diserahkan kepada pengantar.'], 'tanggal_selesai'=>['selesai','Barang diterima. Pesanan selesai.']] as $dateColumn=>[$event,$note]) {
  $conn->execute_query("INSERT IGNORE INTO riwayat_pesanan(kode_transaksi,peristiwa,catatan,kunci_peristiwa,tanggal)
    SELECT t.kode_transaksi,?,?,CONCAT(?,t.kode_transaksi),t.$dateColumn FROM transaksi t WHERE t.$dateColumn IS NOT NULL
    AND NOT EXISTS(SELECT 1 FROM riwayat_pesanan r WHERE r.kode_transaksi=t.kode_transaksi AND r.peristiwa=?)",[$event,$note,$event . ':',$event]);
}
// Terapkan aturan baru juga pada pembayaran lunas yang belum mulai diproses.
$conn->query("INSERT INTO riwayat_pesanan(kode_transaksi,peristiwa,catatan)
  SELECT kode_transaksi,'diproses','Pembayaran telah diterima. Pesanan mulai disiapkan.' FROM transaksi WHERE sumber_transaksi='online' AND status='pending' AND status_bayar='lunas'");
$conn->query("UPDATE transaksi SET status='diproses',batas_bayar=NULL WHERE sumber_transaksi='online' AND status='pending' AND status_bayar='lunas'");
// Tabel lama tetap tersedia sebagai arsip sampai finalisasi eksplisit.
require_once __DIR__ . '/notification-schema.php';
notificationSchema($conn);
