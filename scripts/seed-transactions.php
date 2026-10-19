<?php
require __DIR__ . '/bootstrap.php';
$receipt=ROOT_PATH . '/storage/seed-receipt.json';
if (is_file($receipt)) {
  $prior=json_decode(file_get_contents($receipt),true)['transaksi'] ?? [];
  if (count($prior)!==10) throw new RuntimeException('Catatan pengisian data tidak lengkap.');
  $found=(int)oq('SELECT COUNT(*) n FROM transaksi WHERE kode_transaksi IN (' . implode(',',array_fill(0,count($prior),'?')) . ')',$prior)->fetch_assoc()['n'];
  if ($found===10) { echo "Data contoh sudah pernah diisi; tidak ditambahkan lagi.\n"; exit; }
  if ($found>0) throw new RuntimeException('Sebagian data contoh masih ada. Periksa database sebelum menambah data lagi.');
}
$customer=oq("SELECT id_pengguna,nama,no_hp,alamat FROM pengguna WHERE role='pelanggan' ORDER BY id_pengguna LIMIT 1")->fetch_assoc();
if (!$customer) throw new RuntimeException('Akun pelanggan yang sudah ada diperlukan. Tidak membuat pengguna baru.');
$products=oq('SELECT kode_produk,harga_jual FROM produk WHERE harga_jual>0 ORDER BY kode_produk LIMIT 3')->fetch_all(MYSQLI_ASSOC);
if (count($products)!==3) throw new RuntimeException('Setidaknya tiga produk diperlukan.');
$codes=[]; $mutations=[];
db()->begin_transaction();
try {
  lockProducts(array_column($products,'kode_produk'));
  foreach ($products as $p) {
    oq("INSERT INTO mutasi_stok(kode_produk,type,jumlah,harga_pokok,keterangan,sisa_stok,tanggal) VALUES (?,'masuk',30,?,'Penerimaan persediaan',30,?)",[$p['kode_produk'],round((float)$p['harga_jual']*0.7,2),date('Y-m-d 08:00:00',strtotime('-40 days'))]);
    $mutations[]=db()->insert_id; refreshAvailableStock($p['kode_produk']);
  }
  $offsets=[0,1,2,3,5,12,33,0,0,2];
  for ($i=0;$i<10;$i++) {
    $p=$products[$i%3]; $qty=1+$i%3; $subtotal=(float)$p['harga_jual']*$qty;
    $method=['tunai','transfer','qris'][$i%3];
    if ($i===7 || $i===8) $method='transfer';
    $shipping=$i%2===1 ? 5000 : 0;
    $code='GG-' . strtoupper(bin2hex(random_bytes(6))); $codes[]=$code;
    $date=date('Y-m-d',strtotime('-' . $offsets[$i] . ' days')) . ' ' . sprintf('%02d:15:00',9+$i%7);
    $paid=$i<7 || $i===8;
    oq("INSERT INTO transaksi(kode_transaksi,id_pengguna,tanggal_transaksi,total_harga,total_pokok,status,metode_bayar,status_bayar,metode_pengiriman,kelurahan,nama_penerima,no_hp_penerima,alamat_pengiriman,ongkir,total_tagihan,batas_bayar,tanggal_bayar) VALUES (?,?,?,?,0,'pending',?,?,?,?,?,?,?,?,?,DATE_ADD(NOW(),INTERVAL 2 HOUR),?)",[$code,$customer['id_pengguna'],$date,$subtotal,$method,$paid ? 'lunas' : 'belum_bayar',$shipping ? 'kirim' : 'ambil',$shipping ? 'Fatululi' : null,$customer['nama'],$customer['no_hp'],$shipping ? ($customer['alamat'] ?: 'Kelurahan Fatululi, Kota Kupang') : null,$shipping,$subtotal+$shipping,$paid ? $date : null]);
    oq('INSERT INTO detail_transaksi(kode_transaksi,kode_produk,jumlah,harga_satuan,harga_pokok) VALUES (?,?,?,?,0)',[$code,$p['kode_produk'],$qty,$p['harga_jual']]);
    oq('INSERT INTO reservasi_stok(kode_transaksi,kode_produk,jumlah) VALUES (?,?,?)',[$code,$p['kode_produk'],$qty]);
    refreshAvailableStock($p['kode_produk']);
    recordOrderEvent($code,'checkout','Pesanan dibuat.',(int)$customer['id_pengguna'],null,'belum_bayar');
    if ($paid) recordPayment($code,'lunas','Pembayaran diterima.');
    if ($i<7) {
      oq("UPDATE transaksi SET status='diproses' WHERE kode_transaksi=?",[$code]);
      recordOrderEvent($code,'diproses','Pesanan mulai disiapkan.');
      if ($shipping) {
        oq("UPDATE transaksi SET status='dikirim',tanggal_kirim=? WHERE kode_transaksi=?",[$date,$code]);
        recordOrderEvent($code,'dikirim','Barang diserahkan kepada pengantar.');
      } else {
        oq('UPDATE transaksi SET siap_diambil=1 WHERE kode_transaksi=?',[$code]);
        recordOrderEvent($code,'siap_diambil','Pesanan siap diambil di GGmart.');
      }
      completeOrder($code);
      oq('UPDATE transaksi SET tanggal_selesai=? WHERE kode_transaksi=?',[$date,$code]);
      oq('UPDATE mutasi_stok SET tanggal=? WHERE kode_transaksi=?',[$date,$code]);
      foreach (oq('SELECT id_mutasi FROM mutasi_stok WHERE kode_transaksi=?',[$code])->fetch_all(MYSQLI_ASSOC) as $m) $mutations[]=$m['id_mutasi'];
    } elseif ($i===8) {
      oq("UPDATE transaksi SET status='diproses' WHERE kode_transaksi=?",[$code]);
      recordOrderEvent($code,'diproses','Pesanan mulai disiapkan.');
    }
    elseif ($i===9) cancelOrder($code);
    oq('UPDATE riwayat_pesanan SET tanggal=? WHERE kode_transaksi=?',[$date,$code]);
  }
  db()->commit();
  file_put_contents($receipt,json_encode(['transaksi'=>$codes,'mutasi'=>$mutations,'tanggal'=>date(DATE_ATOM)],JSON_PRETTY_PRINT));
  echo "Ditambahkan 10 transaksi dan 10 mutasi stok, memakai akun dan produk yang sudah ada.\n";
} catch (Throwable $e) { db()->rollback(); throw $e; }
