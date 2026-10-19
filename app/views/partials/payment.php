<section class="sf-payment-panel card space-y-4 p-4 sm:p-5" x-data="paymentPanel()" @order-updated.window="if ($event.detail === code) load()">
  <div class="flex items-center justify-between gap-3"><h2 class="text-lg font-semibold" x-text="counterFlow ? 'Pembayaran Kasir' : 'Informasi Pembayaran'"></h2><button class="user-action-outline" @click="load()" :disabled="busy || checking">Refresh</button></div>
  <p x-show="loading" class="text-sm text-slate-500">Memuat pembayaran...</p>
  <p x-show="error" x-text="error" class="rounded-xl bg-red-50 p-3 text-sm text-red-700" role="alert"></p>
  <template x-if="trx"><div class="space-y-4">
    <div class="grid gap-3 sm:grid-cols-2">
      <div><p class="label">Kode pesanan</p><p class="font-semibold" x-text="trx.kode_transaksi"></p></div>
      <div><p class="label">Status pembayaran</p><p class="font-semibold" :class="trx.status_bayar === 'lunas' ? 'text-emerald-700' : 'text-amber-700'" x-text="trx.metode_bayar === 'tunai' && trx.status_bayar === 'belum_bayar' ? 'Bayar saat ambil' : paymentLabel(trx.status_bayar)"></p></div>
      <div><p class="label">Subtotal barang</p><p x-text="utils.formatRupiah(trx.total_harga)"></p></div>
      <div><p class="label">Ongkir</p><p x-text="utils.formatRupiah(trx.ongkir)"></p></div>
    </div>
    <div class="rounded-xl bg-emerald-50 p-4"><p class="text-sm text-emerald-800">Total pembayaran</p><p class="text-2xl font-bold text-emerald-800" x-text="utils.formatRupiah(trx.total_tagihan)"></p></div>
    <p x-show="trx.status === 'pending' && trx.batas_bayar && ['belum_bayar','ditolak'].includes(trx.status_bayar)" class="text-sm text-amber-700">Bayar sebelum <span x-text="utils.formatDateTime(trx.batas_bayar)"></span> WITA.</p>
    <p x-show="trx.catatan_bayar" class="rounded-xl bg-amber-50 p-3 text-sm" x-text="trx.catatan_bayar"></p>
    <template x-if="['transfer','qris'].includes(trx.metode_bayar)"><div class="space-y-3"><h3 class="font-semibold" x-text="trx.metode_bayar === 'qris' ? 'Pembayaran QRIS' : 'Transfer manual'"></h3>
      <div x-show="trx.metode_bayar === 'qris' && trx.status_bayar !== 'lunas'" class="rounded-xl border border-slate-200 p-4 text-center">
        <p class="font-semibold">Pindai QRIS GGmart</p>
        <img :src="BASE_URL + '/assets/qris_ggmart.jpeg'" alt="QRIS GGmart" class="mx-auto mt-3 w-64 max-w-full rounded-xl border bg-white p-3">
        <p class="mt-2 text-sm text-slate-500">Bayar sesuai total pesanan. Setelah membayar, unggah bukti agar admin dapat memverifikasi pembayaran.</p>
      </div>
      <div x-show="canUpload" class="rounded-xl border border-slate-200 p-4">
        <template x-if="trx.metode_bayar === 'transfer'"><div><p class="font-semibold">Transfer ke rekening toko</p><p class="mt-2" x-text="trx.rekening.bank_nama"></p><p class="text-xl font-semibold" x-text="trx.rekening.bank_rekening"></p><p x-text="'a.n. ' + trx.rekening.bank_pemilik"></p></div></template>
        <p class="mt-2 text-sm text-slate-500" x-show="trx.metode_bayar === 'transfer'">Transfer sesuai total, lalu unggah bukti. Admin akan memeriksa uang yang masuk.</p>
        <label class="label mt-4" for="payment-proof">Bukti pembayaran (JPG, PNG, WebP; maksimal 5 MB)</label>
        <input id="payment-proof" type="file" accept="image/jpeg,image/png,image/webp" @change="proof = $event.target.files[0]" class="input mt-2 w-full">
        <button class="user-action-primary mt-3" @click="upload()" :disabled="!proof || busy">Kirim bukti pembayaran</button>
      </div>
      <p x-show="trx.status_bayar === 'menunggu_verifikasi'" class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800">Bukti sudah diterima dan menunggu verifikasi admin.</p>
      <a x-show="trx.bukti_url && role !== 'pimpinan'" :href="trx.bukti_url" target="_blank" rel="noopener" class="user-action-outline">Lihat bukti pembayaran</a>
      <template x-if="isAdmin && trx.status === 'pending' && trx.status_bayar === 'menunggu_verifikasi'"><div class="rounded-xl border border-amber-200 p-4">
        <p class="font-semibold">Verifikasi pembayaran</p><p class="mt-1 text-sm text-slate-500">Periksa bukti dan pastikan pembayaran benar-benar diterima.</p>
        <textarea class="input mt-3 w-full" x-model="note" maxlength="500" placeholder="Alasan penolakan atau catatan pemeriksaan"></textarea>
        <div class="mt-3 flex flex-wrap gap-2"><button class="admin-action-primary" @click="verify('terima')" :disabled="busy">Terima pembayaran</button><button class="admin-action-danger" @click="verify('tolak')" :disabled="busy">Tolak bukti</button></div>
      </div></template>
    </div></template>
    <template x-if="trx.metode_bayar === 'tunai'"><div class="rounded-xl border border-slate-200 p-4 text-sm"><h3 class="font-semibold">Tunai</h3><p class="mt-1" x-text="trx.status_bayar === 'lunas' ? 'Pembayaran tunai sudah diterima.' : 'Pesanan disiapkan terlebih dahulu. Bayar tunai di toko saat mengambil pesanan.'"></p></div></template>
    <template x-if="isAdmin && trx.status_bayar === 'perlu_refund'"><div class="rounded-xl bg-amber-50 p-4"><p class="font-semibold">Pengembalian dana</p><p class="text-sm">Kembalikan dana melalui kanal pembayaran asal, kemudian catat hasilnya.</p><textarea class="input mt-3 w-full" x-model="note" maxlength="500" placeholder="Tanggal dan referensi pengembalian"></textarea><button class="admin-action-primary mt-3" @click="refund()" :disabled="busy">Catat dana sudah dikembalikan</button></div></template>
    <div x-show="trx.sumber_transaksi !== 'kasir'" class="rounded-xl border border-slate-200 p-4 text-sm">
      <h3 class="font-semibold">Informasi Pengambilan / Pengiriman</h3><p class="mt-2 font-semibold" x-text="trx.metode_pengiriman === 'kirim' ? 'Pengiriman lokal — ' + trx.kelurahan : 'Ambil di tempat'"></p>
      <p class="mt-1" x-text="trx.nama_penerima || trx.user"></p><p x-text="trx.no_hp_penerima || ''"></p>
      <p class="mt-1" x-show="trx.metode_pengiriman === 'kirim'" x-text="trx.kecamatan"></p>
      <p class="mt-1 whitespace-pre-line" x-text="trx.metode_pengiriman === 'kirim' ? trx.alamat_pengiriman : 'GGmart, Jl. S. K. Lerik, Kota Baru, Kelapa Lima, Kota Kupang.'"></p>
      <p class="mt-2 font-semibold text-emerald-700" x-text="utils.orderStatus(trx)"></p>
    </div>
    <template x-if="isAdmin && trx.sumber_transaksi === 'online'"><div><a x-show="waLink()" :href="waLink()" target="_blank" rel="noopener noreferrer" class="admin-action-primary">Hubungi Pelanggan</a><p x-show="!waLink()" class="text-sm text-slate-500">Nomor WhatsApp pelanggan belum tersedia atau tidak valid.</p></div></template>
    <div x-show="trx.riwayat_pesanan?.length" class="border-t pt-4"><h3 class="font-semibold">Riwayat pesanan</h3><ol class="mt-4 space-y-4 border-l-2 border-emerald-100 pl-4"><template x-for="item in trx.riwayat_pesanan" :key="item.id"><li class="relative text-sm"><span class="absolute -left-5 top-1 h-2 w-2 rounded-full bg-emerald-600"></span><p class="font-semibold" x-text="eventLabel(item.peristiwa)"></p><p class="text-xs text-slate-500" x-text="utils.formatDateTime(item.tanggal) + ' WITA' + (item.petugas ? ' · ' + item.petugas : '')"></p><p class="mt-1 text-slate-600" x-text="item.catatan"></p></li></template></ol></div>
    <a x-show="role === 'pelanggan' && trx.status === 'selesai' && trx.status_bayar === 'lunas'" :href="BASE_URL + '/struk/' + encodeURIComponent(code)" class="user-action-outline">Cetak Struk</a>
  </div></template>
</section>
