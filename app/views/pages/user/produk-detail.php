<div class="sf-page sf-detail user-section" x-data="detailProdukPage()">

  <div class="user-container">
    <!-- BACK -->
    <div class="sf-breadcrumb"><a href="<?= BASE_URL ?>">Beranda</a><span>/</span><a href="<?= BASE_URL ?>/produk">Katalog Produk</a><span>/</span><span>Detail Produk</span></div>

    <!-- LOADING -->
    <div x-show="loading" x-cloak class="grid gap-8 md:grid-cols-2" aria-label="Memuat detail produk" aria-live="polite">
      <div class="aspect-square animate-pulse rounded-3xl bg-slate-100"></div>
      <div class="space-y-4 py-4">
        <div class="h-8 w-4/5 animate-pulse rounded bg-slate-100"></div>
        <div class="h-4 w-full animate-pulse rounded bg-slate-100"></div>
        <div class="h-4 w-5/6 animate-pulse rounded bg-slate-100"></div>
        <div class="h-10 w-2/5 animate-pulse rounded bg-slate-100"></div>
        <div class="h-16 w-full animate-pulse rounded-2xl bg-slate-100"></div>
        <div class="h-11 w-full animate-pulse rounded-xl bg-slate-100"></div>
      </div>
    </div>

    <!-- ERROR / NOT FOUND -->
    <div x-show="!loading && error" x-cloak class="user-empty mx-auto max-w-xl">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-xl text-red-600" aria-hidden="true">!</div>
      <h1 class="mt-4 text-lg font-bold text-slate-900">Produk tidak dapat ditemukan</h1>
      <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500" x-text="errorMessage"></p>
      <div class="mt-5 flex flex-col justify-center gap-3 sm:flex-row">
        <button type="button" @click="retry()" class="user-action-primary">Coba Lagi</button>
        <a :href="BASE_URL + '/produk'" class="user-action-outline">Kembali ke Produk</a>
      </div>
    </div>

    <!-- PRODUCT -->
    <template x-if="produk && !error">
      <div>
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)] lg:items-start lg:gap-12">

          <!-- IMAGE -->
          <div class="lg:sticky lg:top-24">
            <div class="user-product-detail-media aspect-square sm:aspect-auto sm:min-h-[520px]">
              <img
                id="img_detail"
                :src="produk.gambar ? BASE_URL + '/uploads/' + produk.gambar : BASE_URL + '/assets/no-image.svg'"
                :alt="produk.nama_produk"
                class="max-h-[520px] w-full object-contain"
                decoding="async">
            </div>
          </div>

          <!-- INFORMATION -->
          <div class="space-y-5">
            <div>
              <div class="mb-3 flex flex-wrap items-center gap-2">
                <span
                  x-show="produk.asal_produk"
                  x-text="produk.asal_produk"
                  class="status-success">
                </span>
                <span
                  x-show="produk.stok > 0"
                  class="status-neutral">
                  Stok tersedia
                </span>
                <span
                  x-show="produk.stok <= 0"
                  class="status-danger">
                  Stok habis
                </span>
              </div>

              <h1 class="sf-detail-title" x-text="produk.nama_produk"></h1>

              <div class="mt-4 text-2xl font-semibold text-primary sm:text-3xl" x-text="$store.utils.formatRupiah(produk.harga_jual)"></div>
            </div>

            <div class="user-product-info">
              <h2 class="text-sm font-semibold text-slate-900">Tentang produk</h2>
              <p
                class="mt-2 whitespace-pre-wrap text-sm leading-7 text-slate-600"
                x-text="produk.deskripsi || 'Belum ada deskripsi produk.'">
              </p>
            </div>

            <div class="user-product-info">
              <div class="flex items-center justify-between gap-4">
                <div>
                  <p class="text-sm font-semibold text-slate-900">Jumlah</p>
                  <p class="form-help">Maksimal sesuai stok yang tersedia.</p>
                </div>

                <div class="user-quantity-control" aria-label="Pilih jumlah produk">
                  <button
                    type="button"
                    @click="qty = Math.max(1, qty - 1)"
                    :disabled="qty <= 1 || produk.stok <= 0"
                    class="user-quantity-button"
                    aria-label="Kurangi jumlah"><?= masterIconSvg('minus', 'h-4 w-4') ?></button>
                  <span class="w-10 text-center text-sm font-semibold text-slate-900" x-text="qty"></span>
                  <button
                    type="button"
                    @click="qty = Math.min(produk.stok, qty + 1)"
                    :disabled="qty >= produk.stok || produk.stok <= 0"
                    class="user-quantity-button"
                    aria-label="Tambah jumlah"><?= masterIconSvg('plus', 'h-4 w-4') ?></button>
                </div>
              </div>

              <div class="mt-4 rounded-xl bg-slate-50 px-3 py-3 text-sm text-slate-600">
                <div class="flex items-center justify-between gap-4">
                  <span>Stok tersedia</span>
                  <strong class="text-slate-900" x-text="produk.stok > 0 ? produk.stok : 'Habis'"></strong>
                </div>
              </div>
            </div>

            <div class="user-product-detail-actions">
              <a
                :href="`https://wa.me/${NOMOR_WA}?text=${encodeURIComponent('Halo, saya ingin bertanya tentang ' + produk.nama_produk)}`"
                target="_blank"
                rel="noopener noreferrer"
                class="user-action-outline min-h-11">
                Hubungi Admin
              </a>
              <button
                type="button"
                @click="addToCart()"
                :disabled="produk.stok <= 0 || adding"
                class="user-action-primary min-h-11">
                <span x-show="!adding && produk.stok > 0">+ Tambah ke Keranjang</span>
                <span x-show="adding">Menambahkan...</span>
                <span x-show="!adding && produk.stok <= 0">Stok Habis</span>
              </button>
            </div>

            <div x-show="successMessage" x-cloak class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" x-text="successMessage" role="status"></div>
          </div>
        </div>

        <!-- RELATED -->
        <div class="mt-14 border-t border-slate-200 pt-10 sm:mt-16 sm:pt-12">
          <div class="user-section-header">
            <h2 class="user-section-title">Produk Terkait</h2>
            <p class="user-section-subtitle">Temukan produk lain yang mungkin sesuai dengan pilihanmu.</p>
          </div>

          <div x-show="loadingRelated" x-cloak class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4" aria-label="Memuat produk terkait">
            <template x-for="i in 4" :key="i">
              <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="aspect-square animate-pulse bg-slate-100"></div>
                <div class="space-y-2 p-3">
                  <div class="h-4 w-4/5 animate-pulse rounded bg-slate-100"></div>
                  <div class="h-4 w-2/5 animate-pulse rounded bg-slate-100"></div>
                </div>
              </div>
            </template>
          </div>

          <div x-show="!loadingRelated && produkTerkait.length === 0" x-cloak class="user-empty">
            <p class="font-semibold text-slate-800">Belum ada produk terkait.</p>
            <p class="form-help">Kamu bisa melihat produk lainnya melalui katalog.</p>
            <a :href="BASE_URL + '/produk'" class="user-action-outline mt-4">Lihat Semua Produk</a>
          </div>

          <div x-show="!loadingRelated && produkTerkait.length > 0" class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
            <template x-for="item in produkTerkait" :key="item.kode_produk">
              <article class="sf-product-card group user-card user-card-hover flex min-h-full flex-col overflow-hidden">
                <button type="button" @click="goDetail(item.kode_produk)" class="aspect-square overflow-hidden bg-slate-50" :aria-label="'Lihat ' + item.nama_produk">
                  <img
                    loading="lazy"
                    decoding="async"
                    :src="item.gambar ? BASE_URL + '/uploads' + item.gambar : BASE_URL + '/assets/no-image.svg'"
                    :alt="item.nama_produk"
                    class="h-full w-full object-contain transition duration-200 group-hover:scale-105">
                </button>

                <div class="sf-card-content flex flex-1 flex-col p-3">
                  <h3 class="line-clamp-2 text-sm font-medium text-slate-900" x-text="item.nama_produk"></h3>
                  <p class="mt-1 font-poppins font-semibold text-primary" x-text="$store.utils.formatRupiah(item.harga_jual)"></p>
                  <p x-show="item.asal_produk" x-text="item.asal_produk" class="mt-1 line-clamp-1 text-xs italic text-slate-500"></p>

                  <div class="sf-card-actions mt-auto grid grid-cols-2 gap-2 pt-3">
                    <button
                      type="button"
                      @click="quickAddToCart(item, $event)"
                      :disabled="item.stok <= 0"
                      class="user-action-primary min-h-9 px-2 py-2 text-xs disabled:cursor-not-allowed disabled:opacity-50">
                      <span x-text="item.stok > 0 ? '+ Keranjang' : 'Habis'"></span>
                    </button>
                    <button
                      type="button"
                      @click="goDetail(item.kode_produk)"
                      :aria-label="'Detail ' + item.nama_produk"
                      class="user-action-outline min-h-9 px-2 py-2 text-xs">
                      <?= masterIconSvg('arrow-right', 'h-4 w-4') ?>
                    </button>
                  </div>
                </div>
              </article>
            </template>
          </div>
        </div>
      </div>
    </template>
  </div>
