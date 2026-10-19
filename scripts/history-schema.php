<?php
// Migrasi riwayat dipanggil oleh script CLI setelah database dicadangkan.
function legacyPaymentHistoryExists(mysqli $conn): bool
{
  return (bool)$conn->query("SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='riwayat_pembayaran'")->fetch_row();
}

function legacyPaymentEventSql(): string
{
  return "CASE WHEN p.catatan='Pesanan dibuat.' THEN 'checkout' WHEN p.catatan LIKE 'Pesanan dibatalkan.%' AND p.status<>'perlu_refund' THEN 'dibatalkan' ELSE CONCAT('pembayaran_',p.status) END";
}

function verifyLegacyPaymentHistory(mysqli $conn): int
{
  if (!legacyPaymentHistoryExists($conn)) return 0;
  $event = legacyPaymentEventSql();
  $missing = (int)$conn->query("SELECT COUNT(*) FROM riwayat_pembayaran p
    LEFT JOIN riwayat_pesanan r ON r.kunci_peristiwa=CONCAT('pembayaran:',p.id)
    WHERE r.id IS NULL OR NOT (r.kode_transaksi <=> p.kode_transaksi)
    OR NOT (BINARY r.peristiwa <=> BINARY ($event))
    OR NOT (BINARY r.catatan <=> BINARY p.catatan)
    OR NOT (r.petugas <=> p.petugas) OR NOT (r.tanggal <=> p.tanggal)
    OR NOT (r.status_bayar <=> p.status)
    OR r.kategori<>CASE WHEN ($event) IN ('checkout','dibatalkan') THEN 'pesanan' ELSE 'pembayaran' END")->fetch_row()[0];
  if ($missing) throw new RuntimeException("$missing catatan pembayaran belum tersalin lengkap. Tabel lama dipertahankan.");
  return (int)$conn->query('SELECT COUNT(*) FROM riwayat_pembayaran')->fetch_row()[0];
}

// Tanpa DDL/commit agar pemindahan data dapat diuji dan dibatalkan secara atomik.
function importLegacyPaymentHistory(mysqli $conn): int
{
  if (!legacyPaymentHistoryExists($conn)) return 0;
  $collision = (int)$conn->query("SELECT COUNT(*) FROM riwayat_pembayaran p
    JOIN riwayat_pesanan r ON r.kunci_peristiwa=CONCAT('pembayaran:',p.id)
    WHERE r.kode_transaksi<>p.kode_transaksi")->fetch_row()[0];
  if ($collision) throw new RuntimeException('Kunci riwayat lama bertabrakan dengan pesanan lain. Migrasi dihentikan.');
  $event = legacyPaymentEventSql();
  $conn->query("UPDATE riwayat_pesanan r JOIN riwayat_pembayaran p ON r.kunci_peristiwa=CONCAT('pembayaran:',p.id)
    SET r.peristiwa=$event, r.catatan=p.catatan, r.petugas=p.petugas, r.tanggal=p.tanggal,
    r.status_bayar=p.status,
    r.kategori=CASE WHEN ($event) IN ('checkout','dibatalkan') THEN 'pesanan' ELSE 'pembayaran' END");
  $conn->query("INSERT INTO riwayat_pesanan(kode_transaksi,kategori,peristiwa,status_bayar,catatan,petugas,kunci_peristiwa,tanggal)
    SELECT p.kode_transaksi,CASE WHEN ($event) IN ('checkout','dibatalkan') THEN 'pesanan' ELSE 'pembayaran' END,
    $event,p.status,p.catatan,p.petugas,CONCAT('pembayaran:',p.id),p.tanggal FROM riwayat_pembayaran p
    WHERE NOT EXISTS (SELECT 1 FROM riwayat_pesanan r WHERE r.kunci_peristiwa=CONCAT('pembayaran:',p.id))");
  $added = $conn->affected_rows;
  verifyLegacyPaymentHistory($conn);
  return $added;
}

function unifyHistorySchema(mysqli $conn): array
{
  $conn->query("CREATE TABLE IF NOT EXISTS riwayat_pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(15) NOT NULL,
    kategori ENUM('pesanan','pembayaran') NOT NULL DEFAULT 'pesanan',
    peristiwa VARCHAR(50) NOT NULL,
    status_bayar VARCHAR(30) DEFAULT NULL,
    catatan VARCHAR(500), petugas INT DEFAULT NULL,
    kunci_peristiwa VARCHAR(100) DEFAULT NULL UNIQUE,
    tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_pesanan_tanggal(kode_transaksi,tanggal,id),
    FOREIGN KEY(kode_transaksi) REFERENCES transaksi(kode_transaksi) ON DELETE CASCADE
  ) ENGINE=InnoDB");
  $columns = array_column($conn->query('SHOW COLUMNS FROM riwayat_pesanan')->fetch_all(MYSQLI_ASSOC),'Field');
  if (!in_array('kategori',$columns,true)) $conn->query("ALTER TABLE riwayat_pesanan ADD kategori ENUM('pesanan','pembayaran') NOT NULL DEFAULT 'pesanan' AFTER kode_transaksi");
  if (!in_array('status_bayar',$columns,true)) $conn->query('ALTER TABLE riwayat_pesanan ADD status_bayar VARCHAR(30) DEFAULT NULL AFTER peristiwa');
  $conn->begin_transaction();
  try {
    $conn->query("UPDATE riwayat_pesanan SET kategori='pembayaran',status_bayar=SUBSTRING(peristiwa,12)
      WHERE LEFT(peristiwa,11)='pembayaran_' AND status_bayar IS NULL");
    $conn->query("UPDATE riwayat_pesanan SET status_bayar='belum_bayar' WHERE peristiwa='checkout' AND status_bayar IS NULL");
    $added = importLegacyPaymentHistory($conn);
    $verified = verifyLegacyPaymentHistory($conn);
    $conn->commit();
    return ['ditambahkan'=>$added,'riwayat_lama_terverifikasi'=>$verified];
  } catch (Throwable $e) { $conn->rollback(); throw $e; }
}

function retireLegacyPaymentHistory(mysqli $conn): void
{
  if (!legacyPaymentHistoryExists($conn)) return;
  verifyLegacyPaymentHistory($conn);
  $conn->query('DROP TABLE riwayat_pembayaran');
}
