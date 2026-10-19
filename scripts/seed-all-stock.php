<?php
require __DIR__ . '/bootstrap.php';
$plan=require __DIR__ . '/data/stok-masuk.php';
$description='Penerimaan persediaan 12 Oktober 2026';
$lock='ggmart-stock-' . substr(hash('sha256',$_ENV['DB_NAME'] . $description),0,40);
if ((int)oq('SELECT GET_LOCK(?,10) AS acquired',[$lock])->fetch_assoc()['acquired']!==1) throw new RuntimeException('Pengisian stok lain sedang berjalan.');
$inTransaction=false; $temporaryReceipt=null;
try {
  $products=oq('SELECT kode_produk,nama_produk,harga_jual FROM produk ORDER BY kode_produk')->fetch_all(MYSQLI_ASSOC);
  foreach ($products as $p) if (!isset($plan[$p['kode_produk']])) throw new RuntimeException('Rencana stok belum tersedia untuk ' . $p['nama_produk']);
  if (count($products)!==count($plan)) throw new RuntimeException('Daftar produk berubah. Periksa rencana penerimaan sebelum menjalankan ulang.');
  $prior=oq("SELECT kode_produk,COUNT(*) n FROM mutasi_stok WHERE type='masuk' AND keterangan=? GROUP BY kode_produk",[$description])->fetch_all(MYSQLI_ASSOC);
  if ($prior) {
    if (count($prior)!==count($products)) throw new RuntimeException('Sebagian penerimaan sudah ada. Periksa catatan sebelum mengisi kembali.');
    foreach ($prior as $p) if (!isset($plan[$p['kode_produk']]) || (int)$p['n']!==1) throw new RuntimeException('Catatan penerimaan tidak sesuai. Tidak menambahkan stok ganda.');
    echo 'Penerimaan untuk ' . count($prior) . " produk sudah ada; stok tidak ditambahkan lagi.\n";
    return;
  }

  // Backup seluruh database pada satu snapshot konsisten sebelum menulis stok.
  $backupDir=ROOT_PATH . '/storage/backups';
  if (!is_dir($backupDir)) mkdir($backupDir,0700,true);
  $backupPath=$backupDir . '/before-all-stock-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.sql';
  $backup=fopen($backupPath,'x');
  if (!$backup) throw new RuntimeException('Cadangan database belum dapat dibuat.');
  db()->query('START TRANSACTION WITH CONSISTENT SNAPSHOT'); $inTransaction=true;
  try {
    $write=function(string $text) use($backup): void { if (fwrite($backup,$text)!==strlen($text)) throw new RuntimeException('Cadangan database belum tersimpan lengkap.'); };
    $write("SET FOREIGN_KEY_CHECKS=0;\n");
    foreach (oq('SHOW TABLES')->fetch_all(MYSQLI_NUM) as [$table]) {
      if (!preg_match('/^[a-zA-Z0-9_]+$/',$table)) throw new RuntimeException('Nama tabel tidak valid.');
      $create=oq("SHOW CREATE TABLE `$table`")->fetch_row()[1];
      $write("DROP TABLE IF EXISTS `$table`;\n$create;\n");
      foreach (oq("SELECT * FROM `$table`")->fetch_all(MYSQLI_ASSOC) as $row) {
        $columns=implode(',',array_map(fn($v)=>"`$v`",array_keys($row)));
        $values=array_map(fn($v)=>$v===null ? 'NULL' : "'" . db()->real_escape_string((string)$v) . "'",array_values($row));
        $write("INSERT INTO `$table` ($columns) VALUES (" . implode(',',$values) . ");\n");
      }
    }
    $write("SET FOREIGN_KEY_CHECKS=1;\n");
  } finally { fclose($backup); db()->rollback(); $inTransaction=false; }

  db()->begin_transaction(); $inTransaction=true;
  lockProducts(array_column($products,'kode_produk'));
  $counts=['pengguna'=>(int)oq('SELECT COUNT(*) n FROM pengguna')->fetch_assoc()['n'],'transaksi'=>(int)oq('SELECT COUNT(*) n FROM transaksi')->fetch_assoc()['n'],'produk'=>(int)oq('SELECT COUNT(*) n FROM produk')->fetch_assoc()['n']];
  $receipt=['tanggal'=>date(DATE_ATOM),'keterangan'=>$description,'dasar_hpp'=>'Perkiraan biaya pengadaan untuk data contoh; bukan nota pemasok.','backup'=>basename($backupPath),'perubahan_produk'=>[],'mutasi'=>[]];
  $units=0; $value=0; $margins=[];
  foreach ($products as $item) {
    $p=oq('SELECT * FROM produk WHERE kode_produk=? FOR UPDATE',[$item['kode_produk']])->fetch_assoc();
    [$qty,$cost]=$plan[$p['kode_produk']];
    if ($p['kode_produk']==='PRD_61762411140' && (float)$p['harga_jual']===1.0) {
      $receipt['perubahan_produk'][]=['kode_produk'=>$p['kode_produk'],'sebelum'=>['nama_produk'=>$p['nama_produk'],'harga_jual'=>$p['harga_jual'],'satuan_dasar'=>$p['satuan_dasar'],'deskripsi'=>$p['deskripsi']],'sesudah'=>['nama_produk'=>'Kecap Manis Sedaap 550 ML','harga_jual'=>25000,'satuan_dasar'=>'Pouch']];
      oq('UPDATE produk SET nama_produk=?,harga_jual=?,satuan_dasar=?,deskripsi=? WHERE kode_produk=?',['Kecap Manis Sedaap 550 ML',25000,'Pouch','Kecap manis kedelai hitam Special dalam kemasan isi ulang 550 ml. Dijual per pouch.',$p['kode_produk']]);
      $p['nama_produk']='Kecap Manis Sedaap 550 ML'; $p['harga_jual']=25000; $p['satuan_dasar']='Pouch';
    }
    if (!is_int($qty) || $qty<1 || !is_int($cost) || $cost<=0 || $cost>=(float)$p['harga_jual']) throw new RuntimeException('Jumlah atau selisih HPP tidak valid untuk ' . $p['nama_produk']);
    $physical=(int)oq("SELECT COALESCE(SUM(sisa_stok),0) n FROM mutasi_stok WHERE kode_produk=? AND type='masuk'",[$p['kode_produk']])->fetch_assoc()['n'];
    $reserved=(int)oq('SELECT COALESCE(SUM(jumlah),0) n FROM reservasi_stok WHERE kode_produk=?',[$p['kode_produk']])->fetch_assoc()['n'];
    oq("INSERT INTO mutasi_stok(kode_produk,type,jumlah,harga_pokok,keterangan,sisa_stok) VALUES (?,'masuk',?,?,?,?)",[$p['kode_produk'],$qty,$cost,$description,$qty]);
    $id=db()->insert_id; refreshAvailableStock($p['kode_produk']);
    $available=(int)oq('SELECT stok FROM produk WHERE kode_produk=?',[$p['kode_produk']])->fetch_assoc()['stok'];
    if ($available!==max(0,$physical+$qty-$reserved)) throw new RuntimeException('Stok tersedia tidak sesuai reservasi untuk ' . $p['nama_produk']);
    $margin=round(((float)$p['harga_jual']-$cost)/(float)$p['harga_jual']*100,2);
    $receipt['mutasi'][]=['id_mutasi'=>$id,'kode_produk'=>$p['kode_produk'],'nama_produk'=>$p['nama_produk'],'jumlah'=>$qty,'satuan'=>$p['satuan_dasar'],'harga_pokok'=>$cost,'harga_jual'=>(float)$p['harga_jual'],'selisih_per_unit'=>(float)$p['harga_jual']-$cost,'margin_persen'=>$margin,'stok_tersedia'=>$available];
    $units+=$qty; $value+=$qty*$cost; $margins[]=$margin;
  }
  foreach ($counts as $table=>$count) if ((int)oq("SELECT COUNT(*) n FROM $table")->fetch_assoc()['n']!==$count) throw new RuntimeException('Jumlah data lain berubah dalam pengisian stok.');
  $receipt['ringkasan']=['produk'=>count($products),'unit_masuk'=>$units,'nilai_persediaan'=>$value,'margin_min'=>min($margins),'margin_max'=>max($margins)];
  $json=json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
  $temporaryReceipt=ROOT_PATH . '/storage/stok-masuk-receipt.json.tmp';
  if (file_put_contents($temporaryReceipt,$json,LOCK_EX)!==strlen($json)) throw new RuntimeException('Catatan penerimaan belum dapat disimpan.');
  db()->commit(); $inTransaction=false;
  if (!rename($temporaryReceipt,ROOT_PATH . '/storage/stok-masuk-receipt.json')) throw new RuntimeException('Stok sudah tersimpan, tetapi catatan penerimaan belum dapat dipindahkan. Jangan mengisi ulang.');
  $temporaryReceipt=null;
  echo json_encode($receipt['ringkasan'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) . "\n";
  echo "Mutasi masuk tersimpan; stok lama, reservasi, transaksi, dan pengguna dipertahankan.\n";
} catch (Throwable $e) {
  if ($inTransaction) { db()->rollback(); if ($temporaryReceipt && is_file($temporaryReceipt)) unlink($temporaryReceipt); }
  throw $e;
} finally { oq('SELECT RELEASE_LOCK(?)',[$lock]); }
