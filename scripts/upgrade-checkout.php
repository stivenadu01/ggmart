<?php
require __DIR__ . '/bootstrap.php';
$conn=db();
$dir=__DIR__ . '/../storage/backups';
if (!is_dir($dir)) mkdir($dir,0700,true);
$backup=fopen($dir . '/before-checkout-fix-' . date('Ymd-His') . '.sql','x');
if (!$backup) throw new RuntimeException('Backup database belum dapat dibuat.');
fwrite($backup,"SET FOREIGN_KEY_CHECKS=0;\n");
foreach ($conn->query('SHOW TABLES')->fetch_all(MYSQLI_NUM) as [$table]) {
  if (!preg_match('/^[a-zA-Z0-9_]+$/',$table)) continue;
  $create=$conn->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
  fwrite($backup,"DROP TABLE IF EXISTS `$table`;\n$create;\n");
  foreach ($conn->query("SELECT * FROM `$table`")->fetch_all(MYSQLI_ASSOC) as $row) {
    $values=array_map(fn($v)=>$v===null ? 'NULL' : "'" . $conn->real_escape_string((string)$v) . "'",array_values($row));
    $columns=implode(',',array_map(fn($v)=>"`$v`",array_keys($row)));
    fwrite($backup,"INSERT INTO `$table` ($columns) VALUES (" . implode(',',$values) . ");\n");
  }
}
fwrite($backup,"SET FOREIGN_KEY_CHECKS=1;\n"); fclose($backup);
require __DIR__ . '/checkout-schema.php';
echo "Struktur checkout diperbarui; data dan tarif yang sudah ada dipertahankan. Backup tersedia di storage/backups.\n";
