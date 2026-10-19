<?php
class UserController
{

  public function home()
  {
    view('user/home', [
      'title' => 'GGmart | Toko Pangan Sinode GMIT',
      'description' => 'Belanja produk lokal NTT dan kebutuhan sehari-hari di GGmart, toko pangan milik Sinode GMIT. Ambil di toko atau pilih pengiriman lokal di Kupang.',
      'isLanding' => true
    ]);
  }

  public function tentang()
  {
    view('user/tentang', [
      'title' => 'Tentang Kami | GGmart Toko Pangan Sinode GMIT',
      'description' => 'Kenali GGmart, toko pangan milik Sinode GMIT di Kupang yang mendukung pemasaran produk jemaat dan UMKM lokal Nusa Tenggara Timur.'
    ]);
  }

  public function keranjang()
  {
    view('user/keranjang', ['title' => 'Keranjang | GGMart']);
  }

  public function transaksi()
  {
    view('user/transaksi', ['title' => 'Pesanan Saya | GGmart']);
  }

  public function produk()
  {
    view('user/produk', ['title' => 'Katalog Produk | GGMart']);
  }

  public function produkDetail()
  {
    view('user/produk-detail', ['title' => 'Detail Produk | GGMart']);
  }

  public function profil()
  {
    view('user/profil', ['title' => 'Profil Saya | GGMart']);
  }

  public function pembayaran()
  {
    $role=$_SESSION['user']['role'] ?? 'pelanggan';
    view('user/pembayaran', ['title' => ($role === 'pelanggan' ? 'Detail Pesanan' : 'Pembayaran') . ' | GGmart'], $role==='admin' ? 'admin' : ($role==='kasir' ? 'kasir' : 'user'));
  }
  public function struk()
  {
    $trx=orderRow((string)params('kode'));
    if (!$trx) { http_response_code(404); echo 'Transaksi tidak ditemukan.'; return; }
    $user=$_SESSION['user'];
    $cashPickup=$user['role']==='kasir' && $trx['sumber_transaksi']==='online' && $trx['metode_bayar']==='tunai' && $trx['metode_pengiriman']==='ambil';
    if (!in_array($user['role'],['admin','pimpinan'],true) && (int)$trx['id_pengguna']!==(int)$user['id_pengguna'] && !$cashPickup) { http_response_code(403); echo 'Akses struk ditolak.'; return; }
    if ($trx['status']!=='selesai' || $trx['status_bayar']!=='lunas') { http_response_code(409); echo 'Struk tersedia setelah transaksi lunas dan selesai.'; return; }
    $items=oq('SELECT d.jumlah,d.harga_satuan,p.nama_produk FROM detail_transaksi d JOIN produk p ON p.kode_produk=d.kode_produk WHERE d.kode_transaksi=? ORDER BY d.id_detail',[$trx['kode_transaksi']])->fetch_all(MYSQLI_ASSOC);
    $returnUrl=BASE_URL . ($user['role']==='pelanggan' ? '/transaksi?tahap=selesai' : ($user['role']==='pimpinan' ? '/admin/transaksi' : (query('kembali')==='pesanan' && $user['role']==='admin' ? '/admin/pesanan?tahap=selesai' : '/admin/kasir')));
    header('Cache-Control: private, no-store');
    require ROOT_PATH . '/app/views/pages/user/struk.php';
  }
}
