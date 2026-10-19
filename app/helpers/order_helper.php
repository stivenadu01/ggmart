<?php

function oq(string $sql, array $params = []) { return db()->execute_query($sql, $params); }
function orderRow(string $code, bool $lock = false): ?array
{
  return oq('SELECT t.*, u.nama AS user, u.role AS user_role, u.no_hp AS user_phone FROM transaksi t LEFT JOIN pengguna u ON u.id_pengguna=t.id_pengguna WHERE t.kode_transaksi=?' . ($lock ? ' FOR UPDATE' : ''), [$code])->fetch_assoc();
}
function orderError(Throwable $e): void
{
  $status = (int)$e->getCode();
  if ($status < 400 || $status > 599) { error_log((string)$e); $status = 500; }
  response(['success'=>false,'message'=>$status === 500 ? 'Terjadi kesalahan pada server. Silakan coba lagi.' : $e->getMessage()], $status);
}
function normalizePhone(string $phone): string
{
  $phone = preg_replace('/[^0-9]/', '', $phone);
  if (str_starts_with($phone, '0')) $phone = '62' . substr($phone, 1);
  if (!preg_match('/^628[0-9]{7,12}$/', $phone)) throw new Exception('Nomor WhatsApp harus berupa nomor seluler Indonesia yang valid.', 422);
  return $phone;
}
function storeSettings(): array
{
  $settings = ['bank_nama'=>'','bank_rekening'=>'','bank_pemilik'=>''];
  foreach (oq('SELECT nama,nilai FROM pengaturan')->fetch_all(MYSQLI_ASSOC) as $row) {
    if (array_key_exists($row['nama'], $settings)) $settings[$row['nama']] = $row['nilai'];
  }
  return $settings;
}
function paymentMethods(): array
{
  $bank = storeSettings();
  return ['tunai'=>true, 'transfer'=> $bank['bank_nama'] !== '' && $bank['bank_rekening'] !== '' && $bank['bank_pemilik'] !== '', 'qris'=>true];
}
function recordOrderEvent(string $code, string $event, string $note = '', ?int $staff = null, ?string $key = null, ?string $paymentStatus = null): void
{
  $category=str_starts_with($event,'pembayaran_') ? 'pembayaran' : 'pesanan';
  if ($key===null && in_array($event,['checkout','diproses','siap_diambil','dikirim','selesai','dibatalkan'],true)) $key=$event . ':' . $code;
  oq('INSERT INTO riwayat_pesanan(kode_transaksi,kategori,peristiwa,status_bayar,catatan,petugas,kunci_peristiwa) VALUES (?,?,?,?,?,?,?)', [$code,$category,$event,$paymentStatus,mb_substr($note,0,500),$staff,$key]);
  queueOrderNotifications((int)db()->insert_id,$code,$event,$note,$staff);
}
function recordPayment(string $code, string $status, string $note = '', ?int $staff = null, ?string $key = null): void
{
  recordOrderEvent($code,'pembayaran_' . $status,$note,$staff,$key,$status);
}
// Harus dipanggil dalam transaksi database, setelah pembayaran sah.
function processPaidOrder(string $code, ?int $staff = null): void
{
  $trx=orderRow($code,true);
  if (!$trx || $trx['status_bayar']!=='lunas' || $trx['status']==='dibatalkan') return;
  if ($trx['status']==='pending') {
    oq("UPDATE transaksi SET status='diproses',batas_bayar=NULL WHERE kode_transaksi=?",[$code]);
    recordOrderEvent($code,'diproses','Pembayaran diterima. Pesanan mulai disiapkan.',$staff);
  }
  if ($trx['sumber_transaksi']==='kasir' && in_array($trx['status'],['pending','diproses'],true)) completeOrder($code,$staff);
}
function receivePickupCash(string $code, int $staff): void
{
  $trx=orderRow($code,true);
  if (!$trx || $trx['sumber_transaksi']!=='online' || $trx['metode_pengiriman']!=='ambil' || $trx['metode_bayar']!=='tunai') throw new Exception('Pembayaran tunai hanya untuk pesanan ambil di tempat.',409);
  if ($trx['status']==='selesai' && $trx['status_bayar']==='lunas') return;
  if ($trx['status']!=='diproses' || !(int)$trx['siap_diambil'] || $trx['status_bayar']!=='belum_bayar') throw new Exception('Pesanan harus siap diambil dan belum dibayar.',409);
  oq("UPDATE transaksi SET status_bayar='lunas',tanggal_bayar=NOW() WHERE kode_transaksi=?",[$code]);
  recordPayment($code,'lunas','Uang tunai diterima saat pesanan diserahkan.',$staff,'tunai:' . $code);
  completeOrder($code,$staff);
}
function lockProducts(array $codes): void
{
  sort($codes, SORT_STRING);
  foreach (array_unique($codes) as $code) {
    if (!oq('SELECT kode_produk FROM produk WHERE kode_produk=? FOR UPDATE', [$code])->fetch_assoc()) throw new Exception('Produk tidak ditemukan.',404);
  }
}
function refreshAvailableStock(string $code): void
{
  oq("UPDATE produk SET stok=GREATEST(0,(SELECT COALESCE(SUM(sisa_stok),0) FROM mutasi_stok WHERE kode_produk=? AND type='masuk')-(SELECT COALESCE(SUM(jumlah),0) FROM reservasi_stok WHERE kode_produk=?)) WHERE kode_produk=?",[$code,$code,$code]);
}
function releaseReservation(string $code): void
{
  $items = oq('SELECT kode_produk FROM reservasi_stok WHERE kode_transaksi=?',[$code])->fetch_all(MYSQLI_ASSOC);
  lockProducts(array_column($items,'kode_produk'));
  oq('DELETE FROM reservasi_stok WHERE kode_transaksi=?',[$code]);
  foreach ($items as $item) refreshAvailableStock($item['kode_produk']);
}
function expireOrders(): void
{
  $codes = oq("SELECT kode_transaksi FROM transaksi WHERE status='pending' AND status_bayar IN ('belum_bayar','ditolak') AND batas_bayar<NOW() LIMIT 30")->fetch_all(MYSQLI_ASSOC);
  foreach ($codes as $row) {
    db()->begin_transaction();
    try {
      $trx = orderRow($row['kode_transaksi'],true);
      if ($trx && $trx['status']==='pending' && in_array($trx['status_bayar'],['belum_bayar','ditolak'],true) && strtotime($trx['batas_bayar'])<time()) {
        oq("UPDATE transaksi SET status='dibatalkan',status_bayar='kedaluwarsa' WHERE kode_transaksi=?",[$trx['kode_transaksi']]);
        releaseReservation($trx['kode_transaksi']);
        recordPayment($trx['kode_transaksi'],'kedaluwarsa','Batas waktu pembayaran berakhir.');
        recordOrderEvent($trx['kode_transaksi'],'dibatalkan','Pesanan dibatalkan karena batas pembayaran berakhir.');
      }
      db()->commit();
    } catch (Throwable $e) { db()->rollback(); throw $e; }
  }
}
function orderItems(array $input): array
{
  if (empty($input['detail']) || !is_array($input['detail']) || count($input['detail'])>100) throw new Exception('Isi pesanan tidak valid.',422);
  $items = [];
  foreach ($input['detail'] as $item) {
    $code = trim((string)($item['kode_produk'] ?? ''));
    $qty = filter_var($item['jumlah'] ?? null,FILTER_VALIDATE_INT);
    if ($code==='' || $qty===false || $qty<1 || $qty>10000 || isset($items[$code])) throw new Exception('Produk dan jumlah pesanan tidak valid.',422);
    $items[$code] = $qty;
  }
  ksort($items,SORT_STRING); return $items;
}
function createOrder(array $input, array $user, bool $cashier = false): string
{
  $method = $input['metode_bayar'] ?? ($cashier ? 'tunai' : 'transfer');
  if (!in_array($method, ['tunai','transfer','qris'],true)) throw new Exception('Metode pembayaran tidak valid.',422);
  if ($method!=='tunai' && !paymentMethods()[$method]) throw new Exception('Metode pembayaran ini belum tersedia. Hubungi admin.',422);
  $key = trim((string)($input['checkout_key'] ?? ''));
  if (!preg_match('/^[a-zA-Z0-9-]{20,64}$/',$key)) throw new Exception('Identitas checkout tidak valid. Muat ulang halaman.',422);
  $prior = oq('SELECT kode_transaksi,id_pengguna FROM transaksi WHERE checkout_key=?',[$key])->fetch_assoc();
  if ($prior) {
    if ((int)$prior['id_pengguna']!==(int)$user['id_pengguna']) throw new Exception('Identitas checkout sudah digunakan.',409);
    return $prior['kode_transaksi'];
  }
  $items = orderItems($input);
  $delivery = $cashier ? 'ambil' : ($input['metode_pengiriman'] ?? 'ambil');
  if (!in_array($delivery,['ambil','kirim'],true)) throw new Exception('Pilihan pengiriman tidak valid.',422);
  if ($method==='tunai' && $delivery!=='ambil') throw new Exception('Pembayaran tunai tersedia hanya untuk ambil di tempat.',422);
  $name = $cashier ? $user['nama'] : trim((string)($input['nama_penerima'] ?? ''));
  $phone = $cashier ? null : normalizePhone((string)($input['no_hp_penerima'] ?? ''));
  if (!$cashier && (mb_strlen($name)<2 || mb_strlen($name)>150)) throw new Exception('Nama penerima wajib diisi (2–150 karakter).',422);
  $address = null; $district = null; $subdistrict = null; $shipping = 0;
  if ($delivery==='kirim') {
    $zone = oq('SELECT kecamatan,kelurahan,tarif FROM zona_pengiriman WHERE id=? AND aktif=1',[(int)($input['zona_id'] ?? 0)])->fetch_assoc();
    if (!$zone) throw new Exception('Kelurahan belum termasuk wilayah pengiriman.',422);
    if (($input['kecamatan'] ?? '')!==$zone['kecamatan']) throw new Exception('Pilih kelurahan sesuai kecamatan.',422);
    $district = $zone['kelurahan']; $subdistrict=$zone['kecamatan']; $shipping=(float)$zone['tarif'];
    $address=trim((string)($input['alamat_pengiriman'] ?? ''));
    if (mb_strlen($address)<10 || mb_strlen($address)>1000) throw new Exception('Alamat pengiriman lengkap wajib diisi (10–1000 karakter).',422);
  }
  db()->begin_transaction();
  try {
    lockProducts(array_keys($items));
    $details=[]; $subtotal=0;
    foreach ($items as $code=>$qty) {
      refreshAvailableStock($code);
      $p=oq('SELECT * FROM produk WHERE kode_produk=?',[$code])->fetch_assoc();
      if ((int)$p['stok']<$qty) throw new Exception('Stok tidak cukup untuk ' . $p['nama_produk'],422);
      if ((float)$p['harga_jual']<=0) throw new Exception('Harga produk belum tersedia.',422);
      $subtotal+=(float)$p['harga_jual']*$qty;
      $details[]=[$code,$qty,(float)$p['harga_jual']];
    }
    if (!isset($input['total_harga']) || abs($subtotal-(float)$input['total_harga'])>0.01) throw new Exception('Harga produk berubah. Perbarui keranjang lalu coba lagi.',409);
    $code='GG-' . strtoupper(bin2hex(random_bytes(6)));
    $minutes=120;
    $expiry=date('Y-m-d H:i:s',time()+$minutes*60);
    oq("INSERT INTO transaksi(kode_transaksi,id_pengguna,total_harga,total_pokok,status,metode_bayar,status_bayar,metode_pengiriman,kelurahan,nama_penerima,no_hp_penerima,alamat_pengiriman,ongkir,total_tagihan,batas_bayar,checkout_key) VALUES (?,?,?,0,'pending',?,'belum_bayar',?,?,?,?,?,?,?,?,?)",[$code,$user['id_pengguna'],$subtotal,$method,$delivery,$district,$name,$phone,$address,$shipping,$subtotal+$shipping,$expiry,$key]);
    oq('UPDATE transaksi SET sumber_transaksi=?,kecamatan=? WHERE kode_transaksi=?',[$cashier ? 'kasir' : 'online',$subdistrict,$code]);
    foreach ($details as [$product,$qty,$price]) {
      oq('INSERT INTO detail_transaksi(kode_transaksi,kode_produk,jumlah,harga_satuan,harga_pokok) VALUES (?,?,?,?,0)',[$code,$product,$qty,$price]);
      oq('INSERT INTO reservasi_stok(kode_transaksi,kode_produk,jumlah) VALUES (?,?,?)',[$code,$product,$qty]);
      refreshAvailableStock($product);
    }
    recordOrderEvent($code,'checkout','Pesanan dibuat.',(int)$user['id_pengguna'],null,'belum_bayar');
    if ($cashier && $method==='tunai') {
      oq("UPDATE transaksi SET status_bayar='lunas',tanggal_bayar=NOW() WHERE kode_transaksi=?",[$code]);
      recordPayment($code,'lunas','Pembayaran tunai diterima.',(int)$user['id_pengguna'],'tunai:' . $code);
      processPaidOrder($code,(int)$user['id_pengguna']);
    } elseif (!$cashier && $method==='tunai') {
      oq("UPDATE transaksi SET status='diproses',batas_bayar=NULL WHERE kode_transaksi=?",[$code]);
      recordOrderEvent($code,'diproses','Pesanan mulai disiapkan. Bayar tunai saat ambil di toko.');
    }
    if (!$cashier) oq('DELETE FROM keranjang WHERE id_pengguna=?',[$user['id_pengguna']]);
    db()->commit(); return $code;
  } catch (Throwable $e) {
    db()->rollback();
    // Request ganda dengan key sama mengembalikan pesanan yang telah dibuat.
    if ($e instanceof mysqli_sql_exception && $e->getCode()===1062) {
      $prior=oq('SELECT kode_transaksi FROM transaksi WHERE checkout_key=? AND id_pengguna=?',[$key,$user['id_pengguna']])->fetch_assoc();
      if ($prior) return $prior['kode_transaksi'];
    }
    throw $e;
  }
}
// Dipanggil di dalam transaksi database dengan baris pesanan terkunci.
function completeOrder(string $code, ?int $staff = null): void
{
  if (func_num_args()<2) $staff=$_SESSION['user']['id_pengguna'] ?? null;
  $trx=orderRow($code,true);
  if (!$trx || $trx['status_bayar']!=='lunas') throw new Exception('Pesanan harus sudah lunas.',409);
  if ($trx['status']==='selesai') return;
  if ($trx['sumber_transaksi']==='online' && $trx['metode_pengiriman']==='ambil' && !(int)$trx['siap_diambil']) throw new Exception('Pesanan harus siap diambil sebelum diselesaikan.',409);
  $expected=$trx['metode_pengiriman']==='kirim' ? 'dikirim' : 'diproses';
  if ($trx['status']!==$expected) throw new Exception($expected==='dikirim' ? 'Pesanan pengiriman harus sudah dikirim sebelum diselesaikan.' : 'Pesanan harus sudah diproses.',409);
  $items=oq('SELECT * FROM detail_transaksi WHERE kode_transaksi=? ORDER BY kode_produk',[$code])->fetch_all(MYSQLI_ASSOC);
  lockProducts(array_column($items,'kode_produk'));
  $totalCost=0;
  foreach ($items as $item) {
    $product=$item['kode_produk']; $remaining=(int)$item['jumlah']; $cost=0;
    $reserved=oq('SELECT jumlah FROM reservasi_stok WHERE kode_transaksi=? AND kode_produk=?',[$code,$product])->fetch_assoc();
    if (!$reserved || (int)$reserved['jumlah']!==$remaining) throw new Exception('Reservasi stok pesanan tidak lengkap.',409);
    $batches=oq("SELECT * FROM mutasi_stok WHERE kode_produk=? AND type='masuk' AND sisa_stok>0 ORDER BY tanggal,id_mutasi FOR UPDATE",[$product])->fetch_all(MYSQLI_ASSOC);
    foreach ($batches as $batch) {
      if ($remaining===0) break;
      $take=min($remaining,(int)$batch['sisa_stok']); $remaining-=$take; $cost+=$take*(float)$batch['harga_pokok'];
      oq('UPDATE mutasi_stok SET sisa_stok=sisa_stok-? WHERE id_mutasi=?',[$take,$batch['id_mutasi']]);
      oq('INSERT INTO alokasi_stok(kode_transaksi,id_mutasi,jumlah,harga_pokok) VALUES (?,?,?,?)',[$code,$batch['id_mutasi'],$take,$batch['harga_pokok']]);
    }
    if ($remaining>0) throw new Exception('Stok fisik tidak cukup.',409);
    $avg=round($cost/(int)$item['jumlah'],2); $totalCost+=$avg*(int)$item['jumlah'];
    oq('UPDATE detail_transaksi SET harga_pokok=? WHERE id_detail=?',[$avg,$item['id_detail']]);
    oq("INSERT INTO mutasi_stok(kode_produk,type,jumlah,harga_pokok,keterangan,sisa_stok,kode_transaksi) VALUES (?,'keluar',?,?,?,NULL,?)",[$product,$item['jumlah'],$avg,'Penjualan ' . $code,$code]);
    oq('DELETE FROM reservasi_stok WHERE kode_transaksi=? AND kode_produk=?',[$code,$product]);
    oq('UPDATE produk SET terjual=terjual+? WHERE kode_produk=?',[$item['jumlah'],$product]);
    refreshAvailableStock($product);
  }
  oq("UPDATE transaksi SET status='selesai',total_pokok=?,tanggal_selesai=NOW() WHERE kode_transaksi=?",[round($totalCost,2),$code]);
  recordOrderEvent($code,'selesai','Barang diterima. Pesanan selesai.',$staff);
}
function cancelOrder(string $code, bool $customer=false): void
{
  $trx=orderRow($code,true);
  if (!$trx) throw new Exception('Pesanan tidak ditemukan.',404);
  if ($trx['status']==='dibatalkan') return;
  $unpreparedCash=$trx['metode_bayar']==='tunai' && $trx['status']==='diproses' && !(int)$trx['siap_diambil'] && $trx['status_bayar']==='belum_bayar';
  if ($customer && ((!$unpreparedCash && $trx['status']!=='pending') || in_array($trx['status_bayar'],['lunas','menunggu_verifikasi','perlu_refund','dikembalikan'],true))) throw new Exception('Pesanan ini perlu dibatalkan melalui admin.',409);
  if ($trx['status']==='selesai') {
    if (time()-strtotime($trx['tanggal_selesai'] ?? $trx['tanggal_transaksi'])>86400) throw new Exception('Pesanan selesai lebih dari 24 jam tidak dapat dibatalkan.',409);
    $allocations=oq('SELECT a.*,m.kode_produk FROM alokasi_stok a JOIN mutasi_stok m ON m.id_mutasi=a.id_mutasi WHERE a.kode_transaksi=? ORDER BY m.kode_produk,a.id_mutasi',[$code])->fetch_all(MYSQLI_ASSOC);
    if (!$allocations) throw new Exception('Transaksi lama tidak memiliki catatan batch. Hubungi pengelola untuk koreksi stok.',409);
    lockProducts(array_column($allocations,'kode_produk'));
    foreach ($allocations as $a) {
      oq('UPDATE mutasi_stok SET sisa_stok=sisa_stok+? WHERE id_mutasi=?',[$a['jumlah'],$a['id_mutasi']]);
      oq("INSERT INTO mutasi_stok(kode_produk,type,jumlah,harga_pokok,keterangan,sisa_stok,kode_transaksi) VALUES (?,'masuk',?,?,?,0,?)",[$a['kode_produk'],$a['jumlah'],$a['harga_pokok'],'Pengembalian ' . $code,$code]);
      oq('UPDATE produk SET terjual=terjual-? WHERE kode_produk=?',[$a['jumlah'],$a['kode_produk']]);
      refreshAvailableStock($a['kode_produk']);
    }
  } else releaseReservation($code);
  $payment=$trx['status_bayar']==='lunas' ? 'perlu_refund' : $trx['status_bayar'];
  oq("UPDATE transaksi SET status='dibatalkan',status_bayar=? WHERE kode_transaksi=?",[$payment,$code]);
  recordOrderEvent($code,'dibatalkan','Pesanan dibatalkan.',$_SESSION['user']['id_pengguna'] ?? null,null,$payment);
  if ($payment==='perlu_refund') recordPayment($code,$payment,'Pesanan dibatalkan. Pengembalian dana harus ditangani admin.',$_SESSION['user']['id_pengguna'] ?? null,'refund-diperlukan:' . $code);
}
