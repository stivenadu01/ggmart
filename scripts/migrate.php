<?php
require __DIR__ . '/bootstrap.php';
$conn = db();
// Simpan salinan struktur dan data sebelum migrasi. Folder ini tidak dilayani HTTP.
$dir = __DIR__ . '/../storage/backups';
if (!is_dir($dir)) mkdir($dir, 0700, true);
$backup = fopen($dir . '/before-checkout-' . date('Ymd-His') . '.sql', 'x');
fwrite($backup, "SET FOREIGN_KEY_CHECKS=0;\n");
foreach ($conn->query('SHOW TABLES')->fetch_all(MYSQLI_NUM) as [$table]) {
  if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) continue;
  $create = $conn->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
  fwrite($backup, "DROP TABLE IF EXISTS `$table`;\n$create;\n");
  foreach ($conn->query("SELECT * FROM `$table`")->fetch_all(MYSQLI_ASSOC) as $row) {
    $values = array_map(fn($v) => $v === null ? 'NULL' : "'" . $conn->real_escape_string((string)$v) . "'", array_values($row));
    $columns = implode(',', array_map(fn($v) => "`$v`", array_keys($row)));
    fwrite($backup, "INSERT INTO `$table` ($columns) VALUES (" . implode(',', $values) . ");\n");
  }
}
fwrite($backup, "SET FOREIGN_KEY_CHECKS=1;\n"); fclose($backup);
$conn->query("ALTER TABLE transaksi MODIFY metode_bayar ENUM('tunai','transfer','qris') DEFAULT 'tunai'");
$columns = [
 'status_bayar' => "VARCHAR(30) NOT NULL DEFAULT 'belum_bayar'",
 'metode_pengiriman' => "VARCHAR(10) NOT NULL DEFAULT 'ambil'",
 'kelurahan' => 'VARCHAR(100) DEFAULT NULL',
 'nama_penerima' => 'VARCHAR(150) DEFAULT NULL',
 'no_hp_penerima' => 'VARCHAR(25) DEFAULT NULL',
 'alamat_pengiriman' => 'TEXT DEFAULT NULL',
 'ongkir' => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
 'total_tagihan' => 'DECIMAL(12,2) NOT NULL DEFAULT 0',
 'batas_bayar' => 'DATETIME DEFAULT NULL',
 'tanggal_bayar' => 'DATETIME DEFAULT NULL',
 'tanggal_selesai' => 'DATETIME DEFAULT NULL',
 'bukti_bayar' => 'VARCHAR(255) DEFAULT NULL',
 'catatan_bayar' => 'VARCHAR(500) DEFAULT NULL',
 'midtrans_order_id' => 'VARCHAR(80) DEFAULT NULL',
 'midtrans_id' => 'VARCHAR(100) DEFAULT NULL',
 'midtrans_env' => 'VARCHAR(15) DEFAULT NULL',
 'qris_url' => 'VARCHAR(500) DEFAULT NULL',
 'checkout_key' => 'VARCHAR(64) DEFAULT NULL',
];
$existing = array_column($conn->query('SHOW COLUMNS FROM transaksi')->fetch_all(MYSQLI_ASSOC), 'Field');
foreach ($columns as $name => $definition) {
  if (!in_array($name, $existing, true)) $conn->query("ALTER TABLE transaksi ADD `$name` $definition");
}
$conn->query("UPDATE transaksi SET total_tagihan=total_harga+ongkir WHERE total_tagihan=0");
$conn->query("UPDATE transaksi SET status_bayar='lunas', tanggal_bayar=COALESCE(tanggal_bayar,tanggal_transaksi), tanggal_selesai=COALESCE(tanggal_selesai,tanggal_transaksi) WHERE status='selesai' AND status_bayar='belum_bayar'");
$conn->query("CREATE TABLE IF NOT EXISTS zona_pengiriman (id INT AUTO_INCREMENT PRIMARY KEY, kelurahan VARCHAR(100) NOT NULL UNIQUE, tarif DECIMAL(12,2) NOT NULL, aktif TINYINT NOT NULL DEFAULT 1) ENGINE=InnoDB");
$conn->query("CREATE TABLE IF NOT EXISTS pengaturan (nama VARCHAR(80) PRIMARY KEY, nilai TEXT NOT NULL) ENGINE=InnoDB");
$conn->query("CREATE TABLE IF NOT EXISTS reservasi_stok (kode_transaksi VARCHAR(15) NOT NULL, kode_produk VARCHAR(15) NOT NULL, jumlah INT NOT NULL, PRIMARY KEY(kode_transaksi,kode_produk), FOREIGN KEY(kode_transaksi) REFERENCES transaksi(kode_transaksi) ON DELETE CASCADE, FOREIGN KEY(kode_produk) REFERENCES produk(kode_produk) ON DELETE RESTRICT ON UPDATE CASCADE) ENGINE=InnoDB");
$conn->query("CREATE TABLE IF NOT EXISTS alokasi_stok (kode_transaksi VARCHAR(15) NOT NULL, id_mutasi INT NOT NULL, jumlah INT NOT NULL, harga_pokok DECIMAL(12,2) NOT NULL, PRIMARY KEY(kode_transaksi,id_mutasi), FOREIGN KEY(kode_transaksi) REFERENCES transaksi(kode_transaksi), FOREIGN KEY(id_mutasi) REFERENCES mutasi_stok(id_mutasi)) ENGINE=InnoDB");
$indexes = array_column($conn->query('SHOW INDEX FROM transaksi')->fetch_all(MYSQLI_ASSOC), 'Key_name');
foreach (['uq_checkout_key' => 'checkout_key', 'uq_midtrans_order' => 'midtrans_order_id'] as $index => $column) {
  if (!in_array($index, $indexes, true)) $conn->query("ALTER TABLE transaksi ADD UNIQUE KEY $index ($column)");
}
$mutasiColumns = array_column($conn->query('SHOW COLUMNS FROM mutasi_stok')->fetch_all(MYSQLI_ASSOC), 'Field');
if (!in_array('kode_transaksi', $mutasiColumns, true)) $conn->query('ALTER TABLE mutasi_stok ADD kode_transaksi VARCHAR(15) DEFAULT NULL');
foreach (require __DIR__ . '/data/tarif-awal.php' as $tarif => $names) {
  foreach ($names as $name) $conn->execute_query('INSERT IGNORE INTO zona_pengiriman(kelurahan,tarif) VALUES (?,?)', [$name,$tarif]);
}
require __DIR__ . '/checkout-schema.php';
echo "Migrasi selesai. Zona pengiriman tersedia; backup disimpan di storage/backups.\n";
