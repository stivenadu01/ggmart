<header x-data="" class="fixed top-0 w-full bg-gg-primary text-white font-sans shadow-medium bg-gradient-to-br from-gg-primary to-gg-secondary">

  <div class="md:px-6 lg:px-12 xl:px-16">
    <!-- 🔹 TOPBAR -->
    <div class="hidden md:flex justify-between items-center py-2 text-sm">
      <div class="flex space-x-4">
        <a href="#" class="font-extralight hover:opacity-75 transition text-white">Gabung Jadi Mitra UMKM GG MART
        </a>
        <span class="font-extralight">|</span>
        <a href="#" class="font-extralight hover:opacity-75 transition text-white">Lihat Cara Bergabung
        </a>
        <span class="font-extralight">|</span>
        <a href="#" class="font-extralight hover:opacity-75 transition">Ikuti Kami</a>
        </a>
      </div>
      <div class="flex space-x-4 items-center">
        <a href="#" class="font-extralight hover:opacity-75 transition text-white">Bantuan</a>
        <template x-if="!currentUser">
          <a :href="baseUrl + '/auth/login'" class="font-extralight hover:opacity-75 transition text-white">Login</a>
        </template>
        <template x-if="currentUser">
          <div class="flex items-center space-x-2">
            <a :href="baseUrl + '/admin/dashboard'" class="font-extralight hover:opacity-75 transition text-white">Kelola Toko</a>
            <span x-html="icon('user')" class="w-6 h-6 ms-5"></span>
            <span x-text="currentUser.nama"></span>
          </div>
        </template>
      </div>
    </div>
    <!-- 🔸 MAIN HEADER -->
    <div class="flex flex-wrap justify-between items-center px-4 md:px-8 py-3">
      <!-- LOGO -->
      <a href="/" class="flex items-center space-x-2">
        <img :src="assetsUrl + '/logo.png'" alt="GG MART" class="h-8 md:h-10 filter logo">
        <span class="text-2xl font-bold tracking-tight text-white drop-shadow hidden md:block">GG MART</span>
      </a>

      <!-- SEARCH BAR (desktop) -->
      <div class="flex-1 max-w-2xl mx-4 hidden md:flex">
        <input type="text" placeholder="Cari produk, kategori, atau brand..."
          class="flex-1 px-4 py-2 rounded-l-xl text-gray-800 focus:outline-none shadow-soft">
        <button class="bg-gg-secondary px-4 py-2 rounded-r-xl hover:bg-gg-accent transition">
          🔍
        </button>
      </div>

      <!-- ICONS -->
      <div class="flex items-center space-x-4">
        <button class="relative">
          <span class="w-6 lg:w-8 fill-current" x-html="icon('keranjang')"></span>
          <span class="absolute -top-1 -right-2 bg-gg-accent text-xs rounded-full px-1">3</span>
        </button>
        <button class="md:hidden w-6" @click="navOpen = !navOpen" x-html="icon('menu')"></button>
      </div>
    </div>
  </div>
  <!-- 🔻 NAVBAR (desktop) -->
  <nav class="hidden md:flex flex-nowrap justify-center text-sm py-2 space-x-6 !text-white border-t border-white/20 font-medium" x-cloak>
    <a :href="baseUrl" class="hover:opacity-75">
      <!-- <span class="w-4 fill-current" x-html="icon('home')"></span> -->
      Home
    </a>
    <a :href="`${baseUrl}/user/produk`" class="hover:opacity-75">
      <!-- <span class="w-4 fill-current" x-html="icon('produk')"></span> -->
      Produk
    </a>
    <a :href="`${baseUrl}/tentang`" class="hover:opacity-75">
      <!-- <span class="w-4 fill-current" x-html="icon('produk')"></span> -->
      Tentang Kami
    </a>
    <a :href="`${baseUrl}/kontak`" class="hover:opacity-75">
      <!-- <span class="w-4 fill-current" x-html="icon('produk')"></span> -->
      Hubungi Kami
    </a>
    <a :href="`${baseUrl}/galeri`" class="hover:opacity-75">
      <!-- <span class="w-4 fill-current" x-html="icon('gambar')"></span> -->
      Galery Foto
    </a>
  </nav>

  <!-- 📱 MOBILE SIDEBAR -->
  <div
    x-cloak
    x-show="navOpen"
    class="fixed inset-0 flex z-30 lg:hidden text-slate-900"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-x-full"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 -translate-x-full">

    <!-- Sidebar -->
    <aside class="relative w-full bg-gray-100 shadow-md flex flex-col z-50">
      <div class="flex items-center bg-white justify-between p-3 border-b">
        <!-- LOGO -->
        <a href="/" class="flex items-center gap-x-4">
          <img :src="assetsUrl + '/logo.png'" alt="GG MART" class="h-8 filter logo">
          <span class="font-bold text-xl">Menu Utama</span>
        </a>
        <button @click="navOpen = false" class="p-2 rounded hover:bg-gray-100">
          <span class="w-6" x-html="icon('x')"></span>
        </button>
      </div>

      <!-- MENU ITEMS -->
      <nav class="space-y-5">

        <section class="bg-white flex flex-col p-4 space-y-5">
          <a :href="baseUrl" class="menu-item">
            <span class="w-5 fill-current" x-html="icon('home')"></span>
            Home
          </a>
          <a :href="`${baseUrl}/user/produk`" class="menu-item">
            <span class="w-5 fill-current" x-html="icon('produk')"></span>
            Produk
          </a>
          <a :href="`${baseUrl}/tentang`" class="menu-item">
            <span class="w-5 h-6 fill-current" x-html="icon('about')"></span>
            Tentang Kami
          </a>
          <a :href="`${baseUrl}/kontak`" class="menu-item">
            <span class="w-5 fill-current" x-html="icon('email')"></span>
            Hubungi Kami
          </a>
          <a :href="`${baseUrl}/galeri`" class="menu-item">
            <span class="w-5 fill-current" x-html="icon('galeri')"></span>
            Galery Foto
          </a>
        </section>

        <section class="bg-white flex flex-col p-4 py-2 space-y-5">
          <a :href="baseUrl" class="menu-item">
            <span class="w-7 fill-current" x-html="icon('jabatTangan')"></span>
            Gabung Jadi Mitra UMKM GG MART
          </a>
          <a :href="baseUrl" class="menu-item">
            <span class="w-5 fill-current" x-html="icon('buku')"></span>
            Lihat Cara Bergabung
          </a>
          <a :href="baseUrl" class="menu-item">
            <span class="w-5 fill-current" x-html="icon('tanya')"></span>
            Bantuan
          </a>
          <template x-if="!currentUser">
            <a :href="`${baseUrl}/auth/login`" class="menu-item">
              <span class="w-5 fill-current" x-html="icon('login')"></span>
              Login
            </a>
          </template>
          <template x-if="currentUser">
            <a :href="`${baseUrl}/admin/dashboard`" class="menu-item">
              <span class="w-5 fill-current" x-html="icon('pengaturan')"></span>
              Kelola Toko
            </a>
          </template>
        </section>

      </nav>
    </aside>
  </div>
</header>

<main class="mt-[3.52rem] md:mt-[8.57rem]">