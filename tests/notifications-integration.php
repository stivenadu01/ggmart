<?php
require __DIR__ . '/../scripts/bootstrap.php';
ob_start();
function notificationCheck(bool $ok,string $label): void {
  if (!$ok) throw new RuntimeException($label);
  echo "PASS: $label\n";
}
function notificationCall(string $path,array $session,string $method='GET',?array $data=null,bool $csrf=true): array {
  $ch=curl_init(BASE_URL . '/api/notifikasi' . $path);
  $headers=['Accept: application/json','Cookie: ggmart_session=' . $session['id']];
  if ($csrf) $headers[]='X-CSRF-Token: ' . $session['csrf'];
  if ($data!==null) { $headers[]='Content-Type: application/json'; curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data)); }
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>60]);
  $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
  return [$status,json_decode($raw,true)];
}
function notificationFixture(int $owner,string $source='online',string $method='transfer'): string {
  $code='NT-' . strtoupper(bin2hex(random_bytes(6)));
  oq("INSERT INTO transaksi(kode_transaksi,id_pengguna,total_harga,total_tagihan,status,metode_bayar,status_bayar,sumber_transaksi,metode_pengiriman) VALUES (?,?,10000,10000,'pending',?,'belum_bayar',?,'ambil')",[$code,$owner,$method,$source]);
  return $code;
}
$users=[];
foreach (['admin','pelanggan','kasir','pimpinan'] as $role) {
  $users[$role]=oq('SELECT id_pengguna,nama,role,is_verified FROM pengguna WHERE role=? AND is_verified=1 LIMIT 1',[$role])->fetch_assoc();
  if (!$users[$role]) throw new RuntimeException('Existing verified users required.');
}
$original=(int)oq('SELECT COUNT(*) n FROM notifikasi')->fetch_assoc()['n'];
// Pemeriksaan unit integrasi ini tidak meninggalkan pesanan maupun notifikasi.
db()->begin_transaction();
try {
  $code=notificationFixture((int)$users['pelanggan']['id_pengguna']);
  recordOrderEvent($code,'checkout','Pesanan dibuat.',(int)$users['pelanggan']['id_pengguna']);
  $customer=oq("SELECT * FROM notifikasi WHERE id_riwayat IN(SELECT id FROM riwayat_pesanan WHERE kode_transaksi=?) AND peran='pelanggan'",[$code])->fetch_all(MYSQLI_ASSOC);
  notificationCheck(count($customer)===1 && (int)$customer[0]['popup']===0,'Own checkout is saved without a popup');
  notificationCheck((int)oq("SELECT COUNT(*) n FROM notifikasi WHERE id_riwayat IN(SELECT id FROM riwayat_pesanan WHERE kode_transaksi=?) AND peran='admin' AND judul='Pesanan baru masuk'",[$code])->fetch_assoc()['n']>0,'Online checkout alerts admins by event rather than changing badge totals');
  oq("UPDATE transaksi SET status_bayar='menunggu_verifikasi' WHERE kode_transaksi=?",[$code]);
  recordPayment($code,'menunggu_verifikasi','Bukti diunggah.',(int)$users['pelanggan']['id_pengguna'],'proof:' . $code);
  notificationCheck(oq("SELECT judul FROM notifikasi WHERE id_riwayat IN(SELECT id FROM riwayat_pesanan WHERE kode_transaksi=? AND peristiwa='pembayaran_menunggu_verifikasi') AND peran='admin' LIMIT 1",[$code])->fetch_assoc()['judul']==='Bukti transfer perlu diverifikasi','Transfer proof is awaiting verification, never labeled paid');
  oq("UPDATE transaksi SET status_bayar='lunas' WHERE kode_transaksi=?",[$code]);
  recordPayment($code,'lunas','Transfer diterima.',(int)$users['admin']['id_pengguna'],'verified:' . $code);
  oq("UPDATE transaksi SET status='diproses' WHERE kode_transaksi=?",[$code]);
  recordOrderEvent($code,'diproses','Mulai disiapkan.',(int)$users['admin']['id_pengguna']);
  notificationCheck((int)oq("SELECT COUNT(*) n FROM notifikasi n JOIN riwayat_pesanan r ON r.id=n.id_riwayat WHERE r.kode_transaksi=? AND n.peran='pelanggan' AND n.popup=1",[$code])->fetch_assoc()['n']===1,'Paid and automatic processing produce one customer popup');
  notificationCheck((int)oq("SELECT popup FROM notifikasi n JOIN riwayat_pesanan r ON r.id=n.id_riwayat WHERE r.kode_transaksi=? AND n.id_pengguna=? AND r.peristiwa='pembayaran_lunas'",[$code,$users['admin']['id_pengguna']])->fetch_assoc()['popup']===0,'Admin does not get a popup for their own payment confirmation');
  recordOrderEvent($code,'siap_diambil','Siap diambil.',(int)$users['admin']['id_pengguna']);
  recordOrderEvent($code,'dikirim','Dalam pengiriman.',(int)$users['admin']['id_pengguna']);
  recordOrderEvent($code,'selesai','Barang diterima.',(int)$users['admin']['id_pengguna']);
  notificationCheck((int)oq("SELECT COUNT(*) n FROM notifikasi n JOIN riwayat_pesanan r ON r.id=n.id_riwayat WHERE r.kode_transaksi=? AND n.peran='pelanggan' AND n.jenis IN('siap_diambil','dikirim','selesai')",[$code])->fetch_assoc()['n']===3,'Pickup, shipping and completion alert only the order owner');
  oq("UPDATE transaksi SET status='dibatalkan',status_bayar='perlu_refund' WHERE kode_transaksi=?",[$code]);
  recordOrderEvent($code,'dibatalkan','Pesanan dibatalkan.',(int)$users['admin']['id_pengguna']);
  recordPayment($code,'perlu_refund','Pengembalian dana perlu ditangani.',(int)$users['admin']['id_pengguna']);
  notificationCheck((int)oq("SELECT COUNT(*) n FROM notifikasi n JOIN riwayat_pesanan r ON r.id=n.id_riwayat WHERE r.kode_transaksi=? AND n.peran='pelanggan' AND n.jenis IN('dibatalkan','pembayaran_perlu_refund')",[$code])->fetch_assoc()['n']===1,'Cancelled paid orders receive one refund notice');
  $counter=notificationFixture((int)$users['kasir']['id_pengguna'],'kasir');
  recordOrderEvent($counter,'checkout','Transaksi kasir.',(int)$users['kasir']['id_pengguna']);
  notificationCheck((int)oq('SELECT COUNT(*) n FROM notifikasi n JOIN riwayat_pesanan r ON r.id=n.id_riwayat WHERE r.kode_transaksi=?',[$counter])->fetch_assoc()['n']===0,'Counter sale does not create an online new-order alert');
  recordPayment($counter,'ditolak','Bukti kurang jelas.',(int)$users['admin']['id_pengguna']);
  notificationCheck(oq("SELECT tautan FROM notifikasi n JOIN riwayat_pesanan r ON r.id=n.id_riwayat WHERE r.kode_transaksi=? AND n.peran='kasir'",[$counter])->fetch_assoc()['tautan']==='/admin/transaksi/' . $counter,'Cashier gets their own rejected proof with an authorized detail link');
  $cash=notificationFixture((int)$users['pelanggan']['id_pengguna'],'online','tunai');
  recordOrderEvent($cash,'siap_diambil','Siap diambil.',(int)$users['admin']['id_pengguna']);
  notificationCheck((int)oq("SELECT COUNT(*) n FROM notifikasi n JOIN riwayat_pesanan r ON r.id=n.id_riwayat WHERE r.kode_transaksi=? AND n.peran='kasir' AND n.tautan='/admin/kasir?pesanan=1'",[$cash])->fetch_assoc()['n']>0,'Cashiers receive cash pickup readiness through restricted incoming orders');
  notificationCheck((int)oq("SELECT COUNT(*) n FROM notifikasi WHERE peran='pimpinan'")->fetch_assoc()['n']===0,'Leadership receives no operational notifications');
} finally { db()->rollback(); }
notificationCheck((int)oq('SELECT COUNT(*) n FROM notifikasi')->fetch_assoc()['n']===$original,'Rollback also removes every queued notification');

