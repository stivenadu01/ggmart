<?php
$navbarPath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
$navbarBase = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
$navbarMainPaths = array_map(static fn($path) => $navbarBase . $path, ['', '/produk', '/tentang', '/profil']);
$navbarCompactMobile = !in_array($navbarPath, $navbarMainPaths, true);
?>
<nav class="sf-navbar <?= $navbarCompactMobile ? 'sf-navbar-compact-mobile' : '' ?> sticky top-0 z-50" x-data="{ mobileMenu: false, query: new URLSearchParams(location.search).get('search') || '' }" @catalog-search-reset.window="query = ''" @keydown.escape.window="mobileMenu = false; $store.ui.openUserMenu = false">
  <?php if ($navbarCompactMobile): ?>
    <div class="sf-navbar-back user-container border-b border-slate-100 bg-white">
      <button type="button" class="user-icon-button" onclick="window.history.back()" aria-label="Kembali ke halaman sebelumnya">
        <?= masterIconSvg('arrow-left', 'h-6 w-6') ?>
      </button>
    </div>
  <?php endif; ?>
  <div class="border-b border-slate-100 bg-white">
    <div class="sf-navbar-grid user-container">
      <a href="<?= BASE_URL ?>" class="sf-navbar-brand flex shrink-0 items-center gap-2 font-poppins text-lg font-bold text-slate-900" aria-label="GG MART Beranda">
        <img src="<?= BASE_URL ?>/assets/logo.png" class="h-7 w-7 object-contain" alt="Logo GG MART"><span>GG MART</span>
      </a>
      <div class="sf-navbar-links hidden items-center gap-4 lg:flex" aria-label="Navigasi utama">
        <?php foreach ([['', 'Beranda'], ['/produk', 'Produk'], ['/tentang', 'Tentang Kami']] as [$navbarHref, $navbarLabel]): $navbarActive = $navbarPath === $navbarBase . $navbarHref; ?>
          <a href="<?= BASE_URL . $navbarHref ?>" class="user-nav-link <?= $navbarActive ? 'is-active' : '' ?>" <?= $navbarActive ? 'aria-current="page"' : '' ?>><?= $navbarLabel ?></a>
        <?php endforeach; ?>
      </div>
      <form class="sf-navbar-search" role="search" action="<?= BASE_URL ?>/produk" method="get">
        <label class="sr-only" for="navbar-search">Cari produk</label>
        <input id="navbar-search" name="search" type="search" x-model.trim="query" placeholder="Cari produk…" maxlength="150" autocomplete="off">
        <button type="submit" aria-label="Cari produk"><?= masterIconSvg('search', 'h-5 w-5') ?></button>
      </form>
      <div class="sf-navbar-actions flex shrink-0 items-center gap-2">
        <a id="cart-icon" href="<?= BASE_URL ?>/keranjang" x-show="!$store.auth.user || $store.auth.user.role === 'pelanggan'" class="relative user-icon-button" aria-label="Keranjang">
          <?= masterIconSvg('cart', 'h-5 w-5') ?>
          <span x-cloak x-show="$store.cart.totalQty > 0" x-text="$store.cart.totalQty" class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"></span>
        </a>
        <?php include __DIR__ . '/notification-button.php'; ?>
        <div class="hidden md:block">
          <template x-if="!$store.auth.user">
            <a href="<?= BASE_URL ?>/login" class="user-action-primary min-h-9 px-4 py-2">Masuk</a>
          </template>
          <template x-if="$store.auth.user">
            <div class="relative">
              <button type="button" class="sf-navbar-account" @click="$store.ui.openUserMenu = !$store.ui.openUserMenu" :aria-expanded="$store.ui.openUserMenu" aria-label="Buka menu akun"><span x-text="($store.auth.user.nama || $store.auth.user.email).charAt(0).toUpperCase()"></span></button>
              <div x-show="$store.ui.openUserMenu" x-transition.origin.top.right x-cloak @click.outside="$store.ui.openUserMenu = false" class="absolute right-0 mt-2 w-60 rounded-xl border border-slate-200 bg-white p-2 shadow-lg">
                <div class="truncate border-b border-slate-100 px-3 py-2 text-xs text-slate-500" x-text="$store.auth.user.nama"></div>
                <a href="<?= BASE_URL ?>/profil" class="user-menu-item">Profil Saya</a>
                <template x-if="$store.auth.user.role === 'pelanggan'"><a href="<?= BASE_URL ?>/transaksi" class="user-menu-item">Pesanan Saya</a></template>
                <template x-if="['admin', 'pimpinan'].includes($store.auth.user.role)"><a href="<?= BASE_URL ?>/admin" class="user-menu-item">Dashboard Admin</a></template>
                <template x-if="$store.auth.user.role === 'kasir'"><a href="<?= BASE_URL ?>/admin/kasir" class="user-menu-item">Kasir</a></template>
                <button type="button" @click="$store.auth.logout()" class="user-menu-item w-full text-left text-red-600 hover:bg-red-50 hover:text-red-700">Keluar</button>
              </div>
            </div>
          </template>
        </div>
        <button type="button" class="user-icon-button lg:hidden" @click="mobileMenu = true; $store.ui.openUserMenu = false" :aria-expanded="mobileMenu" aria-label="Buka menu"><?= masterIconSvg('menu', 'h-5 w-5') ?></button>
      </div>
    </div>
  </div>
  <div x-show="mobileMenu" x-cloak x-transition.opacity class="fixed inset-0 z-60 overflow-y-auto bg-white lg:hidden">
    <div class="user-container py-5">
      <div class="flex items-center justify-between">
        <a href="<?= BASE_URL ?>" class="flex items-center gap-2 font-poppins text-lg font-bold text-slate-900"><img src="<?= BASE_URL ?>/assets/logo.png" class="h-7 w-7 object-contain" alt="Logo GG MART">GG MART</a>
        <button type="button" @click="mobileMenu = false" class="user-icon-button" aria-label="Tutup menu"><?= masterIconSvg('close', 'h-5 w-5') ?></button>
      </div>
      <div class="mt-6 space-y-1 border-t border-slate-100 pt-4">
        <a href="<?= BASE_URL ?>" class="user-menu-item">Beranda</a>
        <a href="<?= BASE_URL ?>/produk" class="user-menu-item">Produk</a>
        <a href="<?= BASE_URL ?>/tentang" class="user-menu-item">Tentang Kami</a>
      </div>
      <div class="mt-5 border-t border-slate-100 pt-4">
        <template x-if="!$store.auth.user"><div class="grid grid-cols-2 gap-3"><a href="<?= BASE_URL ?>/login" class="user-action-primary">Masuk</a><a href="<?= BASE_URL ?>/register" class="user-action-outline">Daftar</a></div></template>
        <template x-if="$store.auth.user">
          <div class="space-y-1">
            <p class="mb-3 px-3 text-sm text-slate-500" x-text="$store.auth.user.nama"></p>
            <a href="<?= BASE_URL ?>/profil" class="user-menu-item">Profil Saya</a>
            <template x-if="$store.auth.user.role === 'pelanggan'"><a href="<?= BASE_URL ?>/transaksi" class="user-menu-item">Pesanan Saya</a></template>
            <template x-if="['admin', 'pimpinan'].includes($store.auth.user.role)"><a href="<?= BASE_URL ?>/admin" class="user-menu-item">Dashboard Admin</a></template>
            <template x-if="$store.auth.user.role === 'kasir'"><a href="<?= BASE_URL ?>/admin/kasir" class="user-menu-item">Kasir</a></template>
            <button type="button" @click="$store.auth.logout()" class="user-action-danger mt-4 w-full">Keluar</button>
          </div>
        </template>
      </div>
    </div>
  </div>
</nav>
