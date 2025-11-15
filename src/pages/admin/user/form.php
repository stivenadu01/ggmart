<?php
page_require(['admin']);
$pageTitle = "Form User";
include INCLUDES_PATH . "/admin/layout/header.php";

$id = $_GET['id'] ?? '';
?>

<div x-data="userFormPage('<?= $id ?>')" x-init="initForm()" class="bg-gray-50 min-h-[100dvh] p-4 lg:p-6 space-y-4">
  <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-6 space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between border-b pb-4 border-gray-200">
      <div>
        <h1 class="text-2xl font-bold text-gray-800"
          x-text="mode === 'edit' ? 'Edit User' : 'Tambah User'"></h1>
        <p class="text-sm text-gray-500"
          x-text="mode === 'edit' 
             ? 'Ubah data pengguna yang sudah terdaftar' 
             : 'Tambahkan akun pengguna baru ke sistem'"></p>
      </div>
      <a :href="baseUrl + '/admin/user'"
        class="text-sm text-gray-500 hover:text-emerald-600 font-medium transition">← Kembali</a>
    </div>

    <!-- Form -->
    <form @submit.prevent="submitForm" class="space-y-5">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <!-- Nama -->
        <div>
          <label for="nama" class="font-semibold text-gray-700">Nama Lengkap</label>
          <input type="text" id="nama" x-model="form.nama"
            placeholder="Masukkan nama lengkap"
            required>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="font-semibold text-gray-700">Email</label>
          <input type="email" id="email" x-model="form.email"
            placeholder="Masukkan email"
            required>
        </div>

        <!-- Password -->
        <div x-show="mode === 'tambah'">
          <label for="password" class="font-semibold text-gray-700">Password</label>
          <input type="password" id="password" x-model="form.password"
            placeholder="Masukkan password">
        </div>

        <!-- Konfirmasi Password -->
        <div x-show="mode === 'tambah'">
          <label for="rePassword" class="font-semibold text-gray-700">Konfirmasi Password</label>
          <input type="password" id="rePassword" x-model="form.rePassword"
            placeholder="Ulangi password">
        </div>

        <!-- Role -->
        <div>
          <label for="role" class="font-semibold text-gray-700">Role</label>
          <select id="role" x-model="form.role"
            required>
            <option value="">Pilih role...</option>
            <option value="user">User</option>
            <option value="kasir">Kasir</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>

      <!-- Tombol -->
      <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
        <a :href="baseUrl + '/admin/user'"
          class="btn btn-gray px-5 py-2 w-auto">Batal</a>

        <?php include INCLUDES_PATH . '/btn_simpan.php' ?>
      </div>
    </form>
  </div>
</div>

<script src="<?= ASSETS_URL . "js/admin/userFormPage.js" ?>"></script>

<?php include INCLUDES_PATH . "/admin/layout/footer.php"; ?>