<?php
class ApiPengaturanController
{
  public function checkout()
  {
    try {
      expireOrders();
      response(['success'=>true,'data'=>['zona'=>oq('SELECT id,kecamatan,kelurahan,tarif FROM zona_pengiriman WHERE aktif=1 ORDER BY kecamatan,kelurahan')->fetch_all(MYSQLI_ASSOC),'rekening'=>storeSettings(),'metode'=>paymentMethods(),'alamat_toko'=>'Jl. S. K. Lerik, Kota Baru, Kecamatan Kelapa Lima, Kota Kupang']]);
    } catch (Throwable $e) { orderError($e); }
  }
  public function detail()
  {
    try { response(['success'=>true,'data'=>['rekening'=>storeSettings(),'zona'=>oq('SELECT * FROM zona_pengiriman ORDER BY kecamatan,kelurahan')->fetch_all(MYSQLI_ASSOC),'qris_tersedia'=>true]]); }
    catch (Throwable $e) { orderError($e); }
  }
  public function pembayaran() { $this->save('pembayaran'); }
  public function pengiriman() { $this->save('pengiriman'); }
  public function simpan() { $this->save('semua'); }
  private function save(string $section): void
  {
    db()->begin_transaction();
    try {
      $input=input();
      if ($section!=='pengiriman') $this->saveBank($input['rekening'] ?? []);
      if ($section!=='pembayaran') $this->saveZones($input['zona'] ?? null);
      db()->commit();
      response(['success'=>true,'message'=>$section==='pembayaran' ? 'Pengaturan pembayaran disimpan.' : ($section==='pengiriman' ? 'Tarif pengiriman disimpan.' : 'Pengaturan toko disimpan.')]);
    } catch (Throwable $e) { db()->rollback(); orderError($e); }
  }
  private function saveBank($bank): void
  {
    if (!is_array($bank)) throw new Exception('Informasi rekening tidak valid.',422);
    $settings=[];
    foreach (['bank_nama','bank_rekening','bank_pemilik'] as $name) {
      if (!is_scalar($bank[$name] ?? '')) throw new Exception('Informasi rekening tidak valid.',422);
      $value=trim((string)($bank[$name] ?? ''));
      if (mb_strlen($value)>120) throw new Exception('Informasi rekening terlalu panjang.',422);
      if ($name==='bank_rekening' && $value!=='' && !preg_match('/^[0-9 -]{5,40}$/',$value)) throw new Exception('Nomor rekening tidak valid.',422);
      $settings[$name]=$value;
    }
    $filled=count(array_filter($settings,fn($v)=>$v!==''));
    if ($filled!==0 && $filled!==3) throw new Exception('Nama bank, nomor rekening, dan pemilik harus diisi lengkap.',422);
    foreach ($settings as $name=>$value) oq('INSERT INTO pengaturan(nama,nilai) VALUES (?,?) ON DUPLICATE KEY UPDATE nilai=VALUES(nilai)',[$name,$value]);
  }
  private function saveZones($zones): void
  {
    if (!is_array($zones) || !$zones || count($zones)>1000) throw new Exception('Daftar kelurahan tidak valid.',422);
    $seen=[];
    foreach ($zones as $zone) {
      if (!is_array($zone)) throw new Exception('Tarif pengiriman tidak valid.',422);
      $id=filter_var($zone['id'] ?? null,FILTER_VALIDATE_INT);
      $row=$id ? oq('SELECT kelurahan FROM zona_pengiriman WHERE id=? FOR UPDATE',[$id])->fetch_assoc() : null;
      if (!$row || isset($seen[$id])) throw new Exception('Kelurahan tidak valid atau terduplikasi.',422);
      $value=$zone['tarif'] ?? null;
      if ((!is_string($value) && !is_int($value) && !is_float($value)) || !preg_match('/^\d+(?:\.0{1,2})?$/',(string)$value) || (float)$value>1000000) throw new Exception('Tarif ' . $row['kelurahan'] . ' harus berupa rupiah bulat antara 0 dan 1.000.000.',422);
      $active=filter_var($zone['aktif'] ?? null,FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE);
      if ($active===null || !isset($zone['aktif'])) throw new Exception('Status layanan kelurahan tidak valid.',422);
      $seen[$id]=true;
      oq('UPDATE zona_pengiriman SET tarif=?,aktif=? WHERE id=?',[(int)$value,$active ? 1 : 0,$id]);
    }
  }
}
