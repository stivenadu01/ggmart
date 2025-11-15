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
        <a @click="navOpen = false" :href="baseUrl" class="menu-item">
          <span class="w-5 fill-current" x-html="icon('home')"></span>
          Home
        </a>
        <a @click="navOpen = false" :href="`${baseUrl}/user/produk`" class="menu-item">
          <span class="w-5 fill-current" x-html="icon('produk')"></span>
          Produk
        </a>
        <a @click="navOpen = false" :href="`${baseUrl}/tentang`" class="menu-item">
          <span class="w-5 h-6 fill-current" x-html="icon('about')"></span>
          Tentang Kami
        </a>
        <a @click="navOpen = false" :href="`${baseUrl}/kontak`" class="menu-item">
          <span class="w-5 fill-current" x-html="icon('email')"></span>
          Hubungi Kami
        </a>
        <a @click="navOpen = false" :href="`${baseUrl}/galeri`" class="menu-item">
          <span class="w-5 fill-current" x-html="icon('galeri')"></span>
          Galery Foto
        </a>
      </section>

      <section class="bg-white flex flex-col p-4 py-2 space-y-5">
        <a @click="navOpen = false" :href="baseUrl" class="menu-item">
          <span class="w-7 fill-current" x-html="icon('jabatTangan')"></span>
          Gabung Jadi Mitra UMKM GG MART
        </a>
        <a @click="navOpen = false" :href="baseUrl" class="menu-item">
          <span class="w-5 fill-current" x-html="icon('buku')"></span>
          Lihat Cara Bergabung
        </a>
        <a @click="navOpen = false" :href="baseUrl" class="menu-item">
          <span class="w-5 fill-current" x-html="icon('tanya')"></span>
          Bantuan
        </a>
        <template x-if="!currentUser">
          <a @click="navOpen = false" :href="`${baseUrl}/auth/login`" class="menu-item">
            <span class="w-5 fill-current" x-html="icon('login')"></span>
            Login
          </a>
        </template>
        <template x-if="currentUser">
          <a @click="navOpen = false" :href="`${baseUrl}/admin/dashboard`" class="menu-item">
            <span class="w-5 fill-current" x-html="icon('pengaturan')"></span>
            Kelola Toko
          </a>
        </template>
      </section>

    </nav>
  </aside>

</div>