// API: akun yang sudah ada, sesi sementara khusus pengujian, tanpa login browser.
ini_set('session.use_cookies','0'); session_cache_limiter(''); session_name('ggmart_session');
$sessions=[]; $codes=[]; $device='11111111-2222-4333-8444-555555555555';
try {
  foreach (['admin','pelanggan','kasir','pimpinan','pelanggan2'] as $role) {
    $user=$users[$role==='pelanggan2' ? 'pelanggan' : $role];
    session_id(bin2hex(random_bytes(16))); session_start(); $csrf=bin2hex(random_bytes(32));
    $_SESSION=['user'=>$user,'csrf_token'=>$csrf,'_last_activity'=>time(),'_last_regenerated'=>time()];
    $sessions[$role]=['id'=>session_id(),'csrf'=>$csrf]; session_write_close();
  }
  $old=notificationFixture((int)$users['pelanggan']['id_pengguna']); $codes[]=$old;
  recordOrderEvent($old,'checkout','Pesanan lama.',(int)$users['pelanggan']['id_pengguna']);
  recordPayment($old,'ditolak','Bukti kurang jelas.',(int)$users['admin']['id_pengguna']);
  foreach (['admin','pelanggan','kasir','pelanggan2'] as $role) {
    [$status,$result]=notificationCall('?perangkat=' . $device,$sessions[$role]);
    notificationCheck($status===200 && $result['data']['events']===[],"Fresh $role session keeps history in the inbox without replaying popups");
    $sessions[$role]['marker']=$result['data']['session'];
  }
  [$status]=notificationCall('?perangkat=' . $device,$sessions['pimpinan']);
  notificationCheck($status===403,'Operational notification endpoints reject leadership role');
  $code=notificationFixture((int)$users['pelanggan']['id_pengguna']); $codes[]=$code;
  recordOrderEvent($code,'checkout','Pesanan dibuat.',(int)$users['pelanggan']['id_pengguna']);
  recordPayment($code,'ditolak','Nominal belum terlihat.',(int)$users['admin']['id_pengguna']);
  [$status,$result]=notificationCall('?perangkat=' . $device,$sessions['pelanggan']);
  $events=$result['data']['events'];
  notificationCheck($status===200 && count($events)===1 && $events[0]['jenis']==='pembayaran_ditolak','New events after session start produce eligible customer popups');
  $id=(int)$events[0]['id']; $body=['ids'=>[$id],'perangkat'=>$device,'sesi'=>$sessions['pelanggan']['marker']];
  [$status]=notificationCall('/klaim',$sessions['pelanggan'],'POST',$body,false);
  notificationCheck($status===403,'Popup claims require CSRF');
  [$status,$result]=notificationCall('/klaim',$sessions['pelanggan'],'POST',$body);
  notificationCheck($status===200 && $result['data']['ids']===[$id],'First tab atomically claims the popup');
  $body['sesi']=$sessions['pelanggan2']['marker'];
  [$status,$result]=notificationCall('/klaim',$sessions['pelanggan2'],'POST',$body);
  notificationCheck($status===200 && $result['data']['ids']===[],'A second tab/session on the same device cannot claim the same popup');
  [$status,$result]=notificationCall('?perangkat=' . $device,$sessions['pelanggan']);
  notificationCheck($status===200 && $result['data']['events']===[] && $result['data']['unread']>0,'Popup delivery does not mark the notification as read');
  [$status]=notificationCall('/baca',$sessions['kasir'],'POST',['id'=>$id,'sesi'=>$sessions['kasir']['marker']]);
  notificationCheck($status===404,'A cashier cannot mark another customer notification as read');
  [$status,$result]=notificationCall('/klaim',$sessions['kasir'],'POST',['ids'=>[$id],'perangkat'=>$device,'sesi'=>$sessions['kasir']['marker']]);
  notificationCheck($status===200 && $result['data']['ids']===[],'A cashier cannot claim another recipient popup');
  [$status]=notificationCall('/baca',$sessions['pelanggan'],'POST',['id'=>$id,'sesi'=>'wrong-session']);
  notificationCheck($status===409,'Old account/session state cannot mark notifications read');
  [$status]=notificationCall('/baca',$sessions['pelanggan'],'POST',['id'=>$id,'sesi'=>$sessions['pelanggan']['marker']]);
  notificationCheck($status===200 && oq('SELECT dibaca_pada FROM notifikasi WHERE id=?',[$id])->fetch_assoc()['dibaca_pada']!==null,'Authorized read state persists in the database');
  [$status,$result]=notificationCall('?perangkat=' . $device,$sessions['pelanggan']);
  $upto=$result['data']['latest_id'];
  recordPayment($code,'menunggu_verifikasi','Bukti diunggah ulang.',(int)$users['pelanggan']['id_pengguna']);
  [$status]=notificationCall('/baca',$sessions['pelanggan'],'POST',['sampai'=>$upto,'sesi'=>$sessions['pelanggan']['marker']]);
  notificationCheck($status===200 && (int)oq('SELECT COUNT(*) n FROM notifikasi WHERE id_pengguna=? AND id>? AND dibaca_pada IS NULL',[$users['pelanggan']['id_pengguna'],$upto])->fetch_assoc()['n']===1,'Mark-all uses a watermark and preserves an arriving new notification');
  [$status,$result]=notificationCall('?perangkat=' . $device,$sessions['kasir']);
  notificationCheck($status===200 && !array_filter($result['data']['items'],fn($n)=>str_contains($n['pesan'],$code)),'Cashier inbox cannot expose customer order notifications');
  for ($i=0;$i<23;$i++) recordPayment($code,'menunggu_verifikasi','Bukti diunggah ulang.',(int)$users['pelanggan']['id_pengguna']);
  [$status,$page1]=notificationCall('?perangkat=' . $device,$sessions['pelanggan']);
  [$status2,$page2]=notificationCall('?perangkat=' . $device . '&sebelum=' . $page1['data']['next'],$sessions['pelanggan']);
  $firstIds=array_column($page1['data']['items'],'id'); $secondIds=array_column($page2['data']['items'],'id');
  notificationCheck($status===200 && $status2===200 && count($firstIds)===20 && $page1['data']['more'] && !$page2['data']['more'] && !array_intersect($firstIds,$secondIds),'Inbox pagination returns older notifications without overlap');
  [$status,$invalid]=notificationCall('?perangkat%5B%5D=invalid',$sessions['pelanggan']);
  notificationCheck($status===422 && $invalid['success']===false,'Invalid device arrays return JSON validation without PHP warnings');
} finally {
  foreach ($codes as $code) oq('DELETE FROM transaksi WHERE kode_transaksi=?',[$code]);
  foreach ($sessions as $session) { session_id($session['id']); session_start(); $_SESSION=[]; session_destroy(); }
}
notificationCheck((int)oq('SELECT COUNT(*) n FROM notifikasi')->fetch_assoc()['n']===$original,'API test fixtures clean up through history foreign keys');
ob_end_flush();
