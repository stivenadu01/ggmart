<?php
require __DIR__ . '/../scripts/bootstrap.php';
model('Transaksi');
function regressionCheck(bool $ok,string $label): void { if (!$ok) throw new RuntimeException($label); echo "PASS: $label\n"; }
$customer=oq("SELECT * FROM pengguna WHERE role='pelanggan' LIMIT 1")->fetch_assoc();
$cashier=oq("SELECT * FROM pengguna WHERE role IN ('admin','kasir') LIMIT 1")->fetch_assoc();
$product='GT-' . strtoupper(bin2hex(random_bytes(6))); $codes=[];
function regressionOrder(array $user,string $product,string $source='online',string $delivery='ambil'): string {
  global $codes;
  $code='GT-' . strtoupper(bin2hex(random_bytes(6))); $codes[]=$code;
  oq("INSERT INTO transaksi(kode_transaksi,id_pengguna,total_harga,total_tagihan,status,metode_bayar,status_bayar,sumber_transaksi,metode_pengiriman,midtrans_order_id,midtrans_env,batas_bayar) VALUES (?,?,1000,1000,'pending','qris','belum_bayar',?,?,?,?,DATE_ADD(NOW(),INTERVAL 1 HOUR))",[$code,$user['id_pengguna'],$source,$delivery,$code,midtransEnvironment()]);
  oq('INSERT INTO detail_transaksi(kode_transaksi,kode_produk,jumlah,harga_satuan,harga_pokok) VALUES (?,?,1,1000,0)',[$code,$product]);
  oq('INSERT INTO reservasi_stok(kode_transaksi,kode_produk,jumlah) VALUES (?,?,1)',[$code,$product]); refreshAvailableStock($product); return $code;
}
function verifiedFixture(string $code,string $status='settlement'): array {
  // Unit fixture at service boundary; HTTP entry points still verify against Midtrans.
  return ['order_id'=>$code,'transaction_id'=>'fixture-' . $code,'payment_type'=>'qris','currency'=>'IDR','gross_amount'=>'1000.00','transaction_status'=>$status,'fraud_status'=>'accept'];
}
try {
  oq("INSERT INTO produk(kode_produk,nama_produk,harga_jual,satuan_dasar,stok,terjual) VALUES (?,'Pemeriksaan checkout',1000,'Pcs',15,0)",[$product]);
  oq("INSERT INTO mutasi_stok(kode_produk,type,jumlah,harga_pokok,keterangan,sisa_stok) VALUES (?,'masuk',15,400,'Pemeriksaan sementara',15)",[$product]);
  $counter=regressionOrder($cashier,$product,'kasir');
  $pickup=regressionOrder($customer,$product);
  $delivery=regressionOrder($customer,$product,'online','kirim');
  try { syncMidtrans(array_replace(verifiedFixture($counter),['gross_amount'=>'1.00'])); throw new RuntimeException('Wrong amount accepted'); } catch (Exception $e) { regressionCheck($e->getCode()===422 && orderRow($counter)['status_bayar']==='belum_bayar','Wrong payment amount cannot complete cashier sale'); }
  syncMidtrans(verifiedFixture($counter,'pending')); regressionCheck(orderRow($counter)['status']==='pending','Pending QRIS does not finish sale');
  $_SESSION['user']=$customer;
  syncMidtrans(verifiedFixture($counter)); $trx=orderRow($counter);
  regressionCheck(oq("SELECT petugas FROM riwayat_pesanan WHERE kode_transaksi=? AND peristiwa='selesai'",[$counter])->fetch_assoc()['petugas']===null,'Gateway completion is attributed to the system, not the user polling another order');
  unset($_SESSION['user']);
  regressionCheck($trx['status']==='selesai' && $trx['status_bayar']==='lunas','Verified cashier QRIS automatically completes sale');
  $ledger=(int)oq('SELECT COUNT(*) n FROM mutasi_stok WHERE kode_transaksi=?',[$counter])->fetch_assoc()['n'];
  $history=(int)oq('SELECT COUNT(*) n FROM riwayat_pesanan WHERE kode_transaksi=?',[$counter])->fetch_assoc()['n'];
  $notifications=(int)oq('SELECT COUNT(*) n FROM notifikasi n JOIN riwayat_pesanan r ON r.id=n.id_riwayat WHERE r.kode_transaksi=?',[$counter])->fetch_assoc()['n'];
  syncMidtrans(verifiedFixture($counter));
  regressionCheck((int)oq('SELECT COUNT(*) n FROM mutasi_stok WHERE kode_transaksi=?',[$counter])->fetch_assoc()['n']===$ledger && (int)oq('SELECT COUNT(*) n FROM riwayat_pesanan WHERE kode_transaksi=?',[$counter])->fetch_assoc()['n']===$history,'Repeated settlement cannot duplicate stock or payment history');
  regressionCheck((int)oq('SELECT COUNT(*) n FROM notifikasi n JOIN riwayat_pesanan r ON r.id=n.id_riwayat WHERE r.kode_transaksi=?',[$counter])->fetch_assoc()['n']===$notifications,'Repeated QRIS settlement does not duplicate recipient notifications');
  syncMidtrans(verifiedFixture($pickup)); regressionCheck(orderRow($pickup)['status']==='diproses' && orderRow($pickup)['status_bayar']==='lunas','Verified customer QRIS automatically enters processing');
  [$data,$total]=getTransaksiList(1,10,$pickup,null,null,null,$customer['id_pengguna'],null,'diproses','online'); regressionCheck($total===1 && $data[0]['kode_transaksi']===$pickup,'Paid pending order appears in Diproses tab');
  [, $total]=getTransaksiList(1,10,$pickup,null,null,null,$customer['id_pengguna'],null,'menunggu_pembayaran','online'); regressionCheck($total===0,'Paid pending order leaves waiting-payment tab');
  [, $total]=getTransaksiList(1,10,$counter,null,null,null,null,null,'selesai','online'); regressionCheck($total===0,'Cashier sales are excluded from customer orders');
  db()->begin_transaction();
  try {
    oq("UPDATE transaksi SET status='diproses' WHERE kode_transaksi=?",[$pickup]);
    try { completeOrder($pickup); throw new RuntimeException('Pickup completed before ready'); } catch (Exception $e) { regressionCheck($e->getCode()===409,'Pickup must be ready before completion'); }
    oq('UPDATE transaksi SET siap_diambil=1 WHERE kode_transaksi=?',[$pickup]); completeOrder($pickup); completeOrder($pickup);
    regressionCheck(orderRow($pickup)['status']==='selesai','Ready pickup completes once'); db()->commit();
  } catch (Throwable $e) { db()->rollback(); throw $e; }
  syncMidtrans(verifiedFixture($delivery,'expire'));
  regressionCheck(orderRow($delivery)['status']==='dibatalkan' && (int)oq('SELECT COUNT(*) n FROM reservasi_stok WHERE kode_transaksi=?',[$delivery])->fetch_assoc()['n']===0,'Expired QRIS cancels order and releases stock');
  regressionCheck((int)oq('SELECT terjual FROM produk WHERE kode_produk=?',[$product])->fetch_assoc()['terjual']===2,'Only completed cashier and pickup sales count as sold');
} finally {
  db()->begin_transaction();
  try {
    foreach ($codes as $code) {
      $trx=orderRow($code,true); if ($trx && $trx['status']!=='dibatalkan') cancelOrder($code);
      foreach (['alokasi_stok','mutasi_stok','detail_transaksi','riwayat_pesanan','reservasi_stok','transaksi'] as $table) oq("DELETE FROM $table WHERE kode_transaksi=?",[$code]);
    }
    oq('DELETE FROM mutasi_stok WHERE kode_produk=?',[$product]); oq('DELETE FROM produk WHERE kode_produk=?',[$product]); db()->commit();
  } catch (Throwable $e) { db()->rollback(); throw $e; }
}
regressionCheck(!oq('SELECT kode_produk FROM produk WHERE kode_produk=?',[$product])->fetch_assoc(),'Temporary product and transactions removed');
