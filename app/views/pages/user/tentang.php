<?php
$officialStory = 'https://sinodegmit.or.id/berita-gmit/langkah-maju-dalam-pengembangan-ekonomi-sinode-gmit-launching-toko-pangan-gg-mart-dan-garam-bumi-cendana/';
$mapsUrl = 'https://maps.app.goo.gl/WWrxTkBhbMsbTTCH7';
$whatsapp = 'https://wa.me/' . preg_replace('/\D/', '', $_ENV['NOMOR_WA'] ?? '');
$contactUrl = $whatsapp . '?text=' . rawurlencode('Halo Admin GGmart, saya ingin mengetahui informasi toko dan produk yang tersedia.');
$partnerUrl = $whatsapp . '?text=' . rawurlencode('Halo Admin GGmart, saya ingin bertanya tentang kerja sama pemasok produk lokal.');
$escape = fn($value) => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>

<div class="sf-about">
  <section class="border-b border-green-100 bg-[#f5f8f2]">
    <div class="user-container grid items-center gap-10 py-12 sm:py-16 lg:grid-cols-2 lg:gap-14 lg:py-20">
      <div>
        <p class="mb-4 text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Toko Pangan Sinode GMIT</p>
        <h1 class="font-poppins text-4xl! leading-tight! text-slate-900 sm:text-5xl!">Tentang GGmart</h1>
        <p class="mt-5 text-xl font-medium leading-relaxed text-green-800">Dari potensi lokal,<br>untuk kehidupan bersama.</p>
        <p class="mt-5 max-w-xl leading-7 text-slate-600">GGmart adalah toko pangan milik Sinode Gereja Masehi Injili di Timor (GMIT), berlokasi di Kupang, Nusa Tenggara Timur. Kami menjadi ruang pemasaran produk jemaat dan UMKM lokal agar hasil usaha masyarakat lebih mudah menjangkau pembeli.</p>
        <div class="mt-7 flex flex-wrap gap-3">
          <a href="<?= BASE_URL ?>/produk" class="user-action-primary">Jelajahi Produk <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></a>
          <a href="#kunjungi" class="user-action-outline">Kunjungi Toko</a>
        </div>
      </div>
      <figure class="overflow-hidden rounded-2xl border border-green-100 bg-white shadow-sm">
        <img src="<?= BASE_URL ?>/assets/banner-ggmart.png" alt="Tampak depan toko pangan GGmart saat peresmian" width="592" height="357" class="aspect-5/3 w-full object-cover">
        <figcaption class="flex items-center gap-3 px-5 py-4">
          <img src="<?= BASE_URL ?>/assets/logo.png" width="36" height="36" alt="" class="h-9 w-9 object-contain">
          <div><p class="text-sm font-semibold text-slate-900">GGmart · Kupang, NTT</p><p class="mt-0.5 text-xs text-slate-500">Mendukung gerakan beli produk lokal</p></div>
        </figcaption>
      </figure>
    </div>
  </section>

  <section aria-labelledby="cerita-title" class="user-container grid gap-8 py-12 sm:py-16 lg:grid-cols-3 lg:gap-14">
    <div>
      <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-green-700">Cerita Kami</p>
      <h2 id="cerita-title" class="font-poppins text-2xl! leading-snug! text-slate-900">Menghubungkan hasil usaha dengan peluang.</h2>
      <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-green-50 px-4 py-2 text-sm font-medium text-green-800"><?= masterIconSvg('history', 'h-4 w-4') ?> Diresmikan 26 Juni 2025</p>
    </div>
    <div class="space-y-4 leading-7 text-slate-600 lg:col-span-2">
      <p>GGmart diresmikan di samping Kantor Sinode GMIT, Jl. S. K. Lerik, Kota Baru, Kupang. Kehadirannya merupakan bagian dari upaya Sinode GMIT mendampingi usaha jemaat hingga tahap pemasaran, dengan dukungan Bank Indonesia Perwakilan Nusa Tenggara Timur.</p>
      <p>Melalui toko ini, produk pangan dan olahan lokal memiliki ruang untuk diperkenalkan kepada masyarakat. Setiap pilihan untuk berbelanja produk lokal ikut membuka kesempatan bagi para pelaku usaha di daerah.</p>
      <a href="<?= $escape($officialStory) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-semibold text-green-700 underline underline-offset-4 hover:text-green-900">Baca kisah peresmian di Sinode GMIT <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></a>
    </div>
  </section>

  <section aria-labelledby="tujuan-title" class="bg-green-900 text-white">
    <div class="user-container py-12 sm:py-14">
      <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-green-200">Tujuan Kami</p>
      <h2 id="tujuan-title" class="font-poppins text-2xl!">Bertumbuh bersama produk lokal.</h2>
      <div class="mt-8 grid gap-8 md:grid-cols-3 md:gap-10">
        <?php foreach ([
          ['users', 'Mendukung usaha jemaat', 'Memberi ruang pemasaran bagi hasil usaha jemaat dan UMKM lokal.'],
          ['product', 'Mengenalkan potensi NTT', 'Mendekatkan ragam pangan dan olahan daerah kepada masyarakat.'],
          ['cart', 'Memudahkan akses belanja', 'Menghubungkan toko fisik dengan layanan pemesanan melalui website.']
        ] as [$icon, $heading, $text]): ?>
          <div>
            <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-green-200"><?= masterIconSvg($icon, 'h-6 w-6') ?></div>
            <h3 class="text-base! font-semibold text-white"><?= $heading ?></h3>
            <p class="mt-2 text-sm leading-6 text-green-100"><?= $text ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section aria-labelledby="belanja-title" class="user-container grid gap-10 py-12 sm:py-16 lg:grid-cols-2 lg:gap-14">
    <div>
      <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-green-700">Belanja di GGmart</p>
      <h2 id="belanja-title" class="font-poppins text-2xl! text-slate-900">Temukan pilihan untuk kebutuhan Anda.</h2>
      <p class="mt-4 leading-7 text-slate-600">Kenali produk lokal seperti kopi, madu, gula, dan aneka pangan olahan, serta pilihan kebutuhan sehari-hari. Lihat katalog untuk harga dan ketersediaan terbaru.</p>
      <a href="<?= BASE_URL ?>/produk" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-green-700 hover:text-green-900">Lihat Katalog <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></a>
      <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-6">
        <h3 class="text-base! font-semibold text-slate-900">Punya produk lokal?</h3>
        <p class="mt-2 text-sm leading-6 text-slate-600">Bicarakan peluang kerja sama pemasok langsung dengan admin GGmart.</p>
        <a href="<?= $escape($partnerUrl) ?>" target="_blank" rel="noopener noreferrer" class="user-action-outline mt-4">Tanyakan Kerja Sama</a>
      </div>
    </div>
    <div class="space-y-6">
      <?php foreach ([
        ['01', 'Pilih produk', 'Telusuri katalog, periksa detail produk, lalu masukkan pilihan Anda ke keranjang.'],
        ['02', 'Tentukan cara menerima pesanan', 'Ambil di toko atau pilih pengiriman lokal. Tarif pengiriman mengikuti kelurahan yang dipilih saat checkout.'],
        ['03', 'Bayar dan pantau pesanan', 'Pilih metode pembayaran yang tersedia saat checkout. Tunai tersedia untuk pengambilan di toko; perkembangan pesanan dapat dilihat pada halaman transaksi.']
      ] as [$number, $heading, $text]): ?>
        <div class="flex gap-4 border-b border-slate-100 pb-6 last:border-0 last:pb-0">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-50 text-sm font-semibold text-green-700"><?= $number ?></span>
          <div><h3 class="text-base! font-semibold text-slate-900"><?= $heading ?></h3><p class="mt-2 text-sm leading-6 text-slate-600"><?= $text ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="kunjungi" aria-labelledby="kunjungi-title" class="user-container scroll-mt-24">
    <div class="grid gap-8 rounded-2xl border border-green-100 bg-[#f5f8f2] p-6 sm:p-9 lg:grid-cols-2 lg:gap-14">
      <div>
        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-green-700">Lokasi &amp; Kontak</p>
        <h2 id="kunjungi-title" class="font-poppins text-2xl! text-slate-900">Mari berkunjung.</h2>
        <p class="mt-4 leading-7 text-slate-600">Belanja langsung, ambil pesanan, atau tanyakan produk yang Anda cari kepada petugas toko.</p>
      </div>
      <div>
        <h3 class="text-base! font-semibold text-slate-900">GGmart · Toko Pangan Sinode GMIT</h3>
        <address class="mt-2 text-sm not-italic leading-6 text-slate-600">Jl. S. K. Lerik, di samping Kantor Sinode GMIT<br>Kota Baru, Kecamatan Kelapa Lima<br>Kota Kupang, Nusa Tenggara Timur 85228</address>
        <div class="mt-5 flex flex-wrap gap-3">
          <a href="<?= $escape($mapsUrl) ?>" target="_blank" rel="noopener noreferrer" class="user-action-primary">Buka Google Maps <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></a>
          <a href="<?= $escape($contactUrl) ?>" target="_blank" rel="noopener noreferrer" class="user-action-outline">Hubungi Admin</a>
        </div>
      </div>
    </div>
  </section>
</div>
