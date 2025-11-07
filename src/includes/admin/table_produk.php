<!-- DAFTAR PRODUK -->
<div class="space-y-3 relative">
  <!-- LOADING OVERLAY -->
  <template x-if="loading">
    <div class="absolute inset-0 text-gg-primary top-32 z-10 flex justify-center">
      <span class="w-12 h-12" x-html="icon('loading')"></span>
    </div>
  </template>

  <div class="overflow-auto max-h-[80dvh] custom-scrollbar bg-white rounded-xl shadow-lg border border-gray-100">
    <table class="app-table min-w-full text-sm text-gray-700">
      <thead class="sticky top-0 bg-gray-100">
        <tr>
          <th class="w-[30px] text-center">#</th>
          <th class="w-[280px]">Produk</th>
          <th class="w-28">Harga Jual</th>
          <th class="text-center w-28">Stok & Terjual</th>
          <th class="">Deskripsi</th>
          <th class="text-center w-24">Lokal</th>
          <th x-show="hasRole(['admin'])" class="text-center w-32">Aksi</th>
        </tr>
      </thead>

      <tbody>
        <template x-if="produk.length> 0 && !loading">
          <template x-for="p, i in produk" :key="p.kode_produk">
            <tr class="border-b hover:bg-gray-50 transition">
              <!-- NO -->
              <td class="text-center whitespace-nowrap" x-text="i + 1 + (pagination.page-1)*pagination.limit"></td>
              <!-- PRODUK -->
              <td class="p-3">
                <div class="flex items-center gap-4">
                  <img
                    :src="p.gambar ? `${uploadsUrl}/${p.gambar}` : `${assetsUrl}/img/no-image.webp`"
                    alt="gambar produk"
                    class="w-16 h-16 object-cover rounded-lg border border-gray-200 shadow-sm flex-shrink-0" />
                  <div class="flex flex-col gap-1 font-semibold">
                    <span class="text-gray-800 text-sm md:text-base line-clamp-1" x-text="p.nama_produk"></span>
                    <span class="text-xs text-gray-400" x-text="`Kode: ${p.kode_produk}`"></span>
                    <span class="text-xs text-gray-500" x-text="'Kategori: ' + p.nama_kategori || 'Tanpa Kategori'"></span>
                  </div>
                </div>
              </td>

              <!-- HARGA -->
              <td class="font-semibold text-gray-800 whitespace-nowrap" x-text="formatRupiah(p.harga_jual)"></td>

              <!-- STOK & TERJUAL -->
              <td class="text-center">
                <div class="flex flex-col justify-center items-center gap-y-2">
                  <span class="w-full px-1 py-0.5 rounded-md text-xs font-semibold border"
                    :class="p.stok <= 5 ? 'bg-red-500/10 border-red-500 text-red-600' : 'bg-gg-primary/10 border-gg-primary text-gg-primary'"
                    x-text="`Tersedia: ${p.stok} ${p.satuan_dasar}`"></span>
                  <span class="w-full px-1 py-0.5 rounded-md text-xs font-semibold bg-blue-500/10 text-blue-600 border border-blue-500"
                    x-text="`Terjual: ${p.terjual} ${p.satuan_dasar}`"></span>
                </div>
              </td>

              <!-- DESKRIPSI -->
              <td class="text-gray-600 text-sm max-w-72 align-top">
                <span
                  class="block whitespace-normal"
                  x-text="(p.deskripsi?.length > 150) ? p.deskripsi.substring(0, 150) + '…' : (p.deskripsi || '-')"></span>
              </td>

              <!-- IS LOKAL -->
              <td class="text-center">
                <span
                  :class="p.is_lokal == 1 ? 'bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-md text-xs font-semibold' : 'bg-gray-100 text-gray-500 px-2 py-0.5 rounded-md text-xs font-semibold'"
                  x-text="p.is_lokal == 1 ? 'YA' : 'TIDAK'"></span>
              </td>

              <!-- AKSI -->
              <td x-show="hasRole(['admin'])" class="text-center">
                <div class="flex justify-center items-center gap-2">
                  <a :href="`${baseUrl}/admin/produk/form?id=${p.kode_produk}&act=edit`"
                    class="text-blue-600 hover:text-blue-800 transition p-1 rounded-full"
                    title="Edit Produk">
                    <span class="h-5 w-5" x-html="icon('edit')"></span>
                    <span class="hidden md:inline-block font-normal"> Edit</span>
                  </a>

                  <button @click="hapusProduk(p.kode_produk)"
                    class="text-red-600 hover:text-red-800 transition p-1 rounded-full"
                    title="Hapus Produk"
                    :disabled="submitting">

                    <span class="h-5 w-5" x-html="icon('hapus')"></span>
                    <span class="hidden md:inline-block font-normal"> Hapus</span>
                  </button>
                </div>
              </td>
            </tr>
          </template>
        </template>
      </tbody>
    </table>
  </div>

  <!-- PAGINATION -->
  <template x-if="!loading && produk.length > 0">
    <div class="flex flex-col sm:flex-row justify-between items-center p-4 border-t border-gray-100 gabg-gray-50 rounded-b-xl">
      <p class="text-sm text-gray-500" x-text="`Menampilkan ${produk.length} dari ${pagination.total} data`"></p>
      <div class="flex flex-wrap gap-2">
        <button @click="prevPage" :disabled="pagination.page === 1"
          class="btn px-3 py-1 w-auto shadow-none bg-gray-100 text-gray-700 disabled:opacity-40 hover:bg-gray-200">‹</button>

        <template x-for="n in pagination.total_pages" :key="n">
          <button @click="goPage(n)"
            :class="pagination.page == n ? 'bg-gg-primary text-white shadow-sm' : 'border border-gray-300 text-gray-700 hover:bg-gray-100'"
            class="btn px-3 py-1 w-auto shadow-none rounded-md">
            <span x-text="n"></span>
          </button>
        </template>

        <button @click="nextPage" :disabled="pagination.page == pagination.total_pages"
          class="btn px-3 py-1 w-auto shadow-none bg-gray-100 text-gray-700 disabled:opacity-40 hover:bg-gray-200">›</button>
      </div>
    </div>
  </template>
</div>