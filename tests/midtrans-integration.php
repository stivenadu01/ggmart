<?php
require __DIR__ . '/../scripts/bootstrap.php';
if (midtransEnvironment()!=='sandbox' || !str_starts_with(midtransKey(),'SB-Mid-server-')) throw new RuntimeException('This test only supports a configured Midtrans Sandbox key.');
function gatewayCheck(bool $ok,string $name): void { if (!$ok) throw new RuntimeException($name); echo "PASS: $name\n"; }
$receipt=ROOT_PATH . '/storage/qris-test-receipt.json';
$expire=in_array('--expire',$argv,true); $finish=in_array('--finish',$argv,true) || $expire; $keep=in_array('--keep',$argv,true); $test=null; $failure=null;
$counter=in_array('--counter',$argv,true);
try {
  if ($finish) {
    if (!is_file($receipt)) throw new RuntimeException('No QRIS test receipt.');
    $test=json_decode(file_get_contents($receipt),true); $code=$test['code'];
    if ($expire) midtransRequest(rawurlencode($code) . '/cancel','POST');
    $payload=midtransRequest(rawurlencode($code) . '/status'); syncMidtrans($payload);
    gatewayCheck(orderRow($code)['status_bayar']===($expire ? 'kedaluwarsa' : 'lunas'),$expire ? 'Sandbox cancellation verified by Get Status' : 'Sandbox simulator payment verified by Get Status');
    if (!$expire) gatewayCheck(orderRow($code)['status']=== (($test['source'] ?? 'online')==='kasir' ? 'selesai' : 'diproses'),'Verified Sandbox payment advances order automatically');
    $history=(int)oq('SELECT COUNT(*) n FROM riwayat_pesanan WHERE kode_transaksi=?',[$code])->fetch_assoc()['n'];
    syncMidtrans($payload);
    gatewayCheck((int)oq('SELECT COUNT(*) n FROM riwayat_pesanan WHERE kode_transaksi=?',[$code])->fetch_assoc()['n']===$history,'Repeated gateway notification stays idempotent');
  } else {
    if (is_file($receipt)) throw new RuntimeException('An existing QRIS test must be finished first.');
    $user=oq('SELECT id_pengguna,nama,no_hp FROM pengguna WHERE role=? LIMIT 1',[$counter ? 'admin' : 'pelanggan'])->fetch_assoc();
    $p=oq('SELECT * FROM produk WHERE stok>3 AND harga_jual>0 ORDER BY kode_produk LIMIT 1')->fetch_assoc();
    if (!$user || !$p) throw new RuntimeException('Existing user and stocked product required.');
    $code='GG-' . strtoupper(bin2hex(random_bytes(6)));
    $test=['code'=>$code,'product'=>$p['kode_produk'],'stock'=>(int)$p['stok'],'sold'=>(int)$p['terjual'],'source'=>$counter ? 'kasir' : 'online'];
    file_put_contents($receipt,json_encode($test));
    db()->begin_transaction();
    try {
      lockProducts([$p['kode_produk']]);
      oq("INSERT INTO transaksi(kode_transaksi,id_pengguna,total_harga,status,metode_bayar,status_bayar,nama_penerima,no_hp_penerima,total_tagihan,batas_bayar) VALUES (?,?,?,'pending','qris','belum_bayar',?,?,?,DATE_ADD(NOW(),INTERVAL 30 MINUTE))",[$code,$user['id_pengguna'],$p['harga_jual'],$user['nama'],$user['no_hp'],$p['harga_jual']]);
      oq('UPDATE transaksi SET sumber_transaksi=? WHERE kode_transaksi=?',[$test['source'],$code]);
      recordOrderEvent($code,'checkout','Pesanan dibuat.',null,null,'belum_bayar');
      oq('INSERT INTO detail_transaksi(kode_transaksi,kode_produk,jumlah,harga_satuan,harga_pokok) VALUES (?,?,1,?,0)',[$code,$p['kode_produk'],$p['harga_jual']]);
      oq('INSERT INTO reservasi_stok(kode_transaksi,kode_produk,jumlah) VALUES (?,?,1)',[$code,$p['kode_produk']]); refreshAvailableStock($p['kode_produk']); db()->commit();
    } catch (Throwable $e) { db()->rollback(); throw $e; }
    createQris($code); $trx=orderRow($code);
    gatewayCheck((bool)$trx['qris_url'] && $trx['midtrans_env']==='sandbox','Dynamic QRIS created using real Sandbox API');
    createQris($code); gatewayCheck(orderRow($code)['qris_url']===$trx['qris_url'],'Repeated QRIS creation returns same code');
    $payload=midtransRequest(rawurlencode($code) . '/status'); gatewayCheck(($payload['transaction_status'] ?? '')==='pending','Sandbox QRIS pending status confirmed');
    $ch=curl_init($trx['qris_url']); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>25]); $image=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
    gatewayCheck($status===200 && $image && getimagesizefromstring($image)!==false,'Official QRIS image available');
    if ($keep) { echo 'QRIS_URL=' . $trx['qris_url'] . PHP_EOL; exit; }
    midtransRequest(rawurlencode($code) . '/cancel','POST'); syncMidtrans(midtransRequest(rawurlencode($code) . '/status'));
    gatewayCheck(orderRow($code)['status']==='dibatalkan','Gateway cancellation releases reservation');
  }
} catch (Throwable $e) { $failure=$e; }
// Clean only the random code recorded by this test. Existing orders are untouched.
if ($test) {
  $trx=orderRow($test['code']);
  if ($trx && $trx['midtrans_order_id'] && $trx['status_bayar']==='belum_bayar') {
    try { midtransRequest(rawurlencode($trx['midtrans_order_id']) . '/cancel','POST'); } catch (Throwable $e) { echo "Sandbox cancellation could not be confirmed; test charge will expire.\n"; }
  }
  db()->begin_transaction();
  try {
    if ($trx) cancelOrder($test['code']);
    foreach (['alokasi_stok','mutasi_stok','detail_transaksi','riwayat_pesanan','reservasi_stok','transaksi'] as $table) oq("DELETE FROM $table WHERE kode_transaksi=?",[$test['code']]);
    db()->commit(); if (is_file($receipt)) unlink($receipt);
  } catch (Throwable $e) { db()->rollback(); throw $e; }
  $p=oq('SELECT stok,terjual FROM produk WHERE kode_produk=?',[$test['product']])->fetch_assoc();
  gatewayCheck((int)$p['stok']===$test['stock'] && (int)$p['terjual']===$test['sold'],'Sandbox test stock restored');
}
if ($failure) throw $failure;
