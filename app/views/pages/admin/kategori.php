<div
  class="space-y-6 p-4 sm:p-6"
  x-data="kategoriPage()"
  x-init="init()"
  @keydown.escape.window="showModal && closeModal()">

  <!-- PAGE HEADER -->
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
      <p class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-600">
        Manajemen Produk
      </p>
      <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
        <?= ($_SESSION['user']['role'] ?? '') === 'pimpinan' ? 'Data Kategori' : 'Kelola Kategori' ?>
      </h1>
      <p class="mt-1 text-sm text-slate-500">
        <?= ($_SESSION['user']['role'] ?? '') === 'pimpinan' ? 'Lihat kategori pengelompokan produk GG-Mart.' : 'Atur kategori untuk mengelompokkan produk GG-Mart.' ?>
      </p>
    </div>

    <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin'], true)): ?>
      <button type="button" @click="openCreate()" class="btn-primary w-full sm:w-auto">
        <?= masterIconSvg('plus', 'mr-1.5 h-4 w-4') ?>
        Tambah Kategori
      </button>
    <?php endif; ?>
  </div>

  <!-- SEARCH / SUMMARY -->
  <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div class="w-full lg:max-w-md">
        <label for="kategori-search" class="label">Cari Kategori</label>
        <div class="relative">
          <?= masterIconSvg('search', 'pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400') ?>
          <input id="kategori-search" type="search"
            x-model="search"
            @input.debounce.500="pagination.page = 1; load()"
            class="input pl-10"
            placeholder="Cari nama kategori..."
            autocomplete="off">
        </div>
      </div>

      <div class="flex items-center justify-between gap-3 text-sm text-slate-500">
        <span>
          <strong class="font-semibold text-slate-700" x-text="pagination.total"></strong>
          kategori
        </span>
        <button type="button" x-show="search" x-cloak
          @click="search=''; pagination.page=1; load()"
          class="font-semibold text-emerald-600 hover:text-emerald-700">
          Reset
        </button>
      </div>
    </div>
  </section>

  <!-- TABLE -->
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="table min-w-175">
        <thead>
          <tr>
            <th class="w-16 text-center">No</th>
            <th>Nama Kategori</th>
            <th>Deskripsi</th>
            <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin'], true)): ?>
              <th class="w-36 text-right">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>

        <tbody>
          <template x-if="loading">
            <tr>
              <td colspan="4" class="py-14 text-center">
                <div class="flex flex-col items-center gap-3 text-slate-500">
                  <div class="h-7 w-7 animate-spin rounded-full border-2 border-slate-200 border-t-emerald-500"></div>
                  <span class="text-sm">Memuat kategori...</span>
                </div>
              </td>
            </tr>
          </template>

          <template x-if="!loading && kategori.length === 0">
            <tr>
              <td colspan="4" class="py-14 text-center">
                <div class="mx-auto max-w-sm">
                  <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <?= masterIconSvg('category', 'h-6 w-6') ?>
                  </div>
                  <p class="font-semibold text-slate-800"
                    x-text="search ? 'Kategori tidak ditemukan' : 'Belum ada kategori'"></p>
                  <p class="mt-1 text-sm text-slate-500"
                    x-text="search ? 'Coba gunakan kata kunci lain.' : 'Tambahkan kategori pertama untuk mulai mengelompokkan produk.'"></p>
                  <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin'], true)): ?>
                    <button x-show="!search" type="button" @click="openCreate()" class="btn-primary mt-4">
                      Tambah Kategori
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          </template>

          <template x-for="(item, idx) in kategori" :key="item.id_kategori">
            <tr class="transition hover:bg-slate-50/80">
              <td class="text-center text-slate-500"
                x-text="(pagination.page - 1) * pagination.limit + idx + 1"></td>
              <td>
                <div class="font-semibold text-slate-800" x-text="item.nama_kategori"></div>
              </td>
              <td>
                <div class="max-w-xl truncate text-sm text-slate-500"
                  :title="item.deskripsi || '-'"
                  x-text="item.deskripsi || '-'"></div>
              </td>

              <?php if (in_array($_SESSION['user']['role'] ?? '', ['admin'], true)): ?>
                <td>
                  <div class="flex justify-end gap-1.5">
                    <button type="button" @click="edit(item)"
                      class="rounded-lg px-3 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-50">
                      Edit
                    </button>
                    <button type="button" @click="hapus(item.id_kategori)"
                      class="rounded-lg px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                      Hapus
                    </button>
                  </div>
                </td>
              <?php endif; ?>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
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
          :disabled="pagination.page <= 1 || loading"
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
          :disabled="pagination.page >= pagination.total_pages || loading"
          class="btn-outline px-3 py-2 disabled:cursor-not-allowed disabled:opacity-50">
          <span class="hidden sm:inline">Next</span> <?= masterIconSvg('arrow-right', 'h-4 w-4') ?>
        </button>
      </div>
    </div>
  </section>

  <!-- MODAL FORM -->
  <template x-if="showModal">
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/45 p-4 backdrop-blur-[1px]"
      @click.self="closeModal()" role="dialog" aria-modal="true">
      <div class="relative z-[101] max-h-[calc(100dvh-2rem)] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl"
        @click.stop>

        <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-emerald-600">Kategori Produk</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900 sm:text-xl"
              x-text="editingId ? 'Edit Kategori' : 'Tambah Kategori'"></h2>
            <p class="mt-1 text-sm text-slate-500">
              Lengkapi informasi kategori produk.
            </p>
          </div>
          <button type="button" @click="closeModal()"
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700"
            aria-label="Tutup modal"><?= masterIconSvg('close', 'h-4 w-4') ?></button>
        </div>

        <form @submit.prevent="save" class="space-y-5 p-5 sm:p-6">
          <div>
            <label for="nama-kategori" class="label">Nama Kategori <span class="text-red-500">*</span></label>
            <input id="nama-kategori" type="text" x-model.trim="form.nama_kategori"
              class="input" maxlength="100" placeholder="Contoh: Makanan" required>
          </div>

          <div>
            <label for="deskripsi-kategori" class="label">Deskripsi</label>
            <textarea id="deskripsi-kategori" x-model.trim="form.deskripsi"
              class="input min-h-28 resize-y" rows="4" maxlength="500"
              placeholder="Jelaskan jenis produk dalam kategori ini..."></textarea>
            <div class="mt-1.5 text-right text-xs text-slate-400"
              x-text="(form.deskripsi || '').length + '/500'"></div>
          </div>

          <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
            <button type="button" @click="closeModal()" class="btn-secondary w-full sm:w-auto">Batal</button>
            <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="saving">
              <span x-show="!saving" x-text="editingId ? 'Simpan Perubahan' : 'Tambah Kategori'"></span>
              <span x-show="saving">Menyimpan...</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </template>
