<header class="bg-white border-b shadow-sm z-30 relative">
  <div class="hidden md:flex justify-end text-sm text-gray-600/80 border-b px-10 py-2 bg-gray-50">
    <a :href="baseUrl + '/tentang'" class="hover:text-gg-primary transition">Tentang Kami</a>
    <a :href="baseUrl + '/kontak'" class="hover:text-gg-primary transition ml-4">Hubungi Kami</a>
  </div>

  <div class="flex items-center justify-between px-4 lg:px-10 py-3">
    <a :href="baseUrl" class="flex items-center gap-2">
      <img :src="assetsUrl + '/logo.png'" alt="GG Mart Logo" class="h-10">
      <h1 class="hidden md:block text-2xl font-bold text-gg-primary tracking-tight">GG MART</h1>
    </a>

    <div class="flex items-center gap-4">
      <!-- Desktop menu -->
      <nav class="hidden md:flex items-center gap-5 text-sm font-medium">
        <a :href="`${baseUrl}/user/produk`" class="hover:text-gg-primary transition">Produk</a>
        <a :href="`${baseUrl}/user/keranjang`" class="hover:text-gg-primary transition relative">
          Keranjang
          <span x-show="cartCount > 0"
            class="absolute -top-2 -right-3 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5"
            x-text="cartCount"></span>
        </a>
        <a :href="`${baseUrl}/admin/dashboard`" class="hover:text-gg-primary transition">Kelola Toko</a>
      </nav>

      <!-- Mobile menu button -->
      <button @click="menuOpen = true" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition">
        <span class="h-5 w-5" x-html="icon('menu')"></span>
      </button>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div x-show="menuOpen" x-transition class="fixed inset-0 z-40 md:hidden flex" x-cloak>
    <div class="bg-black/40 absolute inset-0" @click="menuOpen = false"></div>
    <aside
      class="relative bg-white w-3/4 max-w-xs h-full shadow-2xl overflow-y-auto transform transition-all duration-300"
      x-show="menuOpen" x-transition:enter="translate-x-0" x-transition:leave="translate-x-full">
      <div class="flex justify-between items-center bg-gg-primary p-4">
        <span class="text-white font-semibold text-lg">Menu Utama</span>
        <button @click="menuOpen = false" class="p-1 text-white hover:bg-white/20 rounded-full">
          ✕
        </button>
      </div>
      <?php include INCLUDES_PATH . "/user/layout/list_menu_mobile.php"; ?>
    </aside>
  </div>
</header>

<script>
  function navbar() {
    return {
      baseUrl: "<?= BASE_URL ?>",
      assetsUrl: "<?= ASSETS_URL ?>",
      menuOpen: false,
      cartCount: 0,
      init() {
        this.updateCartCount();
      },
      updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        this.cartCount = cart.length;
      }
    };
  }
</script>