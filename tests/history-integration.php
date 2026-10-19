<?php
require __DIR__ . '/../scripts/bootstrap.php';
require __DIR__ . '/../scripts/history-schema.php';
function historyCheck(bool $ok, string $label): void {
  if (!$ok) throw new RuntimeException($label);
  echo "PASS: $label\n";
}
$user=(int)oq("SELECT id_pengguna FROM pengguna WHERE role='pelanggan' LIMIT 1")->fetch_assoc()['id_pengguna'];
$legacy=legacyPaymentHistoryExists(db());
$before=(int)oq('SELECT COUNT(*) n FROM riwayat_pesanan')->fetch_assoc()['n'];
$legacyBefore=$legacy ? (int)oq('SELECT COUNT(*) n FROM riwayat_pembayaran')->fetch_assoc()['n'] : 0;
$code='HG-' . strtoupper(bin2hex(random_bytes(6)));
db()->begin_transaction();
try {
  oq("INSERT INTO transaksi(kode_transaksi,id_pengguna,total_harga,total_tagihan,status,metode_bayar,status_bayar) VALUES (?,?,1000,1000,'pending','transfer','belum_bayar')",[$code,$user]);
  if ($legacy) {
    foreach ([['belum_bayar','Pesanan dibuat.',null],['ditolak','Bukti kurang jelas.',$user],['menunggu_verifikasi','Bukti diunggah.',$user],['menunggu_verifikasi','Bukti diunggah.',$user],['dikembalikan',null,null]] as $i=>[$status,$note,$actor]) {
      oq('INSERT INTO riwayat_pembayaran(kode_transaksi,status,catatan,petugas,tanggal) VALUES (?,?,?,?,?)',[$code,$status,$note,$actor,'2026-01-02 10:00:00']);
      if ($i===1) {
        $id=db()->insert_id;
        oq("INSERT INTO riwayat_pesanan(kode_transaksi,peristiwa,catatan,petugas,kunci_peristiwa,tanggal) VALUES (?,'pembayaran_ditolak',?,?,?,'2026-01-02 10:00:00')",[$code,$note,$actor,'pembayaran:'.$id]);
      }
    }
    historyCheck(importLegacyPaymentHistory(db())===4,'Migration reuses existing mirror and imports only missing payment rows');
    $rows=oq('SELECT * FROM riwayat_pesanan WHERE kode_transaksi=? ORDER BY id',[$code])->fetch_all(MYSQLI_ASSOC);
    historyCheck(count($rows)===5,'Distinct repeated proof submissions remain separate audit events');
    historyCheck(importLegacyPaymentHistory(db())===0,'Rerunning migration cannot duplicate historical rows');
    historyCheck(verifyLegacyPaymentHistory(db())===$legacyBefore+5,'Every original time, actor, note and payment status is preserved');
    oq("UPDATE riwayat_pesanan SET tanggal='2000-01-01 00:00:00' WHERE id=?",[$rows[0]['id']]);
    try { verifyLegacyPaymentHistory(db()); throw new RuntimeException('Mismatch accepted'); }
    catch (RuntimeException $e) { historyCheck(str_contains($e->getMessage(),'belum tersalin lengkap'),'Retirement validation rejects incomplete history'); }
    importLegacyPaymentHistory(db());
    oq('DELETE FROM riwayat_pesanan WHERE kode_transaksi=?',[$code]);
    oq('DELETE FROM riwayat_pembayaran WHERE kode_transaksi=?',[$code]);
  }
  recordOrderEvent($code,'checkout','Pesanan dibuat.',$user,null,'belum_bayar');
  recordPayment($code,'menunggu_verifikasi','Bukti pertama.',$user,'history-test:first:'.$code);
  recordPayment($code,'ditolak','Nominal belum jelas.',$user,'history-test:reject:'.$code);
  recordPayment($code,'menunggu_verifikasi','Bukti kedua.',$user,'history-test:second:'.$code);
  $rows=oq('SELECT * FROM riwayat_pesanan WHERE kode_transaksi=? ORDER BY id',[$code])->fetch_all(MYSQLI_ASSOC);
  historyCheck(count($rows)===4 && array_column($rows,'kategori')===['pesanan','pembayaran','pembayaran','pembayaran'],'Checkout and payment events use one table with explicit categories');
  historyCheck(array_column($rows,'status_bayar')===['belum_bayar','menunggu_verifikasi','ditolak','menunggu_verifikasi'],'Payment state remains available in each audit entry');
  try { recordPayment($code,'menunggu_verifikasi','Duplicate.',$user,'history-test:second:'.$code); throw new RuntimeException('Duplicate accepted'); }
  catch (mysqli_sql_exception $e) { historyCheck($e->getCode()===1062,'Event key rejects duplicate writes'); }
  cancelOrder($code,true); cancelOrder($code,true);
  historyCheck((int)oq('SELECT COUNT(*) n FROM riwayat_pesanan WHERE kode_transaksi=?',[$code])->fetch_assoc()['n']===5,'Unpaid cancellation records one order event without a false payment change');
  if ($legacy) historyCheck((int)oq('SELECT COUNT(*) n FROM riwayat_pembayaran')->fetch_assoc()['n']===$legacyBefore,'New runtime never writes to the legacy payment table');
} finally { db()->rollback(); }
historyCheck((int)oq('SELECT COUNT(*) n FROM riwayat_pesanan')->fetch_assoc()['n']===$before && !orderRow($code),'Fixtures rolled back and existing history preserved');
