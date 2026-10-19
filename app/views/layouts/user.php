<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title><?= $title ?? $_ENV['APP_NAME'] ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php if (!empty($description)): ?>
    <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>
  <meta name="theme-color" content="#176448">
  <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/favicon.ico" type="image/x-icon">
  <!-- <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet"> -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css?v=<?= filemtime(ROOT_PATH . '/public/assets/css/app.css') ?>">
  <link href="<?= BASE_URL ?>/assets/css/css2.css" rel="stylesheet">

  <!-- <script defer src="https://unpkg.com/alpinejs"></script> -->
  <script defer src="<?= BASE_URL ?>/assets/js/cdn.min.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/api.js?v=<?= filemtime(ROOT_PATH . '/public/assets/js/api.js') ?>"></script>
  <script src="<?= BASE_URL ?>/assets/js/store.js?v=<?= filemtime(ROOT_PATH . '/public/assets/js/store.js') ?>"></script>
  <script src="<?= BASE_URL ?>/assets/js/notifications.js?v=<?= filemtime(ROOT_PATH . '/public/assets/js/notifications.js') ?>"></script>
  <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/utils.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/order.js?v=<?= filemtime(ROOT_PATH . '/public/assets/js/order.js') ?>"></script>
  <script src="<?= BASE_URL ?>/assets/js/dropdown.js?v=<?= filemtime(ROOT_PATH . '/public/assets/js/dropdown.js') ?>"></script>
  <script>
    window.__USER__ = <?= json_encode($_SESSION['user'] ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
    window.NOTIFICATION_SESSION = <?= json_encode(isset($_SESSION['user']) ? notificationSession() : '') ?>;
    window.BASE_URL = '<?= BASE_URL ?>';
    window.NOMOR_WA = '<?= $_ENV['NOMOR_WA'] ?>';
  </script>
</head>

<body class="storefront min-w-0 bg-white text-slate-800">

  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <main x-data class="min-w-0">
    <?= $content ?>
  </main>

  <?php include __DIR__ . '/../partials/toast.php'; ?>

  <footer class="mt-16 border-t border-slate-200 bg-white sm:mt-20" x-data>
    <div class="user-container py-10 sm:py-12">
      <div class="grid grid-cols-1 gap-8 md:grid-cols-3 md:gap-10">

        <div class="md:col-span-1">
          <div class="mb-3 flex items-center gap-2 font-poppins text-lg font-bold text-slate-900">
            <img src="<?= BASE_URL ?>/assets/logo.png" class="h-7 w-7 object-contain" alt="Logo GG MART">
            <span>GG MART</span>
          </div>
          <p class="max-w-sm text-sm leading-6 text-slate-500">
            Toko pangan milik Sinode GMIT yang mendukung pemasaran produk jemaat dan UMKM lokal di Nusa Tenggara Timur.
          </p>
        </div>

        <div>
          <h3 class="mb-3 text-sm font-bold text-slate-900">Navigasi</h3>
          <ul class="space-y-1">
            <li><a :href="BASE_URL" class="user-menu-item">Beranda</a></li>
            <li><a :href="BASE_URL + '/produk'" class="user-menu-item">Produk</a></li>
            <li><a href="<?= BASE_URL ?>/tentang" class="user-menu-item">Tentang Kami</a></li>
          </ul>
        </div>

        <div>
          <h3 class="mb-3 text-sm font-bold text-slate-900">Kontak</h3>
          <ul class="space-y-1 text-sm text-slate-500">
            <li>
              <a :href="`https://wa.me/${NOMOR_WA}?text=Halo%20Admin%20GGMart%2C%20saya%20ingin%20bertanya...`" class="user-menu-item" target="_blank">
                WhatsApp Admin
              </a>
            </li>
            <li class="px-3 py-2.5">Kupang, NTT</li>
          </ul>
        </div>

      </div>

      <div class="mt-8 border-t border-slate-100 pt-6 text-center text-xs text-slate-500 sm:mt-10 sm:text-sm">
        © <span x-text="new Date().getFullYear()"></span> GG MART - Sinode GMIT. All rights reserved.
      </div>
    </div>
  </footer>

  <?php include __DIR__ . '/../partials/notifications.php'; ?>
</body>

</html>
