<div class="admin-page space-y-6 p-4 sm:p-6" x-data="riwayatPage()">

  <!-- HEADER -->
  <header class="admin-page-header flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
      <h1 class="admin-page-title">Riwayat Transaksi</h1>
      <p class="admin-page-subtitle">Lihat transaksi yang telah selesai atau dibatalkan, lalu buka detail untuk memeriksa rincian.</p>
    </div>
    <div class="text-sm text-slate-500" x-show="pagination.total > 0">
      <span class="font-semibold text-slate-700" x-text="pagination.total"></span> transaksi ditemukan
    </div>
  </header>

  <!-- FILTER -->
  <section class="card p-4">
    <div class="mb-4">
      <h2 class="font-semibold text-slate-900">Cari dan filter transaksi</h2>
      <p class="form-help">Gunakan filter untuk mempersempit riwayat berdasarkan kode, produk, metode pembayaran, atau periode.</p>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
      <div class="sm:col-span-2 xl:col-span-1">
        <label class="label" for="riwayat-search">Cari</label>
        <input id="riwayat-search" type="search" x-model="search" @input.debounce.500="reload()" class="input w-full" placeholder="Contoh: GG-12345678 atau Beras Merah Pantar">
        <p class="form-help">Cari berdasarkan kode transaksi atau nama produk.</p>
      </div>

      <div>
        <label class="label" for="riwayat-status">Status</label>
        <select x-gg-select id="riwayat-status" x-model="status" @change="reload()" class="input w-full">
          <option value="selesai,dibatalkan">Semua riwayat</option>
          <option value="selesai">Selesai</option>
          <option value="dibatalkan">Dibatalkan</option>
        </select>
      </div>

      <div>
        <label class="label" for="riwayat-metode">Metode pembayaran</label>
        <select x-gg-select id="riwayat-metode" x-model="metode" @change="reload()" class="input w-full">
          <option value="">Semua metode</option>
          <option value="tunai">Tunai</option>
          <option value="qris">QRIS</option>
          <option value="transfer">Transfer manual</option>
        </select>
      </div>

      <div>
        <label class="label" for="riwayat-start">Dari tanggal</label>
        <input id="riwayat-start" type="date" x-model="start" @change="reload()" class="input w-full">
      </div>

      <div>
        <label class="label" for="riwayat-end">Sampai tanggal</label>
        <input id="riwayat-end" type="date" x-model="end" @change="reload()" class="input w-full">
      </div>
    </div>
  </section>

  <!-- ERROR -->
  <template x-if="error">
    <section class="rounded-2xl border border-red-200 bg-red-50 p-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="font-semibold text-red-800">Riwayat transaksi gagal dimuat</p>
          <p class="mt-1 text-sm text-red-700" x-text="error"></p>
        </div>
        <button type="button" @click="load()" class="admin-action-danger">Coba lagi</button>
      </div>
    </section>
  </template>

  <!-- SUMMARY -->
  <div class="grid grid-cols-1 gap-3 sm:grid-cols-3" x-show="status !== 'dibatalkan'" x-cloak>
    <section class="card">
      <p class="text-sm font-medium text-slate-500">Omzet</p>
      <p class="mt-2 text-xl font-bold text-primary" x-text="utils.formatRupiah(summary.jual || 0)"></p>
      <p class="form-help">Total nilai penjualan pada hasil filter.</p>
    </section>
    <section class="card">
      <p class="text-sm font-medium text-slate-500">Modal</p>
      <p class="mt-2 text-xl font-bold text-slate-900" x-text="utils.formatRupiah(summary.pokok || 0)"></p>
      <p class="form-help">Total HPP transaksi selesai.</p>
    </section>
    <section class="card">
      <p class="text-sm font-medium text-slate-500">Laba</p>
      <p class="mt-2 text-xl font-bold text-emerald-600" x-text="utils.formatRupiah(summary.laba || 0)"></p>
      <p class="form-help">Selisih omzet dan modal.</p>
    </section>
  </div>

  <!-- LOADING -->
  <template x-if="loading">
    <section class="card p-6">
      <div class="space-y-3 animate-pulse">
        <div class="h-4 w-40 rounded bg-slate-200"></div>
        <div class="h-10 rounded bg-slate-100"></div>
        <div class="h-10 rounded bg-slate-100"></div>
        <div class="h-10 rounded bg-slate-100"></div>
      </div>
    </section>
  </template>

  <!-- TABLE -->
  <section class="card overflow-hidden" x-show="!loading" x-cloak>
    <div class="flex flex-col gap-1 border-b border-slate-200 px-4 py-4 sm:px-5">
      <h2 class="font-semibold text-slate-900">Daftar transaksi</h2>
      <p class="text-sm text-slate-500">Pilih <strong>Detail</strong> untuk melihat produk, total, HPP, dan status transaksi.</p>
    </div>

    <div class="hidden overflow-x-auto lg:block">
      <table class="table w-full">
        <thead>
          <tr class="bg-slate-50">
            <th>No</th>
            <th>Kode</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Total</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <template x-for="(item, idx) in data" :key="item.kode_transaksi">
            <tr class="transition hover:bg-slate-50">
              <td x-text="(pagination.page - 1) * pagination.limit + idx + 1"></td>
              <td class="font-semibold text-slate-900" x-text="item.kode_transaksi"></td>
              <td class="text-sm text-slate-500" x-text="utils.formatDateTime(item.tanggal_transaksi)"></td>
              <td>
                <span x-show="item.user_role === 'pelanggan'" x-text="item.user || 'Offline'"></span>
                <span x-show="item.user_role !== 'pelanggan'">Transaksi Kasir</span>
              </td>
              <td class="text-sm font-medium uppercase text-slate-600" x-text="item.metode_bayar"></td>
              <td>
                <span :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span>
              </td>
              <td class="font-semibold text-slate-900" x-text="utils.formatRupiah(item.total_harga)"></td>
              <td class="text-right">
                <button type="button" @click="detail(item.kode_transaksi)" class="admin-action-secondary admin-action-sm">Detail</button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- MOBILE -->
    <div class="divide-y divide-slate-200 lg:hidden">
      <template x-if="data.length === 0 && !loading">
        <div class="p-6 text-center">
          <p class="font-semibold text-slate-800">Belum ada transaksi</p>
          <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau rentang tanggal untuk menemukan transaksi.</p>
        </div>
      </template>
      <template x-for="item in data" :key="'mobile-' + item.kode_transaksi">
        <article class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="truncate font-bold text-slate-900" x-text="item.kode_transaksi"></p>
              <p class="mt-1 text-xs text-slate-500" x-text="utils.formatDateTime(item.tanggal_transaksi)"></p>
            </div>
            <span :class="statusClass(item.status)" x-text="statusLabel(item.status)"></span>
          </div>
          <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
            <div>
              <dt class="text-slate-500" x-text="item.user_role === 'pelanggan' ? 'Pelanggan' : 'Sumber Transaksi'"></dt>
              <dd class="mt-1 font-medium text-slate-800" x-text="item.user_role === 'pelanggan' ? (item.user || 'Offline') : 'Kasir'"></dd>
            </div>
            <div>
              <dt class="text-slate-500">Metode</dt>
              <dd class="mt-1 font-medium uppercase text-slate-800" x-text="item.metode_bayar"></dd>
            </div>
            <div class="col-span-2">
              <dt class="text-slate-500">Total</dt>
              <dd class="mt-1 text-lg font-bold text-slate-900" x-text="utils.formatRupiah(item.total_harga)"></dd>
            </div>
          </dl>
          <button type="button" @click="detail(item.kode_transaksi)" class="admin-action-secondary mt-4 w-full">Lihat detail transaksi</button>
        </article>
      </template>
    </div>

    <!-- DESKTOP EMPTY -->
    <template x-if="data.length === 0 && !loading">
      <div class="hidden p-10 text-center lg:block">
        <p class="font-semibold text-slate-800">Belum ada transaksi yang sesuai</p>
        <p class="mt-1 text-sm text-slate-500">Coba ubah filter atau rentang tanggal, lalu muat ulang data.</p>
      </div>
    </template>
  </section>

  <!-- PAGINATION -->
  <div class="flex flex-col gap-3 text-sm text-slate-600 md:flex-row md:items-center md:justify-between" x-show="!loading && pagination.total > 0" x-cloak>
    <div class="flex items-center gap-2">
      <span>Per halaman</span>
      <select x-gg-select x-model.number="pagination.limit" @change="reload()" class="input w-auto min-w-20">
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
      </select>
      <span>dari <strong class="text-slate-800" x-text="pagination.total"></strong> data</span>
    </div>

    <div class="flex flex-wrap gap-2">
      <button type="button" @click="goPage(pagination.page - 1)" :disabled="pagination.page <= 1" class="admin-page-button disabled:cursor-not-allowed disabled:opacity-40" aria-label="Halaman sebelumnya"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?></button>
      <template x-for="page in pagination.total_pages" :key="page">
        <button type="button" @click="goPage(page)" :class="pagination.page == page ? 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700' : ''" class="admin-page-button" x-text="page"></button>
      </template>
      <button type="button" @click="goPage(pagination.page + 1)" :disabled="pagination.page >= pagination.total_pages" class="admin-page-button disabled:cursor-not-allowed disabled:opacity-40" aria-label="Halaman berikutnya"><?= masterIconSvg('arrow-right', 'h-4 w-4') ?></button>
    </div>
  </div>

