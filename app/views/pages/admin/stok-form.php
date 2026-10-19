<div
  x-data="stokFormPage()"
  x-init="init()"
  class="min-h-dvh bg-slate-50 p-4 sm:p-6">

  <div class="mx-auto max-w-3xl">
    <!-- HEADER -->
    <header class="mb-5">
      <a href="<?= BASE_URL ?>/admin/stok" class="btn-link inline-flex items-center text-sm font-semibold">
        <?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali ke Stok
      </a>

      <div class="mt-4">
        <div class="admin-eyebrow">Manajemen Persediaan</div>
        <h1 class="admin-page-title">Tambah Perubahan Stok</h1>
        <p class="admin-page-subtitle">
          Catat stok masuk atau stok keluar melalui alur batch yang sudah digunakan sistem.
        </p>
      </div>
    </header>

    <main class="admin-card overflow-visible">
      <!-- STEPS -->
      <div class="border-b border-slate-200 px-4 py-4 sm:px-6">
        <ol class="flex items-center gap-3 text-sm" aria-label="Tahapan perubahan stok">
          <li class="flex items-center gap-2" :class="step === 1 ? 'font-semibold text-emerald-700' : 'text-slate-400'">
            <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
              :class="step === 1 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">1</span>
            Produk & jenis
          </li>
          <li class="text-slate-300" aria-hidden="true"><?= masterIconSvg('arrow-right', 'h-4 w-4') ?></li>
          <li class="flex items-center gap-2" :class="step === 2 ? 'font-semibold text-emerald-700' : 'text-slate-400'">
            <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
              :class="step === 2 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500'">2</span>
            Detail perubahan
          </li>
        </ol>
      </div>

      <form @submit.prevent="submit" class="p-4 sm:p-6">
        <!-- ERROR -->
        <div x-show="error" x-cloak class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
          <p class="font-semibold">Periksa kembali data.</p>
          <p class="mt-1" x-text="error"></p>
        </div>

        <!-- STEP 1 -->
        <section x-show="step === 1" x-cloak class="space-y-6" aria-labelledby="step1-title">
          <div>
            <h2 id="step1-title" class="text-lg font-bold text-slate-900">Pilih produk dan jenis perubahan</h2>
            <p class="mt-1 text-sm text-slate-500">Tentukan produk terlebih dahulu sebelum mengisi detail stok.</p>
          </div>

          <!-- TYPE -->
          <fieldset class="form-group">
            <legend class="label">Jenis Perubahan *</legend>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <button
                type="button"
                @click="selectType('masuk')"
                :class="form.type === 'masuk'
                  ? 'border-emerald-600 bg-emerald-50 text-emerald-700 ring-2 ring-emerald-600/10'
                  : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                class="min-h-20 rounded-xl border p-4 text-left transition">
                <span class="block font-semibold">Stok Masuk</span>
                <span class="mt-1 block text-xs leading-5 opacity-75">Membuat batch stok baru dan menetapkan HPP.</span>
              </button>

              <button
                type="button"
                @click="selectType('keluar')"
                :class="form.type === 'keluar'
                  ? 'border-red-600 bg-red-50 text-red-700 ring-2 ring-red-600/10'
                  : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                class="min-h-20 rounded-xl border p-4 text-left transition">
                <span class="block font-semibold">Stok Keluar</span>
                <span class="mt-1 block text-xs leading-5 opacity-75">Mengurangi sisa stok dari batch yang dipilih.</span>
              </button>
            </div>
            <p class="form-help">Pilih satu jenis perubahan. Jenis ini menentukan detail yang harus diisi pada langkah berikutnya.</p>
          </fieldset>

          <!-- PRODUCT -->
          <div class="relative form-group">
            <label for="produk-search" class="label">Produk *</label>
            <input
              id="produk-search"
              type="search"
              x-model="produkQuery"
              @focus="produkList.length ? produkOpen = true : loadProduk()"
              @input.debounce.350="loadProduk()"
              placeholder="Contoh: Beras Merah Pantar"
              class="input"
              autocomplete="off"
              aria-describedby="produk-help">

            <p id="produk-help" class="form-help">Ketik nama atau kode produk, lalu pilih produk dari hasil pencarian.</p>

            <div
              x-show="produkOpen"
              x-cloak
              @mousedown.outside="produkOpen=false"
              class="absolute inset-x-0 top-full z-30 mt-1 max-h-72 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl">

              <div x-show="loadingProduk" class="p-4 text-sm text-slate-500">
                Mencari produk...
              </div>

              <template x-if="!loadingProduk && produkList.length === 0">
                <div class="p-4 text-sm text-slate-500">
                  Produk tidak ditemukan. Coba nama atau kode lain.
                </div>
              </template>

              <template x-for="p in produkList" :key="p.kode_produk">
                <button
                  type="button"
                  @click="selectProduk(p)"
                  class="flex w-full items-center justify-between gap-4 border-b border-slate-100 p-3 text-left transition last:border-0 hover:bg-slate-50">
                  <span class="min-w-0">
                    <span class="block truncate font-semibold text-slate-800" x-text="p.nama_produk"></span>
                    <span class="mt-0.5 block text-xs text-slate-500" x-text="p.kode_produk"></span>
                  </span>
                  <span class="shrink-0 text-xs font-medium text-slate-400" x-text="p.satuan_dasar || 'unit'"></span>
                </button>
              </template>
            </div>

            <div x-show="form.kode_produk" x-cloak class="mt-3 flex items-center justify-between gap-3 rounded-xl bg-emerald-50 p-3">
              <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Produk terpilih</p>
                <p class="mt-1 truncate font-semibold text-emerald-900" x-text="form.nama_produk"></p>
                <p class="text-xs text-emerald-700" x-text="form.kode_produk"></p>
              </div>
              <button type="button" @click="clearProduk()" class="admin-action-secondary admin-action-sm shrink-0">Ganti</button>
            </div>
          </div>

          <div class="flex justify-end border-t border-slate-200 pt-5">
            <button type="button" @click="next()" class="btn-primary w-full sm:w-auto">
              Lanjut ke Detail <?= masterIconSvg('arrow-right', 'h-4 w-4') ?>
            </button>
          </div>
        </section>

        <!-- STEP 2 -->
        <section x-show="step === 2" x-cloak class="space-y-6" aria-labelledby="step2-title">
          <div>
            <h2 id="step2-title" class="text-lg font-bold text-slate-900">Detail perubahan stok</h2>
            <p class="mt-1 text-sm text-slate-500">
              <span x-text="form.type === 'masuk' ? 'Buat batch stok baru dengan jumlah dan HPP per satuan.' : 'Pilih batch yang akan dikurangi, lalu tentukan jumlah stok keluar.'"></span>
            </p>
          </div>

          <!-- PRODUCT CONTEXT -->
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Produk</p>
            <div class="mt-1 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
              <p class="font-semibold text-slate-800" x-text="form.nama_produk"></p>
              <p class="text-xs text-slate-500" x-text="form.kode_produk"></p>
            </div>
          </div>

          <!-- QUANTITY -->
          <div class="form-group">
            <label for="stok-jumlah" class="label">Jumlah *</label>
            <input
              id="stok-jumlah"
              type="number"
              min="1"
              step="1"
              x-model.number="form.jumlah"
              class="input"
              placeholder="Contoh: 50">
            <p class="form-help">Masukkan jumlah stok dalam satuan dasar produk. Gunakan bilangan bulat lebih dari 0.</p>
          </div>

          <!-- INCOMING -->
          <template x-if="form.type === 'masuk'">
            <div class="space-y-5">
              <div class="form-group">
                <label for="stok-hpp" class="label">Harga Pokok per Satuan *</label>
                <div class="relative">
                  <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-semibold text-slate-400">Rp</span>
                  <input
                    id="stok-hpp"
                    type="number"
                    min="0"
                    step="0.01"
                    x-model.number="form.harga_pokok"
                    class="input pl-10"
                    placeholder="Contoh: 18000">
                </div>
                <p class="form-help">Masukkan harga pokok per satuan untuk batch stok ini, tanpa titik atau simbol Rupiah.</p>
              </div>

              <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-sm font-semibold text-emerald-900">Batch baru</p>
                <p class="mt-1 text-sm leading-6 text-emerald-800">
                  Sistem akan menyimpan jumlah sebagai sisa stok batch. Tanggal masuk dicatat otomatis saat data disimpan.
                </p>
              </div>
            </div>
          </template>

          <!-- OUTGOING -->
          <template x-if="form.type === 'keluar'">
            <div class="space-y-5">
              <div class="relative form-group">
                <label for="batch-search" class="label">Batch Stok *</label>
                <input
                  id="batch-search"
                  type="search"
                  x-model="mutasiQuery"
                  @focus="mutasiOpen=true"
                  @input="filterMutasi()"
                  placeholder="Contoh: pilih batch berdasarkan tanggal masuk"
                  class="input"
                  autocomplete="off">
                <p class="form-help">Pilih batch yang masih memiliki sisa stok. HPP akan mengikuti batch yang dipilih.</p>

                <div
                  x-show="mutasiOpen"
                  x-cloak
                  @mousedown.outside="mutasiOpen=false"
                  class="absolute inset-x-0 top-full z-30 mt-1 max-h-72 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl">

                  <template x-if="mutasiFiltered.length === 0">
                    <div class="p-4 text-sm text-slate-500">
                      Tidak ada batch aktif untuk produk ini.
                    </div>
                  </template>

                  <template x-for="m in mutasiFiltered" :key="m.id_mutasi">
                    <button
                      type="button"
                      @click="selectMutasi(m)"
                      class="w-full border-b border-slate-100 p-3 text-left transition last:border-0 hover:bg-slate-50">
                      <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-slate-800" x-text="utils.formatDateTime(m.tanggal)"></span>
                        <span class="status-success">
                          Sisa <span x-text="m.sisa_stok"></span>
                        </span>
                      </div>
                      <div class="mt-1 text-xs text-slate-500">
                        HPP:
                        <span class="font-medium text-slate-700" x-text="Alpine.store('utils').formatRupiah(m.harga_pokok)"></span>
                      </div>
                    </button>
                  </template>
                </div>

                <div x-show="form.id_mutasi" x-cloak class="mt-3 rounded-xl bg-slate-50 p-4">
                  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                      <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Batch dipilih</p>
                      <p class="mt-1 text-sm font-semibold text-slate-800" x-text="mutasiQuery"></p>
                    </div>
                    <div>
                      <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Sisa</p>
                      <p class="mt-1 font-semibold text-emerald-700">
                        <span x-text="selectedBatch.sisa_stok"></span>
                        <span class="text-xs text-slate-500" x-text="form.satuan_dasar || 'unit'"></span>
                      </p>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                      <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">HPP</p>
                      <p class="mt-1 text-sm font-semibold text-slate-800" x-text="Alpine.store('utils').formatRupiah(form.harga_pokok)"></p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-900">Perhatikan batch</p>
                <p class="mt-1 text-sm leading-6 text-amber-800">
                  Jumlah stok keluar tidak boleh melebihi sisa stok pada batch yang dipilih.
                </p>
              </div>
            </div>
          </template>

          <!-- NOTE -->
          <div class="form-group">
            <label for="stok-keterangan" class="label">Keterangan <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
            <textarea
              id="stok-keterangan"
              x-model="form.keterangan"
              rows="3"
              class="input min-h-24 resize-y"
              placeholder="Contoh: Restok dari pemasok lokal"></textarea>
            <p class="form-help">Tambahkan catatan yang membantu menjelaskan alasan perubahan stok.</p>
          </div>

          <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-between">
            <button type="button" @click="step=1" :disabled="submitting" class="admin-action-secondary w-full sm:w-auto">
              <?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali
            </button>

            <button type="submit" :disabled="submitting" class="btn-primary w-full sm:w-auto">
              <span x-show="!submitting">Simpan Perubahan</span>
              <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                <?= masterIconSvg('spinner', 'h-4 w-4 animate-spin') ?>
                Menyimpan...
              </span>
            </button>
          </div>
        </section>
      </form>
    </main>
  </div>
