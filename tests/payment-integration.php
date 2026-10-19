<?php
require __DIR__ . '/../scripts/bootstrap.php';
function verifyCheck(bool $ok,string $name): void { if (!$ok) throw new RuntimeException($name); echo "PASS: $name\n"; }
function paymentCall(string $url,array $session,string $method='GET',?array $body=null,bool $file=false): array
{
  $ch=curl_init(BASE_URL . $url);
  $headers=['Accept: application/json','Cookie: ggmart_session=' . $session['id'],'X-CSRF-Token: ' . $session['csrf']];
  if ($body!==null) { if (!$file) $headers[]='Content-Type: application/json'; curl_setopt($ch,CURLOPT_POSTFIELDS,$file ? $body : json_encode($body)); }
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>30]);
  $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); return [$status,json_decode($raw,true),$raw];
}
ini_set('session.use_cookies','0'); session_cache_limiter(''); session_name('ggmart_session');
$users=[]; $sessions=[]; $codes=[]; $cart=[]; $upload=ROOT_PATH . '/storage/proof-check-' . bin2hex(random_bytes(4)) . '.png';
$customer=oq("SELECT * FROM pengguna WHERE role='pelanggan' LIMIT 1")->fetch_assoc();
$product=oq('SELECT * FROM produk WHERE stok>5 AND harga_jual>0 ORDER BY kode_produk LIMIT 1')->fetch_assoc();
$zone=oq("SELECT * FROM zona_pengiriman WHERE kelurahan='Fatululi'")->fetch_assoc();
if (!$customer || !$product || !$zone) throw new RuntimeException('Existing customer/product/zone required.');
$cart=oq('SELECT * FROM keranjang WHERE id_pengguna=?',[$customer['id_pengguna']])->fetch_all(MYSQLI_ASSOC);
$before=['stock'=>(int)$product['stok'],'sold'=>(int)$product['terjual'],'users'=>(int)oq('SELECT COUNT(*) n FROM pengguna')->fetch_assoc()['n'],'trx'=>(int)oq('SELECT COUNT(*) n FROM transaksi')->fetch_assoc()['n']];
try {
  foreach (['admin','pelanggan','kasir','pimpinan'] as $role) {
    $user=oq('SELECT id_pengguna,nama,role,no_hp,alamat,email FROM pengguna WHERE role=? LIMIT 1',[$role])->fetch_assoc();
    session_id(bin2hex(random_bytes(16))); session_start(); $token=bin2hex(random_bytes(32));
    $_SESSION=['user'=>$user,'csrf_token'=>$token,'_last_activity'=>time(),'_last_regenerated'=>time()];
    $sessions[$role]=['id'=>session_id(),'csrf'=>$token]; session_write_close();
  }
  // Key hanya di proses CLI, tanpa charge/HTTP Midtrans atau perubahan .env.
  $_ENV['MIDTRANS_SERVER_KEY']='local-test-no-gateway';
  $payload=['metode_bayar'=>'qris','checkout_key'=>bin2hex(random_bytes(16)),'detail'=>[['kode_produk'=>$product['kode_produk'],'jumlah'=>1]],'total_harga'=>(float)$product['harga_jual'],'metode_pengiriman'=>'kirim','kecamatan'=>$zone['kecamatan'],'zona_id'=>$zone['id'],'ongkir'=>1,'nama_penerima'=>$customer['nama'],'no_hp_penerima'=>'081234567890','alamat_pengiriman'=>'Jl. S. K. Lerik, Kelurahan Fatululi, Kota Kupang'];
  try { createOrder(array_replace($payload,['kecamatan'=>'Alak']),$customer); throw new RuntimeException('Wrong district accepted'); } catch (Exception $e) { verifyCheck($e->getCode()===422,'Kelurahan must belong to selected kecamatan'); }
  try { createOrder(array_replace($payload,['total_harga'=>1]),$customer); throw new RuntimeException('Client price accepted'); } catch (Exception $e) { verifyCheck($e->getCode()===409,'Manipulated goods price rejected'); }
  $code=createOrder($payload,$customer); $codes[]=$code;
  verifyCheck(createOrder($payload,$customer)===$code,'Repeated checkout returns same order');
  $trx=orderRow($code);
  verifyCheck((float)$trx['ongkir']===(float)$zone['tarif'] && (float)$trx['total_tagihan']===(float)$product['harga_jual']+(float)$zone['tarif'],'Server controls shipping and grand total');
  verifyCheck((int)oq('SELECT stok FROM produk WHERE kode_produk=?',[$product['kode_produk']])->fetch_assoc()['stok']===$before['stock']-1,'Checkout reserves stock exactly once');
  db()->begin_transaction(); oq('UPDATE zona_pengiriman SET tarif=tarif+5000 WHERE id=?',[$zone['id']]);
  verifyCheck((float)orderRow($code)['ongkir']===(float)$zone['tarif'],'Old order keeps its shipping rate'); db()->rollback();
  oq("UPDATE transaksi SET metode_bayar='transfer' WHERE kode_transaksi=?",[$code]);
  [$status]=paymentCall('/api/transaksi/proses',$sessions['admin'],'POST',['kode_transaksi'=>$code]); verifyCheck($status===409,'Admin cannot process unpaid order');
  file_put_contents($upload,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWQAAAABJRU5ErkJggg=='));
  $body=['kode_transaksi'=>$code,'bukti'=>new CURLFile($upload,'image/png','transfer.png')];
  [$status]=paymentCall('/api/pembayaran/bukti',$sessions['pelanggan'],'POST',$body,true); verifyCheck($status===200 && orderRow($code)['status_bayar']==='menunggu_verifikasi','Customer uploads image proof');
  model('Transaksi');
  [, $waiting]=getTransaksiList(1,10,$code,null,null,null,null,null,'menunggu_pembayaran','online');
  [, $verification]=getTransaksiList(1,10,$code,null,null,null,null,null,'menunggu_verifikasi','online');
  verifyCheck($waiting===0 && $verification===1,'Verification has a separate order tab');
  [$status]=paymentCall('/api/pembayaran/bukti/' . $code,$sessions['admin']); verifyCheck($status===200,'Admin can read private proof');
  [$status]=paymentCall('/api/pembayaran/verifikasi',$sessions['admin'],'POST',['kode_transaksi'=>$code,'aksi'=>'tolak','catatan'=>'Nominal belum terlihat jelas.']); verifyCheck($status===200 && orderRow($code)['status_bayar']==='ditolak','Admin rejection requires resubmission');
  [$status]=paymentCall('/api/pembayaran/bukti',$sessions['pelanggan'],'POST',$body,true); verifyCheck($status===200,'Customer can upload proof again');
  [$status]=paymentCall('/api/pembayaran/verifikasi',$sessions['admin'],'POST',['kode_transaksi'=>$code,'aksi'=>'terima']); verifyCheck($status===200 && orderRow($code)['status_bayar']==='lunas' && orderRow($code)['status']==='diproses','Accepted manual transfer automatically enters processing');
  [$status,$detail]=paymentCall('/api/transaksi/detail?k=' . $code,$sessions['pelanggan']);
  verifyCheck($status===200 && array_column($detail['data']['riwayat_pesanan'],'peristiwa')===['checkout','pembayaran_menunggu_verifikasi','pembayaran_ditolak','pembayaran_menunggu_verifikasi','pembayaran_lunas','diproses'],'Order timeline traces checkout, evidence, rejection, resubmission, and processing');
  verifyCheck(!array_key_exists('riwayat_pembayaran',$detail['data']) && array_column($detail['data']['riwayat_pesanan'],'kategori')===['pesanan','pembayaran','pembayaran','pembayaran','pembayaran','pesanan'],'Detail API exposes one categorized history only');
  [$status]=paymentCall('/api/transaksi/proses',$sessions['admin'],'POST',['kode_transaksi'=>$code]); verifyCheck($status===200,'Paid order can be processed');
  [$status]=paymentCall('/api/transaksi/konfirmasi',$sessions['admin'],'POST',['kode_transaksi'=>$code]); verifyCheck($status===409,'Delivery cannot complete before dispatch');
  [$status]=paymentCall('/api/transaksi/kirim',$sessions['admin'],'POST',['kode_transaksi'=>$code]); verifyCheck($status===200 && orderRow($code)['status']==='dikirim','Admin dispatches local delivery');
  [$status]=paymentCall('/api/transaksi/konfirmasi',$sessions['admin'],'POST',['kode_transaksi'=>$code]); verifyCheck($status===200 && orderRow($code)['status']==='selesai','Delivery completion records sale');
  [$status,,$receipt]=paymentCall('/struk/' . $code,$sessions['pelanggan']); verifyCheck($status===200 && str_contains($receipt,'LUNAS') && str_contains($receipt,'Cetak / Simpan PDF'),'Completed customer order has printable receipt');
  [$status]=paymentCall('/struk/' . $code,$sessions['kasir']); verifyCheck($status===403,'Cashier cannot read another owner prepaid receipt');
  $ledger=(int)oq("SELECT COUNT(*) n FROM mutasi_stok WHERE kode_transaksi=? AND type='keluar'",[$code])->fetch_assoc()['n'];
  [$status]=paymentCall('/api/transaksi/konfirmasi',$sessions['admin'],'POST',['kode_transaksi'=>$code]); verifyCheck($status===200 && (int)oq("SELECT COUNT(*) n FROM mutasi_stok WHERE kode_transaksi=? AND type='keluar'",[$code])->fetch_assoc()['n']===$ledger,'Repeated completion cannot consume stock again');
  [$status]=paymentCall('/api/transaksi/batal',$sessions['admin'],'POST',['kode_transaksi'=>$code]); verifyCheck($status===200 && orderRow($code)['status_bayar']==='perlu_refund','Paid cancellation restores stock and requires refund');
  [$status]=paymentCall('/api/pembayaran/refund',$sessions['admin'],'POST',['kode_transaksi'=>$code,'catatan'=>'Pengembalian dana telah dicatat untuk pemeriksaan.']); verifyCheck($status===200 && orderRow($code)['status_bayar']==='dikembalikan','Admin records completed refund');
  [$status,$detail]=paymentCall('/api/transaksi/detail?k=' . $code,$sessions['pelanggan']);
  verifyCheck($status===200 && array_slice(array_column($detail['data']['riwayat_pesanan'],'peristiwa'),-5)===['dikirim','selesai','dibatalkan','pembayaran_perlu_refund','pembayaran_dikembalikan'],'Unified history traces delivery, completion, cancellation and refund without duplicate payment rows');

  // Pickup tunai: disiapkan sebelum dibayar, lalu lunas dan selesai saat diserahkan.
  $cashPayload=array_replace($payload,['metode_bayar'=>'tunai','metode_pengiriman'=>'ambil','checkout_key'=>bin2hex(random_bytes(16))]);
  $cash=createOrder($cashPayload,$customer); $codes[]=$cash;
  verifyCheck(orderRow($cash)['status']==='diproses' && orderRow($cash)['status_bayar']==='belum_bayar' && orderRow($cash)['batas_bayar']===null,'Cash pickup is prepared without claiming payment or imposing prepaid expiry');
  [$status]=paymentCall('/struk/' . $cash,$sessions['admin']); verifyCheck($status===409,'Unpaid order cannot print paid receipt');
  [$status]=paymentCall('/api/pembayaran/tunai',$sessions['kasir'],'POST',['kode_transaksi'=>$cash]); verifyCheck($status===409,'Cash cannot be received before pickup ready');
  [$status]=paymentCall('/api/transaksi/siap-ambil',$sessions['admin'],'POST',['kode_transaksi'=>$cash]); verifyCheck($status===200,'Unpaid cash pickup can be marked ready by admin');
  [$status]=paymentCall('/api/transaksi/konfirmasi',$sessions['admin'],'POST',['kode_transaksi'=>$cash]); verifyCheck($status===409,'Ordinary completion cannot bypass cash receipt');
  foreach (['pelanggan','pimpinan'] as $role) { [$status]=paymentCall('/api/pembayaran/tunai',$sessions[$role],'POST',['kode_transaksi'=>$cash]); verifyCheck($status===403,"$role cannot mark pickup cash paid"); }
  [$status]=paymentCall('/api/pembayaran/tunai',$sessions['kasir'],'POST',['kode_transaksi'=>$cash]); verifyCheck($status===200 && orderRow($cash)['status']==='selesai' && orderRow($cash)['status_bayar']==='lunas','Cashier receives cash and completes pickup atomically');
  $events=(int)oq('SELECT COUNT(*) n FROM riwayat_pesanan WHERE kode_transaksi=?',[$cash])->fetch_assoc()['n'];
  [$status]=paymentCall('/api/pembayaran/tunai',$sessions['kasir'],'POST',['kode_transaksi'=>$cash]); verifyCheck($status===200 && (int)oq('SELECT COUNT(*) n FROM riwayat_pesanan WHERE kode_transaksi=?',[$cash])->fetch_assoc()['n']===$events && (int)oq("SELECT COUNT(*) n FROM mutasi_stok WHERE kode_transaksi=? AND type='keluar'",[$cash])->fetch_assoc()['n']===1,'Repeated cash receipt cannot duplicate FIFO or timeline');
  [$status]=paymentCall('/struk/' . $cash,$sessions['kasir']); verifyCheck($status===200,'Cashier can print pickup cash receipt');
  [$status]=paymentCall('/api/transaksi/detail?k=' . $cash,$sessions['kasir']); verifyCheck($status===403,'Incoming orders do not expose customer financial details to cashier');
  [$status]=paymentCall('/api/kasir/pesanan',$sessions['kasir']); verifyCheck($status===200,'Cashier can view incoming orders');
  [$status]=paymentCall('/api/transaksi/badge',$sessions['kasir']); verifyCheck($status===200,'Cashier can read order badge');
  $cashPayload['checkout_key']=bin2hex(random_bytes(16)); $cancelCash=createOrder($cashPayload,$customer); $codes[]=$cancelCash;
  [$status]=paymentCall('/api/transaksi/batal-pending',$sessions['pelanggan'],'POST',['kode_transaksi'=>$cancelCash]); verifyCheck($status===200 && orderRow($cancelCash)['status']==='dibatalkan','Customer can cancel cash pickup before ready');
  try { createOrder(array_replace($cashPayload,['metode_pengiriman'=>'kirim','checkout_key'=>bin2hex(random_bytes(16))]),$customer); throw new RuntimeException('Cash delivery accepted'); } catch (Exception $e) { verifyCheck($e->getCode()===422,'Server rejects cash payment for local delivery'); }

  // Kasir transfer: bukti diunggah kasir; hanya admin mengesahkan dana masuk.
  $staff=oq("SELECT * FROM pengguna WHERE role='kasir' LIMIT 1")->fetch_assoc();
  $counterPayload=array_replace($payload,['metode_bayar'=>paymentMethods()['transfer'] ? 'transfer' : 'qris','checkout_key'=>bin2hex(random_bytes(16))]);
  $counter=createOrder($counterPayload,$staff,true); $codes[]=$counter;
  if ($counterPayload['metode_bayar']!=='transfer') oq("UPDATE transaksi SET metode_bayar='transfer' WHERE kode_transaksi=?",[$counter]);
  $body['kode_transaksi']=$counter;
  [$status]=paymentCall('/api/pembayaran/bukti',$sessions['pelanggan'],'POST',$body,true); verifyCheck($status===403,'Customer cannot upload proof to cashier sale');
  [$status]=paymentCall('/api/pembayaran/bukti',$sessions['kasir'],'POST',$body,true); verifyCheck($status===200 && orderRow($counter)['status_bayar']==='menunggu_verifikasi','Cashier can upload manual transfer proof');
  [$status]=paymentCall('/api/pembayaran/bukti/' . $counter,$sessions['kasir']); verifyCheck($status===200,'Cashier can view authorized private transfer proof');
  [$status]=paymentCall('/api/pembayaran/verifikasi',$sessions['kasir'],'POST',['kode_transaksi'=>$counter,'aksi'=>'terima']); verifyCheck($status===403,'Only admin can verify manual transfer');
  [$status]=paymentCall('/api/pembayaran/verifikasi',$sessions['admin'],'POST',['kode_transaksi'=>$counter,'aksi'=>'terima']); verifyCheck($status===200 && orderRow($counter)['status']==='selesai','Accepted cashier transfer automatically completes sale');
  [$status,,$receipt]=paymentCall('/struk/' . $counter,$sessions['kasir']); verifyCheck($status===200 && str_contains($receipt,'LUNAS') && !str_contains($receipt,'harga_pokok'),'Cashier manual transfer has a paid receipt without HPP');
} finally {
  // Hapus hanya kode acak yang dibuat proses ini, setelah mengembalikan stok.
  foreach ($codes as $code) {
    db()->begin_transaction();
    try {
      $trx=orderRow($code,true); if ($trx && $trx['status']!=='dibatalkan') cancelOrder($code);
      $proof=$trx['bukti_bayar'] ?? null;
      oq('DELETE FROM alokasi_stok WHERE kode_transaksi=?',[$code]);
      oq('DELETE FROM mutasi_stok WHERE kode_transaksi=?',[$code]);
      oq('DELETE FROM detail_transaksi WHERE kode_transaksi=?',[$code]);
      oq('DELETE FROM riwayat_pesanan WHERE kode_transaksi=?',[$code]);
      oq('DELETE FROM reservasi_stok WHERE kode_transaksi=?',[$code]);
      oq('DELETE FROM transaksi WHERE kode_transaksi=?',[$code]); db()->commit();
      if ($proof && preg_match('/^[a-f0-9]{40}\.(png|jpg|webp)$/',$proof)) @unlink(ROOT_PATH . '/storage/bukti/' . $proof);
    } catch (Throwable $e) { db()->rollback(); throw $e; }
  }
  foreach ($cart as $item) oq('INSERT IGNORE INTO keranjang(id_keranjang,id_pengguna,kode_produk,jumlah) VALUES (?,?,?,?)',[$item['id_keranjang'],$item['id_pengguna'],$item['kode_produk'],$item['jumlah']]);
  foreach ($sessions as $session) { session_id($session['id']); session_start(); $_SESSION=[]; session_destroy(); }
  if (is_file($upload)) unlink($upload);
}
$after=oq('SELECT stok,terjual FROM produk WHERE kode_produk=?',[$product['kode_produk']])->fetch_assoc();
verifyCheck((int)$after['stok']===$before['stock'] && (int)$after['terjual']===$before['sold'],'Original stock and sales restored');
verifyCheck((int)oq('SELECT COUNT(*) n FROM transaksi')->fetch_assoc()['n']===$before['trx'] && (int)oq('SELECT COUNT(*) n FROM pengguna')->fetch_assoc()['n']===$before['users'],'Test adds no permanent transactions or users');
echo "Payment checks completed and temporary rows removed.\n";