</div>

<script>
  function detailProdukPage() {
    return {
      produk: null,
      produkTerkait: [],
      loading: true,
      error: false,
      errorMessage: 'Silakan coba lagi atau kembali ke katalog produk.',
      loadingRelated: false,
      adding: false,
      qty: 1,
      successMessage: '',

      async init() {
        const kode = window.location.pathname.split('/').pop()
        await this.loadDetail(kode)
      },

      async loadDetail(kode) {
        this.loading = true
        this.error = false
        this.successMessage = ''

        try {
          const res = await API.get(`/produk/detail?k=${encodeURIComponent(kode)}`)
          this.produk = res.data
          this.qty = this.produk?.stok > 0 ? 1 : 0

          await this.loadRelated(kode)
        } catch (err) {
          console.error(err)
          this.produk = null
          this.error = true
          this.errorMessage = err?.message || 'Produk tidak ditemukan atau sedang tidak dapat dimuat.'
        } finally {
          this.loading = false
        }
      },

      async retry() {
        const kode = window.location.pathname.split('/').pop()
        await this.loadDetail(kode)
      },

      async loadRelated(kodeProduk) {
        this.loadingRelated = true
        try {
          const res = await API.get(`/produk/terkait?k=${encodeURIComponent(kodeProduk)}`, false)
          this.produkTerkait = res.data || []
        } catch (err) {
          console.error(err)
          this.produkTerkait = []
        } finally {
          this.loadingRelated = false
        }
      },

      async addToCart() {
        if (!this.produk || this.produk.stok <= 0 || this.adding) return

        this.adding = true
        this.successMessage = ''
        try {
          const imgEl = document.getElementById('img_detail')
          await Alpine.store('cart').add({
            ...this.produk,
            jumlah: this.qty,
          }, imgEl)
          this.successMessage = `${this.produk.nama_produk} ditambahkan ke keranjang.`
          this.qty = 1
          window.setTimeout(() => this.successMessage = '', 3000)
        } finally {
          this.adding = false
        }
      },

      async quickAddToCart(item, e) {
        if (item.stok <= 0) return
        const card = e.currentTarget.closest('article')
        const imgEl = card?.querySelector('img')
        await Alpine.store('cart').add({
          ...item,
          jumlah: 1
        }, imgEl)
      },

      goDetail(kode) {
        window.location.href = `${BASE_URL}/produk/${encodeURIComponent(kode)}`
      }
    }
  }
</script>
