<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/history-schema.php';
$conn = db();
$finalize = in_array('--finalize',$argv,true);
$dir = ROOT_PATH . '/storage/backups';
if (!is_dir($dir) && !mkdir($dir,0700,true)) throw new RuntimeException('Folder backup belum dapat dibuat.');
$backupPath = $dir . '/before-unified-history-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.sql';
$backup = fopen($backupPath,'x');
if (!$backup) throw new RuntimeException('Backup database belum dapat dibuat.');
$write = function (string $value) use ($backup): void {
  if (fwrite($backup,$value)!==strlen($value)) throw new RuntimeException('Backup database belum lengkap. Migrasi dihentikan.');
};
$conn->begin_transaction(MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT);
try {
  $write("SET FOREIGN_KEY_CHECKS=0;\n");
  foreach ($conn->query('SHOW TABLES')->fetch_all(MYSQLI_NUM) as [$table]) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/',$table)) throw new RuntimeException('Nama tabel tidak dapat dicadangkan.');
    $create = $conn->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
    $write("DROP TABLE IF EXISTS `$table`;\n$create;\n");
    foreach ($conn->query("SELECT * FROM `$table`")->fetch_all(MYSQLI_ASSOC) as $row) {
      $values = array_map(fn($v) => $v===null ? 'NULL' : "'" . $conn->real_escape_string((string)$v) . "'",array_values($row));
      $columns = implode(',',array_map(fn($v) => "`$v`",array_keys($row)));
      $write("INSERT INTO `$table` ($columns) VALUES (" . implode(',',$values) . ");\n");
    }
  }
  $write("SET FOREIGN_KEY_CHECKS=1;\n");
  if (!fflush($backup)) throw new RuntimeException('Backup belum tersimpan lengkap.');
  $conn->commit();
} catch (Throwable $e) { $conn->rollback(); throw $e; }
finally { fclose($backup); }
$result = unifyHistorySchema($conn);
if ($finalize) retireLegacyPaymentHistory($conn);
$result['tabel_lama_dihapus'] = !legacyPaymentHistoryExists($conn);
$result['backup'] = $backupPath;
echo json_encode($result,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
