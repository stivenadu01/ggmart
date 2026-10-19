<?php
$adminRole = $_SESSION['user']['role'] ?? 'pelanggan';
$isAdmin = $adminRole === 'admin';
$isPimpinan = $adminRole === 'pimpinan';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$base = rtrim(BASE_URL, '/');

function adminMenuActive(string $path, string $currentPath, bool $exact = false): string
{
  if ($exact) return $currentPath === $path ? 'is-active' : '';
  return str_starts_with($currentPath, $path) ? 'is-active' : '';
}
?>

<aside
  class="admin-sidebar fixed inset-y-0 left-0 z-[90] w-[min(19rem,86vw)] -translate-x-full border-r border-slate-200 bg-white shadow-xl transition-transform duration-300 lg:static lg:z-auto lg:w-72 lg:translate-x-0 lg:shadow-none"
  :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
  aria-label="Navigasi admin"
  <?= $isAdmin ? 'x-init="$store.pesananBadge.start()"' : '' ?>>

  <div class="flex h-dvh min-h-0 flex-col overflow-auto">
    <!-- BRAND -->
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200 px-4 sm:px-5">
      <a href="<?= $base ?>/admin" @click="sidebarOpen = false" class="flex min-w-0 items-center gap-3">
        <img src="<?= $base ?>/assets/logo.png" alt="GG-Mart" class="h-10 w-10 rounded-xl object-contain">
        <div class="min-w-0">
          <div class="truncate font-poppins text-base font-bold text-slate-900">GG-Mart</div>
          <div class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-400">Admin Panel</div>
        </div>
      </a>
      <button type="button" @click="sidebarOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="Tutup menu">
        <?= masterIconSvg('close', 'h-5 w-5') ?>
      </button>
    </div>

    <!-- USER -->
    <div class="shrink-0 border-b border-slate-100 px-4 py-4 sm:px-5">
      <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700">
          <?= htmlspecialchars(strtoupper(substr($_SESSION['user']['nama'] ?? $_SESSION['user']['email'] ?? 'A', 0, 1))) ?>
        </div>
        <div class="min-w-0">
          <p class="truncate text-sm font-semibold text-slate-800"><?= htmlspecialchars($_SESSION['user']['nama'] ?? 'Pengguna') ?></p>
          <p class="truncate text-xs text-slate-500"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></p>
          <span class="mt-1 inline-flex rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold capitalize text-emerald-700 ring-1 ring-slate-200">
            <?= htmlspecialchars($adminRole) ?>
          </span>
        </div>
      </div>
    </div>

    <nav class="admin-nav min-h-0 flex-1 overflow-y-auto px-3 py-4 sm:px-4" @click="if ($event.target.closest('a')) sidebarOpen = false">
      <div class="space-y-6">
        <!-- MENU UTAMA -->
        <section>
          <p class="admin-nav-heading">Menu Utama</p>
          <div class="space-y-1">
            <a href="<?= $base ?>/admin" class="admin-nav-link <?= adminMenuActive($base . '/admin', $currentPath, true) ?>">
              <span class="admin-nav-icon"><?= masterIconSvg('dashboard') ?></span><span>Dashboard</span>
            </a>

            <?php if ($isAdmin): ?>
              <a href="<?= $base ?>/admin/kasir" class="admin-nav-link <?= adminMenuActive($base . '/admin/kasir', $currentPath, true) ?>">
                <span class="admin-nav-icon"><?= masterIconSvg('cashier') ?></span><span>Kasir</span>
              </a>
              <a href="<?= $base ?>/admin/pesanan" class="admin-nav-link <?= adminMenuActive($base . '/admin/pesanan', $currentPath, true) ?> relative">
                <span class="admin-nav-icon"><?= masterIconSvg('orders') ?></span><span>Pesanan</span>
                <span x-show="$store.pesananBadge.total > 0" x-text="$store.pesananBadge.total" x-cloak class="admin-badge"></span>
              </a>
            <?php endif; ?>
          </div>
        </section>

        <!-- DATA MASTER -->
        <section>
          <p class="admin-nav-heading">Data Master</p>
          <div class="space-y-1">
            <a href="<?= $base ?>/admin/kategori" class="admin-nav-link <?= adminMenuActive($base . '/admin/kategori', $currentPath, true) ?>">
              <span class="admin-nav-icon"><?= masterIconSvg('category') ?></span><span>Kategori</span>
            </a>
            <a href="<?= $base ?>/admin/produk" class="admin-nav-link <?= adminMenuActive($base . '/admin/produk', $currentPath) ?>">
              <span class="admin-nav-icon"><?= masterIconSvg('product') ?></span><span>Produk</span>
            </a>
            <a href="<?= $base ?>/admin/stok" class="admin-nav-link <?= adminMenuActive($base . '/admin/stok', $currentPath) ?>">
              <span class="admin-nav-icon"><?= masterIconSvg('stock') ?></span><span>Stok</span>
            </a>
            <?php if ($isAdmin): ?>
              <a href="<?= $base ?>/admin/user" class="admin-nav-link <?= adminMenuActive($base . '/admin/user', $currentPath, true) ?>">
                <span class="admin-nav-icon"><?= masterIconSvg('users') ?></span><span>User</span>
              </a>
            <?php endif; ?>
          </div>
        </section>

        <!-- MONITORING -->
        <section>
          <p class="admin-nav-heading">Monitoring</p>
          <div class="space-y-1">
            <a href="<?= $base ?>/admin/transaksi" class="admin-nav-link <?= adminMenuActive($base . '/admin/transaksi', $currentPath) ?>">
              <span class="admin-nav-icon"><?= masterIconSvg('history') ?></span><span>Riwayat Transaksi</span>
            </a>
            <?php if ($isPimpinan): ?>
              <a href="<?= $base ?>/admin/laporan" class="admin-nav-link <?= adminMenuActive($base . '/admin/laporan', $currentPath) ?>">
                <span class="admin-nav-icon"><?= masterIconSvg('report') ?></span><span>Laporan</span>
              </a>
            <?php endif; ?>
          </div>
        </section>

        <!-- SISTEM -->
        <section>
          <p class="admin-nav-heading">Sistem</p>
          <div class="space-y-1">
            <?php if ($isAdmin): ?>
              <a href="<?= $base ?>/admin/pengaturan" class="admin-nav-link <?= adminMenuActive($base . '/admin/pengaturan', $currentPath) ?>">
                <span class="admin-nav-icon"><?= masterIconSvg('settings') ?></span><span>Pengaturan</span>
              </a>
            <?php endif; ?>
            <a href="<?= $base ?>" class="admin-nav-link">
              <span class="admin-nav-icon"><?= masterIconSvg('home') ?></span><span>Kembali ke Beranda</span>
            </a>
            <button type="button" @click="$store.auth.logout()" class="admin-nav-link admin-nav-danger w-full text-left">
              <span class="admin-nav-icon"><?= masterIconSvg('logout') ?></span><span>Logout</span>
            </button>
          </div>
        </section>
      </div>
    </nav>

    <div class="shrink-0 border-t border-slate-100 px-4 py-3 text-center text-[11px] text-slate-400">
      GG-Mart • Admin Panel
    </div>
  </div>
</aside>