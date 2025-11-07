<?php
page_require(['admin']);
$pageTitle = "Kelola User";
include INCLUDES_PATH . "/admin/layout/header.php";
?>

<div x-data="kelolaUserPage()" x-init="fetchUsers()" class="bg-gray-50 p-4 lg:p-6 space-y-4">

  <!-- HEADER -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">Kelola User</h1>
      <p class="text-sm text-gray-500">Pantau dan kelola data akun pengguna GG MART.</p>
    </div>

    <div class="flex items-center gap-3">
      <button @click="showFilter = !showFilter"
        class="md:hidden btn btn-gray w-auto flex items-center gap-1 rounded-lg">
        <span class="h-4 w-4" x-html="icon('filter')"></span>
        Filter
      </button>
      <a :href="baseUrl + '/admin/user/form'"
        class="btn btn-accent px-5 py-2.5 w-auto rounded-lg font-semibold">
        <span class="me-1">+</span>
        <span class="hidden sm:inline me-1">Tambah</span>User
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
          <label for="filter_search" class="text-xs font-semibold text-gray-600 mb-1 block">Cari User</label>
          <div class="relative">
            <input type="text" id="filter_search" x-model="filter.search"
              placeholder="Cari nama atau email..."
              class="pl-10 pr-4">
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer">
              <span class="w-4 h-4 text-gray-400" x-html="icon('search')"></span>
            </button>
          </div>
        </form>
      </div>

      <div class="sm:col-span-1">
        <label for="filter_role" class="text-xs font-semibold text-gray-600 mb-1 block">Role</label>
        <select id="filter_role" x-model="filter.role" @change="applyFilter()">
          <option value="">Semua</option>
          <option value="user">User</option>
          <option value="kasir">Kasir</option>
          <option value="manager">Manager</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <!-- BUTTONS -->
      <div class="col-span-4 lg:col-span-2 flex gap-3 pt-2 lg:mt-0 border-gray-300 border-t lg:border-none">
        <button type="button"
          @click="resetFilter"
          :disabled="!filter.search && filter.role === ''"
          class="btn btn-gray flex items-center justify-center gap-2 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
          <span class="w-5 h-5" x-html="icon('refresh')"></span>
          <span>Reset</span>
        </button>
      </div>
    </div>
  </div>


  <!-- TABLE -->
  <?php include INCLUDES_PATH . '/admin/table_user.php' ?>

  <!-- KOSONG -->
  <template x-if="!loading && users.length === 0">
    <div class="bg-white rounded-xl shadow-lg p-12 text-center border-2 border-dashed border-gray-300">
      <div class="mx-auto w-24 h-24 flex items-center justify-center mb-4 text-gray-400">
        <span class="w-20 h-20" x-html="icon('user')"></span>
      </div>
      <h3 class="text-xl font-semibold text-gray-800">Belum ada data user</h3>
      <p class="text-sm text-gray-500 mb-4">Tambahkan user baru untuk memulai pengelolaan akun.</p>
      <a :href="baseUrl + '/admin/user/form'" class="btn btn-accent px-6 py-2.5 w-auto">
        + Tambah User
      </a>
    </div>
  </template>
</div>

<script src="<?= ASSETS_URL . '/js/kelolaUserPage.js' ?>"></script>
<?php include INCLUDES_PATH . "/admin/layout/footer.php"; ?>