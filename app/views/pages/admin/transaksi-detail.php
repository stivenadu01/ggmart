<div class="admin-page space-y-6 p-4 sm:p-6" x-data="transaksiDetailPage()" @payment-updated="trx = $event.detail">

  <!-- HEADER -->
  <header class="admin-page-header flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div class="min-w-0">
      <h1 class="admin-page-title">Detail Transaksi</h1>
      <p class="admin-page-subtitle">Periksa produk, pembayaran, HPP, laba, dan status transaksi secara lengkap.</p>
    </div>
    <div class="flex flex-wrap gap-2"><a x-show="trx?.status === 'selesai' && trx?.status_bayar === 'lunas'" :href="BASE_URL + '/struk/' + encodeURIComponent(trx?.kode_transaksi || '')" class="admin-action-primary">Cetak Struk</a><a href="<?= BASE_URL ?>/admin/transaksi" class="admin-action-secondary"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Riwayat Transaksi</a></div>
  </header>

  <!-- LOADING -->
  <template x-if="loading">
    <div class="grid gap-4 lg:grid-cols-3 animate-pulse">
      <section class="card space-y-4 p-5 lg:col-span-2">
        <div class="h-5 w-40 rounded bg-slate-200"></div>
        <div class="h-20 rounded-xl bg-slate-100"></div>
        <div class="h-20 rounded-xl bg-slate-100"></div>
      </section>
      <section class="card space-y-4 p-5">
        <div class="h-16 rounded bg-slate-100"></div>
        <div class="h-16 rounded bg-slate-100"></div>
        <div class="h-12 rounded bg-slate-100"></div>
      </section>
    </div>
  </template>

  <!-- ERROR / NOT FOUND -->
  <template x-if="!loading && error">
    <section class="rounded-2xl border border-red-200 bg-red-50 p-5">
      <p class="font-semibold text-red-800">Detail transaksi tidak dapat dimuat</p>
      <p class="mt-1 text-sm text-red-700" x-text="error"></p>
      <button type="button" @click="load()" class="admin-action-danger mt-4">Coba lagi</button>
    </section>
  </template>

  <template x-if="!loading && trx && !error">
    <div class="grid gap-4 lg:grid-cols-3">
      <!-- MAIN -->
      <div class="space-y-4 lg:col-span-2">
        <!-- TRANSACTION INFO -->
        <section class="card p-5">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kode transaksi</p>
              <p class="mt-1 break-all text-xl font-bold text-slate-900" x-text="trx.kode_transaksi"></p>
              <p class="mt-1 text-sm text-slate-500" x-text="utils.formatDateTime(trx.tanggal_transaksi)"></p>
            </div>
            <span :class="statusClass(trx.status)" x-text="utils.orderStatus(trx)"></span>
          </div>

          <dl class="mt-5 grid grid-cols-1 gap-4 border-t border-slate-200 pt-5" :class="trx.user_role === 'pelanggan' ? 'sm:grid-cols-3' : 'sm:grid-cols-2'">
            <template x-if="trx.user_role === 'pelanggan'">
              <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Pelanggan</dt>
                <dd class="mt-1 font-medium text-slate-800" x-text="trx.user || 'Tidak diketahui'"></dd>
              </div>
            </template>
            <template x-if="trx.user_role !== 'pelanggan'">
              <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Sumber transaksi</dt>
                <dd class="mt-1 font-medium text-slate-800">Kasir</dd>
                <p class="mt-1 text-xs text-slate-500">Transaksi dibuat langsung melalui menu Kasir.</p>
              </div>
            </template>
            <div>
              <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Metode pembayaran</dt>
              <dd class="mt-1 font-medium uppercase text-slate-800" x-text="trx.metode_bayar"></dd>
            </div>
            <div>
              <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</dt>
              <dd class="mt-1" :class="statusClass(trx.status)" x-text="utils.orderStatus(trx)"></dd>
            </div>
          </dl>
        </section>

        <?php include ROOT_PATH . '/app/views/partials/payment.php'; ?>
        <!-- PRODUCTS -->
        <section class="card overflow-hidden">
          <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Daftar Produk</h2>
            <p class="mt-1 text-sm text-slate-500">Rincian barang yang tercatat pada transaksi ini.</p>
          </div>

          <div class="divide-y divide-slate-200">
            <template x-if="!trx.detail || trx.detail.length === 0">
              <div class="p-6 text-center text-sm text-slate-500">Tidak ada detail produk pada transaksi ini.</div>
            </template>

            <template x-for="item in trx.detail" :key="item.kode_produk">
              <article class="p-4 sm:p-5">
                <div class="flex items-start gap-3 sm:gap-4">
                  <img
                    :src="item.gambar ? BASE_URL + '/uploads/' + item.gambar : BASE_URL + '/assets/no-image.png'"
                    :alt="item.nama_produk || 'Produk'"
                    class="h-14 w-14 shrink-0 rounded-xl border border-slate-200 object-cover sm:h-16 sm:w-16">

                  <div class="min-w-0 flex-1">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                      <div>
                        <h3 class="font-semibold text-slate-900" x-text="item.nama_produk"></h3>
                        <p class="mt-0.5 text-xs text-slate-500" x-text="item.kode_produk"></p>
                      </div>
                      <p class="font-bold text-slate-900" x-text="utils.formatRupiah(item.harga_satuan * item.jumlah)"></p>
                    </div>

                    <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                      <div>
                        <dt class="text-slate-500">Harga jual</dt>
                        <dd class="mt-0.5 font-medium text-slate-800" x-text="utils.formatRupiah(item.harga_satuan)"></dd>
                      </div>
                      <div>
                        <dt class="text-slate-500">Jumlah</dt>
                        <dd class="mt-0.5 font-medium text-slate-800" x-text="item.jumlah"></dd>
                      </div>
                      <div>
                        <dt class="text-slate-500">HPP rata-rata</dt>
                        <dd class="mt-0.5 font-medium text-slate-800" x-text="utils.formatRupiah(item.harga_pokok)"></dd>
                      </div>
                    </dl>
                  </div>
                </div>
              </article>
            </template>
          </div>
        </section>
      </div>

      <!-- SIDEBAR -->
      <aside class="space-y-4">
        <!-- TOTAL -->
        <section class="card">
          <p class="text-sm font-medium text-slate-500">Total pembayaran</p>
          <p class="mt-2 text-2xl font-bold text-primary" x-text="utils.formatRupiah(trx.total_tagihan)"></p>
          <p class="form-help">Subtotal barang dan ongkir.</p>
        </section>

        <!-- PROFIT -->
        <section class="card p-5">
          <h2 class="font-semibold text-slate-900">Ringkasan keuangan</h2>
          <dl class="mt-4 space-y-3 text-sm">
            <div class="flex items-center justify-between gap-4">
              <dt class="text-slate-500">Total penjualan</dt>
              <dd class="font-semibold text-slate-900" x-text="utils.formatRupiah(trx.total_harga)"></dd>
            </div>
            <template x-if="trx.status === 'selesai'">
              <div class="flex items-center justify-between gap-4">
                <dt class="text-slate-500">Total HPP</dt>
                <dd class="font-semibold text-slate-900" x-text="utils.formatRupiah(trx.total_pokok)"></dd>
              </div>
            </template>
            <template x-if="trx.status === 'selesai'">
              <div class="flex items-center justify-between gap-4 border-t border-slate-200 pt-3">
                <dt class="font-semibold text-slate-700">Laba barang</dt>
                <dd class="font-bold text-emerald-600" x-text="utils.formatRupiah(trx.total_harga - trx.total_pokok)"></dd>
              </div>
            </template>
            <template x-if="trx.status !== 'selesai'">
              <p class="rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-500">HPP dan laba ditampilkan setelah transaksi berstatus selesai.</p>
            </template>
          </dl>
        </section>

        <!-- ACTIONS -->
        <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin'], true)): ?>
          <template x-if="trx.status !== 'dibatalkan'">
            <section class="card p-5">
              <h2 class="font-semibold text-slate-900">Aksi transaksi</h2>
              <p class="form-help">Pilih tindakan sesuai status transaksi saat ini.</p>

              <div class="mt-4 space-y-2">
                <template x-if="trx.sumber_transaksi === 'online' && utils.orderAction(trx)"><button type="button" class="admin-action-primary w-full" @click="act()" :disabled="processing" x-text="processing ? 'Memproses...' : utils.orderAction(trx).label"></button></template>

                <button type="button" @click="batal()" :disabled="processing" class="admin-action-danger w-full" :class="{'cursor-not-allowed opacity-60': processing}">
                  Batalkan Transaksi
                </button>
              </div>
            </section>
          </template>

          <template x-if="trx.status === 'dibatalkan'">
            <section class="rounded-2xl border border-red-200 bg-red-50 p-5">
              <p class="font-semibold text-red-800">Transaksi dibatalkan</p>
              <p class="mt-1 text-sm leading-5 text-red-700">Tidak ada aksi lanjutan yang tersedia untuk transaksi ini.</p>
            </section>
          </template>
        <?php endif; ?>
      </aside>
    </div>
  </template>

