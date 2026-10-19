<div class="sf-page sf-orders user-container" x-data="transaksiPage()">
  <div class="sf-page-heading">
    <h1>Pesanan Saya</h1>
    <p>Pantau perjalanan pesanan Anda, dari pembayaran sampai selesai.</p>
  </div>

  <section class="user-transaction-filter" aria-label="Filter transaksi">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h2 class="text-sm font-bold text-slate-900">Status pesanan</h2>
        <p class="form-help">Pilih status untuk melihat transaksi yang sesuai.</p>
      </div>
      <span class="hidden rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 sm:inline-flex" x-text="pagination.total + ' transaksi'"></span>
    </div>

    <div class="mt-4 flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Status transaksi">
      <template x-for="tab in tabs" :key="tab.value">
        <button type="button"
          class="user-transaction-tab"
          :class="filter.status === tab.value ? 'user-transaction-tab-active' : ''"
          :aria-selected="filter.status === tab.value"
          role="tab"
          @click="changeStatus(tab.value)"
          x-text="tab.label">
        </button>
      </template>
    </div>
  </section>

  <div class="mt-6 min-h-[55dvh]">
    <template x-if="loading">
      <div class="space-y-4" aria-live="polite" aria-label="Memuat transaksi">
        <template x-for="i in 3" :key="i">
          <div class="user-transaction-skeleton"></div>
        </template>
      </div>
    </template>

    <template x-if="!loading && error">
      <div class="user-empty">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600">!</div>
        <h2 class="mt-4 font-bold text-slate-900">Transaksi belum dapat dimuat</h2>
        <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500" x-text="error"></p>
        <button type="button" class="user-action-primary mt-5" @click="load()">Coba Lagi</button>
      </div>
    </template>

    <template x-if="!loading && !error && transaksi.length === 0">
      <div class="user-empty">
        <div class="sf-feature-icon mx-auto"><?= masterIconSvg('receipt', 'h-7 w-7') ?></div>
        <h2 class="mt-4 font-bold text-slate-900">Belum ada pesanan di sini</h2>
        <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500" x-text="emptyMessage()"></p>
        <a :href="BASE_URL + '/produk'" class="user-action-primary mt-5">Mulai Belanja</a>
      </div>
    </template>

    <div x-show="!loading && !error && transaksi.length > 0" class="space-y-4">
      <template x-for="t in transaksi" :key="t.kode_transaksi">
        <article class="user-transaction-card">
          <div class="flex flex-col gap-4 border-b border-slate-100 p-4 sm:p-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-bold text-slate-900" x-text="t.kode_transaksi"></span>
                <span :class="statusClass(t.status)" x-text="utils.orderStatus(t)"></span>
              </div>
              <p class="mt-1 text-xs text-slate-500" x-text="formatDate(t.tanggal_transaksi)"></p>
            </div>
            <div class="lg:text-right">
              <p class="text-xs font-medium text-slate-500">Total pembayaran</p>
              <p class="mt-0.5 text-lg font-bold text-slate-900" x-text="$store.utils.formatRupiah(t.total_tagihan)"></p>
            </div>
          </div>

          <div class="p-4 sm:p-5">
            <template x-if="t.detailLoading">
              <div class="flex gap-3 overflow-hidden">
                <template x-for="i in 3" :key="i">
                  <div class="h-16 w-16 shrink-0 animate-pulse rounded-xl bg-slate-100"></div>
                </template>
              </div>
            </template>

            <template x-if="!t.detailLoading && t.detailError">
              <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                Rincian produk belum dapat dimuat. Kamu tetap dapat membuka transaksi atau menghubungi admin.
              </div>
            </template>

            <template x-if="!t.detailLoading && !t.detailError && t.detail.length > 0">
              <div>
                <div class="flex items-center justify-between gap-3">
                  <h3 class="text-sm font-bold text-slate-900">Produk</h3>
                  <span class="text-xs text-slate-500" x-text="detailCountLabel(t.detail)"></span>
                </div>

                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                  <template x-for="d in t.detail" :key="d.kode_produk">
                    <a :href="BASE_URL + '/produk/' + encodeURIComponent(d.kode_produk)" class="user-transaction-product">
                      <template x-if="d.gambar">
                        <img loading="lazy" decoding="async"
                          :src="BASE_URL + '/uploads/' + d.gambar"
                          :alt="d.nama_produk"
                          class="h-14 w-14 shrink-0 rounded-xl object-cover" />
                      </template>
                      <template x-if="!d.gambar">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-xs font-bold text-slate-400">GG</div>
                      </template>
                      <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-800" x-text="d.nama_produk"></p>
                        <p class="mt-1 text-xs text-slate-500">
                          <span x-text="d.jumlah"></span> × <span x-text="$store.utils.formatRupiah(d.harga_satuan)"></span>
                        </p>
                        <p class="mt-1 text-sm font-bold text-slate-900" x-text="$store.utils.formatRupiah(d.harga_satuan * d.jumlah)"></p>
                      </div>
                    </a>
                  </template>
                </div>
              </div>
            </template>
          </div>

          <div class="px-4 pb-3 text-sm text-slate-600 sm:px-5"><span x-text="t.metode_pengiriman === 'kirim' ? 'Pengiriman lokal — ' + t.kelurahan : 'Ambil di tempat'"></span><span class="ml-2" x-text="({belum_bayar:'Belum dibayar',lunas:'Lunas',menunggu_verifikasi:'Menunggu verifikasi',ditolak:'Bukti ditolak',kedaluwarsa:'Kedaluwarsa',perlu_refund:'Menunggu pengembalian dana',dikembalikan:'Dana dikembalikan'})[t.status_bayar]"></span></div>
          <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/70 p-4 sm:flex-row sm:items-center sm:justify-end sm:p-5">
            <button type="button"
              class="user-action-danger user-mobile-action sm:w-auto"
              x-show="canCancel(t)"
              :disabled="!canCancel(t) || t.cancelling"
              @click="batal(t)">
              <span x-text="t.cancelling ? 'Membatalkan...' : 'Batalkan Pesanan'"></span>
            </button>
            <a :href="BASE_URL + '/pembayaran/' + encodeURIComponent(t.kode_transaksi)" class="user-action-outline user-mobile-action sm:w-auto">Detail & Riwayat Pesanan</a>
            <a target="_blank"
              rel="noopener noreferrer"
              class="user-action-primary user-mobile-action sm:w-auto"
              :href="whatsappLink(t)">
              Hubungi Admin
            </a>
          </div>
        </article>
      </template>

      <div x-show="pagination.totalPages > 1" class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 pt-5 sm:flex-row">
        <p class="text-xs text-slate-500">
          Halaman <span class="font-semibold text-slate-700" x-text="pagination.page"></span> dari <span class="font-semibold text-slate-700" x-text="pagination.totalPages"></span>
        </p>
        <div class="flex gap-2">
          <button type="button" class="user-action-outline" :disabled="pagination.page <= 1 || loading" @click="goToPage(pagination.page - 1)">Sebelumnya</button>
          <button type="button" class="user-action-primary" :disabled="pagination.page >= pagination.totalPages || loading" @click="goToPage(pagination.page + 1)">Berikutnya</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function transaksiPage() {
    return {
      transaksi: [],
      loading: false,
      error: '',
      requestId: 0,
      tabs: [
        { value: '', label: 'Semua' },
        { value: 'menunggu_pembayaran', label: 'Menunggu Pembayaran' },
        { value: 'menunggu_verifikasi', label: 'Menunggu Verifikasi' },
        { value: 'diproses', label: 'Diproses' },
        { value: 'dikirim', label: 'Dikirim' },
        { value: 'selesai', label: 'Selesai' },
        { value: 'dibatalkan', label: 'Dibatalkan' }
      ],
      filter: { status: '' },
      pagination: { page: 1, limit: 10, total: 0, totalPages: 1 },

      async init() {
        const stage=utils.getQuery('tahap'); if (this.tabs.some(tab=>tab.value===stage)) this.filter.status=stage;
        await this.load()
      },
      canCancel(t) { return !['lunas','menunggu_verifikasi','perlu_refund','dikembalikan'].includes(t.status_bayar) && (t.status === 'pending' || t.metode_bayar === 'tunai' && t.status === 'diproses' && !Number(t.siap_diambil)); },

      async changeStatus(status) {
        this.filter.status = status
        utils.setQuery('tahap',status)
        this.pagination.page = 1
        await this.load()
        this.scrollToFilter()
      },

      async goToPage(page) {
        if (page < 1 || page > this.pagination.totalPages) return
        this.pagination.page = page
        await this.load()
        this.scrollToList()
      },

      async load() {
        const request = ++this.requestId
        this.loading = true
        this.error = ''

        try {
          const query = new URLSearchParams({
            user: String(Alpine.store('auth').user.id_pengguna),
            page: String(this.pagination.page),
            limit: String(this.pagination.limit)
          })

          if (this.filter.status) query.append('tahap', this.filter.status)
          query.append('sumber', 'online')

          const res = await API.get('/transaksi/list?' + query.toString(), false)
          if (request !== this.requestId) return
          if (!res.success) throw new Error(res.message || 'Gagal memuat transaksi')

          this.transaksi = Array.isArray(res.data) ? res.data : []
          this.pagination.total = Number(res.pagination?.total || 0)
          this.pagination.totalPages = Math.max(1, Number(res.pagination?.total_pages || 1))

          await Promise.all(this.transaksi.map(async (t) => {
            t.detail = []
            t.detailLoading = true
            t.detailError = false
            t.cancelling = false

            try {
              const detail = await API.get('/transaksi/detail?k=' + encodeURIComponent(t.kode_transaksi), false)
              if (!detail.success) throw new Error(detail.message || 'Gagal memuat rincian')
              t.detail = Array.isArray(detail.data?.detail) ? detail.data.detail : []
            } catch (err) {
              t.detailError = true
            } finally {
              t.detailLoading = false
            }
          }))
        } catch (err) {
          if (request !== this.requestId) return
          this.transaksi = []
          this.error = err?.message || 'Terjadi kesalahan saat memuat transaksi.'
        } finally {
          if (request === this.requestId) this.loading = false
        }
      },

      async batal(transaksi) {
        const ok = await Alpine.store('ui').confirm(`Yakin ingin membatalkan pesanan ${transaksi.kode_transaksi}?`)
        if (!ok) return

        transaksi.cancelling = true
        try {
          const res = await API.post('/transaksi/batal-pending', {
            kode_transaksi: transaksi.kode_transaksi
          })
          if (!res.success) throw new Error(res.message || 'Gagal membatalkan pesanan')
          Alpine.store('ui').toast('Pesanan berhasil dibatalkan')
          await this.load()
        } catch (err) {
          transaksi.cancelling = false
          Alpine.store('ui').toast(err?.message || 'Gagal membatalkan pesanan')
        }
      },

      statusLabel(status) {
        return {
          pending: 'Menunggu Pembayaran',
          menunggu_pembayaran: 'Menunggu Pembayaran',
          menunggu_verifikasi: 'Menunggu Verifikasi',
          diproses: 'Diproses',
          dikirim: 'Dikirim',
          selesai: 'Selesai',
          dibatalkan: 'Dibatalkan'
        }[status] || status
      },

      statusClass(status) {
        return {
          pending: 'status-warning',
          diproses: 'status-neutral',
          dikirim: 'status-neutral',
          selesai: 'status-success',
          dibatalkan: 'status-danger'
        }[status] || 'status-neutral'
      },

      formatDate(value) {
        return `Dipesan ${Alpine.store('utils').formatDateTime(value)}`
      },

      detailCountLabel(detail) {
        return `${detail.length} produk`
      },

      emptyMessage() {
        const label = this.statusLabel(this.filter.status)
        return this.filter.status ? `Belum ada transaksi dengan status ${label.toLowerCase()}.` : 'Transaksi yang kamu buat akan muncul di halaman ini.'
      },

      whatsappLink(t) {
        const nama = Alpine.store('auth').user?.nama || 'pelanggan'
        const text = `Halo, saya ${nama} ingin menanyakan transaksi ${t.kode_transaksi}`
        return `https://wa.me/${NOMOR_WA}?text=${encodeURIComponent(text)}`
      },

      scrollToFilter() {
        const target = document.querySelector('.user-transaction-filter')
        if (!target) return
        const navbar = document.querySelector('nav')
        const offset = (navbar?.getBoundingClientRect().height || 64) + 12
        const top = target.getBoundingClientRect().top + window.scrollY - offset
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' })
      },

      scrollToList() {
        const target = document.querySelector('.user-transaction-card')
        if (!target) return
        const navbar = document.querySelector('nav')
        const offset = (navbar?.getBoundingClientRect().height || 64) + 12
        const top = target.getBoundingClientRect().top + window.scrollY - offset
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' })
      }
    }
  }
</script>
