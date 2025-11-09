<?php
page_require(['admin', 'manager']);
$pageTitle = "Kelola Produk";
include INCLUDES_PATH . "/admin/layout/header.php";
?>

<div x-data="kelolaProdukPage()" x-init="fetchProduk()" class="bg-gray-50 p-3 lg:pt-6 space-y-4">

  <!-- HEADER -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">Kelola Produk</h1>
      <p class="text-sm text-gray-500">Atur dan pantau semua produk GGMART dari sini.</p>
    </div>

    <div class="flex items-center gap-3">
      <button @click="showFilter = !showFilter"
        class="md:hidden btn btn-gray w-auto flex items-center gap-1 rounded-lg">
        <span class="w-5 h-5" x-html="icon('filter')"></span>
        Filter
      </button>

      <a x-show="hasRole(['admin'])" :href="baseUrl + '/admin/produk/form'"
        class="btn btn-accent w-auto">
        <span class="me-1 w-5 h-5" x-html="icon('tambah')"></span>
        <span class="hidden sm:inline me-1">Tambah</span>Produk
      </a>
    </div>
  </div>

  <!-- FILTER -->
  <div :class="showFilter ? 'block' : 'hidden md:block'"
    x-transition
    class="bg-white rounded-xl shadow-md p-4 border border-gray-100 animate-fade">

    <div class="grid grid-cols-4 lg:grid-cols-8 gap-3 items-end">
      <!-- SEARCH -->
      <div class=" col-span-4 lg:col-span-3">
        <form @submit.prevent="applyFilter()">
          <label for="filter_search" class="text-xs font-semibold text-gray-600 mb-1 block">
            Cari Produk
          </label>
          <div class="relative">
            <input type="text" id="filter_search" x-model="filter.search"
              placeholder="Cari produk..."
              class="pl-10 pr-4">
            <span>
              <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer"><span class="w-4 h-4 text-gray-400" x-html="icon('cari')"></span></button>
            </span>
          </div>
        </form>
      </div>

      <!-- SORT -->
      <div class="col-span-2 lg:col-span-2">
        <label for="filter_sort" class="text-xs font-semibold text-gray-600 mb-1 block">Urutkan Berdasarkan</label>
        <select id="filter_sort" x-model="filter.sort" @change="applyFilter()">
          <option value="tanggal_dibuat">Tanggal Dibuat</option>
          <option value="nama_produk">Nama Produk</option>
          <option value="stok">Stok</option>
          <option value="terjual">Terjual</option>
        </select>
      </div>

      <!-- DIRECTION -->
      <div class="col-span-2 lg:col-span-1">
        <label for="filter_dir" class="text-xs font-semibold text-gray-600 mb-1 block">Arah</label>
        <select id="filter_dir" x-model="filter.dir" @change="applyFilter()">
          <option value="DESC">Menurun</option>
          <option value="ASC">Menaik</option>
        </select>
      </div>

      <!-- BUTTONS -->
      <div class="col-span-4 lg:col-span-2 flex gap-3 pt-2 lg:mt-0 border-gray-300 border-t lg:border-none">
        <button type="button"
          @click="resetFilter"
          :disabled="!filter.search && filter.sort === 'tanggal_dibuat' && filter.dir === 'DESC'"
          class="btn btn-gray gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
          <span class="w-4 h-4" x-html="icon('refresh')"></span>
          <span>Reset</span>
        </button>
      </div>
    </div>

  </div>

  <!-- TABEL -->
  <?php include INCLUDES_PATH . '/admin/table_produk.php' ?>

  <!-- KOSONG -->
  <template x-if="!loading && produk.length === 0">
    <div class="bg-white rounded-xl shadow-lg p-12 text-center border-2 border-dashed border-gray-300">
      <img :src="baseUrl + '/assets/img/no-image.webp'" alt="Tidak ada data"
        class="mx-auto w-28 h-28 opacity-60 mb-4">
      <h3 class="text-xl font-semibold text-gray-800">Tidak ada produk</h3>
      <a x-show="hasRole(['admin'])" :href="baseUrl + '/admin/produk/form'" class="btn btn-accent px-6 py-2.5 w-auto">
        + <span class="hidden sm:inline">Tambah Produk</span>
      </a>
    </div>
  </template>
</div>

<script src="<?= ASSETS_URL . '/js/kelolaProdukPage.js' ?>"></script>
<?php include INCLUDES_PATH . "/admin/layout/footer.php"; ?>