</div>

<script>
  function riwayatPage() {
    return {
      data: [],
      summary: {},
      loading: true,
      error: '',

      search: utils.getQuery('search') ?? '',
      status: utils.getQuery('status') ?? 'selesai,dibatalkan',
      metode: utils.getQuery('metode') ?? '',
      start: utils.getQuery('start') ?? '',
      end: utils.getQuery('end') ?? '',

      pagination: {
        page: Number(utils.getQuery('page') ?? 1),
        limit: Number(utils.getQuery('limit') ?? 10),
        total: 0,
        total_pages: 1
      },

      async init() {
        await this.load()
      },

      statusClass(status) {
        if (status === 'selesai') return 'status-success'
        if (status === 'dibatalkan') return 'status-danger'
        return 'status-warning'
      },

      statusLabel(status) {
        return status === 'selesai' ? 'Selesai' : status === 'dibatalkan' ? 'Dibatalkan' : status
      },

      async load() {
        this.loading = true
        this.error = ''
        try {
          const params = new URLSearchParams({
            search: this.search,
            status: this.status,
            metode: this.metode,
            start: this.start,
            end: this.end,
            limit: this.pagination.limit,
            page: this.pagination.page
          })

          const res = await API.get('/transaksi/list?' + params.toString())

          if (!res.success) throw new Error(res.message || 'Gagal memuat riwayat transaksi')

          this.data = res.data || []
          this.summary = res.totalSummary || {}
          this.pagination.total = Number(res.pagination?.total || 0)
          this.pagination.total_pages = Math.max(1, Math.ceil(this.pagination.total / this.pagination.limit))

          if (this.pagination.page > this.pagination.total_pages) {
            this.pagination.page = this.pagination.total_pages
          }

          utils.setQuery('search', this.search)
          utils.setQuery('status', this.status)
          utils.setQuery('metode', this.metode)
          utils.setQuery('start', this.start)
          utils.setQuery('end', this.end)
          utils.setQuery('page', this.pagination.page)
          utils.setQuery('limit', this.pagination.limit)
        } catch (err) {
          console.error(err)
          this.data = []
          this.summary = {}
          this.error = err?.message || 'Terjadi kesalahan saat memuat data.'
        } finally {
          this.loading = false
        }
      },

      reload() {
        this.pagination.page = 1
        this.load()
      },

      goPage(page) {
        if (page < 1 || page > this.pagination.total_pages || page === this.pagination.page) return
        this.pagination.page = page
        this.load()
      },

      detail(kode) {
        window.location.href = BASE_URL + '/admin/transaksi/' + encodeURIComponent(kode)
      }
    }
  }
</script>