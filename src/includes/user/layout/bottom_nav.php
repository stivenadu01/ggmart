<nav
  class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t shadow-md flex justify-around py-2 z-30 text-gray-500 text-xs">
  <template x-for="item in [
    { label: 'Home', link: '/', icon: 'M3 12l9-9 9 9M4 10v10h6m8-11v10h-6' },
    { label: 'Produk', link: '/user/produk', icon: 'M4 6h4v4H4zm10 0h4v4h-4zM4 16h4v4H4zm10 0h4v4h-4z' },
    { label: 'Keranjang', link: '/user/keranjang', icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4' }
  ]">
    <a :href="baseUrl + item.link"
      class="flex flex-col items-center p-2 rounded-lg transition hover:text-gg-primary relative"
      :class="location.pathname == item.link ? 'text-gg-primary' : ''">
      <svg class="w-6 h-6 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
      </svg>
      <span class="mt-0.5" x-text="item.label"></span>
      <span x-show="item.label == 'Keranjang' && cartCount > 0"
        class="absolute top-0 right-3 bg-red-500 text-white text-[10px] rounded-full h-4 w-4 flex items-center justify-center"
        x-text="cartCount"></span>
    </a>
  </template>
</nav>