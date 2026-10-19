<div class="admin-page space-y-5 p-4 sm:p-5 lg:p-6" x-data="pesananPage()">
  <header class="admin-page-header"><div><p class="admin-eyebrow">Pelanggan</p><h1 class="admin-page-title">Pesanan</h1><p class="admin-page-subtitle">Pantau pembayaran, penyiapan, dan pengiriman pesanan pelanggan.</p></div><button type="button" class="admin-action-secondary" @click="load()" :disabled="loading">Refresh</button></header>
  <div class="flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2" role="tablist" aria-label="Tahap pesanan">
    <template x-for="tab in tabs" :key="tab.value">
      <button type="button" role="tab" :aria-selected="stage === tab.value" @click="changeStage(tab.value)" :class="stage === tab.value ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100'" class="flex shrink-0 items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold">
        <span x-text="tab.label"></span>
        <span class="inline-flex min-w-6 items-center justify-center rounded-full px-2 py-0.5 text-xs tabular-nums" :class="stage === tab.value ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'" x-text="stageCounts === null ? '—' : (stageCounts[tab.value] || 0)"></span>
      </button>
    </template>
  </div>
  <section class="admin-card grid gap-4 p-4 sm:grid-cols-2">
    <label class="label">Cari pesanan<input type="search" class="input mt-2 w-full" x-model="search" @input.debounce.500ms="pagination.page=1; load()" placeholder="Kode pesanan atau produk..."></label>
    <label class="label">Metode pembayaran<select x-gg-select class="input mt-2 w-full" x-model="metode" @change="pagination.page=1; load()"><option value="">Semua metode</option><option value="qris">QRIS</option><option value="transfer">Transfer manual</option><option value="tunai">Tunai saat ambil</option></select></label>
  </section>
  <p x-show="error" x-text="error" class="rounded-xl bg-red-50 p-4 text-sm text-red-700" role="alert"></p>
  <p x-show="loading" class="text-sm text-slate-500">Memuat pesanan...</p>
  <section class="admin-card hidden overflow-hidden md:block">
    <div class="border-b border-slate-100 p-4"><h2 class="font-semibold">Daftar Pesanan</h2><p class="text-xs text-slate-500"><span x-text="pagination.total"></span> pesanan ditemukan</p></div>
    <div class="overflow-x-auto"><table class="table"><thead><tr><th>Pesanan</th><th>Pelanggan</th><th>Pembayaran</th><th>Pengambilan</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
      <tr x-show="!loading && !data.length"><td colspan="7" class="py-12 text-center text-slate-500">Belum ada pesanan pada tahap ini.</td></tr>
      <template x-for="item in data" :key="item.kode_transaksi"><tr>
        <td><a class="font-semibold text-emerald-700" :href="BASE_URL + '/admin/transaksi/' + encodeURIComponent(item.kode_transaksi)" x-text="item.kode_transaksi"></a><span class="mt-1 block text-xs text-slate-500" x-text="utils.formatDateTime(item.tanggal_transaksi)"></span></td>
        <td x-text="item.nama_penerima || item.user"></td>
        <td><span class="font-semibold uppercase" x-text="item.metode_bayar"></span><span class="mt-1 block text-xs text-slate-500" x-text="paymentLabel(item.status_bayar)"></span></td>
        <td><span x-text="item.metode_pengiriman === 'kirim' ? 'Pengiriman lokal' : 'Ambil di tempat'"></span><span class="mt-1 block text-xs text-slate-500" x-text="item.kelurahan || ''"></span></td>
        <td class="whitespace-nowrap font-semibold" x-text="utils.formatRupiah(item.total_tagihan)"></td>
        <td><span :class="item.status === 'selesai' ? 'status-success' : 'status-warning'" x-text="utils.orderStatus(item)"></span></td>
        <td><div class="flex flex-wrap gap-2"><?php include ROOT_PATH . '/app/views/partials/order-actions.php'; ?></div></td>
      </tr></template>
    </tbody></table></div>
  </section>
  <section class="space-y-3 md:hidden">
    <div x-show="!loading && !data.length" class="admin-card p-8 text-center text-sm text-slate-500">Belum ada pesanan pada tahap ini.</div>
    <template x-for="item in data" :key="'mobile-' + item.kode_transaksi"><article class="admin-card space-y-4 p-4">
      <div class="flex items-start justify-between gap-3"><div><p class="font-semibold" x-text="item.kode_transaksi"></p><p class="mt-1 text-sm text-slate-500" x-text="item.nama_penerima || item.user"></p></div><span class="status-neutral" x-text="utils.orderStatus(item)"></span></div>
      <div class="grid grid-cols-2 gap-3 text-sm"><div><p class="text-slate-500">Pembayaran</p><p class="font-semibold uppercase" x-text="item.metode_bayar"></p><p class="text-xs text-slate-500" x-text="paymentLabel(item.status_bayar)"></p></div><div><p class="text-slate-500">Total</p><p class="font-semibold" x-text="utils.formatRupiah(item.total_tagihan)"></p></div><div class="col-span-2"><p x-text="item.metode_pengiriman === 'kirim' ? 'Pengiriman lokal — ' + item.kelurahan : 'Ambil di tempat'"></p><p class="text-xs text-slate-500" x-text="utils.formatDateTime(item.tanggal_transaksi)"></p></div></div>
      <div class="flex flex-wrap gap-2"><?php include ROOT_PATH . '/app/views/partials/order-actions.php'; ?></div>
    </article></template>
  </section>
  <div class="admin-card flex items-center justify-between gap-3 p-4"><span class="text-sm text-slate-500">Halaman <span x-text="pagination.page"></span> / <span x-text="pagination.total_pages"></span></span><div class="flex gap-2"><button type="button" class="admin-action-secondary" @click="goPage(pagination.page - 1)" :disabled="loading || pagination.page <= 1">Sebelumnya</button><button type="button" class="admin-action-secondary" @click="goPage(pagination.page + 1)" :disabled="loading || pagination.page >= pagination.total_pages">Berikutnya</button></div></div>
