<div class="space-y-3 relative">
  <!-- LOADING OVERLAY -->
  <template x-if="loading">
    <div class="absolute inset-0 text-gg-primary top-32 z-10 flex justify-center">
      <span class="w-12 h-12" x-html="icon('loading')"></span>
    </div>
  </template>

  <div class="overflow-auto max-h-[80dvh] custom-scrollbar bg-white rounded-xl shadow-lg border border-gray-100">
    <table class="app-table text-gray-700">
      <thead>
        <tr>
          <th>#</th>
          <th>Tanggal</th>
          <th>Nama Produk</th>
          <th>Masuk/Keluar</th>
          <th>Sisa Stok</th>
          <th>Harga Pokok/Beli</th>
          <th>Keterangan</th>
          <th x-show="hasRole(['admin'])">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <template x-if="mutasiStok.length > 0 && !loading">
          <template x-for="ms,i in mutasiStok" :key="ms.id_mutasi">
            <tr>
              <!-- NO -->
              <td x-text="i + 1 + (pagination.page-1)*pagination.limit"></td>
              <td x-text="formatDateTime(ms.tanggal)"></td>
              <td x-text="ms.nama_dari_produk ?? ms.nama_produk "></td>
              <td class="text-right text-xs">
                <span :class="ms.type == 'masuk' ? 'bg-green-500/10 text-green-600 border-green-500' : 'bg-red-500/10 border-red-500 text-red-600'" class="uppercase fontme font-medium px-2 py-1 rounded-md border" x-text="`${ms.type} ${ms.jumlah} ${ms.satuan_dasar ?? '' }`"></span>
              </td>
              <td class=" text-right text-xs">
                <template x-if="ms.type == 'keluar'"> <span> - </span> </template>
                <template x-if="ms.type == 'masuk'">
                  <span :class="`${ms.sisa_stok < 5 ? 'bg-red-500/10 text-red-500 border-red-600' : ms.sisa_stok == ms.jumlah ? 'bg-green-500/10 text-green-600 border-green-500' : 'bg-yellow-500/10 text-yellow-500 border-yellow-500'}`" class="border uppercase font-medium px-2 py-1 rounded-md" x-text="`Tersisa ${ms.sisa_stok} ${ms.satuan_dasar ?? '' }`"></span>
                </template>
              </td>
              <td class="text-right">
                <span x-text="formatRupiah(ms.harga_pokok)" class="font-medium"></span>
              </td>
              <td><span x-text="ms.keterangan ?? '-'"></span></td>

              <!-- AKSI -->
              <td x-show="hasRole(['admin'])" class="text-center">
                <template x-if="ms.type != 'keluar' && ms.jumlah == ms.sisa_stok">
                  <div class="flex justify-center items-center gap-2">
                    <button @click="hapusMutasiStok(ms.id_mutasi)"
                      class="text-red-600 hover:text-red-800 transition p-1 rounded-full"
                      title="Hapus Mutasi"
                      :disabled="submitting">
                      <span class="h-5 w-5" x-html="icon('hapus')"></span>
                      <span class="hidden md:inline-block font-normal"> Hapus</span>
                    </button>
                  </div>
                </template>
              </td>
            </tr>
          </template>
        </template>
      </tbody>
    </table>
  </div>

  <!-- PAGINATION -->
  <template x-if="!loading && mutasiStok.length > 0">
    <div class="flex flex-col sm:flex-row justify-between items-center p-4 border-t border-gray-100 gabg-gray-50 rounded-b-xl">
      <p class="text-sm text-gray-500" x-text="`Menampilkan ${mutasiStok.length} dari ${pagination.total} data`"></p>
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