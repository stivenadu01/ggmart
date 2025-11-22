<nav class="flex-1 p-2 space-y-1 list-menu">

  <a
    x-cloak x-show="hasRole(['admin','manager'])" :href="baseUrl + '/admin/dashboard'"
    class="btn btn-primary shadow-none gap-2 justify-start" :class="location.pathname.includes('/admin/dashboard') ? 'bg-gg-primary/80 text-white' : 'bg-transparent text-neutral-900'" title="Dashboard">
    <span class="w-5 h-5" x-html="icon('dashboard')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Dashboard</span>
  </a>

  <a
    x-cloak x-show="hasRole(['admin','manager'])" :href="baseUrl + '/admin/kategori'"
    class="btn btn-primary shadow-none gap-2 justify-start" :class="location.pathname.includes('/admin/kategori') ? 'bg-gg-primary/80 text-white' : 'bg-transparent text-neutral-900'" title="Kelola Kategori">
    <span class="h-5 w-5" x-html="icon('kategori')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Kelola Kategori</span>
  </a>

  <a
    x-cloak x-show="hasRole(['admin','manager'])" :href="baseUrl + '/admin/produk'"
    class="btn btn-primary shadow-none gap-2 justify-start"
    :class="location.pathname.includes('/admin/produk') ? 'bg-gg-primary/80 text-white' : 'bg-transparent text-neutral-900'"
    title="Kelola Produk">
    <span class="h-5 w-5" x-html="icon('produk')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Kelola Produk</span>
  </a>

  <a
    x-cloak x-show="hasRole(['admin','manager'])" :href="baseUrl + '/admin/stok'"
    class="btn btn-primary shadow-none gap-2 justify-start cursor-pointer"
    :class="location.pathname.includes('/admin/stok') ? 'bg-gg-primary/80 text-white' : 'bg-transparent text-neutral-900'"
    title="Kelola Stok">
    <span class="h-5 w-5" x-html="icon('stok')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Kelola Stok</span>
  </a>

  <a
    x-cloak x-show="hasRole(['admin', 'kasir'])" :href="baseUrl + '/admin/transaksi/input'"
    class="btn btn-primary shadow-none gap-2 justify-start" :class="location.pathname.includes('/admin/transaksi/input') ? 'bg-gg-primary/80 text-white' : 'bg-transparent text-neutral-900'" title="Input Transaksi">
    <span class="h-5 w-5" x-html="icon('input')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Input Transaksi</span>
  </a>

  <a
    x-cloak x-show="hasRole(['admin','manager'])" :href="baseUrl + '/admin/transaksi/riwayat'"
    class="btn btn-primary shadow-none gap-2 justify-start" :class="location.pathname.includes('/admin/transaksi/riwayat') ? 'bg-gg-primary/80 text-white' : 'bg-transparent text-neutral-900'" title="Riwayat Transaksi">
    <span class="h-5 w-5" x-html="icon('riwayat')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Riwayat Transaksi</span>
  </a>

  <a
    x-cloak x-show="hasRole(['admin','manager'])" :href="baseUrl + '/admin/laporan'"
    class="btn btn-primary shadow-none gap-2 justify-start" :class="location.pathname == '/admin/laporan' ? 'bg-gg-primary/80 text-white' : 'bg-transparent text-neutral-900'" title="Laporan">
    <span class="h-5 w-5" x-html="icon('laporan')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Laporan</span>
  </a>

  <a
    x-cloak x-show="hasRole(['admin'])" :href="baseUrl + '/admin/user'"
    class="btn btn-primary shadow-none gap-2 justify-start"
    :class="location.pathname.includes('/admin/user') ? 'bg-gg-primary/80 text-white' : 'bg-transparent text-neutral-900'"
    title="Kelola User">
    <span class="h-5 w-5" x-html="icon('kelolauser')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Kelola User</span>
  </a>

  <a
    x-cloak x-show="hasRole(['admin'])" :href="baseUrl + '/admin/pengaturan'"
    class="btn btn-primary shadow-none gap-2 justify-start" :class="location.pathname.includes('/admin/pengaturan') ? 'bg-gg-primary/80 text-white' : 'bg-transparent text-neutral-900'" title="Pengaturan">
    <span class="h-5 w-5" x-html="icon('pengaturan')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Pengaturan</span>
  </a>

  <a
    x-cloak x-show="hasRole(['admin'])" :href="baseUrl + '/admin/landingHero'"
    class="btn btn-primary shadow-none gap-2 justify-start" :class="location.pathname.includes('/admin/landingHero') ? 'bg-gg-primary/80 text-white' : 'bg-transparent text-neutral-900'" title="Landing Hero">
    <span class="h-6 w-6" x-html="icon('landingHero')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Landing Hero</span>
  </a>

  <a
    x-cloak x-show="hasRole(['admin'])" :href="baseUrl + '/admin/article'"
    class="btn btn-primary shadow-none gap-2 justify-start" :class="location.pathname.includes('/admin/article') ? 'bg-gg-primary/80 text-white' : 'bg-transparent text-neutral-900'" title="Kelola Article">
    <span class="h-6 w-6" x-html="icon('article')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Kelola Article</span>
  </a>

  <button
    x-cloak
    onclick="logout()"
    class="w-full btn btn-primary shadow-none gap-2 justify-start bg-transparent text-neutral-900 hover:bg-gg-primary/80 hover:text-white" title="Logout">
    <span class="h-5 w-5" x-html="icon('logout')"></span>
    <span
      x-cloak
      :class="sidebarCollapse ? 'block lg:hidden' : 'block'"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100">Logout</span>
  </button>

</nav>