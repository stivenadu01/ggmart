<div class="sf-page sf-cart user-container" x-data="checkoutPage()">

  <div class="sf-page-heading">
    <h1 x-text="step === 1 ? 'Keranjang Belanja' : 'Lengkapi Pesanan'"></h1>
    <p x-text="step === 1 ? 'Periksa pilihan Anda sebelum melanjutkan ke checkout.' : 'Tentukan pembayaran dan cara menerima pesanan Anda.'"></p>
    <ol x-show="$store.cart.items.length" class="sf-checkout-steps" aria-label="Langkah pemesanan"><li :class="step === 1 ? 'is-active' : 'is-complete'" :aria-current="step === 1 ? 'step' : null"><span>01</span> Keranjang</li><li :class="step === 2 ? 'is-active' : ''" :aria-current="step === 2 ? 'step' : null"><span>02</span> Pembayaran &amp; Pengiriman</li></ol>
  </div>

  <!-- BELUM LOGIN -->
  <template x-if="!$store.auth.user">
    <div class="user-empty">
      <p class="font-semibold text-slate-800">Login untuk melihat keranjang</p>
      <p class="form-help">Keranjang tersimpan di akun agar pesanan dapat diproses dan dilacak.</p>
      <a :href="BASE_URL + '/login'" class="user-action-primary mt-4 w-auto">Login</a>
    </div>
  </template>

  <!-- SUDAH LOGIN -->
  <template x-if="$store.auth.user">

    <div class="sf-cart-grid">

      <!-- EMPTY -->
      <template x-if="$store.cart.items.length === 0">
        <div class="user-empty">
          <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500" aria-hidden="true"><?= masterIconSvg('cart', 'h-6 w-6') ?></div>
          <p class="mt-4 font-semibold text-slate-800">Keranjang kamu masih kosong</p>
          <p class="form-help">Pilih produk dari katalog untuk mulai berbelanja.</p>
          <a :href="BASE_URL + '/produk'" class="user-action-primary mt-4 w-auto">Lihat Produk</a>
        </div>
      </template>

      <!-- LIST -->
      <div class="sf-cart-list space-y-3" x-show="step === 1">
        <div x-show="$store.cart.items.length" class="sf-cart-list-heading"><h2>Produk pilihan Anda</h2><a href="<?= BASE_URL ?>/produk" class="sf-text-link">Tambah produk <?= masterIconSvg('plus', 'h-4 w-4') ?></a></div>

        <template x-for="item in $store.cart.items" :key="item.id_keranjang">

          <article class="user-card flex flex-col gap-4 p-3 transition hover:shadow-sm sm:flex-row sm:items-center sm:p-4">
            <div class="flex min-w-0 flex-1 items-center gap-3 sm:gap-4">
              <button
                type="button"
                @click="window.location.href = BASE_URL + '/produk/' + encodeURIComponent(item.kode_produk)"
                class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-slate-50 sm:h-20 sm:w-20"
                :aria-label="'Lihat ' + item.nama_produk">
                <img
                  loading="lazy"
                  :src="item.gambar ? `${BASE_URL}/uploads${item.gambar}` : `${BASE_URL}/assets/no-image.svg`"
                  :alt="item.nama_produk"
                  class="h-full w-full object-contain">
              </button>

              <div class="min-w-0 flex-1">
                <h3 class="line-clamp-2 text-sm font-semibold text-slate-900 sm:text-base" x-text="item.nama_produk"></h3>
                <p class="mt-1 text-sm font-semibold text-primary" x-text="$store.utils.formatRupiah(item.harga_jual)"></p>
                <p class="mt-1 text-xs text-slate-500">
                  Stok tersedia: <span class="font-medium" x-text="item.stok"></span>
                </p>
              </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3 sm:border-0 sm:pt-0">
              <div class="user-quantity-control" :aria-label="'Jumlah ' + item.nama_produk">
                <button
                  type="button"
                  @click="if (item.jumlah > 1) { item.jumlah--; $store.cart.update(item); }"
                  :disabled="item.jumlah <= 1"
                  class="user-quantity-button"
                  aria-label="Kurangi jumlah"><?= masterIconSvg('minus', 'h-4 w-4') ?></button>
                <span class="w-8 text-center text-sm font-semibold text-slate-700" x-text="item.jumlah"></span>
                <button
                  type="button"
                  @click="if (item.jumlah < item.stok) { item.jumlah++; $store.cart.update(item); }"
                  :disabled="item.jumlah >= item.stok"
                  class="user-quantity-button"
                  aria-label="Tambah jumlah"><?= masterIconSvg('plus', 'h-4 w-4') ?></button>
              </div>

              <div class="min-w-24 text-right">
                <p class="text-xs text-slate-500">Subtotal</p>
                <p class="mt-0.5 text-sm font-semibold text-slate-900" x-text="$store.utils.formatRupiah(item.harga_jual * item.jumlah)"></p>
              </div>

              <button
                type="button"
                @click="$store.cart.remove(item.id_keranjang)"
                class="user-action-danger h-10 w-10 shrink-0 p-0"
                aria-label="Hapus produk dari keranjang">
                <?= masterIconSvg('close', 'h-4 w-4') ?>
              </button>
            </div>
          </article>
        </template>

      </div>

      <section x-show="step === 2 && $store.cart.items.length > 0" x-cloak class="sf-checkout-form user-card space-y-4 p-4 sm:p-5">
        <h3 class="text-lg font-semibold">Pengambilan dan Pembayaran</h3>
        <p x-show="!config" class="form-help">Pilihan pembayaran sedang dimuat. Muat ulang halaman jika belum tersedia.</p>
        <div class="grid gap-4 sm:grid-cols-2">
          <label class="label">Nama penerima<input class="input mt-2 w-full" x-model.trim="form.nama_penerima" maxlength="150" autocomplete="name"></label>
          <label class="label">Nomor WhatsApp<input type="tel" class="input mt-2 w-full" x-model.trim="form.no_hp_penerima" maxlength="25" autocomplete="tel" placeholder="08..."></label>
          <label class="label">Pengambilan<select x-gg-select class="input mt-2 w-full" x-model="form.metode_pengiriman" @change="changeDelivery()"><option value="ambil">Ambil di tempat — gratis</option><option value="kirim">Pengiriman lokal</option></select></label>
          <label class="label">Pembayaran<select x-gg-select class="input mt-2 w-full" x-model="form.metode_bayar"><option value="transfer" :disabled="!config?.metode.transfer">Transfer manual</option><option value="qris" :disabled="!config?.metode.qris">QRIS</option><option value="tunai" :disabled="form.metode_pengiriman !== 'ambil'">Tunai saat ambil</option></select></label>
        </div>
        <p x-show="config && form.metode_pengiriman === 'kirim' && !config.metode.transfer && !config.metode.qris" class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800">Pembayaran pengiriman belum tersedia. Pilih ambil di tempat dengan tunai atau hubungi admin.</p>
        <p x-show="form.metode_bayar === 'tunai'" class="form-help">Bayar di toko saat mengambil pesanan. Pesanan langsung masuk antrean penyiapan.</p>
        <p x-show="form.metode_pengiriman === 'ambil'" class="form-help">Ambil pesanan di GGmart, Jl. S. K. Lerik, Kota Baru, Kelapa Lima, Kota Kupang.</p>
        <div x-show="form.metode_pengiriman === 'kirim'" class="space-y-3">
          <div class="grid gap-3 sm:grid-cols-2">
            <label class="label block">Kecamatan<select x-gg-select class="input mt-2 w-full" x-model="form.kecamatan" @change="changeDistrict()"><option value="">Pilih kecamatan</option><template x-for="name in kecamatan" :key="name"><option :value="name" x-text="name"></option></template></select></label>
            <label class="label block">Kelurahan<select x-gg-select class="input mt-2 w-full" x-model="form.zona_id" :disabled="!form.kecamatan"><option value="">Pilih kelurahan</option><template x-for="z in kelurahan" :key="z.id"><option :value="z.id" x-text="z.kelurahan"></option></template></select></label>
          </div>
          <label class="label block">Alamat lengkap<textarea class="input mt-2 w-full" x-model.trim="form.alamat_pengiriman" maxlength="1000" rows="3" placeholder="Jalan, nomor rumah, RT/RW, dan patokan"></textarea></label>
          <p class="form-help">Pengantaran menggunakan motor toko. Untuk barang besar atau beberapa karung, hubungi admin sebelum memesan.</p>
        </div>
        <div class="flex justify-between text-sm"><span>Subtotal barang</span><span x-text="utils.formatRupiah($store.cart.totalHarga)"></span></div>
        <div class="flex justify-between text-sm"><span>Ongkir</span><span x-text="form.metode_pengiriman === 'kirim' && !zone ? 'Pilih kelurahan dahulu' : utils.formatRupiah(ongkir)"></span></div>
        <p class="text-sm text-slate-500"><span x-text="$store.cart.totalQty"></span> barang dalam pesanan. <button type="button" class="font-semibold text-emerald-700 underline" @click="step = 1">Ubah keranjang</button></p>
      </section>
      <!-- TOTAL -->
      <div x-show="$store.cart.items.length > 0" x-cloak class="sf-cart-summary fixed bottom-0 left-0 z-40 w-full border-t border-slate-200 bg-white/95 p-3 shadow-[0_-8px_24px_rgba(15,23,42,0.08)] backdrop-blur md:relative md:bottom-auto md:left-auto md:z-auto md:mt-3 md:rounded-2xl md:border md:p-4 md:shadow-sm">
        <div class="sf-cart-summary-inner">
          <h2 class="sf-summary-title">Ringkasan pesanan</h2>
          <div class="sf-summary-lines"><p><span>Produk (<span x-text="$store.cart.totalQty"></span>)</span><strong x-text="utils.formatRupiah($store.cart.totalHarga)"></strong></p><p><span>Pengiriman</span><strong x-text="step === 1 ? 'Dipilih saat checkout' : (form.metode_pengiriman === 'kirim' && !zone ? 'Pilih kelurahan' : utils.formatRupiah(ongkir))"></strong></p></div>
          <div>
            <p class="text-xs text-slate-500">Total pembayaran</p>
            <p class="text-xl font-semibold text-primary" x-text="$store.utils.formatRupiah(step === 1 ? $store.cart.totalHarga : total)"></p>
          </div>

          <div class="sf-summary-actions grid grid-cols-2 gap-2">
            <button
              type="button"
              @click="step === 1 ? $store.cart.clear() : step = 1"
              :disabled="$store.cart.items.length === 0"
              class="user-action-danger w-full md:w-auto"
              >
              <span x-text="step === 1 ? 'Hapus semua' : 'Kembali'"></span>
            </button>
            <button
              type="button"
              @click="step === 1 ? next() : checkout()"
              :disabled="(step === 2 && !ready) || $store.cart.items.length === 0 || $store.cart.checkingOut"
              class="user-action-primary w-full md:w-auto disabled:cursor-not-allowed disabled:opacity-50"
              >
              <span x-text="$store.cart.checkingOut ? 'Memproses...' : (step === 1 ? 'Pesan Sekarang' : 'Konfirmasi Pesanan')"></span>
            </button>
          </div>
        </div>
      </div>
    </div>

  </template>

</div>
