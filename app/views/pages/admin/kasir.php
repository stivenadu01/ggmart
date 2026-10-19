<div x-data="kasirPage()" @show-incoming.window="showIncoming = true; refreshQueues()">
  <section x-show="showIncoming || pending.length" x-cloak class="card mb-5 space-y-4 p-4 sm:p-5" id="kasir-pesanan">
    <div class="flex flex-wrap items-center justify-between gap-3"><h1 class="text-lg font-semibold">Pesanan dan Pembayaran</h1><div class="flex gap-2"><button type="button" class="admin-action-secondary" @click="refreshQueues()">Refresh</button><button type="button" class="admin-action-secondary" @click="showIncoming = !showIncoming" x-text="showIncoming ? 'Tutup pesanan masuk' : 'Lihat pesanan masuk'"></button></div></div>
    <div x-show="pending.length"><h2 class="mb-2 text-sm font-semibold">Pembayaran kasir belum selesai</h2><div class="space-y-2"><template x-for="item in pending" :key="item.kode_transaksi"><div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200 p-3 text-sm"><div><p class="font-semibold" x-text="item.kode_transaksi"></p><p class="text-slate-500" x-text="item.metode_bayar.toUpperCase() + ' · ' + utils.orderStatus(item) + ' · ' + utils.formatRupiah(item.total_tagihan)"></p></div><a class="admin-action-primary" :href="BASE_URL + '/pembayaran/' + encodeURIComponent(item.kode_transaksi) + '?cetak=1'">Lanjutkan & Cetak</a></div></template></div></div>
    <div x-show="showIncoming"><h2 class="mb-2 text-sm font-semibold">Pesanan pelanggan masuk</h2><p x-show="!incoming.length" class="text-sm text-slate-500">Belum ada pesanan aktif.</p><div class="space-y-2"><template x-for="item in incoming" :key="item.kode_transaksi"><article class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-3 text-sm"><div><p class="font-semibold" x-text="item.nama_penerima + ' · ' + item.kode_transaksi"></p><p class="text-slate-500" x-text="utils.orderStatus(item) + ' · ' + (item.metode_pengiriman === 'ambil' ? 'Ambil di tempat' : 'Pengiriman lokal') + ' · ' + utils.formatRupiah(item.total_tagihan)"></p><p x-show="item.metode_bayar === 'tunai' && item.status_bayar !== 'lunas'" class="mt-1 text-amber-700">Bayar tunai saat ambil</p></div><div class="flex flex-wrap gap-2"><a x-show="$store.auth.user?.role === 'admin'" :href="BASE_URL + '/admin/transaksi/' + encodeURIComponent(item.kode_transaksi)" class="admin-action-secondary">Detail Pesanan</a><button x-show="item.metode_bayar === 'tunai' && item.status_bayar === 'belum_bayar' && item.status === 'diproses' && Number(item.siap_diambil)" type="button" class="admin-action-primary" @click="receiveCash(item)" :disabled="saving">Terima Tunai & Cetak</button></div></article></template></div></div>
  </section>
  <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1.55fr)_minmax(360px,0.85fr)]">

    <!-- PRODUK -->
    <section class="card min-w-0 p-4 sm:p-5" aria-labelledby="kasir-produk-title">
      <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 id="kasir-produk-title" class="text-base font-bold text-slate-900">Pilih Produk</h2>
          <p class="mt-0.5 text-xs text-slate-500">Klik produk untuk memasukkannya ke keranjang.</p>
        </div>
        <span class="w-fit rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
          x-text="produk.length + ' produk'">
        </span>
      </div>

      <!-- SEARCH -->
      <div class="relative mb-4">
        <?= masterIconSvg('search', 'pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400') ?>
        <input
          type="search"
          x-model="search"
          @input.debounce.300="loadProduk()"
          @keydown.enter.prevent="tambahDariInput()"
          placeholder="Cari nama atau kode produk..."
          class="input pl-11 pr-20"
          x-ref="searchInput"
          aria-label="Cari produk">
        <span class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-md border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-semibold text-slate-400 sm:inline-block">
          Enter
        </span>
      </div>

      <!-- PRODUCT GRID -->
      <div class="min-w-0 rounded-xl bg-slate-50/70 p-2 sm:p-3">
        <div class="grid max-h-[58vh] grid-cols-2 gap-2.5 overflow-y-auto pr-1 sm:grid-cols-3 sm:gap-3 lg:grid-cols-3 2xl:grid-cols-4">
          <template x-for="p in produk" :key="p.kode_produk">
            <button
              type="button"
              @click="tambah(p)"
              :disabled="Number(p.stok) <= 0"
              class="group min-w-0 rounded-xl border border-slate-200 bg-white p-3 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-55 disabled:hover:translate-y-0 disabled:hover:shadow-sm">
              <div class="flex min-h-[116px] flex-col">
                <div class="mb-2 flex items-start justify-between gap-2">
                  <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <?= masterIconSvg('orders', 'h-4 w-4') ?>
                  </span>
                  <span
                    class="max-w-[70%] truncate rounded-full px-2 py-1 text-[10px] font-semibold"
                    :class="Number(p.stok) <= 0 ? 'bg-red-50 text-red-600' : Number(p.stok) <= 5 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700'"
                    x-text="Number(p.stok) <= 0 ? 'Habis' : 'Stok ' + p.stok">
                  </span>
                </div>

                <div class="min-w-0 flex-1">
                  <div class="truncate text-sm font-semibold text-slate-800 group-hover:text-primary" x-text="p.nama_produk"></div>
                  <div class="mt-1 truncate text-[11px] text-slate-400" x-text="p.nama_kategori || p.kode_produk"></div>
                </div>

                <div class="mt-3 flex items-end justify-between gap-2">
                  <span class="text-sm font-bold text-primary" x-text="utils.formatRupiah(p.harga_jual)"></span>
                  <span class="text-[10px] font-semibold text-slate-400 group-hover:text-primary">Tambah</span>
                </div>
              </div>
            </button>
          </template>

          <template x-if="produk.length === 0">
            <div class="col-span-full flex min-h-48 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-4 text-center">
              <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <?= masterIconSvg('search', 'h-5 w-5') ?>
              </div>
              <p class="text-sm font-semibold text-slate-700">Produk tidak ditemukan</p>
              <p class="mt-1 text-xs text-slate-400">Coba gunakan nama atau kode produk yang berbeda.</p>
            </div>
          </template>
        </div>
      </div>
    </section>

    <!-- CART -->
    <section class="card min-w-0 p-4 sm:p-5 xl:sticky xl:top-5 xl:h-[calc(100dvh-7rem)] xl:max-h-[calc(100dvh-7rem)]" aria-labelledby="kasir-cart-title">
      <div class="flex min-w-0 flex-col xl:h-full">
        <div class="mb-4 flex items-center justify-between gap-3">
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <h2 id="kasir-cart-title" class="text-base font-bold text-slate-900">Keranjang</h2>
              <span class="rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-bold text-primary" x-text="keranjang.length"></span>
            </div>
            <p class="mt-0.5 text-xs text-slate-500">Periksa jumlah dan total sebelum menyimpan.</p>
          </div>
          <button type="button" @click="reset()" :disabled="keranjang.length === 0 || saving" class="admin-action-danger admin-action-sm shrink-0 disabled:cursor-not-allowed disabled:opacity-50">
            Kosongkan
          </button>
        </div>

        <!-- CART LIST -->
        <div class="min-h-28 flex-1 overflow-y-auto rounded-xl bg-slate-50/70 p-2 sm:p-3 xl:min-h-0">
          <div class="space-y-2">
            <template x-for="(item, i) in keranjang" :key="item.kode_produk">
              <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="flex min-w-0 items-start gap-3">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary" x-text="i + 1"></div>
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-800" x-text="item.nama_produk"></p>
                    <p class="mt-0.5 text-xs text-slate-400" x-text="utils.formatRupiah(item.harga_satuan) + ' / item'"></p>
                  </div>
                  <button type="button" @click="hapus(i)" :disabled="saving" class="admin-action-icon-sm text-slate-400 hover:bg-red-50 hover:text-red-600" aria-label="Hapus produk dari keranjang">
                    <?= masterIconSvg('close', 'h-4 w-4') ?>
                  </button>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3">
                  <div class="flex items-center rounded-lg border border-slate-200 bg-slate-50">
                    <button type="button" @click="item.jumlah > 1 && (item.jumlah--, update(i))" :disabled="saving" class="admin-action-icon-sm bg-white hover:bg-slate-50" aria-label="Kurangi jumlah"><?= masterIconSvg('minus', 'h-4 w-4') ?></button>
                    <input type="number" min="1" :max="item.stok" :disabled="saving" class="h-9 w-12 border-x border-slate-200 bg-white p-0 text-center text-sm font-semibold text-slate-800 outline-none" x-model.number="item.jumlah" @input="update(i)" aria-label="Jumlah produk">
                    <button type="button" @click="item.jumlah < item.stok && (item.jumlah++, update(i))" :disabled="saving" class="admin-action-icon-sm bg-white hover:bg-slate-50" aria-label="Tambah jumlah"><?= masterIconSvg('plus', 'h-4 w-4') ?></button>
                  </div>
                  <span class="text-sm font-bold text-slate-900" x-text="utils.formatRupiah(item.subtotal)"></span>
                </div>
              </div>
            </template>

            <template x-if="keranjang.length === 0">
              <div class="flex min-h-48 flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-5 text-center">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                  <?= masterIconSvg('orders', 'h-6 w-6') ?>
                </div>
                <p class="text-sm font-semibold text-slate-700">Keranjang masih kosong</p>
                <p class="mt-1 max-w-xs text-xs leading-5 text-slate-400">Pilih produk di sebelah kiri untuk mulai membuat transaksi.</p>
              </div>
            </template>
          </div>
        </div>

        <!-- CHECKOUT -->
        <div class="mt-4 border-t border-slate-200 pt-4">
          <div class="mb-3 rounded-xl bg-slate-900 p-4 text-white">
            <div class="flex items-center justify-between gap-3 text-sm text-slate-300">
              <span>Total item</span>
              <span class="font-semibold text-white" x-text="keranjang.reduce((s, i) => s + Number(i.jumlah || 0), 0)"></span>
            </div>
            <div class="mt-2 flex items-end justify-between gap-3">
              <span class="text-sm font-medium text-slate-300">Total pembayaran</span>
              <span class="text-xl font-bold tracking-tight sm:text-2xl" x-text="utils.formatRupiah(total)"></span>
            </div>
          </div>

          <!-- PAYMENT METHOD -->
          <div class="mb-3">
            <label class="label">Metode Pembayaran</label>
            <div class="grid grid-cols-3 gap-2">
              <button type="button" @click="metode='tunai'" :disabled="saving" :class="metode === 'tunai' ? 'border-primary bg-primary/10 text-primary ring-2 ring-primary/10' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'" class="admin-action min-h-11 w-full rounded-xl border">
                <?= masterIconSvg('money', 'h-4 w-4') ?>
                Tunai
              </button>
              <button type="button" @click="metode='qris'" :disabled="!methods.qris || saving" :class="metode === 'qris' ? 'border-primary bg-primary/10 text-primary ring-2 ring-primary/10' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'" class="admin-action min-h-11 w-full rounded-xl border disabled:opacity-50">
                <?= masterIconSvg('qris', 'h-4 w-4') ?>
                QRIS
              </button>
              <button type="button" @click="metode='transfer'" :disabled="!methods.transfer || saving" :class="metode === 'transfer' ? 'border-primary bg-primary/10 text-primary ring-2 ring-primary/10' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'" class="admin-action min-h-11 w-full rounded-xl border disabled:opacity-50">Transfer</button>
            </div>
            <p x-show="!methods.transfer" class="mt-2 text-xs text-slate-500">Transfer tersedia setelah rekening toko diisi admin.</p>
            <p x-show="metode === 'transfer'" class="mt-2 text-xs text-slate-500">Unggah bukti transfer setelah menyimpan. Transaksi selesai setelah admin memverifikasi.</p>
          </div>

          <div class="grid gap-2 sm:grid-cols-2">
            <button type="button" @click="submit(false)" :disabled="keranjang.length === 0 || saving" class="btn-secondary order-2 sm:order-1 disabled:cursor-not-allowed disabled:opacity-50">
              Simpan Saja
            </button>
            <button type="button" @click="submit(true)" :disabled="keranjang.length === 0 || saving" class="btn-primary order-1 sm:order-2 disabled:cursor-not-allowed disabled:opacity-50">
              <?= masterIconSvg('save', 'h-4 w-4') ?>
              Simpan & Cetak
            </button>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<script>
  function kasirPage() {
    return {

      produk: [],
      keranjang: [],
      search: '',
      total: 0,
      metode: 'tunai',
      checkoutKey: null,
      saving: false,
      methods: { tunai: true, transfer: false, qris: false },
      incoming: [], pending: [], showIncoming: utils.getQuery('pesanan') === '1', queueTimer: null, shortcut: null,

      // ================= INIT =================
      async init() {
        await this.loadProduk()
        try { this.methods = (await API.get('/checkout/config', false)).data.metode; } catch (e) {}
        await this.refreshQueues()
        this.queueTimer = setInterval(() => { if (!document.hidden && !this.saving) this.refreshQueues(); }, 10000)

        // shortcut
        this.shortcut = (e) => {
          if (e.ctrlKey && e.key === 'k') {
            e.preventDefault()
            this.$refs.searchInput.focus()
          }

          if (e.ctrlKey && e.key === 'Enter') {
            this.submit(true)
          }

          if (e.ctrlKey && e.key === 's') {
            e.preventDefault()
            this.submit(false)
          }
        }
        window.addEventListener('keydown', this.shortcut)
      },
      destroy() { clearInterval(this.queueTimer); window.removeEventListener('keydown', this.shortcut); },
      async refreshQueues() {
        try {
          const [online, counter] = await Promise.all([API.get('/kasir/pesanan', false, true), API.get('/transaksi/list?sumber=kasir&status=pending,diproses&limit=50', false, true)]);
          this.incoming = online.data || []; this.pending = counter.data || [];
        } catch (e) { /* Badge dan antrean mencoba kembali saat tab aktif. */ }
      },
      async receiveCash(item) {
        if (this.saving || !await Alpine.store('ui').confirm('Terima uang tunai ' + utils.formatRupiah(item.total_tagihan) + ' dan serahkan barang kepada ' + item.nama_penerima + '?')) return;
        this.saving = true;
        try { await API.post('/pembayaran/tunai', { kode_transaksi: item.kode_transaksi }); window.location.replace(BASE_URL + '/struk/' + encodeURIComponent(item.kode_transaksi) + '?auto=1'); }
        catch (e) { this.saving = false; }
      },

      // ================= PRODUK =================
      async loadProduk() {
        const res = await API.get('/produk/trx?search=' + encodeURIComponent(this.search), false)
        this.produk = res.data || []
      },

      tambahDariInput() {
        if (this.produk.length === 1) {
          this.tambah(this.produk[0])
        } else {
          Alpine.store('ui').toast('Produk tidak ditemukan')
        }
      },

      // ================= KERANJANG =================
      tambah(p) {
        if (this.saving) return
        this.checkoutKey = null
        if (p.stok <= 0) {
          Alpine.store('ui').toast('Stok habis')
          return
        }

        let idx = this.keranjang.findIndex(i => i.kode_produk === p.kode_produk)

        if (idx >= 0) {
          if (this.keranjang[idx].jumlah < p.stok) {
            this.keranjang[idx].jumlah++
            this.update(idx)
          }
        } else {
          this.keranjang.push({
            kode_produk: p.kode_produk,
            nama_produk: p.nama_produk,
            harga_satuan: Number(p.harga_jual),
            jumlah: 1,
            subtotal: Number(p.harga_jual),
            stok: p.stok
          })
        }

        this.search = ''
        this.hitung()
      },

      update(i) {
        if (this.saving) return
        this.checkoutKey = null
        let item = this.keranjang[i]

        if (item.jumlah > item.stok) {
          item.jumlah = item.stok
        }

        item.subtotal = item.jumlah * item.harga_satuan
        this.hitung()
      },

      hapus(i) {
        if (this.saving) return
        this.checkoutKey = null
        this.keranjang.splice(i, 1)
        this.hitung()
      },

      reset() {
        this.checkoutKey = null
        this.keranjang = []
        this.total = 0
      },

      hitung() {
        this.total = this.keranjang.reduce((s, i) => s + i.subtotal, 0)
      },

      // ================= SUBMIT =================
      async submit(cetak = false) {
        if (this.saving) return
        this.saving = true
        try {
          if (this.keranjang.length === 0) {
            Alpine.store('ui').toast('Keranjang kosong')
            return
          }

          this.checkoutKey ||= utils.checkoutKey()
          const payload = {
            checkout_key: this.checkoutKey,
            total_harga: this.total,
            metode_bayar: this.metode,
            detail: this.keranjang.map(i => ({
              kode_produk: i.kode_produk,
              jumlah: i.jumlah,
              harga_satuan: i.harga_satuan,
              subtotal: i.subtotal
            }))
          }

          const res = await API.post('/transaksi', payload)

          if (res.success) {
            Alpine.store('ui').toast('Transaksi berhasil')

            const kode = res.data

            this.reset()
            this.checkoutKey = null
            if (this.metode !== 'tunai') { window.location.replace(BASE_URL + '/pembayaran/' + encodeURIComponent(kode) + (cetak ? '?cetak=1' : '')); return; }

            if (cetak) {
              window.location.replace(BASE_URL + '/struk/' + encodeURIComponent(kode) + '?auto=1')
              return
            }
            await this.loadProduk()
            this.$refs.searchInput.focus()

          }

        } catch (err) {
          console.error(err)
        } finally {
          this.saving = false
        }
      }

    }
  }
</script>
