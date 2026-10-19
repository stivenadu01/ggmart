<div
  x-data="stokPage()"
  x-init="init()"
  @keydown.escape.window="closeBatchModal()"
  class="space-y-6 p-4 sm:p-6">

  <!-- HEADER -->
  <header class="admin-page-header">
    <div class="min-w-0">
      <div class="admin-eyebrow">Manajemen Persediaan</div>
      <h1 class="admin-page-title">Kelola Stok</h1>
      <p class="admin-page-subtitle">
        Pantau stok produk, riwayat mutasi, dan batch yang masih tersedia tanpa mengubah alur FIFO.
      </p>
    </div>

    <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin'], true)): ?>
      <a href="<?= BASE_URL ?>/admin/stok/form" class="btn-primary w-full sm:w-auto">
        <?= masterIconSvg('plus', 'h-4 w-4') ?>
        <span>Tambah Perubahan Stok</span>
      </a>
    <?php endif; ?>
  </header>

  <!-- SUMMARY -->
  <section aria-label="Ringkasan stok" class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="card">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Produk</p>
          <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900" x-text="summary.total_produk"></p>
          <p class="mt-1 text-xs text-slate-500">Produk yang dikelola</p>
        </div>
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600" aria-hidden="true">
          <?= masterIconSvg('stock', 'h-5 w-5 fill-none stroke-current') ?>
        </span>
      </div>
    </div>

    <div class="card">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Stok Menipis</p>
          <p class="mt-2 text-2xl font-bold tracking-tight text-amber-600" x-text="summary.stok_menipis"></p>
          <p class="mt-1 text-xs text-slate-500">Stok 1–5 unit</p>
        </div>
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600" aria-hidden="true">!</span>
      </div>
    </div>

    <div class="card">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Stok Habis</p>
          <p class="mt-2 text-2xl font-bold tracking-tight text-red-600" x-text="summary.stok_habis"></p>
          <p class="mt-1 text-xs text-slate-500">Produk dengan stok 0</p>
        </div>
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600" aria-hidden="true">0</span>
      </div>
    </div>

    <div class="card">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Batch Aktif</p>
          <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900" x-text="summary.batch_aktif"></p>
          <p class="mt-1 text-xs text-slate-500">Batch masuk dengan sisa stok</p>
        </div>
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600" aria-hidden="true">#</span>
      </div>
    </div>
  </section>

  <!-- FILTER -->
  <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
      <div class="min-w-0 flex-1">
        <label for="stok-search" class="label">Cari Produk</label>
        <div class="relative">
          <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400" aria-hidden="true">
            <?= masterIconSvg('search', 'h-5 w-5 fill-none stroke-current') ?>
          </span>
          <input
            id="stok-search"
            type="search"
            x-model="search"
            @input.debounce.500="pagination.page = 1; load()"
            placeholder="Contoh: Beras Merah Pantar"
            class="input pl-10"
            autocomplete="off">
        </div>
        <p class="form-help">Cari riwayat perubahan berdasarkan nama produk.</p>
      </div>

      <div class="w-full lg:w-56">
        <label for="stok-type" class="label">Jenis Mutasi</label>
        <select x-gg-select id="stok-type" x-model="type" @change="pagination.page = 1; load()" class="input">
          <option value="">Semua jenis</option>
          <option value="masuk">Stok Masuk</option>
          <option value="keluar">Stok Keluar</option>
        </select>
        <p class="form-help">Gunakan filter untuk fokus pada stok masuk atau keluar.</p>
      </div>

      <button
        type="button"
        @click="resetFilter()"
        :disabled="!search && !type"
        class="admin-action-secondary w-full lg:w-auto">
        Reset Filter
      </button>
    </div>
  </section>

  <!-- ERROR -->
  <div x-show="error" x-cloak class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
    <div class="flex items-start gap-3">
      <span class="font-bold">Gagal memuat data.</span>
      <span x-text="error"></span>
    </div>
    <button type="button" @click="load()" class="mt-3 font-semibold underline underline-offset-2">Coba lagi</button>
  </div>

  <!-- DATA -->
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
      <div>
        <h2 class="font-semibold text-slate-800">Riwayat Mutasi Stok</h2>
        <p class="mt-0.5 text-xs text-slate-500">Setiap perubahan stok tercatat sebagai mutasi.</p>
      </div>
      <span class="status-neutral self-start sm:self-auto">
        <span x-text="pagination.total"></span> catatan
      </span>
    </div>

    <!-- LOADING -->
    <div x-show="loading" x-cloak class="space-y-3 p-4 sm:p-5" aria-live="polite">
      <div class="h-12 animate-pulse rounded-xl bg-slate-100"></div>
      <div class="h-12 animate-pulse rounded-xl bg-slate-100"></div>
      <div class="h-12 animate-pulse rounded-xl bg-slate-100"></div>
    </div>

    <!-- DESKTOP TABLE -->
    <div x-show="!loading" class="hidden overflow-x-auto md:block">
      <table class="table min-w-[980px]">
        <thead>
          <tr>
            <th class="w-14 text-center">No</th>
            <th>Tanggal</th>
            <th>Produk</th>
            <th>Jenis</th>
            <th>Jumlah</th>
            <th>HPP</th>
            <th>Keterangan</th>
            <th class="w-52 text-right">Aksi</th>
          </tr>
        </thead>

        <tbody>
          <template x-if="mutasi.length === 0 && !loading">
            <tr>
              <td colspan="8" class="py-14 text-center">
                <div class="mx-auto max-w-sm px-4">
                  <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <?= masterIconSvg('category', 'h-6 w-6 fill-none stroke-current') ?>
                  </div>
                  <p class="font-semibold text-slate-800" x-text="search || type ? 'Mutasi tidak ditemukan' : 'Belum ada mutasi stok'"></p>
                  <p class="mt-1 text-sm leading-6 text-slate-500" x-text="search || type ? 'Coba kata kunci lain atau gunakan Reset Filter.' : 'Tambahkan stok masuk atau stok keluar untuk mulai mencatat persediaan.'"></p>
                  <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?><a x-show="!search && !type" href="<?= BASE_URL ?>/admin/stok/form" class="btn-primary mt-4 inline-flex">Tambah Perubahan Stok</a><?php endif; ?>
                </div>
              </td>
            </tr>
          </template>

          <template x-for="(item, idx) in mutasi" :key="item.id_mutasi">
            <tr>
              <td class="text-center text-slate-500" x-text="(pagination.page - 1) * pagination.limit + idx + 1"></td>
              <td class="whitespace-nowrap text-sm text-slate-500" x-text="utils.formatDateTime(item.tanggal)"></td>
              <td>
                <div class="font-semibold text-slate-800" x-text="item.nama_produk || 'Produk tidak ditemukan'"></div>
                <div class="mt-0.5 text-xs text-slate-500" x-text="item.kode_produk || '-'"></div>
              </td>
              <td>
                <span :class="item.type === 'masuk' ? 'status-success' : 'status-danger'">
                  <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                  <span x-text="item.type === 'masuk' ? 'Masuk' : 'Keluar'"></span>
                </span>
              </td>
              <td class="whitespace-nowrap font-semibold text-slate-800">
                <span x-text="item.jumlah"></span>
                <span class="text-xs font-medium text-slate-500" x-text="item.satuan_dasar || 'unit'"></span>
              </td>
              <td class="whitespace-nowrap text-sm text-slate-700" x-text="item.harga_pokok != null ? Alpine.store('utils').formatRupiah(item.harga_pokok) : '-'"></td>
              <td class="max-w-56 text-sm text-slate-600">
                <span class="line-clamp-2" :title="item.keterangan || '-'" x-text="item.keterangan || '-'"></span>
              </td>
              <td>
                <div class="flex flex-wrap justify-end gap-2">
                  <button type="button" @click="lihatBatch(item.kode_produk, item.nama_produk)" class="admin-action-secondary admin-action-sm">
                    Batch
                  </button>
                  <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin'], true)): ?>
                    <button
                      type="button"
                      x-show="item.type === 'masuk'"
                      @click="hapus(item.id_mutasi)"
                      class="admin-action-danger admin-action-sm">
                      Hapus
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- MOBILE LIST -->
    <div x-show="!loading" class="divide-y divide-slate-100 md:hidden">
      <template x-if="mutasi.length === 0 && !loading">
        <div class="px-5 py-12 text-center">
          <p class="font-semibold text-slate-800" x-text="search || type ? 'Mutasi tidak ditemukan' : 'Belum ada mutasi stok'"></p>
          <p class="mt-1 text-sm leading-6 text-slate-500" x-text="search || type ? 'Coba kata kunci lain atau gunakan Reset Filter.' : 'Tambahkan stok untuk mulai mencatat persediaan.'"></p>
          <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?><a x-show="!search && !type" href="<?= BASE_URL ?>/admin/stok/form" class="btn-primary mt-4 inline-flex">Tambah Stok</a><?php endif; ?>
        </div>
      </template>

      <template x-for="(item, idx) in mutasi" :key="'m-'+item.id_mutasi">
        <article class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="truncate font-semibold text-slate-800" x-text="item.nama_produk || 'Produk tidak ditemukan'"></p>
              <p class="mt-0.5 text-xs text-slate-500" x-text="item.kode_produk || '-'"></p>
            </div>
            <span :class="item.type === 'masuk' ? 'status-success' : 'status-danger'" class="shrink-0">
              <span x-text="item.type === 'masuk' ? 'Masuk' : 'Keluar'"></span>
            </span>
          </div>

          <div class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Jumlah</p>
              <p class="mt-1 font-semibold text-slate-800">
                <span x-text="item.jumlah"></span>
                <span class="text-xs font-medium text-slate-500" x-text="item.satuan_dasar || 'unit'"></span>
              </p>
            </div>
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">HPP</p>
              <p class="mt-1 text-sm font-semibold text-slate-800" x-text="item.harga_pokok != null ? Alpine.store('utils').formatRupiah(item.harga_pokok) : '-'"></p>
            </div>
            <div class="col-span-2">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Tanggal</p>
              <p class="mt-1 text-sm text-slate-600" x-text="utils.formatDateTime(item.tanggal)"></p>
            </div>
            <div class="col-span-2">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Keterangan</p>
              <p class="mt-1 text-sm leading-5 text-slate-600" x-text="item.keterangan || '-'"></p>
            </div>
          </div>

          <div class="mt-3 flex gap-2">
            <button type="button" @click="lihatBatch(item.kode_produk, item.nama_produk)" class="admin-action-secondary flex-1">
              Lihat Batch
            </button>
            <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin'], true)): ?>
              <button
                type="button"
                x-show="item.type === 'masuk'"
                @click="hapus(item.id_mutasi)"
                class="admin-action-danger flex-1">
                Hapus
              </button>
            <?php endif; ?>
          </div>
        </article>
      </template>
    </div>

    <!-- PAGINATION -->
    <div x-show="!loading && pagination.total > 0" class="flex flex-col gap-4 border-t border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
      <div class="text-sm text-slate-500">
        Tampilkan
        <select x-gg-select x-model.number="pagination.limit" @change="pagination.page=1; load()" class="input mx-1 inline-block w-auto py-2">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
        </select>
        dari <strong class="font-semibold text-slate-700" x-text="pagination.total"></strong> mutasi
      </div>

      <div class="flex items-center justify-between gap-2 sm:justify-end">
        <button type="button" @click="goPage(pagination.page - 1)" :disabled="loading || pagination.page <= 1" class="admin-action-secondary admin-action-sm shrink-0" aria-label="Halaman sebelumnya">
          <?= masterIconSvg('arrow-left', 'h-4 w-4') ?> <span class="hidden sm:inline">Prev</span>
        </button>

        <div class="flex min-w-0 flex-1 flex-wrap items-center justify-center gap-1 sm:flex-none">
          <template x-for="(page, index) in visiblePages()" :key="String(page) + ':' + index">
            <span class="inline-flex">
            <span x-show="page === '…'" class="flex h-9 min-w-9 items-center justify-center px-1 text-sm text-slate-400">…</span>
            <button
              x-show="page !== '…'"
              type="button"
              @click="goPage(page)"
              :aria-label="'Halaman ' + page"
              :aria-current="pagination.page === page ? 'page' : null"
              :disabled="loading"
              :class="pagination.page === page ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
              class="h-9 min-w-9 rounded-lg border px-2 text-sm font-semibold">
              <span x-text="page"></span>
            </button>
            </span>
          </template>
        </div>

        <button type="button" @click="goPage(pagination.page + 1)" :disabled="loading || pagination.page >= pagination.total_pages" class="admin-action-secondary admin-action-sm shrink-0" aria-label="Halaman berikutnya">
          <span class="hidden sm:inline">Next</span> <?= masterIconSvg('arrow-right', 'h-4 w-4') ?>
        </button>
      </div>
    </div>
  </section>

  <!-- BATCH MODAL -->
  <template x-if="batchModal.open">
    <div class="modal-backdrop" @mousedown.self="closeBatchModal()">
      <section class="modal-box max-h-[calc(100dvh-2rem)] max-w-2xl overflow-hidden p-0" role="dialog" aria-modal="true" aria-labelledby="batch-modal-title">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 p-5">
          <div class="min-w-0">
            <p class="admin-eyebrow">FIFO</p>
            <h2 id="batch-modal-title" class="text-lg font-bold text-slate-900">Batch Stok</h2>
            <p class="mt-1 truncate text-sm text-slate-500" x-text="batchModal.productName"></p>
          </div>
          <button type="button" @click="closeBatchModal()" class="icon-btn shrink-0 text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Tutup">
            ×
          </button>
        </div>

        <div class="max-h-[calc(100dvh-9rem)] overflow-y-auto p-5">
          <div x-show="batchModal.loading" class="space-y-3">
            <div class="h-16 animate-pulse rounded-xl bg-slate-100"></div>
            <div class="h-16 animate-pulse rounded-xl bg-slate-100"></div>
          </div>

          <div x-show="!batchModal.loading && batchModal.error" x-cloak class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p x-text="batchModal.error"></p>
            <button type="button" @click="loadBatches(batchModal.kodeProduk)" class="mt-2 font-semibold underline">Coba lagi</button>
          </div>

          <div x-show="!batchModal.loading && !batchModal.error && batchModal.items.length === 0" x-cloak class="rounded-xl bg-slate-50 p-6 text-center">
            <p class="font-semibold text-slate-800">Tidak ada batch aktif</p>
            <p class="mt-1 text-sm text-slate-500">Produk ini belum memiliki batch masuk dengan sisa stok.</p>
          </div>

          <div x-show="!batchModal.loading && !batchModal.error && batchModal.items.length > 0" x-cloak class="space-y-3">
            <template x-for="(batch, index) in batchModal.items" :key="batch.id_mutasi">
              <div class="rounded-xl border border-slate-200 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                  <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Batch <span x-text="index + 1"></span></p>
                    <p class="mt-1 text-sm font-semibold text-slate-800" x-text="utils.formatDateTime(batch.tanggal)"></p>
                  </div>
                  <span class="status-success">FIFO aktif</span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                  <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Stok Masuk</p>
                    <p class="mt-1 font-semibold text-slate-800">
                      <span x-text="batch.jumlah"></span>
                      <span class="text-xs text-slate-500" x-text="batchModal.unit || 'unit'"></span>
                    </p>
                  </div>
                  <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Sisa Stok</p>
                    <p class="mt-1 font-semibold text-emerald-700">
                      <span x-text="batch.sisa_stok"></span>
                      <span class="text-xs text-slate-500" x-text="batchModal.unit || 'unit'"></span>
                    </p>
                  </div>
                  <div class="col-span-2 sm:col-span-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">HPP</p>
                    <p class="mt-1 font-semibold text-slate-800" x-text="Alpine.store('utils').formatRupiah(batch.harga_pokok)"></p>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </section>
    </div>
  </template>
