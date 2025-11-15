<header x-data="" class="fixed z-20 top-0 w-full text-white font-sans shadow-medium bg-gradient-to-t from-gg-primary to-gg-secondary">

  <div x-cloak class="md:px-6 lg:px-12 xl:px-16">

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
    <div class="flex flex-wrap justify-between items-center px-2 md:px-8 py-2 md:py-3">
      <!-- LOGO -->
      <a :href="baseUrl" class="hidden md:flex items-center space-x-1">
        <img :src="assetsUrl + '/logo.png'" alt="GG MART" class="h-8 md:h-10 filter logo">
        <span x-cloak class="text-2xl font-bold tracking-tight text-white drop-shadow hidden md:block">GG MART</span>
      </a>

      <!-- SEARCH BAR -->
      <div class="flex-1 max-w-2xl mx-6 flex">
        <form @submit.prevent="" class="w-full">
          <div class="relative">
            <div class="">
              <input type="text"
                placeholder="Cari Produk atau kategori..."
                class="w-full form-input h-10 border border-gray-300 !rounded-sm text-sm focus:border-gg-primary focus:ring-gg-primary">
              <span class="">
                <button type="submit" class="md:bg-gradient-to-br from-gg-primary to-gg-secondary md:text-white text-gray-500 absolute right-1 top-1/2 -translate-y-1/2 cursor-pointer py-[5px] px-3 hover:opacity-80 rounded-sm">
                  <span class="w-5 md:w-6" x-html="icon('cari')"></span>
                </button>
              </span>
            </div>
          </div>
        </form>
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
  <nav x-cloak class="hidden md:flex flex-nowrap justify-center text-sm py-2 space-x-6 !text-white border-t border-white/20 font-medium" x-cloak>
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
</header>

<main class="mt-[3.53rem] md:mt-[9rem] flex min-h-full">