<nav class="flex flex-col divide-y">
  <template x-for="item in [
    { label: 'Home', icon: '🏠', link: '/' },
    { label: 'Produk', icon: '🛍️', link: '/user/produk' },
    { label: 'Keranjang', icon: '🛒', link: '/user/keranjang' },
    { label: 'Tentang Kami', icon: 'ℹ️', link: '/tentang' },
    { label: 'Hubungi Kami', icon: '📞', link: '/kontak' },
    { label: 'Kelola Toko', icon: '⚙️', link: '/admin/dashboard' }
  ]">
    <a :href="baseUrl + item.link"
      class="flex items-center gap-3 px-4 py-3 hover:bg-gray-100 transition duration-200"
      :class="location.pathname == item.link ? 'text-gg-primary font-semibold bg-gray-50' : 'text-gray-700'">
      <span x-text="item.icon"></span>
      <span x-text="item.label"></span>
    </a>
  </template>
</nav>