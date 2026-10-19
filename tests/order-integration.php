<?php
require __DIR__ . '/../scripts/bootstrap.php';
function check(bool $ok,string $name): void { if (!$ok) throw new RuntimeException($name); echo "PASS: $name\n"; }
$customer=oq("SELECT * FROM pengguna WHERE role='pelanggan' LIMIT 1")->fetch_assoc();
check((bool)$customer,'Existing customer available');
check(count(oq('SELECT id FROM zona_pengiriman')->fetch_all(MYSQLI_ASSOC))===51,'51 delivery zones');
check(normalizePhone('0812-3456-7890')==='6281234567890','WhatsApp normalization');
try { normalizePhone('123'); throw new RuntimeException('Invalid phone accepted'); } catch (Exception $e) { check($e->getCode()===422,'Invalid phone rejected'); }
try { createOrder(['metode_bayar'=>'transfer','detail'=>[]],$customer); throw new RuntimeException('Invalid checkout accepted'); } catch (Exception $e) { check($e->getCode()===422,'Unconfigured or invalid checkout rejected'); }
$product=oq('SELECT * FROM produk WHERE harga_jual>0 ORDER BY kode_produk LIMIT 1')->fetch_assoc();
$code='GG-' . strtoupper(bin2hex(random_bytes(6)));
$before=$product;
db()->begin_transaction();
try {
  lockProducts([$product['kode_produk']]);
  // Dua batch dengan harga berbeda membuktikan FIFO dan pengembalian batch asal.
  $batches=[];
  foreach ([['2000-01-01',2,1000],['2000-01-02',4,2000]] as [$day,$qty,$price]) {
    oq("INSERT INTO mutasi_stok(kode_produk,type,jumlah,harga_pokok,keterangan,sisa_stok,tanggal) VALUES (?,'masuk',?,?,'Integration fixture',?,?)",[$product['kode_produk'],$qty,$price,$qty,$day]);
    $batches[]=db()->insert_id;
  }
  oq("INSERT INTO transaksi(kode_transaksi,id_pengguna,total_harga,total_pokok,status,metode_bayar,status_bayar,ongkir,total_tagihan,batas_bayar) VALUES (?,?,9000,0,'diproses','transfer','lunas',5000,14000,DATE_ADD(NOW(),INTERVAL 1 HOUR))",[$code,$customer['id_pengguna']]);
  oq('INSERT INTO detail_transaksi(kode_transaksi,kode_produk,jumlah,harga_satuan,harga_pokok) VALUES (?,?,3,3000,0)',[$code,$product['kode_produk']]);
  oq('UPDATE transaksi SET siap_diambil=1 WHERE kode_transaksi=?',[$code]);
  oq('INSERT INTO reservasi_stok(kode_transaksi,kode_produk,jumlah) VALUES (?,?,3)',[$code,$product['kode_produk']]);
  refreshAvailableStock($product['kode_produk']);
  $available=oq('SELECT stok FROM produk WHERE kode_produk=?',[$product['kode_produk']])->fetch_assoc()['stok'];
  check((int)$available===(int)$before['stok']+3,'Reservation reduces available stock');
  oq("UPDATE transaksi SET status_bayar='belum_bayar' WHERE kode_transaksi=?",[$code]);
  try { completeOrder($code); throw new RuntimeException('Unpaid completed'); } catch (Exception $e) { check($e->getCode()===409,'Unpaid order cannot complete'); }
  oq("UPDATE transaksi SET status_bayar='lunas' WHERE kode_transaksi=?",[$code]);
  completeOrder($code);
  $alloc=oq('SELECT * FROM alokasi_stok WHERE kode_transaksi=? ORDER BY id_mutasi',[$code])->fetch_all(MYSQLI_ASSOC);
  check(count($alloc)===2 && (int)$alloc[0]['jumlah']===2 && (int)$alloc[1]['jumlah']===1,'FIFO consumes original batches');
  check((int)oq('SELECT COUNT(*) n FROM reservasi_stok WHERE kode_transaksi=?',[$code])->fetch_assoc()['n']===0,'Completion releases reservation');
  check(abs((float)orderRow($code)['total_pokok']-4000)<0.02,'Historical cost preserved with rounding');
  check((int)oq("SELECT COUNT(*) n FROM mutasi_stok WHERE kode_transaksi=? AND type='keluar'",[$code])->fetch_assoc()['n']===1,'Sale recorded in stock ledger');
  cancelOrder($code);
  check(orderRow($code)['status_bayar']==='perlu_refund','Paid cancellation requires refund');
  check((int)oq('SELECT sisa_stok FROM mutasi_stok WHERE id_mutasi=?',[$batches[0]])->fetch_assoc()['sisa_stok']===2 && (int)oq('SELECT sisa_stok FROM mutasi_stok WHERE id_mutasi=?',[$batches[1]])->fetch_assoc()['sisa_stok']===4,'Cancellation restores correct FIFO batches');
  $ledger=(int)oq('SELECT COUNT(*) n FROM mutasi_stok WHERE kode_transaksi=?',[$code])->fetch_assoc()['n'];
  cancelOrder($code);
  check((int)oq('SELECT COUNT(*) n FROM mutasi_stok WHERE kode_transaksi=?',[$code])->fetch_assoc()['n']===$ledger,'Repeated cancellation does not duplicate stock');
  db()->rollback();
} catch (Throwable $e) { db()->rollback(); throw $e; }
check((int)oq('SELECT stok FROM produk WHERE kode_produk=?',[$product['kode_produk']])->fetch_assoc()['stok']===(int)$before['stok'],'Fixtures rolled back');
echo "Integration checks finished without persisted test rows.\n";
