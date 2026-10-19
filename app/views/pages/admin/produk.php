<div
  class="space-y-6 p-4 sm:p-6"
  x-data="produkPage()">

  <!-- PAGE HEADER -->
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
      <div class="mb-1 text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Manajemen Produk</div>
      <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl"><?= ($_SESSION['user']['role'] ?? '') === 'pimpinan' ? 'Data Produk' : 'Kelola Produk' ?></h1>
      <p class="mt-1 text-sm text-slate-500"><?= ($_SESSION['user']['role'] ?? '') === 'pimpinan' ? 'Pantau informasi produk, harga, kategori, stok, dan penjualan.' : 'Kelola informasi produk, harga, kategori, stok, dan penjualan.' ?></p>
    </div>
    <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin'], true)): ?>
      <a href="<?= BASE_URL ?>/admin/produk/form" class="btn-primary w-full sm:w-auto">
        <?= masterIconSvg('plus', 'mr-1.5 h-4 w-4') ?> Tambah Produk
      </a>
    <?php endif; ?>
  </div>

  <!-- FILTER -->
  <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1.5fr)_minmax(180px,0.75fr)_minmax(150px,0.6fr)]">
      <div>
        <label for="produk-search" class="label">Cari Produk</label>
        <div class="relative">
          <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400" aria-hidden="true">
            <?= masterIconSvg('search', 'h-5 w-5 fill-none stroke-current') ?>
          </span>
          <input
            id="produk-search"
            type="search"
            x-model="search"
            @input.debounce.750="pagination.page = 1; load()"
            placeholder="Contoh: Beras Merah Pantar atau PRD_..."
            class="input pl-10"
            autocomplete="off">
        </div>
        <p class="form-help">Cari berdasarkan nama, kode produk, deskripsi, atau kategori.</p>
      </div>

      <div>
        <label class="label">Urutkan Berdasarkan</label>
        <select x-gg-select x-model="order_by" @change="pagination.page = 1; load()" class="input">
          <template x-for="opt in orderOptions" :key="opt.value">
            <option :value="opt.value" x-text="opt.label"></option>
          </template>
        </select>
      </div>

      <div>
        <label class="label">Arah Urutan</label>
        <select x-gg-select x-model="order_dir" @change="pagination.page = 1; load()" class="input">
          <option value="DESC">Menurun</option>
          <option value="ASC">Menaik</option>
        </select>
      </div>
    </div>
  </section>

  <!-- DATA -->
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-5">
      <div>
        <h2 class="font-semibold text-slate-800">Daftar Produk</h2>
        <p class="text-xs text-slate-500">Informasi stok dan penjualan diperbarui dari sistem.</p>
      </div>
      <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
        <span x-text="pagination.total"></span> produk
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="table min-w-[1050px]">
        <thead>
          <tr class="bg-slate-50">
            <th class="w-14 text-center">No</th>
            <th class="min-w-72">Produk</th>
            <th class="min-w-48">Harga & Stok</th>
            <th class="min-w-56">Deskripsi</th>
            <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin'], true)): ?>
              <th class="w-40 text-right">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <template x-if="produk.length === 0">
            <tr>
              <td colspan="5" class="py-14 text-center">
                <div class="mx-auto max-w-sm">
                  <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <?= masterIconSvg('category', 'h-6 w-6 fill-none stroke-current') ?>
                  </div>
                  <p class="font-semibold text-slate-800" x-text="search ? 'Produk tidak ditemukan' : 'Belum ada produk'"></p>
                  <p class="mt-1 text-sm text-slate-500" x-text="search ? 'Coba kata kunci lain atau hapus pencarian.' : 'Tambahkan produk pertama untuk mulai mengelola katalog.'"></p>
                  <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?><a x-show="!search" href="<?= BASE_URL ?>/admin/produk/form" class="btn-primary mt-4 inline-flex">Tambah Produk</a><?php endif; ?>
                </div>
              </td>
            </tr>
          </template>

          <template x-for="(item, idx) in produk" :key="item.kode_produk">
            <tr class="transition hover:bg-slate-50/80">
              <td class="text-center text-slate-500" x-text="(pagination.page - 1) * pagination.limit + idx + 1"></td>
              <td>
                <div class="flex items-center gap-3">
                  <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                    <template x-if="item.gambar">
                      <img :src="`${BASE_URL}/uploads/${item.gambar}`" :alt="item.nama_produk" class="h-full w-full object-cover" loading="lazy">
                    </template>
                    <template x-if="!item.gambar">
                      <div class="flex h-full items-center justify-center text-[11px] text-slate-400">Tanpa gambar</div>
                    </template>
                  </div>
                  <div class="min-w-0">
                    <div class="font-semibold text-slate-800" x-text="item.nama_produk"></div>
                    <div class="mt-0.5 text-xs text-slate-500" x-text="item.kode_produk"></div>
                    <div class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600" x-text="item.nama_kategori || 'Tanpa kategori'"></div>
                  </div>
                </div>
              </td>
              <td>
                <div class="font-semibold text-slate-800" x-text="Alpine.store('utils').formatRupiah(item.harga_jual)"></div>
                <div class="mt-1 flex flex-wrap gap-1.5 text-xs">
                  <span class="rounded-full bg-emerald-50 px-2 py-1 font-medium text-emerald-700">Stok: <span x-text="item.stok"></span></span>
                  <span class="rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-600">Terjual: <span x-text="item.terjual"></span></span>
                </div>
                <p class="mt-1.5 text-xs text-slate-500">Asal: <span x-text="item.asal_produk || '-'"></span></p>
              </td>
              <td>
                <p class="line-clamp-3 text-sm leading-6 text-slate-600" :title="item.deskripsi || '-'" x-text="item.deskripsi || 'Tidak ada deskripsi.'"></p>
              </td>
              <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin'], true)): ?>
                <td>
                  <div class="flex flex-wrap justify-end gap-2">
                    <a :href="BASE_URL + '/admin/produk/form?k=' + encodeURIComponent(item.kode_produk)" class="rounded-lg px-3 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-50">Edit</a>
                    <button type="button" @click="hapus(item.kode_produk)" class="rounded-lg px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                  </div>
                </td>
              <?php endif; ?>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="flex flex-col gap-4 border-t border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
      <div class="text-sm text-slate-500">
        Menampilkan
        <select x-gg-select x-model.number="pagination.limit"
          @change="pagination.page=1; load()"
          class="input mx-1 inline-block w-auto py-2">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
        </select>
        dari <strong class="font-semibold text-slate-700" x-text="pagination.total"></strong>
      </div>

      <div class="flex items-center gap-2">
        <button type="button"
          @click="pagination.page--; load()"
          :disabled="pagination.page <= 1"
          class="btn-outline px-3 py-2 disabled:cursor-not-allowed disabled:opacity-50">
          <?= masterIconSvg('arrow-left', 'h-4 w-4') ?> <span class="hidden sm:inline">Prev</span>
        </button>

        <div class="flex items-center gap-1">
          <template x-for="page in pagination.total_pages" :key="page">
            <button type="button"
              x-show="pagination.total_pages <= 7 || page === 1 || page === pagination.total_pages || Math.abs(page-pagination.page) <= 1"
              @click="pagination.page=page; load()"
              :class="pagination.page === page
                    ? 'border-emerald-600 bg-emerald-600 text-white'
                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
              class="h-9 min-w-9 rounded-lg border px-2 text-sm font-semibold">
              <span x-text="page"></span>
            </button>
          </template>
        </div>

        <button type="button"
          @click="pagination.page++; load()"
          :disabled="pagination.page >= pagination.total_pages"
          class="btn-outline px-3 py-2 disabled:cursor-not-allowed disabled:opacity-50">
          <span class="hidden sm:inline">Next</span> <?= masterIconSvg('arrow-right', 'h-4 w-4') ?>
        </button>
      </div>
    </div>
  </section>
