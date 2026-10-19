<?php
class ApiPembayaranController
{
  private function accessible(string $code,bool $lock=false): array
  {
    $trx=orderRow($code,$lock);
    if (!$trx) throw new Exception('Pesanan tidak ditemukan.',404);
    $user=$_SESSION['user'];
    if ($user['role']!=='admin' && (int)$trx['id_pengguna']!==(int)$user['id_pengguna']) throw new Exception('Akses pembayaran ditolak.',403);
    return $trx;
  }
  public function bukti()
  {
    $new=null; $old=null;
    db()->begin_transaction();
    try {
      $trx=$this->accessible(trim((string)input('kode_transaksi','')),true);
      if (!in_array($trx['metode_bayar'],['transfer','qris'],true) || $trx['status']!=='pending' || !in_array($trx['status_bayar'],['belum_bayar','ditolak'],true) || strtotime($trx['batas_bayar'])<time()) throw new Exception('Unggah bukti tidak tersedia atau batas pembayaran telah berakhir.',409);
      $f=$_FILES['bukti'] ?? null;
      if (!$f || $f['error']!==UPLOAD_ERR_OK || $f['size']<=0 || $f['size']>5*1024*1024) throw new Exception('Pilih gambar bukti pembayaran maksimal 5 MB.',422);
      $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
      $types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
      if (!isset($types[$mime]) || !getimagesize($f['tmp_name'])) throw new Exception('Bukti harus berupa gambar JPG, PNG, atau WebP.',422);
      $dir=ROOT_PATH . '/storage/bukti'; if (!is_dir($dir)) mkdir($dir,0700,true);
      $new=bin2hex(random_bytes(20)) . '.' . $types[$mime]; $old=$trx['bukti_bayar'];
      if (!move_uploaded_file($f['tmp_name'],$dir . '/' . $new)) throw new Exception('Bukti belum dapat disimpan.',500);
      oq("UPDATE transaksi SET bukti_bayar=?,status_bayar='menunggu_verifikasi',catatan_bayar=NULL WHERE kode_transaksi=?",[$new,$trx['kode_transaksi']]);
      recordPayment($trx['kode_transaksi'],'menunggu_verifikasi','Bukti pembayaran diunggah. Menunggu pemeriksaan admin.',(int)$_SESSION['user']['id_pengguna'],'bukti:' . $new);
      db()->commit();
      if ($old && preg_match('/^[a-f0-9]{40}\.(jpg|png|webp)$/',$old)) @unlink($dir . '/' . $old);
      response(['success'=>true,'message'=>'Bukti terkirim. Menunggu pemeriksaan admin.']);
    } catch (Throwable $e) { db()->rollback(); if ($new) @unlink(ROOT_PATH . '/storage/bukti/' . $new); orderError($e); }
  }
  public function gambar()
  {
    try {
      $trx=$this->accessible((string)params('kode'));
      $file=$trx['bukti_bayar'];
      if (!$file || !preg_match('/^[a-f0-9]{40}\.(jpg|png|webp)$/',$file)) throw new Exception('Bukti pembayaran belum tersedia.',404);
      $path=ROOT_PATH . '/storage/bukti/' . $file;
      if (!is_file($path)) throw new Exception('Bukti pembayaran tidak ditemukan.',404);
      header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($path));
      header('X-Content-Type-Options: nosniff'); header('Cache-Control: private, no-store');
      readfile($path); exit;
    } catch (Throwable $e) { orderError($e); }
  }
  public function verifikasi()
  {
    db()->begin_transaction();
    try {
      $trx=$this->accessible(trim((string)input('kode_transaksi','')),true);
      $accept=input('aksi')==='terima'; $note=trim((string)input('catatan',''));
      if (!in_array(input('aksi'),['terima','tolak'],true)) throw new Exception('Aksi verifikasi tidak valid.',422);
      if (!in_array($trx['metode_bayar'],['transfer','qris'],true) || $trx['status']!=='pending' || $trx['status_bayar']!=='menunggu_verifikasi' || !$trx['bukti_bayar']) throw new Exception('Bukti tidak sedang menunggu verifikasi.',409);
      if (!$accept && (mb_strlen($note)<5 || mb_strlen($note)>500)) throw new Exception('Isi alasan penolakan (5–500 karakter).',422);
      $state=$accept ? 'lunas' : 'ditolak';
      oq('UPDATE transaksi SET status_bayar=?,catatan_bayar=?,tanggal_bayar=' . ($accept ? 'NOW()' : 'NULL') . ',batas_bayar=' . ($accept ? 'batas_bayar' : 'DATE_ADD(NOW(),INTERVAL 2 HOUR)') . ' WHERE kode_transaksi=?',[$state,$note,$trx['kode_transaksi']]);
      recordPayment($trx['kode_transaksi'],$state,$accept ? 'Pembayaran telah diterima dan diverifikasi admin.' : $note,(int)$_SESSION['user']['id_pengguna'],'verifikasi:' . $state . ':' . $trx['bukti_bayar']);
      if ($accept) processPaidOrder($trx['kode_transaksi'],(int)$_SESSION['user']['id_pengguna']);
      db()->commit(); response(['success'=>true,'message'=>$accept ? ($trx['sumber_transaksi']==='kasir' ? 'Pembayaran diterima. Transaksi kasir selesai.' : 'Pembayaran diterima. Pesanan otomatis diproses.') : 'Bukti ditolak. Bukti dapat diunggah ulang.']);
    } catch (Throwable $e) { db()->rollback(); orderError($e); }
  }
  public function refund()
  {
    db()->begin_transaction();
    try {
      $trx=$this->accessible(trim((string)input('kode_transaksi','')),true);
      $note=trim((string)input('catatan',''));
      if ($trx['status_bayar']!=='perlu_refund' || mb_strlen($note)<5 || mb_strlen($note)>500) throw new Exception('Isi catatan pengembalian dana (5–500 karakter).',422);
      oq("UPDATE transaksi SET status_bayar='dikembalikan',catatan_bayar=? WHERE kode_transaksi=?",[$note,$trx['kode_transaksi']]);
      recordPayment($trx['kode_transaksi'],'dikembalikan',$note,(int)$_SESSION['user']['id_pengguna'],'refund-selesai:' . $trx['kode_transaksi']);
      db()->commit(); response(['success'=>true,'message'=>'Pengembalian dana dicatat.']);
    } catch (Throwable $e) { db()->rollback(); orderError($e); }
  }
  public function tunai()
  {
    db()->begin_transaction();
    try {
      receivePickupCash(trim((string)input('kode_transaksi','')),(int)$_SESSION['user']['id_pengguna']);
      db()->commit(); response(['success'=>true,'message'=>'Uang tunai diterima. Pesanan selesai.']);
    } catch (Throwable $e) { db()->rollback(); orderError($e); }
  }

}
