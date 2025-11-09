<!-- LIST USER -->
<div class="space-y-3 relative">
  <!-- LOADING OVERLAY -->
  <template x-if="loading">
    <div class="absolute inset-0 text-gg-primary top-32 z-10 flex justify-center">
      <span class="w-12 h-12" x-html="icon('loading')"></span>
    </div>
  </template>

  <!-- TABEL USER -->
  <div class="overflow-auto max-h-[80dvh] custom-scrollbar bg-white rounded-xl shadow-lg border border-gray-100">
    <table class="app-table min-w-full text-sm text-gray-700">
      <thead class="sticky top-0 bg-gray-100 text-gray-700">
        <tr>
          <th>#</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>

      <tbody>
        <template x-if="!loading && users.length > 0">
          <template x-for="(u, i) in users" :key="u.id_user">
            <tr>
              <!-- NO -->
              <td class="p-3" x-text="(pagination.page - 1) * pagination.limit + i + 1"></td>

              <!-- NAMA -->
              <td class="p-3 font-semibold text-gray-800" x-text="u.nama"></td>

              <!-- EMAIL -->
              <td class="p-3 text-gray-600" x-text="u.email"></td>

              <!-- ROLE -->
              <td class="p-3 text-center">
                <span class="px-2 py-1 rounded-md text-xs border uppercase font-semibold"
                  :class="{
                    'bg-red-500/10 text-red-600 border-red-500': u.role === 'admin',
                    'bg-blue-500/10 text-blue-600 border-blue-500': u.role === 'manager',
                    'bg-yellow-500/10 text-yellow-600 border-yellow-500': u.role === 'user',
                    'bg-green-500/10 text-green-600 border-green-500': u.role === 'kasir'
                  }"
                  x-text="u.role">
                </span>
              </td>

              <!-- AKSI -->
              <td class="text-center">
                <div class="flex justify-center items-center gap-2">
                  <a :href='`${baseUrl}/admin/user/form?id=${u.id_user}`'
                    class="text-blue-600 hover:text-blue-800 transition p-1 rounded-full"
                    title="Edit Kategori">
                    <span class="h-5 w-5" x-html="icon('edit')"></span>
                    <span class="hidden md:inline-block font-normal"> Edit</span>
                  </a>
                  <button @click="hapusUser(u.id_user)"
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
  <template x-if="!loading && users.length > 0">
    <div class="flex flex-col sm:flex-row justify-between items-center p-4 border-t border-gray-100 gap-4 bg-gray-50 rounded-b-xl">
      <p class="text-sm text-gray-500">
        <span x-text="'Menampilkan '"></span>
        <select class="inline-block w-16" name="limit" @change="fetchUsers()" x-model="pagination.limit" id="limit">
          <option value="10">10</option>
          <option value="20">25</option>
          <option value="50">50</option>
        </select>
        <span x-text="` dari ${pagination.total} User`"></span>
      </p>
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