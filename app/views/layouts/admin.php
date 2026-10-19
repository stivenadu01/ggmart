<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title ?? 'GGMart') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#22c55e">
  <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/favicon.ico" type="image/x-icon">
  <!-- <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet"> -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css?v=<?= filemtime(ROOT_PATH . '/public/assets/css/app.css') ?>">
  <link href="<?= BASE_URL ?>/assets/css/css2.css" rel="stylesheet">

  <!-- <script defer src="https://unpkg.com/alpinejs"></script> -->
  <script defer src="<?= BASE_URL ?>/assets/js/cdn.min.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/api.js?v=<?= filemtime(ROOT_PATH . '/public/assets/js/api.js') ?>"></script>
  <script src="<?= BASE_URL ?>/assets/js/store.js?v=<?= filemtime(ROOT_PATH . '/public/assets/js/store.js') ?>"></script>
  <script src="<?= BASE_URL ?>/assets/js/notifications.js?v=<?= filemtime(ROOT_PATH . '/public/assets/js/notifications.js') ?>"></script>
  <script src="<?= BASE_URL ?>/assets/js/storeAdmin.js?v=<?= filemtime(ROOT_PATH . '/public/assets/js/storeAdmin.js') ?>"></script>
  <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/utils.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/order.js?v=<?= filemtime(ROOT_PATH . '/public/assets/js/order.js') ?>"></script>
  <script src="<?= BASE_URL ?>/assets/js/dropdown.js?v=<?= filemtime(ROOT_PATH . '/public/assets/js/dropdown.js') ?>"></script>
  <script>
    window.__USER__ = <?= json_encode($_SESSION['user'] ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
    window.NOTIFICATION_SESSION = <?= json_encode(notificationSession()) ?>;
    window.BASE_URL = '<?= BASE_URL ?>';
    window.NOMOR_WA = '<?= $_ENV['NOMOR_WA'] ?? '' ?>';
  </script>
</head>

<body class="admin-body bg-slate-50 text-slate-800">
  <div
    x-data="{ sidebarOpen: false }"
    @keydown.escape.window="sidebarOpen = false"
    class="admin-shell min-h-dvh">

    <!-- MOBILE OVERLAY -->
    <div
      x-show="sidebarOpen"
      x-cloak
      x-transition.opacity
      @click="sidebarOpen = false"
      class="admin-overlay fixed inset-0 z-[80] bg-slate-950/45 lg:hidden"
      aria-hidden="true">
    </div>

    <!-- SIDEBAR -->
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- MAIN AREA -->
    <div class="admin-main min-w-0 flex h-dvh flex-col overflow-auto">
      <header class="admin-topbar sticky top-0 z-[70] border-b border-slate-200/80 bg-white/95 backdrop-blur">
        <div class="flex min-h-16 items-center gap-3 px-4 sm:px-5 lg:px-7">
          <button
            type="button"
            @click="sidebarOpen = true"
            class="admin-menu-button inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 lg:hidden"
            aria-label="Buka menu admin">
            <?= masterIconSvg('menu', 'h-5 w-5') ?>
          </button>

          <div class="min-w-0 flex-1">
            <div class="truncate text-xs font-semibold uppercase tracking-[0.12em] text-slate-400 sm:text-sm">
              GG-Mart Admin Panel
            </div>
          </div>

          <div class="flex items-center gap-2 sm:gap-3">
            <?php include __DIR__ . '/../partials/notification-button.php'; ?>
            <div class="hidden items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 sm:flex">
              <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
              <span class="text-xs font-medium text-slate-600">Online</span>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-sm font-bold text-emerald-700 ring-1 ring-emerald-100">
              <?= htmlspecialchars(strtoupper(substr($_SESSION['user']['nama'] ?? $_SESSION['user']['email'] ?? 'A', 0, 1))) ?>
            </div>

            <div class="hidden max-w-44 sm:block">
              <p class="truncate text-sm font-semibold text-slate-800"><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Pengguna') ?></p>
              <p class="truncate text-xs capitalize text-slate-500"><?= htmlspecialchars($_SESSION['user']['role'] ?? 'pelanggan') ?></p>
            </div>
          </div>
        </div>
      </header>

      <main class="admin-content min-w-0 flex-1">
        <?= $content ?>
      </main>
    </div>

    <?php include __DIR__ . '/../partials/toast.php'; ?>
  </div>
  <?php include __DIR__ . '/../partials/notifications.php'; ?>
</body>

</html>
