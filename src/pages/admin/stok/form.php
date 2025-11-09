<?php
page_require(['admin']);
$pageTitle = "Tambah Stok";
include INCLUDES_PATH . "/admin/layout/header.php";
?>

<div x-data="stokFormPage()" x-init="produkList.fetch()" class="bg-gray-50 p-4 lg:p-6">
  <div class="max-w-2xl mx-auto bg-white shadow-xl rounded-2xl border border-gray-200 p-6 space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between border-b pb-4">
      <h1 class="text-2xl font-bold text-gray-800">Tambah Perubahan Stok</h1>
      <a :href="baseUrl + '/admin/stok'"
        class="text-sm text-gray-500 hover:text-gg-primary transition font-medium">← Kembali</a>
    </div>

    <!-- Form -->
    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- STEP 1: Jenis Perubahan -->
      <template x-if="page === 1">
        <div class="space-y-4 animate-fade">
          <h2 class="text-lg font-semibold text-gray-700">1️⃣ Jenis Perubahan & Produk</h2>
          <div>
            <label>Jenis Perubahan</label>
            <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-1 sm:space-y-0">
              <label class="flex items-center justify-center w-full border-2 rounded-lg py-3 cursor-pointer transition-all"
                :class="form.type === 'masuk' ? 'border-green-500 bg-green-50 text-green-700 font-semibold' : 'border-gray-300 hover:bg-gray-50'">
                <input type="radio" value="masuk" x-model="form.type" class="sr-only"> Stok Masuk
              </label>

              <label class="flex items-center justify-center w-full border-2 rounded-lg py-3 cursor-pointer transition-all"
                :class="form.type === 'keluar' ? 'border-red-500 bg-red-50 text-red-700 font-semibold' : 'border-gray-300 hover:bg-gray-50'">
                <input type="radio" value="keluar" x-model="form.type" class="sr-only"> Stok Keluar
              </label>
            </div>
          </div>
          <!-- input dan dropdown produk -->
          <div class="relative" @click.outside="produkList.open = false">
            <label>Pilih Produk</label>
            <input
              type="text"
              x-model="produkList.query"
              @focus="produkList.open = true"
              @input.debounce.500ms="produkList.fetch()"
              placeholder="Ketik nama atau kode produk..." />

            <!-- Dropdown -->
            <div
              x-show="produkList.open"
              x-transition
              class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg 
                shadow-lg z-20 max-h-60 overflow-y-auto">
              <template x-if="produkList.loading">
                <div class="px-4 py-2 text-gray-500 italic">Memuat...</div>
              </template>

              <template x-if="!produkList.loading && produkList.data.length > 0">
                <div>
                  <template x-for="p in produkList.data" :key="p.kode_produk">
                    <div
                      @click="selectProdukList(p)"
                      class="px-4 py-2 cursor-pointer hover:bg-gg-primary/20 border-b border-gray-200">
                      <span x-text="p.nama_produk"></span>
                    </div>
                  </template>
                </div>
              </template>

              <template x-if="!produkList.loading && produkList.data.length === 0">
                <div class="px-4 py-2 text-gray-400 italic">Produk tidak ditemukan</div>
              </template>
            </div>
            <p class="text-xs text-gray-500">Pilih produk yang ingin diubah stoknya.</p>
          </div>

          <div class="flex justify-end">
            <button type="button" @click="nextPage()"
              class="btn btn-primary mt-3">Berikutnya</button>
          </div>
        </div>
      </template>

      <!-- STEP 2: Detail Perubahan-->
      <template x-if="page === 2">
        <div class="space-y-4 animate-fade">
          <h2 class="text-lg font-semibold text-gray-700">2️⃣ Detail Perubahan <span x-text="form.nama_produk"></span></h2>
          <p class="text-xs text-red-500" x-text="form.type == 'masuk' ? 'Setelah input, jika batch stok ini sudah dijual di transaksi maka tidak bisa dihapus' : 'Setelah input, perubahan stok keluar tidak bisa di hapus!'"></p>

          <!-- Jumlah -->
          <div>
            <label class=" block mb-1 font-medium capitalize">Jumlah <span x-text="form.type"></span> (<span x-text="produkList.satuan_dasar"></span>)</label>
            <input type="number" @input="syncHargaPokok('jumlah')" x-model="form.jumlah" min="1" required>
            <p class="text-xs text-gray-500">Jumlah stok <span x-text="form.type"></span> dalam <span x-text="produkList.satuan_dasar"></span>.</p>
          </div>

          <!-- Jika Masuk -->
          <template x-if="form.type === 'masuk'">
            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1 font-medium">Harga Pokok/Beli</label>
                <input step="0.00000000000001" type="number" x-model="form.harga_pokok" @input="syncHargaPokok('harga')" required
                  class="w-full rounded-lg focus:ring-gg-primary focus:border-gg-primary p-2.5">
                <span class="text-xs text-gray-400">harga modal pembelian dari pemasok atau produksi lokal</span>
              </div>
              <div>
                <label class="block mb-1 font-medium">Total Pokok</label>
                <input step="0.000000000000001" type="number" x-model="form.total_pokok" @input="syncHargaPokok('total')" required>
                <span class="text-xs text-gray-400">Total harga pembelian (harga pokok x jumlah)</span>
              </div>
            </div>
          </template>

          <!-- Jika Keluar -->
          <template x-if="form.type === 'keluar'">
            <div class="relative" @click.outside="mutasiList.open = false">
              <label class="block mb-1 font-medium">Pilih Mutasi/Batch</label>
              <!-- input -->
              <input @focus="mutasiList.open = true" @input.debounce.300="mutasiList.change()" type="text" x-model="mutasiList.query">
              <!-- dropdown -->
              <div class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 max-h-60 overflow-y-auto"
                x-transition
                x-show="mutasiList.open">
                <template x-if="mutasiList.filtered.length > 0">
                  <div>
                    <template x-for="m in mutasiList.filtered" :key="m.id_mutasi">
                      <div
                        @click="selectMutasiList(m)"
                        class="px-4 py-2 cursor-pointer hover:bg-gg-primary/20 border-b border-gray-200 text-xs md:text-base">
                        <span x-text="formatDateTime(m.tanggal)"></span>
                        <span class="text-blue-400" x-text="` Masuk ${m.jumlah} ${produkList.satuan_dasar}`"></span>
                      </div>
                    </template>
                  </div>
                </template>

                <template x-if="mutasiList.filtered.length === 0">
                  <div class="px-4 py-2 text-gray-400 italic">Mutasi tidak ditemukan</div>
                </template>
              </div>
              <span class="text-xs text-gray-400">Pilih mutasi/batch yang ingin dikurangi stoknya</span>
            </div>
          </template>

          <!-- Keterangan -->
          <div>
            <label class="block mb-1 font-medium">Keterangan (Opsional)</label>
            <textarea x-model="form.keterangan" rows="3"></textarea>
          </div>

          <!-- Tombol -->
          <div class="flex justify-end pt-4 gap-2 border-t border-gray-200">
            <button type="button" @click="page = 1" class="btn bg-gray-100 text-gray-700 hover:bg-gray-200 px-4">Kembali</button>
            <?php include INCLUDES_PATH . '/btn_simpan.php' ?>
          </div>
        </div>
      </template>
    </form>
  </div>
</div>

<script src="<?= ASSETS_URL . '/js/stokFormPage.js' ?>"></script>
<?php include INCLUDES_PATH . "/admin/layout/footer.php"; ?>