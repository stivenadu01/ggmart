<?php
require __DIR__ . '/../scripts/bootstrap.php';
model('Transaksi');
function tabCheck(bool $ok, string $label): void {
  if (!$ok) throw new RuntimeException($label);
  echo "PASS: $label\n";
}
$customer = oq("SELECT id_pengguna FROM pengguna WHERE role='pelanggan' LIMIT 1")->fetch_assoc()['id_pengguna'];
$other = oq("SELECT id_pengguna FROM pengguna WHERE role='admin' LIMIT 1")->fetch_assoc()['id_pengguna'];
$prefix = 'TB' . bin2hex(random_bytes(5));
$expected = ['menunggu_pembayaran'=>3, 'menunggu_verifikasi'=>1, 'diproses'=>3, 'dikirim'=>1, 'selesai'=>1, 'dibatalkan'=>1];
$fixtures = [
  ['pending','belum_bayar','transfer'], ['pending','belum_bayar','transfer'],
  ['pending','ditolak','transfer'], ['pending','menunggu_verifikasi','transfer'],
  ['pending','lunas','transfer'], ['diproses','belum_bayar','tunai'],
  ['diproses','lunas','transfer'], ['dikirim','lunas','transfer'],
  ['selesai','lunas','transfer'], ['dibatalkan','kedaluwarsa','transfer']
];
db()->begin_transaction();
try {
  foreach ($fixtures as $i => [$status,$payment,$method]) {
    oq('INSERT INTO transaksi (kode_transaksi,id_pengguna,total_harga,total_pokok,total_tagihan,status,status_bayar,metode_bayar,sumber_transaksi) VALUES (?,?,1000,700,1000,?,?,?,?)',
      [$prefix . sprintf('%02d',$i),$customer,$status,$payment,$method,'online']);
  }
  foreach ([[$customer,'kasir'],[$other,'online']] as $i => [$owner,$source]) {
    oq("INSERT INTO transaksi (kode_transaksi,id_pengguna,total_harga,total_pokok,total_tagihan,status,status_bayar,metode_bayar,sumber_transaksi) VALUES (?,?,1000,700,1000,'diproses','lunas','transfer',?)",
      [$prefix . (10+$i),$owner,$source]);
  }
  [$rows,$total,,$counts] = getTransaksiList(1,1,$prefix,null,null,null,$customer,null,'menunggu_pembayaran','online',true);
  tabCheck(count($rows)===1 && $total===3 && $counts===$expected,'Badges include every page and stage, scoped to customer and online orders');
  foreach ($expected as $stage => $count) {
    [,$total,,$counts] = getTransaksiList(1,1,$prefix,null,null,null,$customer,null,$stage,'online',true);
    tabCheck($total===$count && $counts===$expected,"Consistent list and badge: $stage");
  }
  [$rows,$total,$summary,$counts] = getTransaksiList(1,100,$prefix,null,null,null,$customer,null,null,'online',true);
  tabCheck($total===10 && count($rows)===10 && array_sum($counts)===$total,'All tab includes waiting, verification, processing, shipped, completed and cancelled');
  tabCheck((float)$summary['jual']===1000.0,'Sales summary still counts completed orders only');
  [,$total,,$counts] = getTransaksiList(1,1,$prefix,null,null,'tunai',$customer,null,null,'online',true);
  tabCheck($total===1 && $counts['diproses']===1 && array_sum($counts)===1,'Payment filter applies to every badge');
  [,$total,,$counts] = getTransaksiList(1,1,$prefix.'99',null,null,null,$customer,null,null,'online',true);
  tabCheck($total===0 && array_sum($counts)===0 && count($counts)===6,'Empty search returns numeric zero badges');
  [,$total,,$counts] = getTransaksiList(1,1,$prefix,null,null,null,$customer,'selesai,dibatalkan',null,'online',true);
  tabCheck($total===2 && $counts['selesai']===1 && $counts['dibatalkan']===1 && array_sum($counts)===2,'History status filter remains compatible');
} finally {
  db()->rollback();
}
tabCheck((int)oq('SELECT COUNT(*) n FROM transaksi WHERE kode_transaksi LIKE ?',[$prefix.'%'])->fetch_assoc()['n']===0,'All fixtures rolled back; existing users and orders preserved');
