<?php
page_require(['admin']);
$pageTitle = "Kelola Landing Hero";

include INCLUDES_PATH . "/admin/layout/header.php";
?>

<div x-data="landingHeroPage()" x-init="initPage()"
  class="bg-gray-50 p-4 lg:p-6">

  <div class="px-10 mx-auto bg-white shadow-xl rounded-2xl border border-gray-200 p-6 space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row gap-y-2 items-center justify-between border-b pb-4">
      <h1 class="text-2xl font-bold text-gray-800">Kelola Landing Hero</h1>
      <button @click="addSlide"
        class="px-6 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition font-medium">
        + Tambah Slide <span class="hidden md:inline">Baru</span>
      </button>
    </div>

    <!-- Navigation -->
    <template x-if="slides.length > 0">
      <div class="flex items-center justify-between mt-2">
        <button @click="prevSlide"
          class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
          ‹ Prev
        </button>

        <div class="font-semibold text-gray-700">
          Slide <span x-text="index + 1"></span> / <span x-text="slides.length"></span>
        </div>

        <button @click="nextSlide"
          class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
          Next ›
        </button>
      </div>
    </template>
    <template x-if="slides.length < 1">
      <div class="text-center py-20 text-gray-500">
        Belum ada slide. <a @click="addSlide" class="text-gg-primary cursor-pointer">Tambah Slide Baru</a> untuk membuat slide pertama Anda.
      </div>
    </template>

    <!-- Form -->
    <template x-if="!submitting">
      <form x-cloak x-show="slides.length > 0" @submit.prevent="saveSlide" enctype="multipart/form-data" class="space-y-6">
        <div class="space-y-4 animate-fade">

          <!-- IMAGE PREVIEW + UPLOAD -->
          <div>
            <label class="block font-medium mb-1">Gambar</label>
            <div
              x-ref="dropZone"
              @dragover.prevent="dragOver = true"
              @dragleave.prevent="dragOver = false"
              @drop.prevent="handleDrop($event)"
              :class="dragOver ? 'border-gg-primary bg-gg-primary/5' : 'border-gray-300 bg-gray-50'"
              class="border-2 border-dashed rounded-xl p-6 text-center transition-all cursor-pointer hover:border-gg-primary hover:bg-gray-100"
              @click="$refs.fileInput.click()">

              <!-- File input asli -->
              <input type="file" x-ref="fileInput" class="hidden" accept="image/*"
                @change="onFileChange">

              <!-- Isi Drop Zone -->
              <div class="flex flex-col items-center space-y-2">
                <span class="w-10 h-10 opacity-80" x-html="icon('gambar')"></span>

                <span class="font-medium text-gray-600"
                  x-text="'Seret & lepas file di sini, atau klik untuk memilih'">
                </span>

                <span class="text-xs text-gray-400">Format: JPG, PNG, WEBP</span>
                <span class="text-xs !text-red-400">Pastikan Gambar berukuran optimal(16:9) untuk tampilan terbaik</span>
              </div>

              <!-- Preview -->
              <template x-if="preview">
                <div class="md:px-10">
                  <img :src="preview"
                    class="mt-3 w-full h-fit object-cover rounded-lg shadow-lg mx-auto">
                </div>
              </template>
            </div>
          </div>

          <div>
            <label class="block font-medium mb-1">Title</label>
            <input type="text" x-model="current.title">
          </div>

          <div>
            <label class="block font-medium mb-1">Subtitle</label>
            <input type="text" x-model="current.subtitle">
          </div>

          <div>
            <label class="block font-medium mb-1">Text</label>
            <textarea x-model="current.text" rows="3">
          </textarea>
          </div>

          <div class="flex flex-col md:flex-row gap-4">
            <div class="md:w-1/2">
              <label class="block font-medium mb-1">CTA Primary Text</label>
              <input type="text" x-model="current.cta_primary_text">
            </div>

            <div class="md:w-1/2">
              <label class="block font-medium mb-1">CTA Primary URL</label>
              <input type="text" x-model="current.cta_primary_url">
            </div>
          </div>

          <div class="flex flex-col md:flex-row gap-4">
            <div class="md:w-1/2">
              <label class="block font-medium mb-1">CTA Secondary Text</label>
              <input type="text" x-model="current.cta_secondary_text">
            </div>

            <div class="md:w-1/2">
              <label class="block font-medium mb-1">CTA Secondary URL</label>
              <input type="text" x-model="current.cta_secondary_url">
            </div>
          </div>


        </div>
        <!-- Button Save -->
        <div class="flex justify-end gap-3 border-t pt-4">
          <button type="button" @click="deleteSlide"
            class="btn gap-x-2 bg-transparent hover:bg-red-500 hover:text-white px-4 py-2">
            <span class="w-5 h-5" x-html="icon('hapus')"></span>
            <span>Hapus Slide</span>
          </button>

          <button type="submit" class="btn btn-primary px-4 py-2">
            <span class="w-5 h-5 fill-current" x-html="icon('save')"></span>
            <span>Simpan Perubahan</span>
          </button>
        </div>
      </form>
    </template>
    <template x-if="submitting">
      <div class="flex flex-col items-center justify-center py-20 space-y-4">
        <span class="w-12 h-12 animate-spin text-gg-primary" x-html="icon('loading')"></span>
        <div class="text-gray-600 font-medium">Memproses...</div>
      </div>
    </template>
  </div>
</div>

<script src="<?= ASSETS_URL . '/js/admin/landingHeroPage.js' ?>"></script>

<?php include INCLUDES_PATH . "/admin/layout/footer.php"; ?>