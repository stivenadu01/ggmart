<?php
require __DIR__ . '/../scripts/bootstrap.php';
function ensure(bool $condition,string $label): void { if (!$condition) throw new RuntimeException($label); echo "PASS: $label\n"; }
function callApp(string $route,?array $session=null,string $method='GET',?array $body=null,bool $csrf=true): array
{
  $ch=curl_init(BASE_URL . $route);
  $headers=['Accept: application/json'];
  if ($session) { $headers[]='Cookie: ggmart_session=' . $session['id']; if ($csrf) $headers[]='X-CSRF-Token: ' . $session['csrf']; }
  if ($body!==null) { $headers[]='Content-Type: application/json'; curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($body)); }
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>30]);
  $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE);
  if ($raw===false) throw new RuntimeException('Cannot reach local Apache: ' . curl_error($ch));
  curl_close($ch); return [$status,$raw,json_decode($raw,true)];
}
ini_set('session.use_cookies','0'); session_cache_limiter(''); session_name('ggmart_session');
$sessions=[]; $tempFiles=[];
try {
  foreach (['admin','pimpinan','kasir','pelanggan'] as $role) {
    $user=oq('SELECT id_pengguna,nama,role,no_hp,alamat,email FROM pengguna WHERE role=? LIMIT 1',[$role])->fetch_assoc();
    if (!$user) throw new RuntimeException('Existing role required: ' . $role);
    session_id(bin2hex(random_bytes(16))); session_start();
    $token=bin2hex(random_bytes(32)); $_SESSION=['user'=>$user,'csrf_token'=>$token,'_last_activity'=>time(),'_last_regenerated'=>time()];
    $sessions[$role]=['id'=>session_id(),'csrf'=>$token]; session_write_close();
  }
  foreach ($sessions as $role=>$session) {
    [$status,, $json]=callApp('/api/auth/me',$session);
    ensure($status===200 && $json['data']['role']===$role,'Session for existing ' . $role);
  }
  [$status]=callApp('/api/transaksi/list'); ensure($status===401,'Transactions require login');
  foreach (['transaksi','mutasi-stok'] as $report) {
    [$status]=callApp('/api/laporan/' . $report,$sessions['admin']); ensure($status===403,'Admin cannot export ' . $report);
  }
  [$status]=callApp('/api/pengaturan/toko',$sessions['pimpinan']); ensure($status===403,'Pimpinan cannot change store configuration');
  foreach (['produk','stok','kategori'] as $page) {
    [$status,$html]=callApp('/admin/' . $page,$sessions['pimpinan']);
    ensure($status===200 && !preg_match('/<a[^>]*>[^<]*(Tambah Produk|Tambah Stok)|@click="openCreate\(\)"/i',$html),'Read-only pimpinan page: ' . $page);
  }
  [$status]=callApp('/api/transaksi/user',$sessions['pelanggan'],'POST',[],false); ensure($status===403,'Checkout requires CSRF');
  [$status]=callApp('/api/produk',$sessions['pimpinan'],'POST',[]); ensure($status===403,'Pimpinan cannot create products through API');
  [$status]=callApp('/api/pembayaran/verifikasi',$sessions['pelanggan'],'POST',[]); ensure($status===403,'Customer cannot verify own payment');
  [$status]=callApp('/api/pembayaran/notifikasi',null,'POST',['order_id'=>'GG-invalid-local','status_code'=>'200','gross_amount'=>'10000.00','signature_key'=>str_repeat('0',128)]);
  ensure($status===(midtransKey()==='' ? 503 : 403),'Forged gateway notification rejected');
  [$status]=callApp('/api/pengaturan/toko',$sessions['admin'],'PUT',['rekening'=>[],'zona'=>[]]); ensure($status===422,'Incomplete zone updates rejected');
  [$status,, $json]=callApp('/api/checkout/config',$sessions['pelanggan']); ensure($status===200 && count($json['data']['zona'])===51,'Checkout provides 51 delivery zones');
  ensure(!str_contains(json_encode($json),'MIDTRANS_SERVER_KEY'),'No server key in checkout API');
  $code=oq("SELECT kode_transaksi FROM transaksi WHERE id_pengguna=(SELECT id_pengguna FROM pengguna WHERE role='pelanggan' LIMIT 1) LIMIT 1")->fetch_assoc()['kode_transaksi'] ?? null;
  if ($code) {
    [$status]=callApp('/api/transaksi/detail?k=' . $code,$sessions['kasir']); ensure($status===403,'Cashier cannot read another user transaction');
    [$status,, $json]=callApp('/api/transaksi/detail?k=' . $code,$sessions['pelanggan']); ensure($status===200 && !isset($json['data']['checkout_key'],$json['data']['bukti_bayar']),'Customer gets protected payment detail');
  }
  foreach (['harian','bulanan','tahunan'] as $type) {
    foreach (['current','empty'] as $period) {
      $query=$period==='empty' ? '&tanggal=2001-01-01&bulan=2001-01&tahun=2001' : '';
      [$status,$raw]=callApp('/api/laporan/transaksi?tipe=' . $type . $query,$sessions['pimpinan']);
      ensure($status===200 && str_starts_with($raw,'PK'),'Excel ' . $type . ' (' . $period . ') generated');
      $file=ROOT_PATH . '/storage/report-check-' . bin2hex(random_bytes(4)) . '.xlsx'; $tempFiles[]=$file; file_put_contents($file,$raw);
      $book=PhpOffice\PhpSpreadsheet\IOFactory::load($file);
      ensure($book->getSheetByName('Pengiriman')!==null,'Shipping sheet in ' . $type);
      foreach ($book->getWorksheetIterator() as $sheet) {
        foreach ($sheet->getCellCollection()->getCoordinates() as $coordinate) {
          $cell=$sheet->getCell($coordinate);
          if ($cell->getDataType()==='f') ensure(!str_starts_with((string)$cell->getCalculatedValue(),'#'),'Excel formula ' . $type . ' ' . $coordinate);
        }
      }
      $book->disconnectWorksheets(); unlink($file);
    }
  }
  [$status,$raw]=callApp('/api/laporan/mutasi-stok',$sessions['pimpinan']); ensure($status===200 && str_starts_with($raw,'PK'),'Pimpinan stock Excel generated');
  foreach (['/scripts/seed-transactions.php','/tests/http-integration.php','/storage/seed-receipt.json','/.env'] as $path) {
    [$status]=callApp($path); ensure(in_array($status,[403,404],true),'Private resource blocked: ' . $path);
  }
  echo "HTTP checks completed; no new business data or users created.\n";
} finally {
  foreach ($tempFiles as $file) if (is_file($file)) unlink($file);
  foreach ($sessions as $session) { session_id($session['id']); session_start(); $_SESSION=[]; session_destroy(); }
}