</div>

<script>
  function produkPage() {
    return {
      produk: [],
      search: '',
      order_by: utils.getQuery('order_by'),
      order_dir: utils.getQuery('order_dir'),
      orderOptions: [{
          label: 'Terbaru',
          value: ''
        }, {
          label: 'Nama Produk',
          value: 'nama_produk'
        },
        {
          label: 'Harga',
          value: 'harga_jual'
        }, {
          label: 'Stok',
          value: 'stok'
        }, {
          label: 'Terjual',
          value: 'terjual'
        }
      ],
      pagination: {
        page: utils.getQuery('page') || 1,
        total: 0,
        total_pages: 1,
        limit: utils.getQuery('limit') || 10
      },
      async init() {
        await this.load()
      },
      async load() {
        try {
          const res = await API.get('/produk/list?search=' + encodeURIComponent(this.search) + '&order_by=' + this.order_by + '&order_dir=' + this.order_dir + '&limit=' + this.pagination.limit + '&page=' + this.pagination.page);
          if (res.success) {
            this.produk = res.data || [];
            this.pagination.total = res.pagination.total || 0;
            this.pagination.total_pages = Math.max(1, Math.ceil(this.pagination.total / this.pagination.limit));
            if (this.pagination.page > this.pagination.total_pages) this.pagination.page = this.pagination.total_pages;
            utils.setQuery('search', this.search);
            utils.setQuery('order_by', this.order_by);
            utils.setQuery('order_dir', this.order_dir);
            utils.setQuery('page', this.pagination.page);
            utils.setQuery('limit', this.pagination.limit)
          } else {
            this.produk = [];
            this.pagination.total = 0;
            this.pagination.total_pages = 1
          }
        } catch (err) {
          console.error(err)
        }
      },
      imageUrl(path) {
        if (!path) return '';
        if (path.startsWith('http')) return path;
        return BASE_URL + path
      },
      async hapus(kode) {
        const ok = await Alpine.store('ui').confirm('Produk akan dihapus dari katalog. Pastikan produk tersebut memang tidak diperlukan lagi. Lanjutkan?');
        if (!ok) return;
        try {
          await API.delete('/produk', {
            kode_produk: kode
          });
          Alpine.store('ui').toast('Produk berhasil dihapus');
          await this.load()
        } catch (err) {
          console.error(err)
        }
      }
    }
  }
</script>
