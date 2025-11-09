<?php
page_require(['admin', 'manager']);
$pageTitle = "Kelola Kategori";
include INCLUDES_PATH . "admin/layout/header.php";
?>

<div x-data="kelolaKategoriPage()" x-init="fetchKategori()" class="bg-gray-50 p-3 lg:pt-6 space-y-4">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">Kelola Kategori</h1>
      <p class="text-sm text-gray-500">Pantau dan kelola seluruh kategori GG MART dari sini.</p>
    </div>

    <div class="flex items-center gap-3">
      <!-- SEARCH -->
      <form @submit.prevent="doSearch()">
        <div class="relative">
          <input type="text" id="filter_search" x-model="search"
            placeholder="Cari kategori..."
            class="w-full form-input h-10 border border-gray-300 rounded-lg pl-10 pr-4 text-sm focus:border-gg-primary focus:ring-gg-primary">
          <span>
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-400">
              <span class="w-4 h-4" x-html="icon('cari')"></span>
            </button>
          </span>
        </div>
      </form>

      <a x-show="hasRole(['admin'])" :href="baseUrl + '/admin/kategori/form?act=tambah'"
        class="btn btn-accent w-auto">
        <span class="me-1 w-4 h-4" x-html="icon('tambah')"></span>
        <span class="hidden md:inline me-1">Tambah</span>Kategori
      </a>
    </div>
  </div>

  <?php include INCLUDES_PATH . '/admin/table_kategori.php' ?>


  <template x-if="!loading && kategori.length === 0">
    <div class="bg-white rounded-xl shadow-lg p-12 text-center border-2 border-dashed border-gray-300">
      <h3 class="text-xl font-semibold text-gray-500">Tidak ada kategori</h3>
      <p class="text-sm text-gray-500 mb-4">Tambahkan kategori baru untuk mulai mengelompokan produk.</p>
      <a x-show="hasRole(['admin'])" :href=" baseUrl + '/admin/kategori/form?act=tambah'"
        class="btn btn-primary px-6 py-2.5 w-auto">Tambah Kategori</a>
    </div>
  </template>

</div>


<script src="<?= ASSETS_URL . '/js/kelolaKategoriPage.js' ?>"></script>
<?php include INCLUDES_PATH . "/admin/layout/footer.php"; ?>