</div>

<script>
  function kategoriPage() {
    return {
      kategori: [],
      search: '',
      loading: false,
      saving: false,
      pagination: {
        page: 1,
        total: 0,
        total_pages: 1,
        limit: 10
      },
      showModal: false,
      editingId: null,
      form: {
        nama_kategori: '',
        deskripsi: ''
      },

      async init() {
        await this.load()
      },

      async load() {
        this.loading = true
        try {
          const url = '/kategori/list?search=' + encodeURIComponent(this.search) +
            '&limit=' + this.pagination.limit +
            '&page=' + this.pagination.page
          const res = await API.get(url)

          if (res.success) {
            this.kategori = res.data || []
            this.pagination.total = Number(res.pagination?.total || 0)
            this.pagination.total_pages = Math.max(1, Math.ceil(this.pagination.total / this.pagination.limit))
          } else {
            this.kategori = []
            this.pagination.total = 0
            this.pagination.total_pages = 1
          }
        } catch (err) {
          console.error(err)
        } finally {
          this.loading = false
        }
      },

      openCreate() {
        this.editingId = null
        this.form = {
          nama_kategori: '',
          deskripsi: ''
        }
        this.showModal = true
        this.$nextTick(() => document.getElementById('nama-kategori')?.focus())
      },

      edit(item) {
        this.editingId = item.id_kategori
        this.form = {
          nama_kategori: item.nama_kategori || '',
          deskripsi: item.deskripsi || ''
        }
        this.showModal = true
        this.$nextTick(() => document.getElementById('nama-kategori')?.focus())
      },

      closeModal() {
        this.showModal = false
        this.editingId = null
        this.form = {
          nama_kategori: '',
          deskripsi: ''
        }
        this.saving = false
      },

      async save() {
        if (!this.form.nama_kategori.trim() || this.saving) return
        this.saving = true
        try {
          if (this.editingId) {
            await API.put('/kategori?id=' + this.editingId, {
              ...this.form
            })
            Alpine.store('ui').toast('Kategori berhasil diperbarui')
          } else {
            await API.post('/kategori', {
              ...this.form
            })
            Alpine.store('ui').toast('Kategori berhasil ditambahkan')
          }
          this.closeModal()
          await this.load()
        } catch (err) {
          console.error(err)
        } finally {
          this.saving = false
        }
      },

      async hapus(id) {
        const ok = await Alpine.store('ui').confirm('Yakin ingin menghapus kategori ini?')
        if (!ok) return
        try {
          await API.delete('/kategori?id=' + id)
          Alpine.store('ui').toast('Kategori berhasil dihapus')
          if (this.kategori.length === 1 && this.pagination.page > 1) this.pagination.page--
          await this.load()
        } catch (err) {
          console.error(err)
        }
      }
    }
  }
</script>
