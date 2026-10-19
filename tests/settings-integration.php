<?php
require __DIR__ . '/../scripts/bootstrap.php';
function settingsCheck(bool $ok,string $label): void { if (!$ok) throw new RuntimeException($label); echo "PASS: $label\n"; }
function settingsCall(string $path,array $session,string $method='GET',?array $body=null): array {
  $ch=curl_init(BASE_URL . $path); $headers=['Accept: application/json','Cookie: ggmart_session=' . $session['id'],'X-CSRF-Token: ' . $session['csrf']];
  if ($body!==null) { $headers[]='Content-Type: application/json'; curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($body)); }
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>30]);
  $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); return [$status,json_decode($raw,true)];
}
ini_set('session.use_cookies','0'); session_cache_limiter(''); session_name('ggmart_session');
$sessions=[]; $oldBank=storeSettings(); $oldRows=oq('SELECT nama,nilai FROM pengaturan WHERE nama IN (?,?,?)',array_keys($oldBank))->fetch_all(MYSQLI_ASSOC);
$zones=oq('SELECT * FROM zona_pengiriman ORDER BY id')->fetch_all(MYSQLI_ASSOC); $zone=$zones[0]; $tariff=(int)$zone['tarif']+1;
$testBank=['bank_nama'=>'Bank Pengujian Lokal','bank_rekening'=>'000001234567','bank_pemilik'=>'Pemeriksaan Formulir'];
try {
  foreach (['admin','pimpinan','pelanggan'] as $role) {
    $user=oq('SELECT id_pengguna,nama,role,no_hp,alamat,email FROM pengguna WHERE role=? LIMIT 1',[$role])->fetch_assoc();
    session_id(bin2hex(random_bytes(16))); session_start(); $token=bin2hex(random_bytes(32));
    $_SESSION=['user'=>$user,'csrf_token'=>$token,'_last_activity'=>time(),'_last_regenerated'=>time()]; $sessions[$role]=['id'=>session_id(),'csrf'=>$token]; session_write_close();
  }
  [$status]=settingsCall('/api/pengaturan/pembayaran',$sessions['admin'],'PUT',['rekening'=>$testBank]);
  settingsCheck($status===200 && storeSettings()===$testBank,'Bank settings save independently and preserve leading zeroes');
  [$status]=settingsCall('/api/pengaturan/pembayaran',$sessions['admin'],'PUT',['rekening'=>array_replace($testBank,['bank_pemilik'=>''])]);
  settingsCheck($status===422 && storeSettings()===$testBank,'Incomplete bank settings do not overwrite saved bank');
  [$status]=settingsCall('/api/pengaturan/pengiriman',$sessions['admin'],'PUT',['zona'=>array_map(fn($z)=>array_replace($z,['aktif'=>(bool)$z['aktif']]),$zones)]);
  settingsCheck($status===200,'All decimal database tariffs can be saved unchanged');
  $change=array_replace($zone,['tarif'=>$tariff,'aktif'=>(bool)$zone['aktif']]);
  [$status]=settingsCall('/api/pengaturan/pengiriman',$sessions['admin'],'PUT',['zona'=>[$change]]);
  settingsCheck($status===200 && (int)oq('SELECT tarif FROM zona_pengiriman WHERE id=?',[$zone['id']])->fetch_assoc()['tarif']===$tariff,'One tariff can be updated without requiring 51 rows');
  settingsCheck(storeSettings()===$testBank,'Shipping updates leave bank settings intact');
  foreach ([-1,5000.5,'',1000001,true] as $bad) {
    [$status]=settingsCall('/api/pengaturan/pengiriman',$sessions['admin'],'PUT',['zona'=>[array_replace($change,['tarif'=>$bad])]]);
    settingsCheck($status===422,'Invalid shipping tariff rejected: ' . json_encode($bad));
  }
  [$status]=settingsCall('/api/pengaturan/pengiriman',$sessions['admin'],'PUT',['zona'=>[$change,$change]]);
  settingsCheck($status===422,'Duplicate zone IDs rejected');
  [$status]=settingsCall('/api/pengaturan/pembayaran',$sessions['pimpinan'],'PUT',['rekening'=>$testBank]); settingsCheck($status===403,'Pimpinan cannot change payments');
  [$status]=settingsCall('/api/pengaturan/pengiriman',$sessions['pelanggan'],'PUT',['zona'=>[$change]]); settingsCheck($status===403,'Customer cannot change delivery fees');
  [$status,$config]=settingsCall('/api/checkout/config',$sessions['pelanggan']);
  settingsCheck($status===200 && count(array_unique(array_column($config['data']['zona'],'kecamatan')))===6 && !in_array('',array_column($config['data']['zona'],'kecamatan'),true),'Checkout groups all delivery zones into six kecamatan');
  [$status,$config]=settingsCall('/api/pengaturan/toko',$sessions['admin']); settingsCheck($status===200 && !isset($config['data']['notification_url']) && !isset($config['data']['midtrans_env']),'Settings API omits technical QRIS configuration');
} finally {
  db()->begin_transaction();
  try {
    oq('UPDATE zona_pengiriman SET tarif=? WHERE id=? AND tarif=?',[$zone['tarif'],$zone['id'],$tariff]);
    if (storeSettings()===$testBank) {
      foreach (array_keys($oldBank) as $key) oq('DELETE FROM pengaturan WHERE nama=?',[$key]);
      foreach ($oldRows as $row) oq('INSERT INTO pengaturan(nama,nilai) VALUES (?,?)',[$row['nama'],$row['nilai']]);
    }
    db()->commit();
  } catch (Throwable $e) { db()->rollback(); throw $e; }
  foreach ($sessions as $session) { session_id($session['id']); session_start(); $_SESSION=[]; session_destroy(); }
}
settingsCheck(storeSettings()===$oldBank,'Original bank settings restored');
settingsCheck(oq('SELECT * FROM zona_pengiriman ORDER BY id')->fetch_all(MYSQLI_ASSOC)===$zones,'Original shipping settings restored');