</div>

<script>
  function stokPage() {
    return {
      mutasi: [],
      search: utils.getQuery('search') || '',
      type: utils.getQuery('type') || '',
      loading: true,
      error: '',
      requestId: 0,
      summary: {
        total_produk: 0,
        stok_menipis: 0,
        stok_habis: 0,
        batch_aktif: 0
      },
      batchModal: {
        open: false,
        loading: false,
        error: '',
        kodeProduk: '',
        productName: '',
        unit: '',
        items: []
      },
      pagination: {
        page: Number(utils.getQuery('page')) || 1,
        total: 0,
        total_pages: 1,
        limit: Number(utils.getQuery('limit')) || 10
      },

      async init() {
        await Promise.all([this.load(), this.loadSummary()])
      },

      async load() {
        const request = ++this.requestId
        this.loading = true
        this.error = ''

        try {
          const query =
            '/mutasi/list?search=' + encodeURIComponent(this.search) +
            '&type=' + encodeURIComponent(this.type) +
            '&limit=' + this.pagination.limit +
            '&page=' + this.pagination.page

          const res = await API.get(query)
          if (request !== this.requestId) return

          if (!res.success) {
            throw new Error(res.message || 'Data mutasi tidak dapat dimuat.')
          }

          this.mutasi = res.data || []
          this.pagination.total = Number(res.pagination?.total || 0)
          this.pagination.total_pages = Math.max(
            1,
            Math.ceil(this.pagination.total / this.pagination.limit)
          )

          if (this.pagination.page > this.pagination.total_pages) {
            this.pagination.page = this.pagination.total_pages
            return await this.load()
          }

          utils.setQuery('search', this.search)
          utils.setQuery('type', this.type)
          utils.setQuery('page', this.pagination.page)
          utils.setQuery('limit', this.pagination.limit)
        } catch (err) {
          if (request !== this.requestId) return
          console.error(err)
          this.error = err.message || 'Terjadi kesalahan saat memuat data.'
          this.mutasi = []
        } finally {
          if (request === this.requestId) this.loading = false
        }
      },

      async loadSummary() {
        try {
          const res = await API.get('/mutasi/summary')
          if (!res.success) throw new Error(res.message || 'Ringkasan tidak tersedia.')
          this.summary = {
            ...this.summary,
            ...(res.data || {})
          }
        } catch (err) {
          console.error(err)
          // Ringkasan tidak menghalangi halaman mutasi untuk digunakan.
        }
      },

      resetFilter() {
        this.search = ''
        this.type = ''
        this.pagination.page = 1
        this.load()
      },

      async goPage(page) {
        page = Number(page)
        if (!Number.isInteger(page) || this.loading) return
        if (page < 1 || page > this.pagination.total_pages || page === this.pagination.page) return
        this.pagination.page = page
        await this.load()
      },

      visiblePages() {
        const total = this.pagination.total_pages
        const current = this.pagination.page

        if (total <= 5) return Array.from({
          length: total
        }, (_, i) => i + 1)

        const pages = [1]
        if (current > 3) pages.push('…')
        for (let p = Math.max(2, current - 1); p <= Math.min(total - 1, current + 1); p++) {
          pages.push(p)
        }
        if (current < total - 2) pages.push('…')
        pages.push(total)

        return pages
      },

      async lihatBatch(kodeProduk, productName) {
        if (!kodeProduk) return

        const item = this.mutasi.find(m => m.kode_produk === kodeProduk)
        this.batchModal.open = true
        this.batchModal.kodeProduk = kodeProduk
        this.batchModal.productName = productName || item?.nama_produk || 'Produk'
        this.batchModal.unit = item?.satuan_dasar || 'unit'
        await this.loadBatches(kodeProduk)
      },

      async loadBatches(kodeProduk) {
        this.batchModal.loading = true
        this.batchModal.error = ''
        this.batchModal.items = []

        try {
          const res = await API.get('/mutasi/dropdown?kode=' + encodeURIComponent(kodeProduk))
          if (!res.success) throw new Error(res.message || 'Batch tidak dapat dimuat.')
          this.batchModal.items = res.data || []
        } catch (err) {
          console.error(err)
          this.batchModal.error = err.message || 'Terjadi kesalahan saat memuat batch.'
        } finally {
          this.batchModal.loading = false
        }
      },

      closeBatchModal() {
        this.batchModal.open = false
      },

      async hapus(id) {
        const ok = await Alpine.store('ui').confirm(
          'Mutasi stok masuk ini akan dihapus. Pastikan batch belum digunakan dan perubahan masih diperbolehkan. Lanjutkan?'
        )
        if (!ok) return

        try {
          const res = await API.delete('/mutasi', {
            id
          })
          if (!res.success) throw new Error(res.message || 'Mutasi gagal dihapus.')
          Alpine.store('ui').toast('Mutasi stok berhasil dihapus')
          await Promise.all([this.load(), this.loadSummary()])
        } catch (err) {
          console.error(err)
          Alpine.store('ui').toast(err.message || 'Mutasi stok gagal dihapus')
        }
      }
    }
  }
</script>
