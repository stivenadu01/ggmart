<?php
class ApiNotifikasiController
{
  private array $user;
  private string $since;
  private string $session;
  public function __construct()
  {
    $current=$_SESSION['user'];
    $fresh=oq('SELECT id_pengguna,role,is_verified FROM pengguna WHERE id_pengguna=?',[(int)$current['id_pengguna']])->fetch_assoc();
    if (!$fresh || !(int)$fresh['is_verified'] || $fresh['role']!==$current['role']) {
      response(['success'=>false,'message'=>'Sesi berubah. Silakan masuk kembali.'],403); exit;
    }
    $this->user=$fresh; $this->session=notificationSession();
    if (empty($_SESSION['notifikasi_sejak'])) $_SESSION['notifikasi_sejak']=oq('SELECT CURRENT_TIMESTAMP(6) tanggal')->fetch_assoc()['tanggal'];
    $this->since=$_SESSION['notifikasi_sejak'];
    header('Cache-Control: private, no-store'); session_write_close();
  }
  private function device(mixed $device): string
  {
    if (!is_string($device) || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/',$device)) throw new Exception('Perangkat notifikasi tidak valid.',422);
    return $device;
  }
  public function list()
  {
    try {
      $device=$this->device(query('perangkat',''));
      $before=filter_var(query('sebelum','0'),FILTER_VALIDATE_INT);
      if ($before===false || $before<0) throw new Exception('Halaman notifikasi tidak valid.',422);
      if ($before===0) { expireOrders(); }
      $id=(int)$this->user['id_pengguna']; $role=$this->user['role'];
      $params=[$id,$role]; $filter='id_pengguna=? AND peran=?';
      if ($before>0) { $filter.=' AND id<?'; $params[]=$before; }
      $rows=oq("SELECT id,judul,pesan,tautan,jenis,tanggal,dibaca_pada FROM notifikasi WHERE $filter ORDER BY id DESC LIMIT 21",$params)->fetch_all(MYSQLI_ASSOC);
      $more=count($rows)>20; $rows=array_slice($rows,0,20);
      $summary=oq('SELECT COALESCE(MAX(id),0) terbaru,COALESCE(SUM(dibaca_pada IS NULL),0) belum_dibaca FROM notifikasi WHERE id_pengguna=? AND peran=?',[$id,$role])->fetch_assoc();
      $events=$before===0 ? oq("SELECT n.id,n.judul,n.pesan,n.tautan,n.jenis FROM notifikasi n WHERE n.id_pengguna=? AND n.peran=? AND n.popup=1 AND n.dibaca_pada IS NULL AND n.tanggal>=? AND n.tanggal>=DATE_SUB(NOW(6),INTERVAL 5 MINUTE) AND NOT EXISTS(SELECT 1 FROM notifikasi_kirim k WHERE k.id_notifikasi=n.id AND k.perangkat=?) ORDER BY n.id LIMIT 25",[$id,$role,$this->since,$device])->fetch_all(MYSQLI_ASSOC) : [];
      response(['success'=>true,'data'=>['items'=>$rows,'more'=>$more,'next'=>$rows ? (int)end($rows)['id'] : 0,'unread'=>(int)$summary['belum_dibaca'],'latest_id'=>(int)$summary['terbaru'],'events'=>$events,'user_id'=>$id,'session'=>$this->session]]);
    } catch(Throwable $e) { orderError($e); }
  }
  public function read()
  {
    try {
      if (input('sesi','')!==$this->session) throw new Exception('Sesi notifikasi berubah.',409);
      $id=(int)$this->user['id_pengguna']; $role=$this->user['role'];
      $notification=filter_var(input('id',null),FILTER_VALIDATE_INT);
      if ($notification!==false && $notification>0) {
        if (!oq('SELECT id FROM notifikasi WHERE id=? AND id_pengguna=? AND peran=?',[$notification,$id,$role])->fetch_assoc()) throw new Exception('Notifikasi tidak ditemukan.',404);
        oq('UPDATE notifikasi SET dibaca_pada=COALESCE(dibaca_pada,NOW()) WHERE id=? AND id_pengguna=? AND peran=?',[$notification,$id,$role]);
      } else {
        $upto=filter_var(input('sampai',null),FILTER_VALIDATE_INT);
        if ($upto===false || $upto<1) throw new Exception('Notifikasi tidak valid.',422);
        oq('UPDATE notifikasi SET dibaca_pada=NOW() WHERE id_pengguna=? AND peran=? AND id<=? AND dibaca_pada IS NULL',[$id,$role,$upto]);
      }
      response(['success'=>true]);
    } catch(Throwable $e) { orderError($e); }
  }
  public function claim()
  {
    try {
      $device=$this->device(input('perangkat',''));
      if (input('sesi','')!==$this->session) throw new Exception('Sesi notifikasi berubah.',409);
      $ids=input('ids',[]);
      if (!is_array($ids) || count($ids)>25 || !$ids) throw new Exception('Notifikasi tidak valid.',422);
      foreach ($ids as $value) if (filter_var($value,FILTER_VALIDATE_INT)===false || (int)$value<1) throw new Exception('Notifikasi tidak valid.',422);
      $claimed=[]; db()->begin_transaction();
      foreach (array_unique($ids) as $notification) {
        // Klaim atomik mencegah popup ganda dari beberapa tab pada perangkat sama.
        // Status dibaca tetap terpisah dari status popup yang sudah dikirim.
        oq("INSERT INTO notifikasi_kirim(id_notifikasi,perangkat) SELECT id,? FROM notifikasi WHERE id=? AND id_pengguna=? AND peran=? AND popup=1 AND dibaca_pada IS NULL AND tanggal>=? AND tanggal>=DATE_SUB(NOW(6),INTERVAL 5 MINUTE) ON DUPLICATE KEY UPDATE id_notifikasi=VALUES(id_notifikasi)",[$device,(int)$notification,(int)$this->user['id_pengguna'],$this->user['role'],$this->since]);
        if (db()->affected_rows===1) $claimed[]=(int)$notification;
      }
      db()->commit(); response(['success'=>true,'data'=>['ids'=>$claimed,'session'=>$this->session,'user_id'=>(int)$this->user['id_pengguna']]]);
    } catch(Throwable $e) { db()->rollback(); orderError($e); }
  }
}