</div>

<script>
  function stokFormPage() {
    return {
      step: 1,
      submitting: false,
      error: '',

      form: {
        kode_produk: '',
        nama_produk: '',
        satuan_dasar: '',
        type: 'masuk',
        jumlah: '',
        harga_pokok: '',
        id_mutasi: '',
        keterangan: ''
      },

      produkQuery: '',
      produkList: [],
      produkOpen: false,
      loadingProduk: false,

      mutasiQuery: '',
      mutasiList: [],
      mutasiFiltered: [],
      mutasiOpen: false,
      selectedBatch: {},

      async init() {
        const p = utils.getQuery('p')
        if (!p) return

        try {
          const res = await API.get('/produk/detail?k=' + encodeURIComponent(p))
          if (res.success && res.data) {
            this.selectProduk(res.data)
            this.selectType('masuk')
            this.step = 2
          }
        } catch (err) {
          console.error(err)
        }
      },

      selectType(type) {
        this.form.type = type
        this.error = ''

        if (type !== 'keluar') {
          this.form.id_mutasi = ''
          this.mutasiQuery = ''
          this.selectedBatch = {}
        }
      },

      async loadProduk() {
        this.loadingProduk = true
        try {
          const res = await API.get('/produk/dropdown?search=' + encodeURIComponent(this.produkQuery))
          this.produkList = res.data || []
          this.produkOpen = true
        } catch (err) {
          console.error(err)
          this.produkList = []
          this.error = err.message || 'Produk tidak dapat dimuat.'
        } finally {
          this.loadingProduk = false
        }
      },

      selectProduk(p) {
        this.form.kode_produk = p.kode_produk
        this.form.nama_produk = p.nama_produk
        this.form.satuan_dasar = p.satuan_dasar || 'unit'
        this.produkQuery = p.nama_produk
        this.produkOpen = false
        this.error = ''

        this.form.id_mutasi = ''
        this.mutasiQuery = ''
        this.mutasiList = []
        this.mutasiFiltered = []
        this.selectedBatch = {}
      },

      clearProduk() {
        this.form.kode_produk = ''
        this.form.nama_produk = ''
        this.form.satuan_dasar = ''
        this.produkQuery = ''
        this.form.id_mutasi = ''
        this.mutasiQuery = ''
        this.mutasiList = []
        this.mutasiFiltered = []
        this.selectedBatch = {}
        this.step = 1
      },

      async loadMutasi() {
        const res = await API.get('/mutasi/dropdown?kode=' + encodeURIComponent(this.form.kode_produk))
        this.mutasiList = res.data || []
        this.mutasiFiltered = [...this.mutasiList]
      },

      filterMutasi() {
        const q = this.mutasiQuery.toLowerCase().trim()
        this.mutasiFiltered = this.mutasiList.filter(m => {
          const text = [
            utils.formatDateTime(m.tanggal),
            m.harga_pokok,
            m.sisa_stok
          ].join(' ').toLowerCase()

          return text.includes(q)
        })
      },

      selectMutasi(m) {
        this.form.id_mutasi = m.id_mutasi
        this.form.harga_pokok = Number(m.harga_pokok || 0)
        this.selectedBatch = m
        this.mutasiQuery = utils.formatDateTime(m.tanggal)
        this.mutasiOpen = false
        this.error = ''

        if (Number(this.form.jumlah) > Number(m.sisa_stok)) {
          this.form.jumlah = Number(m.sisa_stok)
        }
      },

      async next() {
        this.error = ''

        if (!this.form.type) {
          this.error = 'Pilih jenis perubahan stok terlebih dahulu.'
          return
        }

        if (!this.form.kode_produk) {
          this.error = 'Pilih produk terlebih dahulu.'
          return
        }

        if (this.form.type === 'keluar') {
          try {
            await this.loadMutasi()
          } catch (err) {
            console.error(err)
            this.error = err.message || 'Batch stok tidak dapat dimuat.'
            return
          }

          if (this.mutasiList.length === 0) {
            this.error = 'Tidak ada batch aktif dengan sisa stok untuk produk ini.'
            return
          }
        }

        this.step = 2
      },

      validate() {
        if (!this.form.kode_produk || !this.form.type) {
          return 'Produk dan jenis perubahan wajib dipilih.'
        }

        const jumlah = Number(this.form.jumlah)
        if (!Number.isInteger(jumlah) || jumlah <= 0) {
          return 'Jumlah stok harus berupa bilangan bulat lebih dari 0.'
        }

        if (this.form.type === 'masuk') {
          const hpp = Number(this.form.harga_pokok)
          if (!Number.isFinite(hpp) || hpp < 0) {
            return 'Harga pokok wajib diisi dengan nilai yang valid.'
          }
        }

        if (this.form.type === 'keluar') {
          if (!this.form.id_mutasi) {
            return 'Pilih batch stok terlebih dahulu.'
          }

          if (jumlah > Number(this.selectedBatch.sisa_stok || 0)) {
            return 'Jumlah stok keluar melebihi sisa stok pada batch yang dipilih.'
          }
        }

        return ''
      },

      async submit() {
        this.error = this.validate()
        if (this.error || this.submitting) return

        this.submitting = true

        try {
          const payload = new FormData()
          Object.keys(this.form).forEach(key => payload.append(key, this.form[key] ?? ''))

          if (this.form.type === 'masuk') {
            payload.append('sisa_stok', this.form.jumlah)
          }

          const res = await API.post('/mutasi', payload)

          if (!res.success) {
            throw new Error(res.message || 'Perubahan stok gagal disimpan.')
          }

          Alpine.store('ui').toast('Perubahan stok berhasil disimpan')
          setTimeout(() => {
            window.location.href = '<?= BASE_URL ?>/admin/stok'
          }, 700)
        } catch (err) {
          console.error(err)
          this.error = err.message || 'Terjadi kesalahan saat menyimpan perubahan stok.'
        } finally {
          this.submitting = false
        }
      }
    }
  }
</script>
