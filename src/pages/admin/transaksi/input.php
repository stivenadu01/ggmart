<?php
page_require(['admin', 'kasir']);
$pageTitle = "Input Transaksi";
include INCLUDES_PATH . "admin/layout/header.php";
?>

<div x-data="transaksiPage()" class="bg-gray-50 p-2 lg:p-4 space-y-4">
  <div class="flex  flex-col md:flex-row">

    <div class="w-full md:w-2/3 bg-white p-4 rounded-xl shadow-lg h-[85dvh] flex flex-col">
      <div class="mb-4 bg-white pb-4 border-b border-gray-100 relative">
        <div class="relative">
          <input
            id="searchProduk"
            type="text"
            placeholder="Cari Produk..."
            x-ref="searchInput"
            autofocus
            x-model="search"
            class="p-5 md:p-6 w-full text-2xl font-semibold tracking-wide input-trx
           bg-white border border-gray-300 rounded-xl shadow-sm
           placeholder-gray-400 text-gray-900 pr-28 focus:ring-1 focus:ring-gg-primary focus:border-gg-primary focus:outline-none"
            @input.debounce.200="fetchProduk()"
            @keydown.enter.prevent="tambahProdukDariInput()">
          <!-- Shortcut Key Label -->
          <span
            class="shortcut-key absolute right-5 top-1/2 -translate-y-1/2 text-sm pointer-events-none select-none">
            Ctrl + K
          </span>
        </div>
      </div>



      <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
        <div x-show="produk.length === 0 && search != ''" class="text-center text-gray-500 py-10">
          <p>Tidak ada produk ditemukan.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          <template x-for="p in produk" :key="p.kode_produk">
            <div
              class="relative bg-white border border-gray-200 rounded-xl hover:shadow-xl hover:border-emerald-300 transition-all duration-200 cursor-pointer overflow-hidden group"
              :class="p.stok == 0 ? 'opacity-50 cursor-not-allowed' : ''"
              @click="p.stok > 0 && tambahKeranjang(p)">

              <div class="relative">
                <img :src="p.gambar ? `${uploadsUrl}/${p.gambar}` : `${assetsUrl}/img/no-image.webp`"
                  class="w-full h-28 object-cover transition duration-200 group-hover:scale-105">

                <span
                  class="absolute top-1 right-1 text-xs font-bold px-1.5 py-1 rounded-md shadow-lg transition-all duration-200"
                  :class="{'bg-red-500 text-white': p.stok == 0, 'bg-yellow-400 text-gray-800': p.stok > 0 && p.stok <= 5, 'bg-emerald-500 text-white': p.stok > 10}"
                  x-text="p.stok > 0 ? p.stok +' '+p.satuan_dasar : 'HABIS'">
                </span>
              </div>

              <div class="p-2">
                <p class="text-xs font-medium text-gray-400 truncate" x-text="p.nama_kategori"></p>
                <h2 class="text-sm font-bold text-gray-800 truncate" x-text="p.nama_produk"></h2>
                <p class="text-lg font-extrabold text-emerald-600 mt-1" x-text="formatRupiah(p.harga_jual)"></p>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>


    <div class="fixed bottom-0 w-full md:static md:w-1/3 bg-white rounded-xl shadow-2xl flex flex-col">
      <div x-cloak :class="openRincian ? 'flex' : 'hidden md:flex'" class="items-center justify-between text-emerald-700 mb-2 px-4 border-b pb-4 pt-3 md:pt-0 border-t md:border-t-0">
        <h2 class="text-xl font-extrabold">RINCIAN TRANSAKSI</h2>

        <div class="flex justify-center items-center gap-x-4">
          <button @click="resetKeranjang()"
            class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-md transition flex items-center justify-center text-sm"
            title="Reset Keranjang">
            <span class="mr-1 text-sm">Reset </span>
            <span class="w-4 h-4 fill-current" x-html="icon('refresh')"></span>
          </button>
          <button x-cloak :class="openRincian ? 'flex' : 'flex md:hidden'" @click="openRincian = false" class="w-5 h-5 fill-current" x-html="icon('arrowCloseAtasBawah')"></button>
        </div>
      </div>

      <div x-cloak :class="openRincian ? 'flex' : 'hidden md:flex'" class="flex-1 flex-col space-y-3 p-4 overflow-y-auto max-h-[43dvh]">
        <template x-for="(item, index) in keranjang" :key="item.kode_produk">
          <div class="flex justify-between items-center border-b border-gray-200 pb-3 hover:bg-gray-50 -mx-4 px-4 transition duration-200">
            <div class="flex-1 pr-2">
              <h3 class="font-semibold text-gray-800 text-sm truncate" x-text="item.nama_produk"></h3>
              <p class="text-xs text-gray-500" x-text="formatRupiah(item.harga_satuan)"></p>
            </div>

            <div class="flex items-center gap-2">
              <input type="number" min="1"
                class="!w-14 py-1 px-1"
                x-model.number="item.jumlah" @input.debounce.200="updateSubtotal(index)">

              <p class="text-sm font-bold text-gray-900 text-right" x-text="formatRupiah(item.subtotal)"></p>

              <button @click="hapusKeranjang(index)" class="text-red-500 hover:text-red-700 p-1 rounded-full transition">
                <span class="w-4 h-4" x-html="icon('x')"></span>
              </button>
            </div>
          </div>
        </template>


        <div x-show="keranjang.length === 0" class="text-gray-500 text-center p-10">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          <p class="text-sm">Silakan tambahkan produk dari daftar.</p>
        </div>
      </div>

      <div class="mt-auto border-t-4 border-emerald-200 pt-3 space-y-2 p-3 bg-gray-50 rounded-b-xl shadow-inner">

        <div class="flex justify-between text-2xl text-emerald-700 font-extrabold pb-1 border-b-2 border-emerald-100">
          <div>TOTAL</div>
          <div class="flex gap-x-4 justify-center items-center">
            <span x-text="formatRupiah(totalHarga)"></span>
            <button x-cloak :class="openRincian ? 'hidden' : 'flex md:hidden'" @click="openRincian = true" class="w-5 h-5 fill-current" x-html="icon('arrowOpenAtasBawah')"></button>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Pembayaran</label>
          <div class="flex space-x-2">
            <label for="bayar-tunai" class="flex-1 cursor-pointer">
              <input type="radio" id="bayar-tunai" name="metode" value="tunai" x-model="metodeBayar" class="sr-only peer">
              <div class="w-full text-center p-1.5 rounded-lg border-2 border-gray-300 text-gray-700 
                peer-checked:bg-emerald-500 peer-checked:border-emerald-600 
                peer-checked:text-white
                transition duration-200 hover:bg-gray-100 font-medium shadow-sm flex items-center justify-center text-sm">
                <span class="w-4 h-4 fill-current" x-html="icon('tunai')"></span>
                <span>TUNAI <span class="shortcut-key">F2</span></span>
              </div>
            </label>
            <label for="bayar-qris" class="flex-1 cursor-pointer">
              <input type="radio" id="bayar-qris" name="metode" value="qris" x-model="metodeBayar" class="sr-only peer">
              <div class="w-full text-center p-1.5 rounded-lg border-2 border-gray-300 
                peer-checked:text-white text-gray-700 peer-checked:bg-emerald-500 peer-checked:border-emerald-600 transition duration-200 hover:bg-gray-100 font-medium shadow-sm flex items-center justify-center text-sm">
                <span x-html="icon('qris')" class="fill-current w-4 h-4"></span>
                <span>QRIS <span class="shortcut-key">F3</span></span>
              </div>
            </label>
          </div>
        </div>

        <div>
          <template x-if="submitting">
            <button class="w-full bg-green-600 hover:bg-green-600/80 text-white font-extrabold py-5 rounded-xl shadow-lg transition cursor-not-allowed flex items-center justify-center text-base uppercase tracking-wider gap-x-2">
              <span class="w-6 h-6" x-html="icon('loading')"></span>
              <span>Memproses Transaksi...</span>
            </button>
          </template>
          <template x-if="!submitting">
            <div class="space-y-1 mt-1">
              <button @click="simpanTransaksi(true)"
                :disabled="keranjang.length === 0"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-extrabold py-2.5 rounded-xl shadow-lg transition disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center text-base uppercase tracking-wider">
                <span class="w-5 h-5 mr-2 fill-current" x-html="icon('cetak')" />
                </span>
                <span>SIMPAN & CETAK STRUK <span class="shortcut-key">Ctrl + Enter</span></span>
              </button>
              <button @click="simpanTransaksi(false)"
                :disabled="keranjang.length === 0"
                class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-1.5 rounded-xl shadow-md transition disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center text-sm">
                <span class="fill-current w-4 h-4" x-html="icon('save')"></span>
                <span>Simpan Tanpa Cetak <span class="shortcut-key">Ctrl + S</span></span>
              </button>
            </div>
          </template>
        </div>

      </div>
    </div>
  </div>
  <div class="printFrame hidden"></div>
</div>

<script src="<?= ASSETS_URL . '/js/transaksiPage.js' ?>"></script>
<?php
include INCLUDES_PATH . "admin/layout/footer.php";
