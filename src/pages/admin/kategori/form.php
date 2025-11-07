<?php
page_require(['admin']);
$act = $_GET['act'] ?? 'tambah';
$id  = $_GET['id'] ?? null;
$pageTitle = ($act === 'edit') ? "Edit Kategori" : "Tambah Kategori";

include INCLUDES_PATH . "/admin/layout/header.php";
?>

<div x-data="kategoriFormPage('<?= $act ?>', '<?= $id ?>')" x-init="initPage()" class="bg-gray-50 min-h-[100dvh] p-4 lg:p-6 space-y-4">
  <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-6 space-y-6">

    <div class="flex items-center justify-between border-b pb-4 border-gray-200">
      <h1 class="text-2xl font-bold text-gray-800" x-text="formTitle"></h1>
      <a :href="baseUrl + '/admin/kategori'" class="text-sm text-gray-500 hover:text-emerald-600 font-medium transition">← Kembali</a>
    </div>

    <form @submit.prevent="submitForm" class="space-y-5">
      <div>
        <label>Nama Kategori</label>
        <input type="text" x-model="form.nama_kategori" placeholder="Nama kategori"
          autofocus required>
      </div>

      <div>
        <label>Deskripsi</label>
        <textarea x-model="form.deskripsi" rows="4" placeholder="Deskripsi (opsional)"></textarea>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
        <button type="submit"
          class="btn btn-primary px-5 gap-2 py-2 w-auto"><span class="h-5 w-5" x-html="icon('save')"></span>Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src=" <?= ASSETS_URL . 'js/kategoriFormPage.js' ?>"></script>
<?php include INCLUDES_PATH . "admin/layout/footer.php"; ?>