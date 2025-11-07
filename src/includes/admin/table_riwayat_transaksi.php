<!-- RIWAYAT TRANSAKSI -->
<div class="space-y-3 relative">
  <!-- LOADING OVERLAY -->
  <template x-if="loading">
    <div class="absolute inset-0 text-gg-primary top-32 z-10 flex justify-center">
      <span class="w-12 h-12" x-html="icon('loading')"></span>
    </div>
  </template>

  <!-- INFO FILTER & RINGKASAN TOTAL -->
  <template x-if="!loading">
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-gray-700 space-y-3">

      <!-- Bagian Informasi Filter -->
      <div class="space-y-1" x-show="!filter.search && (filter.start || filter.end || filter.metode)">
        <!-- Periode -->
        <template x-if="filter.start || filter.end">
          <p>
            <span class="font-semibold text-amber-700">Periode:</span>
            <span class="font-medium">
              <template x-if="filter.start && filter.end && filter.start != filter.end">
                <span x-text="`${formatDate(filter.start)} - ${formatDate(filter.end)}`"></span>
              </template>
              <template x-if="filter.start && filter.end && filter.start == filter.end">
                <span x-text="formatDate(filter.start)"></span>
              </template>
              <template x-if="filter.start && !filter.end">
                <span x-text="`${formatDate(filter.start)} - Sekarang`"></span>
              </template>
              <template x-if="!filter.start && filter.end">
                <span x-text="`Awal - ${formatDate(filter.end)}`"></span>
              </template>
            </span>
          </p>
        </template>

        <!-- Metode -->
        <template x-if="filter.metode">
          <p>
            <span class="font-semibold text-amber-700">Metode:</span>
            <span class="font-medium uppercase" x-text="filter.metode"></span>
          </p>
        </template>

        <div class="border-t border-amber-200 my-2"></div>
      </div>

      <!-- Ringkasan Total -->
      <div class="space-y-1">
        <p>
          <span class="font-semibold text-amber-700">Total Pokok:</span>
          <span class="font-medium text-gray-700" x-text="formatRupiah(totalSummary.pokok)"></span>
        </p>
        <p>
          <span class="font-semibold text-amber-700">Total Penjualan:</span>
          <span class="font-medium text-gray-800" x-text="formatRupiah(totalSummary.jual)"></span>
        </p>
        <p>
          <span class="font-semibold text-amber-700">Total Laba:</span>
          <span class="font-bold"
            :class="totalSummary.laba >= 0 ? 'text-emerald-600' : 'text-red-600'"
            x-text="formatRupiah(totalSummary.laba)"></span>
        </p>
      </div>
    </div>
  </template>

  <div class="overflow-auto max-h-[80dvh] custom-scrollbar bg-white rounded-xl shadow-lg border border-gray-100">
    <table class="app-table min-w-full text-sm text-gray-700">
      <thead class="sticky top-0 bg-gray-100">
        <tr>
          <th>#</th>
          <th>Kode Transaksi</th>
          <th>Tanggal</th>
          <th>Kasir</th>
          <th class="text-right">Total Pokok/Beli</th>
          <th class="text-right">Total Harga/Jual</th>
          <th class="text-center">Metode Bayar</th>
          <th class="text-right">Total Laba</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>

      <tbody>
        <template x-if="!loading && transaksi.length > 0">
          <template x-for="t, i in transaksi" :key="t.kode_transaksi">
            <tr class="border-b hover:bg-gray-50 transition">
              <!-- NO -->
              <td x-text="i + 1 + (pagination.page-1)*pagination.limit"></td>
              <!-- KODE TRANSAKSI -->
              <td class="p-3 font-semibold text-gray-800">
                <span class="text-sm md:text-base text-gg-primary" x-text="t.kode_transaksi"></span>
              </td>

              <!-- TANGGAL -->
              <td class="text-gray-600 text-sm md:text-base" x-text="formatDateTime(t.tanggal_transaksi)"></td>

              <!-- KASIR -->
              <td class="text-gray-700 font-medium" x-text="t.kasir"></td>

              <!-- TOTAL POKOK -->
              <td class="text-right whitespace-nowrap"
                x-text="formatRupiah(t.total_pokok)"></td>

              <!-- TOTAL HARGA -->
              <td class="text-right font-semibold text-gray-800 whitespace-nowrap"
                x-text="formatRupiah(t.total_harga)"></td>

              <!-- METODE BAYAR -->
              <td class="text-center uppercase font-semibold">
                <span class="px-2 py-1 rounded-md text-xs border"
                  :class="{
                  'bg-blue-500/10 text-blue-600 border-blue-500': t.metode_bayar === 'qris','bg-green-500/10 text-green-600 border-green-500': t.metode_bayar === 'tunai'
                  }"
                  x-text="t.metode_bayar"></span>
              </td>

              <!-- TOTAL LABA -->
              <td class="text-right font-semibold whitespace-nowrap text-green-600"
                x-text="formatRupiah(parseFloat(t.total_harga) - parseFloat(t.total_pokok))"></td>

              <!-- AKSI -->
              <td class="text-center">
                <div class="flex justify-center items-center gap-2">
                  <button @click="lihatDetail(t.kode_transaksi)"
                    class="text-blue-600 flex items-center hover:text-blue-800 transition p-1 rounded-full"
                    title="Detail Transaksi">
                    <span class="h-5 w-5" x-html="icon('mata')"></span>
                    <span class="hidden md:inline-block font-normal"> Detail</span>
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
  <template x-if="!loading && transaksi.length > 0">
    <div class="flex flex-col sm:flex-row justify-between items-center p-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
      <p class="text-sm text-gray-500" x-text="`Menampilkan ${transaksi.length} dari ${pagination.total} transaksi`"></p>
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