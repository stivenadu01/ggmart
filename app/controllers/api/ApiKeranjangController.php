<?php
class ApiKeranjangController
{
  public function __construct() { model('Keranjang'); expireOrders(); }
  private function owner(): int { return (int)$_SESSION['user']['id_pengguna']; }
  private function quantity($value, bool $allowZero = false): int
  {
    $qty=filter_var($value,FILTER_VALIDATE_INT);
    if ($qty===false || $qty<($allowZero ? 0 : 1) || $qty>10000) throw new Exception('Jumlah produk tidak valid.',422);
    return $qty;
  }
  private function ownItem($id): array
  {
    $item=oq('SELECT * FROM keranjang WHERE id_keranjang=? AND id_pengguna=?',[(int)$id,$this->owner()])->fetch_assoc();
    if (!$item) throw new Exception('Item keranjang tidak ditemukan.',404);
    return $item;
  }
  private function stock(string $code, int $qty): void
  {
    $product=oq('SELECT stok FROM produk WHERE kode_produk=?',[$code])->fetch_assoc();
    if (!$product) throw new Exception('Produk tidak ditemukan.',404);
    if ($qty>(int)$product['stok']) throw new Exception('Stok tidak mencukupi.',422);
  }
  public function list() { response(['success'=>true,'data'=>getKeranjangByUser($this->owner())]); }
  public function tambah()
  {
    try {
      $data=input(); $code=trim((string)($data['kode_produk'] ?? '')); $qty=$this->quantity($data['jumlah'] ?? null);
      $item=cekKeranjang($this->owner(),$code);
      $total=$qty+(int)($item['jumlah'] ?? 0); $this->stock($code,$total);
      if ($item) updateKeranjang($item['id_keranjang'],$total); else tambahKeranjang($this->owner(),$code,$qty);
      response(['success'=>true]);
    } catch (Throwable $e) { orderError($e); }
  }
  public function update()
  {
    try {
      $data=input(); $item=$this->ownItem($data['id_keranjang'] ?? 0); $qty=$this->quantity($data['jumlah'] ?? null,true);
      $this->stock($item['kode_produk'],$qty); updateKeranjang($item['id_keranjang'],$qty);
      response(['success'=>true]);
    } catch (Throwable $e) { orderError($e); }
  }
  public function hapus()
  {
    try { $item=$this->ownItem(input('id_keranjang',0)); hapusItemKeranjang($item['id_keranjang']); response(['success'=>true]); }
    catch (Throwable $e) { orderError($e); }
  }
  public function clear() { clearKeranjang($this->owner()); response(['success'=>true]); }
}
