<?php
page_require(['admin']);
$pageTitle = "Pengaturan Sistem";
include INCLUDES_PATH . "admin/layout/header.php";
?>

<div x-data="pengaturanPage()" x-init="init()" class="bg-gray-50 min-h-[100dvh] p-4 lg:p-6 space-y-4">
  <!-- HEADER -->
  <div>
    <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Pengaturan Sistem</h1>
    <p class="text-sm text-gray-500">Atur informasi toko dan sistem GG Mart di sini.</p>
  </div>

  <!-- FORM -->
  <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 space-y-6">
    <div>
      <!-- Tab navigasi -->
      <div class="flex flex-wrap gap-2 border-b pb-2 text-sm">
        <template x-for="(tab, index) in tabs" :key="tab.name">
          <button @click="currentTab = index"
            class="px-4 py-2 rounded-lg font-medium"
            :class="currentTab === index ? 'bg-gg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
            <span x-text="tab.label"></span>
          </button>
        </template>
      </div>


      <!-- Konten tab -->
      <template x-if="loading">
        <div class="flex justify-center items-center py-10">
          <span class="fill-current w-4 h-4" x-html="icon('loading')"></span>
        </div>
      </template>

      <template x-if="!loading">
        <div class="pt-5">

          <!-- SISTEM -->
          <template x-if="currentTab === 0">
            <!-- Sistem -->
            <div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label>Nomor WhatsApp</label>
                  <input type="text" x-model="settings.nomor_wa">
                </div>
              </div>

              <div class="mt-3">
                <button @click="simpanSettings" :disabled="loading"
                  class="btn bg-gg-primary text-white hover:bg-gg-primary-dark flex items-center gap-2">
                  <span class="h-4 w-4 fill-current" x-html="icon('save')"></span>
                  <span>Simpan Perubahan</span>
                </button>
              </div>

            </div>
          </template>

          <!-- CAROUSEL/HERO -->
          <template x-if="currentTab === 1">
            <div class="space-y-4">

            </div>
          </template>

        </div>
      </template>
    </div>
  </div>
</div>

<script src="<?= ASSETS_URL ?>/js/pengaturanPage.js"></script>
<?php include INCLUDES_PATH . "admin/layout/footer.php"; ?>