</div>
<script>
function pesananPage() {
  return {
    data: [], search: utils.getQuery('search') || '', metode: utils.getQuery('metode') || '', stage: 'menunggu_pembayaran', loading: false, error: '', processing: false, requestId: 0,
    tabs: [{ value: 'menunggu_pembayaran', label: 'Menunggu Pembayaran' }, { value: 'menunggu_verifikasi', label: 'Menunggu Verifikasi' }, { value: 'diproses', label: 'Diproses' }, { value: 'dikirim', label: 'Dikirim' }],
    stageCounts: null,
    pagination: { page: 1, limit: 10, total: 0, total_pages: 1 },
    async init() { const value=utils.getQuery('tahap'); if (value === 'selesai') { window.location.replace(BASE_URL + '/admin/transaksi?status=selesai'); return; } if (this.tabs.some(t => t.value === value)) this.stage=value; await this.load(); },
    paymentLabel(value) { return ({ belum_bayar: 'Belum dibayar', menunggu_verifikasi: 'Menunggu verifikasi', lunas: 'Lunas', ditolak: 'Bukti ditolak', kedaluwarsa: 'Kedaluwarsa', perlu_refund: 'Menunggu pengembalian dana', dikembalikan: 'Dana dikembalikan' })[value] || value; },
    async changeStage(value) { if (!this.tabs.some(t => t.value === value)) return; this.stage=value; this.pagination.page=1; await this.load(); },
    async goPage(page) { if (page<1 || page>this.pagination.total_pages) return; this.pagination.page=page; await this.load(); },
    async load() {
      const request=++this.requestId; this.loading=true; this.error='';
      try {
        const params=new URLSearchParams({ tahap:this.stage, sumber:'online', search:this.search, metode:this.metode, page:this.pagination.page, limit:this.pagination.limit });
        const res=await API.get('/transaksi/list?' + params, false);
        if (request !== this.requestId) return;
        this.data=res.data; this.stageCounts=res.stageCounts; this.pagination.total=res.pagination.total; this.pagination.total_pages=res.pagination.total_pages || 1;
        utils.setQuery('tahap',this.stage); utils.setQuery('search',this.search); utils.setQuery('metode',this.metode);
      } catch (e) { if (request === this.requestId) this.error=e.message; }
      finally { if (request === this.requestId) this.loading=false; }
    },
    async act(item) {
      const action=utils.orderAction(item); if (!action || this.processing) return;
      if (!await Alpine.store('ui').confirm(action.question)) return;
      this.processing=true;
      try {
        await API.post(utils.orderEndpoint(action),{ kode_transaksi:item.kode_transaksi });
        if (['konfirmasi','tunai'].includes(action.endpoint)) {
          this.pagination.page=1;
          await this.load();
          Alpine.store('ui').toast('Pesanan selesai. Tersimpan di Riwayat Transaksi.');
        } else await this.changeStage(action.endpoint === 'kirim' ? 'dikirim' : 'diproses');
      }
      catch (e) { this.error=e.message; } finally { this.processing=false; }
    },
    async batal(item) {
      if (this.processing || !await Alpine.store('ui').confirm('Batalkan pesanan ini? Pembayaran yang sudah diterima perlu dikembalikan.')) return;
      this.processing=true;
      try { await API.post('/transaksi/batal',{ kode_transaksi:item.kode_transaksi }); await this.load(); }
      catch (e) { this.error=e.message; } finally { this.processing=false; }
    }
  };
}
</script>