</div>

<script>
  function transaksiDetailPage() {
    return {
      trx: null,
      loading: true,
      error: '',
      processing: false,
      kode: <?= json_encode(params('kode')) ?>,

      statusClass(status) {
        if (status === 'selesai') return 'status-success'
        if (status === 'dibatalkan') return 'status-danger'
        if (status === 'diproses') return 'status-warning'
        return 'status-neutral'
      },

      statusLabel(status) {
        const labels = {
          pending: 'Menunggu',
          diproses: 'Diproses',
          selesai: 'Selesai',
          dibatalkan: 'Dibatalkan'
        }
        return labels[status] || status
      },

      async init() {
        await this.load()
      },

      async load() {
        this.loading = true
        this.error = ''
        try {
          if (!this.kode) throw new Error('Kode transaksi tidak valid')

          const res = await API.get('/transaksi/detail?k=' + encodeURIComponent(this.kode))
          if (!res.success) throw new Error(res.message || 'Gagal memuat detail transaksi')

          this.trx = res.data
        } catch (err) {
          console.error(err)
          this.trx = null
          this.error = err?.message || 'Terjadi kesalahan saat memuat detail transaksi.'
        } finally {
          this.loading = false
        }
      },

      async proses() {
        const ok = await Alpine.store('ui').confirm('Proses pesanan ini?')
        if (!ok) return
        this.processing = true
        try {
          const res = await API.post('/transaksi/proses', {
            kode_transaksi: this.trx.kode_transaksi
          })
          if (!res.success) throw new Error(res.message || 'Gagal memproses pesanan')
          Alpine.store('ui').toast('Pesanan diproses')
          await this.load()
        } catch (err) {
          console.error(err)
          Alpine.store('ui').toast(err?.message || 'Gagal memproses pesanan')
        } finally {
          this.processing = false
        }
      },

      async act() {
        const action=utils.orderAction(this.trx);
        if (!action || this.processing || !await Alpine.store('ui').confirm(action.question)) return;
        this.processing=true;
        try { await API.post(utils.orderEndpoint(action),{ kode_transaksi:this.trx.kode_transaksi }); window.location.replace(BASE_URL + (this.trx.sumber_transaksi === 'kasir' ? '/admin/kasir' : '/admin/pesanan?tahap=' + (action.endpoint === 'kirim' ? 'dikirim' : ['konfirmasi','tunai'].includes(action.endpoint) ? 'selesai' : 'diproses'))); }
        catch (e) { this.error=e.message; }
        finally { this.processing=false; }
      },

      async konfirmasi() {
        const ok = await Alpine.store('ui').confirm('Pastikan barang sudah diterima pelanggan. Selesaikan pesanan ini?')
        if (!ok) return
        this.processing = true
        try {
          const res = await API.post('/transaksi/konfirmasi', {
            kode_transaksi: this.trx.kode_transaksi
          })
          if (!res.success) throw new Error(res.message || 'Gagal mengonfirmasi pesanan')
          Alpine.store('ui').toast('Pesanan dikonfirmasi')
          await this.load()
        } catch (err) {
          console.error(err)
          Alpine.store('ui').toast(err?.message || 'Gagal mengonfirmasi pesanan')
        } finally {
          this.processing = false
        }
      },

      async batal() {
        const jenis = this.trx.status === 'selesai' ? 'yang telah selesai' : ''
        const ok = await Alpine.store('ui').confirm(`Yakin ingin membatalkan transaksi ${jenis} ini?`)
        if (!ok) return
        this.processing = true
        try {
          const res = await API.post('/transaksi/batal', {
            kode_transaksi: this.trx.kode_transaksi
          })
          if (!res.success) throw new Error(res.message || 'Gagal membatalkan transaksi')
          Alpine.store('ui').toast('Transaksi dibatalkan')
          window.location.replace(BASE_URL + (this.trx.sumber_transaksi === 'kasir' ? '/admin/kasir' : '/admin/transaksi?status=dibatalkan'))
        } catch (err) {
          console.error(err)
          Alpine.store('ui').toast(err?.message || 'Gagal membatalkan transaksi')
        } finally {
          this.processing = false
        }
      }
    }
  }
</script>
