<?php
$esc=fn($value)=>htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');
$rupiah=fn($value)=>'Rp ' . number_format((float)$value,0,',','.');
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Struk <?= $esc($trx['kode_transaksi']) ?> | GGmart</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f1f5f9;color:#0f172a;font:13px/1.5 Arial,sans-serif}.receipt{width:80mm;max-width:100%;margin:24px auto;background:white;padding:6mm;box-shadow:0 4px 20px #0f172a12}h1{font-size:22px;margin:0;text-align:center}p{margin:3px 0}.center{text-align:center}.small{font-size:11px}.line{border-top:1px dashed #94a3b8;margin:12px 0}.row{display:flex;justify-content:space-between;gap:12px}.item{margin:10px 0;break-inside:avoid}.total{font-size:16px;font-weight:bold}.controls{display:flex;justify-content:center;gap:10px;margin:20px auto;flex-wrap:wrap}button,a{padding:10px 16px;border:1px solid #d1d5db;border-radius:10px;background:white;color:#334155;text-decoration:none;font:inherit;cursor:pointer}button{background:#059669;color:white;border-color:#059669}.code{overflow-wrap:anywhere}
@media print{body{background:white;font-size:12px}.receipt{width:72mm;margin:0;padding:0;box-shadow:none}.controls{display:none}.line{border-color:#333}@page{size:auto;margin:4mm}}
</style></head><body>
<main class="receipt" aria-label="Struk pembelian">
  <h1>GGmart</h1><p class="center small">Toko Pangan Sinode GMIT</p><p class="center small">Jl. S. K. Lerik, Kota Baru<br>Kelapa Lima, Kota Kupang</p>
  <div class="line"></div>
  <p class="code"><strong><?= $esc($trx['kode_transaksi']) ?></strong></p><p class="small"><?= $esc(date('d/m/Y H:i',strtotime($trx['tanggal_selesai'] ?: $trx['tanggal_transaksi']))) ?> WITA</p>
  <p class="small"><?= $trx['sumber_transaksi']==='kasir' ? 'Kasir: ' : 'Pelanggan: ' ?><?= $esc($trx['sumber_transaksi']==='kasir' ? $trx['user'] : ($trx['nama_penerima'] ?: $trx['user'])) ?></p>
  <div class="line"></div>
  <?php foreach ($items as $item): ?><div class="item"><p><strong><?= $esc($item['nama_produk']) ?></strong></p><div class="row"><span><?= (int)$item['jumlah'] ?> × <?= $rupiah($item['harga_satuan']) ?></span><span><?= $rupiah($item['jumlah']*$item['harga_satuan']) ?></span></div></div><?php endforeach; ?>
  <div class="line"></div>
  <div class="row"><span>Subtotal</span><span><?= $rupiah($trx['total_harga']) ?></span></div>
  <?php if ((float)$trx['ongkir']>0): ?><div class="row"><span>Ongkir</span><span><?= $rupiah($trx['ongkir']) ?></span></div><?php endif; ?>
  <div class="row total"><span>Total</span><span><?= $rupiah($trx['total_tagihan']) ?></span></div>
  <div class="row"><span>Pembayaran</span><span><?= $esc(['tunai'=>'Tunai','transfer'=>'Transfer manual','qris'=>'QRIS'][$trx['metode_bayar']] ?? $trx['metode_bayar']) ?></span></div>
  <p class="center"><strong>LUNAS</strong></p><div class="line"></div><p class="center small">Terima kasih sudah berbelanja di GGmart.</p>
</main>
<div class="controls"><button type="button" onclick="window.print()">Cetak / Simpan PDF</button><a href="<?= $esc($returnUrl) ?>"><?= $_SESSION['user']['role']==='pelanggan' ? 'Pesanan Saya' : 'Lanjutkan' ?></a></div>
<?php if (query('auto')==='1'): ?><script>
// Tutup atau batalkan dialog cetak: operator langsung dapat melayani berikutnya.
window.addEventListener('afterprint',()=>window.location.replace(<?= json_encode($returnUrl,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>),{once:true});
window.addEventListener('load',()=>setTimeout(()=>window.print(),200),{once:true});
</script><?php endif; ?>
</body></html>
