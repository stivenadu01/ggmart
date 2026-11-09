<?php
class ApiTransaksiController
{
  public function __construct() { model('Transaksi'); model('DetailTransaksi'); expireOrders(); expireDeliveredOrders(); }
  public function list()
  {
    try {
      $page=max(1,(int)query('page',1)); $limit=max(1,min(100,(int)query('limit',10)));
      $user=$_SESSION['user'];
      $owner=in_array($user['role'],['admin','pimpinan'],true) ? query('user') : $user['id_pengguna'];
      $source=query('sumber'); $stage=query('tahap');
      if ($source!==null && !in_array($source,['online','kasir'],true)) throw new Exception('Jenis transaksi tidak valid.',422);
      if ($stage!==null && !in_array($stage,['menunggu_pembayaran','menunggu_verifikasi','diproses','dikirim','telah_diserahkan','selesai','dibatalkan'],true)) throw new Exception('Tahap pesanan tidak valid.',422);
      [$data,$total,$summary,$stageCounts]=getTransaksiList($page,$limit,trim(query('search','')),query('start'),query('end'),query('metode'),$owner,query('status'),$stage,$source,$source==='online');
      foreach ($data as &$row) unset($row['checkout_key'],$row['bukti_bayar']);
      unset($row);
      response(['success'=>true,'data'=>$data,'totalSummary'=>$summary,'stageCounts'=>$stageCounts,'pagination'=>['page'=>$page,'limit'=>$limit,'total'=>$total,'total_pages'=>max(1,ceil($total/$limit))]]);
    } catch (Throwable $e) { orderError($e); }
  }
  public function detail()
  {
    try {
      $trx=$this->accessible(trim(query('k','')));
      $trx['detail']=getDetailTransaksi($trx['kode_transaksi']);
      $trx['riwayat_pesanan']=oq('SELECT r.id,r.kategori,r.peristiwa,r.catatan,r.tanggal,u.nama AS petugas FROM riwayat_pesanan r LEFT JOIN pengguna u ON u.id_pengguna=r.petugas WHERE r.kode_transaksi=? ORDER BY r.tanggal,r.id',[$trx['kode_transaksi']])->fetch_all(MYSQLI_ASSOC);
      $trx['rekening']=storeSettings();
      $trx['bukti_url']=$trx['bukti_bayar'] ? BASE_URL . '/api/pembayaran/bukti/' . rawurlencode($trx['kode_transaksi']) : null;
      unset($trx['bukti_bayar'],$trx['checkout_key']);
      response(['success'=>true,'data'=>$trx]);
    } catch (Throwable $e) { orderError($e); }
  }
  public function accessible(string $code): array
  {
    $trx=orderRow($code);
    if (!$trx) throw new Exception('Transaksi tidak ditemukan.',404);
    $user=$_SESSION['user'];
    if (!in_array($user['role'],['admin','pimpinan'],true) && (int)$trx['id_pengguna']!==(int)$user['id_pengguna']) throw new Exception('Anda tidak memiliki akses ke transaksi ini.',403);
    return $trx;
  }
  public function tambah_transaksi() { $this->create(true); }
  public function tambah_transaksi_user() { $this->create(false); }
  private function create(bool $cashier): void
  {
    try { $code=createOrder(input(),$_SESSION['user'],$cashier); response(['success'=>true,'message'=>$cashier ? 'Transaksi tersimpan.' : 'Pesanan dibuat. Silakan lanjutkan pembayaran.','data'=>$code],201); }
    catch (Throwable $e) { orderError($e); }
  }
  public function proses_transaksi() { $this->change('proses'); }
  public function kirim_transaksi() { $this->change('kirim'); }
  public function siap_ambil() { $this->change('siap-ambil'); }
  public function serahkan_ke_penerima() { $this->change('serahkan'); }
  public function konfirmasi_penerimaan() { $this->change('konfirmasi-penerimaan',true); }
  public function batal_transaksi() { $this->change('batal'); }
  public function batal_transaksi_pending() { $this->change('batal',true); }
  private function change(string $action,bool $customer=false): void
  {
    try {
      $code=trim((string)input('kode_transaksi',''));
      $trx=$this->accessible($code);
      db()->begin_transaction();
      try {
        $trx=orderRow($code,true);
        if ($customer && (int)$trx['id_pengguna']!==(int)$_SESSION['user']['id_pengguna']) throw new Exception('Akses ditolak.',403);
        if ($action==='proses') {
          if (!in_array($trx['status'],['pending','diproses'],true) || $trx['status_bayar']!=='lunas') throw new Exception('Pesanan harus sudah lunas sebelum diproses.',409);
          processPaidOrder($code,(int)$_SESSION['user']['id_pengguna']);
        } elseif ($action==='kirim' || $action==='siap-ambil') {
          $cashPickup=$action==='siap-ambil' && $trx['metode_bayar']==='tunai' && $trx['metode_pengiriman']==='ambil' && $trx['status_bayar']==='belum_bayar';
          if ($trx['status']!=='diproses' || ($trx['status_bayar']!=='lunas' && !$cashPickup) || $trx['sumber_transaksi']!=='online') throw new Exception('Pesanan belum dapat disiapkan atau dikirim.',409);
          if ($action==='kirim') {
            if ($trx['metode_pengiriman']!=='kirim') throw new Exception('Pesanan ini diambil di tempat.',409);
            oq("UPDATE transaksi SET status='dikirim' WHERE kode_transaksi=?",[$code]);
            oq('UPDATE pengiriman SET tanggal_kirim=NOW() WHERE kode_transaksi=?',[$code]);
            recordOrderEvent($code,'dikirim','Barang diserahkan kepada pengantar.',(int)$_SESSION['user']['id_pengguna']);
          } else {
            if ($trx['metode_pengiriman']!=='ambil') throw new Exception('Pesanan ini menggunakan pengiriman.',409);
            oq('UPDATE pengiriman SET siap_diambil=1 WHERE kode_transaksi=?',[$code]);
            if (!(int)$trx['siap_diambil']) recordOrderEvent($code,'siap_diambil','Pesanan siap diambil di GGmart.',(int)$_SESSION['user']['id_pengguna']);
          }
        } elseif ($action==='serahkan') {
          if ($trx['status']==='telah_diserahkan') throw new Exception('Pesanan sudah dicatat telah diserahkan.',409);
          markOrderDelivered($code,(int)$_SESSION['user']['id_pengguna']);
        } elseif ($action==='konfirmasi-penerimaan') {
          if (!$customer || $trx['sumber_transaksi']!=='online' || (int)$trx['id_pengguna']!==(int)$_SESSION['user']['id_pengguna']) throw new Exception('Hanya pemilik pesanan yang dapat mengonfirmasi penerimaan.',403);
          if ($trx['status']!=='telah_diserahkan') throw new Exception('Pesanan belum dicatat telah diserahkan oleh admin.',409);
          if ($trx['status_bayar']!=='lunas') throw new Exception('Pesanan harus sudah lunas.',409);
          if (empty($trx['batas_konfirmasi'])) throw new Exception('Batas konfirmasi pesanan tidak ditemukan. Hubungi admin.',409);
          if (strtotime($trx['batas_konfirmasi'])<=time()) completeOrder($code,null,'selesai_otomatis');
          else completeOrder($code,(int)$_SESSION['user']['id_pengguna'],'konfirmasi_penerimaan');
        } elseif ($action==='selesai') completeOrder($code);
        else cancelOrder($code,$customer);
        db()->commit();
      } catch (Throwable $e) { db()->rollback(); throw $e; }
      $message=$action==='konfirmasi-penerimaan' && strtotime((string)($trx['batas_konfirmasi'] ?? '1970-01-01'))<=time() ? 'Batas konfirmasi telah berakhir. Pesanan diselesaikan otomatis.' : (['proses'=>'Pesanan diproses.','kirim'=>'Pesanan diserahkan kepada petugas pengiriman.','siap-ambil'=>'Pesanan siap diambil.','serahkan'=>'Penyerahan kepada penerima berhasil dicatat. Pelanggan memiliki waktu 24 jam untuk konfirmasi.','konfirmasi-penerimaan'=>'Penerimaan pesanan berhasil dikonfirmasi.','selesai'=>'Pesanan selesai.','batal'=>'Pesanan dibatalkan.'][$action] ?? 'Tindakan berhasil.');
      response(['success'=>true,'message'=>$message]);
    } catch (Throwable $e) { orderError($e); }
  }
  public function badge()
  {
    try { $total=oq("SELECT COUNT(*) total FROM transaksi WHERE sumber_transaksi='online' AND status IN ('pending','diproses','dikirim','telah_diserahkan')")->fetch_assoc()['total']; response(['success'=>true,'data'=>['total'=>(int)$total]]); }
    catch (Throwable $e) { orderError($e); }
  }
  // Kasir melihat pesanan masuk tanpa memperoleh akses ke data keuangan pelanggan.
  public function pesanan_kasir()
  {
    try {
      $rows=oq("SELECT t.kode_transaksi,pg.nama_penerima,t.metode_bayar,t.status_bayar,t.status,pg.metode_pengiriman,pg.siap_diambil,t.total_tagihan,t.tanggal_transaksi FROM transaksi t LEFT JOIN pengiriman pg ON pg.kode_transaksi=t.kode_transaksi WHERE t.sumber_transaksi='online' AND t.status IN ('pending','diproses','dikirim') ORDER BY tanggal_transaksi DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
      response(['success'=>true,'data'=>$rows]);
    } catch (Throwable $e) { orderError($e); }
  }
}
