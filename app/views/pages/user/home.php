<?php
$landingWa = 'https://wa.me/' . preg_replace('/\D/', '', $_ENV['NOMOR_WA'] ?? '');
$landingContact = $landingWa . '?text=' . rawurlencode('Halo Admin GGmart, saya ingin bertanya tentang produk dan layanan toko.');
$landingEscape = fn($value) => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<div class="landing">
  <section class="landing-hero" aria-labelledby="landing-title">
    <div class="user-container landing-hero-grid">
      <div class="landing-hero-copy">
        <p class="landing-eyebrow"><?= masterIconSvg('leaf', 'h-4 w-4') ?> Gerakan beli produk lokal</p>
        <h1 id="landing-title">Dari tanah NTT,<br><span>ke meja Anda.</span></h1>
        <p class="landing-intro">Temukan pangan dan olahan lokal dari jemaat serta UMKM Nusa Tenggara Timur. Belanja kebutuhan Anda, ikut dukung usaha di sekitar kita.</p>
        <div class="landing-hero-actions">
          <a href="<?= BASE_URL ?>/produk" class="landing-button">Mulai Belanja <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></a>
          <a href="<?= BASE_URL ?>/tentang" class="landing-text-link">Kenali GGmart <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></a>
        </div>
        <form action="<?= BASE_URL ?>/produk" method="get" class="landing-search" role="search">
          <label for="landing-search" class="sr-only">Cari produk GGmart</label>
          <?= masterIconSvg('search', 'h-5 w-5') ?>
          <input type="search" id="landing-search" name="search" placeholder="Cari kopi, madu, atau kebutuhan Anda…" maxlength="150">
          <button type="submit" aria-label="Cari produk di katalog"><?= masterIconSvg('arrow-right', 'h-5 w-5') ?></button>
        </form>
        <div class="landing-search-hints"><span>Coba cari:</span>
          <?php foreach (['Kopi', 'Madu', 'Gula', 'Kue'] as $keyword): ?>
            <a href="<?= BASE_URL ?>/produk?search=<?= rawurlencode($keyword) ?>"><?= $keyword ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="landing-showcase" aria-label="Pilihan produk lokal GGmart">
        <div class="landing-showcase-top"><span>PILIHAN DARI NTT</span><span aria-hidden="true">01 — 03</span></div>
        <a href="<?= BASE_URL ?>/produk?search=Kopi%20Amfoang" class="landing-feature landing-feature-coffee">
          <span class="landing-feature-label">DARI AMFOANG</span>
          <img src="<?= BASE_URL ?>/uploads/produk/PRD_60274096744.png" alt="Kemasan Kopi Amfoang" width="500" height="500" fetchpriority="high" decoding="async">
          <span class="landing-feature-caption">Kopi Amfoang <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></span>
        </a>
        <a href="<?= BASE_URL ?>/produk?search=Madu%20Batu" class="landing-feature landing-feature-honey">
          <span class="landing-feature-label">DARI SEMAU</span>
          <img src="<?= BASE_URL ?>/uploads/produk/PRD_10996118655.png" alt="Botol Madu Batu dari Klasis Semau" width="500" height="500" decoding="async">
          <span class="landing-feature-caption">Madu Batu <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></span>
        </a>
        <div class="landing-local-note"><?= masterIconSvg('leaf', 'h-6 w-6') ?><span>Potensi daerah.<br><strong>Pilihan kita bersama.</strong></span></div>
        <a href="<?= BASE_URL ?>/produk?search=Gula%20Semut" class="landing-feature landing-feature-sugar">
          <span class="landing-feature-label">OLAHAN LOKAL</span>
          <img src="<?= BASE_URL ?>/uploads/produk/PRD_55071163152.png" alt="Kemasan Gula Semut" width="500" height="500" decoding="async">
          <span class="landing-feature-caption">Gula Semut <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></span>
        </a>
      </div>
    </div>
  </section>

  <div class="landing-service-strip">
    <div class="user-container landing-services">
      <?php foreach ([['users', 'Dukung usaha lokal', 'Produk jemaat & UMKM NTT'], ['home', 'Ambil di toko', 'Pesan online, ambil di GGmart'], ['truck', 'Pengiriman lokal', 'Tarif sesuai kelurahan di Kupang']] as [$icon, $heading, $text]): ?>
        <div class="landing-service"><?= masterIconSvg($icon, 'h-6 w-6') ?><div><strong><?= $heading ?></strong><span><?= $text ?></span></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <section id="pilihan-produk" class="user-container landing-section" x-data="landingProducts()" aria-labelledby="pilihan-title">
    <div class="landing-section-heading">
      <div>
        <p class="landing-eyebrow">Dari pelaku usaha lokal</p>
        <h2 id="pilihan-title">Pilihan untuk hari Anda.</h2>
        <p>Kenali produk, lihat harganya, temukan yang Anda suka.</p>
      </div>
      <a href="<?= BASE_URL ?>/produk" class="landing-text-link">Lihat semua produk <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></a>
    </div>
    <div x-show="loading" class="landing-product-grid" aria-label="Memuat produk pilihan" :aria-busy="loading">
      <?php for ($i = 0; $i < 6; $i++): ?><div class="landing-skeleton animate-pulse">
          <div></div><span></span><span></span>
        </div><?php endfor; ?>
    </div>
    <div x-show="error" x-cloak class="landing-product-message" role="status">
      <h3>Produk belum dapat dimuat.</h3>
      <p>Periksa koneksi Anda, lalu coba lagi.</p><button type="button" @click="load()" class="landing-button">Coba Lagi</button>
    </div>
    <div x-show="!loading && !error && items.length === 0" x-cloak class="landing-product-message">
      <h3>Pilihan lokal akan hadir di sini.</h3>
      <p>Sementara itu, jelajahi produk lainnya di katalog GGmart.</p><a href="<?= BASE_URL ?>/produk" class="landing-button">Buka Katalog</a>
    </div>
    <div x-show="!loading && !error && items.length > 0" x-cloak>
      <div class="landing-product-grid">
        <template x-for="item in visibleItems" :key="item.kode_produk">
          <a :href="BASE_URL + '/produk/' + item.kode_produk" class="landing-product-card">
            <div class="landing-product-media">
              <span class="landing-product-tag">Produk lokal</span>
              <img x-show="item.gambar && !failedImages[item.kode_produk]" :src="item.gambar ? BASE_URL + '/uploads/' + item.gambar.replace(/^\/+/, '') : ''" :alt="item.nama_produk" loading="lazy" decoding="async" @error="failedImages[item.kode_produk] = true">
              <div x-show="!item.gambar || failedImages[item.kode_produk]" class="landing-image-fallback"><?= masterIconSvg('product', 'h-10 w-10') ?><span>Foto belum tersedia</span></div>
              <span class="landing-product-arrow" aria-hidden="true"><?= masterIconSvg('arrow-right', 'h-5 w-5') ?></span>
            </div>
            <div class="landing-product-copy">
              <p class="landing-product-origin" x-text="item.asal_produk"></p>
              <h3 x-text="item.nama_produk"></h3>
              <div class="landing-product-bottom"><strong x-text="$store.utils.formatRupiah(item.harga_jual)"></strong><span x-text="Number(item.stok) > 0 ? '/ ' + (item.satuan_dasar || 'kemasan') : 'Stok habis'"></span></div>
            </div>
          </a>
        </template>
      </div>
      <div x-show="pages > 1" class="landing-product-pagination">
        <span aria-live="polite" x-text="'Pilihan ' + (page + 1) + ' dari ' + pages"></span>
        <div><button type="button" @click="page--" :disabled="page === 0" aria-label="Produk sebelumnya"><?= masterIconSvg('arrow-left', 'h-5 w-5') ?></button><button type="button" @click="page++" :disabled="page >= pages - 1" aria-label="Produk berikutnya"><?= masterIconSvg('arrow-right', 'h-5 w-5') ?></button></div>
      </div>
    </div>
    <noscript>
      <p class="landing-product-message">Lihat harga dan pilihan produk di <a href="<?= BASE_URL ?>/produk">katalog GGmart</a>.</p>
    </noscript>
  </section>

  <section class="landing-story-section" aria-labelledby="cerita-landing-title">
    <div class="user-container landing-story">
      <figure><img src="<?= BASE_URL ?>/assets/banner-ggmart.png" alt="Toko pangan GGmart milik Sinode GMIT di Kupang" width="592" height="357" loading="lazy">
        <figcaption><?= masterIconSvg('map-pin', 'h-4 w-4') ?> Kota Baru · Kupang, NTT</figcaption>
      </figure>
      <div class="landing-story-copy">
        <p class="landing-eyebrow">Toko Pangan Sinode GMIT</p>
        <h2 id="cerita-landing-title">Ada cerita baik<br>di balik setiap pilihan.</h2>
        <p>GGmart hadir sebagai ruang pemasaran bagi produk jemaat dan UMKM lokal. Kami ingin hasil usaha masyarakat NTT semakin dikenal dan lebih mudah menjangkau pembeli.</p>
        <p>Dari kopi dan madu hingga aneka pangan olahan, mari beri ruang bagi potensi daerah untuk bertumbuh.</p><a href="<?= BASE_URL ?>/tentang" class="landing-button">Cerita Tentang Kami <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></a>
      </div>
    </div>
  </section>

  <section class="user-container landing-section" aria-labelledby="belanja-landing-title">
    <div class="landing-section-heading">
      <div>
        <p class="landing-eyebrow">Belanja tanpa ribet</p>
        <h2 id="belanja-landing-title">Pilih. Pesan. Nikmati.</h2>
        <p>Kebutuhan Anda, dengan cara belanja yang Anda pilih.</p>
      </div>
    </div>
    <div class="landing-steps">
      <?php foreach ([['01', 'Pilih yang Anda suka', 'Cari produk di katalog, periksa detailnya, lalu masukkan ke keranjang.'], ['02', 'Atur pesanan Anda', 'Pilih ambil di toko atau pengiriman lokal, kemudian pilih pembayaran yang tersedia.'], ['03', 'Pantau sampai selesai', 'Lihat perkembangan pesanan melalui akun Anda. Kami siapkan pesanan untuk diambil atau dikirim.']] as [$number, $heading, $text]): ?>
        <div class="landing-step"><span><?= $number ?></span>
          <h3><?= $heading ?></h3>
          <p><?= $text ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="landing-faq-section" aria-labelledby="faq-landing-title">
    <div class="user-container landing-faq">
      <div>
        <p class="landing-eyebrow">Sebelum berbelanja</p>
        <h2 id="faq-landing-title">Ada yang ingin <br>Anda tanyakan?</h2>
        <p>Kami bantu Anda mengenal layanan GGmart.</p><a href="<?= $landingEscape($landingContact) ?>" target="_blank" rel="noopener noreferrer" class="landing-text-link">Tanya Admin <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></a>
      </div>
      <div class="landing-faq-list">
        <?php foreach (
          [
            ['Bagaimana cara membeli produk?', 'Anda dapat berbelanja langsung di toko atau melalui website. Untuk memesan online, masuk ke akun pelanggan, pilih produk, lalu lanjutkan dari keranjang ke checkout.'],
            ['Bisa ambil sendiri atau diantar?', 'Bisa. Pilih ambil di toko atau pengiriman lokal saat checkout. Untuk pengiriman, pilih kecamatan dan kelurahan; tarifnya akan muncul sebelum pesanan dibuat.'],
            ['Apa saja pilihan pembayarannya?', 'Metode yang tersedia ditampilkan saat checkout. Pembayaran tunai dapat dipilih untuk pengambilan di toko. Transfer manual memerlukan verifikasi bukti pembayaran oleh admin.'],
            ['Bagaimana menawarkan produk sebagai pemasok?', 'Hubungi admin melalui WhatsApp atau datang langsung ke GGmart untuk membicarakan produk dan ketentuan kerja sama pemasok.']
          ] as [$question, $answer]
        ): ?>
          <details>
            <summary><?= $question ?><span class="landing-faq-plus" aria-hidden="true"><?= masterIconSvg('plus', 'h-4 w-4') ?></span></summary>
            <p><?= $answer ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="lokasi" class="user-container landing-section" aria-labelledby="lokasi-landing-title">
    <div class="landing-location">
      <div class="landing-location-copy">
        <p class="landing-eyebrow">Sampai jumpa di GGmart</p>
        <h2 id="lokasi-landing-title">Dekat dengan Anda.<br>Di Kota Kupang.</h2>
        <address>Jl. S. K. Lerik, di samping Kantor Sinode GMIT<br>Kota Baru, Kecamatan Kelapa Lima<br>Kota Kupang, Nusa Tenggara Timur 85228</address>
        <div class="landing-location-actions"><a href="https://maps.app.goo.gl/WWrxTkBhbMsbTTCH7" target="_blank" rel="noopener noreferrer" class="landing-button">Petunjuk Arah <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></a><a href="<?= $landingEscape($landingContact) ?>" target="_blank" rel="noopener noreferrer" class="landing-text-link">Hubungi Admin</a></div>
        <p class="landing-hours-note">Hubungi admin untuk informasi jam buka dan pengambilan pesanan.</p>
      </div>
      <iframe title="Lokasi toko GGmart di Google Maps" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3927.2451329903033!2d123.61511911068168!3d-10.160719039911163!2m3!1f0!2f0!3f0!2m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2c568366993c75cb%3A0x95756d2b31e5b80e!2sGG%20Mart%20Sinode!5e0!3m2!1sid!2sid" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </div>
  </section>
</div>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('landingProducts', () => ({
      items: [],
      loading: true,
      error: false,
      page: 0,
      pending: false,
      failedImages: {},
      get pages() {
        return Math.ceil(this.items.length / 6);
      },
      get visibleItems() {
        return this.items.slice(this.page * 6, (this.page + 1) * 6);
      },
      init() {
        this.load();
      },
      async load() {
        if (this.pending) return;
        this.pending = true;
        this.loading = true;
        this.error = false;
        try {
          const response = await API.get('/produk/landing', false, true);
          if (!Array.isArray(response.data)) throw new Error('Data produk tidak valid');
          this.items = response.data;
          this.page = 0;
        } catch (_) {
          this.items = [];
          this.error = true;
        } finally {
          this.loading = false;
          this.pending = false;
        }
      }
    }));
  });
</script>