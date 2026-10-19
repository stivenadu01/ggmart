<?php
// Backup privat sebelum perubahan skema; tidak mencetak isi/rahasia database.
function backupDatabase(string $label): string
{
  if (!preg_match('/^[a-z0-9-]+$/',$label)) throw new RuntimeException('Nama backup tidak valid.');
  $dir=ROOT_PATH . '/storage/backups';
  if (!is_dir($dir) && !mkdir($dir,0700,true)) throw new RuntimeException('Folder backup tidak dapat dibuat.');
  $path=$dir . '/' . $label . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.sql';
  $file=fopen($path,'x');
  if (!$file) throw new RuntimeException('Backup tidak dapat dibuat.');
  $write=static function(string $value) use($file): void {
    if (fwrite($file,$value)!==strlen($value)) throw new RuntimeException('Backup belum lengkap.');
  };
  $conn=db(); $conn->begin_transaction(MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT);
  try {
    $write("SET FOREIGN_KEY_CHECKS=0;\n");
    foreach ($conn->query('SHOW TABLES')->fetch_all(MYSQLI_NUM) as [$table]) {
      if (!preg_match('/^[a-zA-Z0-9_]+$/',$table)) throw new RuntimeException('Nama tabel tidak valid.');
      $write("DROP TABLE IF EXISTS `$table`;\n" . $conn->query("SHOW CREATE TABLE `$table`")->fetch_row()[1] . ";\n");
      foreach ($conn->query("SELECT * FROM `$table`")->fetch_all(MYSQLI_ASSOC) as $row) {
        $columns=implode(',',array_map(static fn($v)=>"`$v`",array_keys($row)));
        $values=array_map(static fn($v)=>$v===null ? 'NULL' : "'" . $conn->real_escape_string((string)$v) . "'",array_values($row));
        $write("INSERT INTO `$table` ($columns) VALUES (" . implode(',',$values) . ");\n");
      }
    }
    $write("SET FOREIGN_KEY_CHECKS=1;\n");
    if (!fflush($file)) throw new RuntimeException('Backup belum tersimpan.');
    $conn->commit();
  } catch(Throwable $e) { $conn->rollback(); throw $e; }
  finally { fclose($file); }
  return $path;
}
