<?php
page_require(['admin', 'manager']);
$pageTitle = "Kelola Stok";
include INCLUDES_PATH . "/admin/layout/header.php";
?>

<div x-data="kelolaStokPage()" x-init="fetchMutasiStok()" class="bg-gray-50 p-4 lg:p-6 space-y-4">

  <!-- HEADER -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">Kelola Perubahan Stok Produk</h1>
      <p class="text-sm text-gray-500">Pantau dan kelola perubahan stok produk GG MART dari sini.</p>
    </div>

    <div class="flex items-center gap-3">
      <button @click="showFilter = !showFilter"
        class="md:hidden btn btn-gray w-auto flex items-center gap-1 rounded-lg">
        <span class="w-5 h-5" x-html="icon('filter')">
          Filter
      </button>
      <a x-show="hasRole(['admin'])" :href="baseUrl + '/admin/stok/form'"
        class="btn btn-accent w-auto">
        <span class="me-1 w-5 h-5" x-html="icon('tambah')"></span>
        <span class="hidden sm:inline me-1">Tambah</span>Perubahan Stok
      </a>
    </div>
  </div>

  <!-- FILTER -->
  <div :class="showFilter ? 'block' : 'hidden md:block'"
    x-transition
    class="bg-white rounded-xl shadow-md p-4 border border-gray-100 animate-fade">

    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3 items-end">

      <div class="lg:col-span-3">
        <form @submit.prevent="applyFilter()">
          <label for="filter_search" class="text-xs font-semibold text-gray-600 mb-1 block">Cari Produk</label>
          <div class="relative">
            <input type="text" id="filter_search" x-model="filter.search"
              placeholder="Cari Produk"
              class="pl-10 pr-4">
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer">
              <span class="w-4 h-4 text-gray-400" x-html="icon('cari')">
            </button>
          </div>
        </form>
      </div>

      <div class="sm:col-span-1">
        <label for="filter_type" class="text-xs font-semibold text-gray-600 mb-1 block">Tipe</label>
        <select id="filter_type" x-model="filter.type" @change="applyFilter()">
          <option value="">Semua</option>
          <option value="masuk">Stok Masuk</option>
          <option value="keluar">Stok Keluar</option>
        </select>
      </div>

      <!-- BUTTONS -->
      <div class="col-span-4 lg:col-span-2 flex gap-3 pt-2 lg:mt-0 border-gray-300 border-t lg:border-none">
        <button type="button"
          @click="resetFilter"
          :disabled="!filter.search && filter.type === ''"
          class="btn btn-gray flex items-center justify-center gap-2 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
          <span x-html="icon('refresh')" class="w-4 h-4 text-gray-400"></span>
          <span>Reset</span>
        </button>
      </div>
    </div>
  </div>

  <!-- TABLE -->
  <?php include INCLUDES_PATH . '/admin/table_mutasi_stok.php' ?>

  <!-- KOSONG -->
  <template x-if="!loading && mutasiStok.length === 0">
    <div class="bg-white rounded-xl shadow-lg p-12 text-center border-2 border-dashed border-gray-300">
      <div class="mx-auto w-24 h-24 flex items-center justify-center mb-4 text-gray-400">
        <span x-html="icon('stok')">
      </div>
      <h3 class="text-xl font-semibold text-gray-800">Belum ada perubahan stok</h3>
      <p class="text-sm text-gray-500 mb-4">Tambahkan perubahan stok untuk memulai pencatatan.</p>
      <a x-show="hasRole(['admin'])" :href="baseUrl + '/admin/stok/form'" class="btn btn-accent px-6 py-2.5 w-auto">
        + Tambah perubahan stok</span>
      </a>
    </div>
  </template>
</div>

<script src="<?= ASSETS_URL . '/js/kelolaStokPage.js' ?>"></script>
<?php include INCLUDES_PATH . "/admin/layout/footer.php"; ?>