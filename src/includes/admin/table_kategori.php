<div class="space-y-3 relative">
  <!-- LOADING OVERLAY -->
  <template x-if="loading">
    <div class="absolute inset-0 text-gg-primary top-32 z-10 flex justify-center">
      <span class="w-12 h-12" x-html="icon('loading')"></span>
    </div>
  </template>

  <div class="overflow-auto max-h-[80dvh] custom-scrollbar bg-white rounded-xl shadow-lg border border-gray-100">
    <table class="app-table">
      <thead>
        <tr>
          <th class="w-1">#</th>
          <th>Nama Kategori</th>
          <th>Deskripsi</th>
          <th x-show="hasRole(['admin'])" class="w-1 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <template x-if="!loading && kategori.length > 0">
          <template x-for="(k, i) in kategori" :key="k.id_kategori">
            <tr>
              <td x-text="i + 1 + (pagination.page-1)*pagination.limit"></td>
              <td class="font-semibold text-gray-800 min-w-[200px]" x-text="k.nama_kategori"></td>
              <td class="text-gray-600 truncate max-w-xs" x-text="k.deskripsi || '-'"></td>
              <td x-show="hasRole(['admin'])" class="text-center">
                <div class="flex justify-center items-center gap-2">
                  <a :href="baseUrl + '/admin/kategori/form?act=edit&id=' + k.id_kategori"
                    class="text-blue-600 hover:text-blue-800 transition p-1 rounded-full"
                    title="Edit Kategori">
                    <span class="h-5 w-5" x-html="icon('edit')"></span>
                    <span class="hidden md:inline-block font-normal"> Edit</span>
                  </a>
                  <button @click="hapusKategori(k.id_kategori)"
                    class="text-red-600 hover:text-red-800 transition p-1 rounded-full"
                    title="Hapus Kategori">

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
  <div x-show="!loading" class="flex flex-col md:flex-row justify-between items-center p-4 border-t border-gray-100 gap-3 bg-gray-50">
    <p class="text-sm text-gray-500" x-text="`Menampilkan ${kategori.length} dari ${pagination.total} kategori`"></p>
    <div class="flex flex-wrap gap-2">
      <button @click="prevPage" :disabled="pagination.page === 1"
        class="px-3 py-1 border rounded-md disabled:opacity-40 hover:bg-gray-100">‹</button>
      <template x-for="n in pagination.total_pages" :key="n">
        <button @click="goPage(n)"
          :class="{'bg-gg-primary text-white shadow-sm': pagination.page === n, 'border text-gray-700 hover:bg-gray-100': pagination.page !== n}"
          class="px-3 py-1 rounded-md transition">
          <span x-text="n"></span>
        </button>
      </template>
      <button @click="nextPage" :disabled="pagination.page === pagination.total_pages"
        class="px-3 py-1 border rounded-md disabled:opacity-40 hover:bg-gray-100">›</button>
    </div>
  </div>
</div>