<div x-data="produkFormPage()"
  @paste.window="handlePaste($event)"
  @dragover.window.prevent="dragging = true"
  @dragleave.window="dragging = false"
  @drop.window.prevent="handleDrop($event)"
  class="min-h-dvh bg-slate-50 p-4 sm:p-6">

  <!-- DRAG OVERLAY -->
  <template x-if="dragging">
    <div class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-950/20 p-4 backdrop-blur-sm">
      <div class="w-full max-w-md rounded-2xl border-2 border-dashed border-emerald-400 bg-white p-8 text-center shadow-2xl">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700"><?= masterIconSvg('image', 'h-7 w-7') ?></div>
        <h2 class="text-xl font-bold text-slate-900">Lepaskan gambar di sini</h2>
        <p class="mt-2 text-sm text-slate-500">Anda juga dapat klik <b>Pilih File</b> atau tempel gambar dengan <b>Ctrl + V</b>.</p>
        <p class="mt-3 text-xs font-medium text-slate-400">JPG, JPEG, PNG, WebP • Maksimal 5MB</p>
      </div>
    </div>
  </template>

  <div class="mx-auto max-w-4xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <!-- HEADER -->
    <div class="border-b border-slate-200 px-5 py-5 sm:px-7">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <div class="mb-1 text-xs font-semibold uppercase tracking-[0.12em] text-emerald-600">Manajemen Produk</div>
          <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl" x-text="formTitle"></h1>
          <p class="mt-1 text-sm text-slate-500">Isi informasi produk secara bertahap. Contoh dan petunjuk tersedia di setiap bagian.</p>
        </div>
        <button type="button" @click="history.back()" class="btn-secondary w-full sm:w-auto"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali</button>
      </div>
    </div>

    <!-- STEP INDICATOR -->
    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-7">
      <div class="grid grid-cols-2 gap-3">
        <div :class="step === 1 ? 'border-emerald-500 bg-white text-emerald-700 shadow-sm' : 'border-transparent text-slate-400'" class="rounded-xl border p-3">
          <div class="text-xs font-semibold uppercase tracking-wide">Langkah 1</div>
          <div class="mt-1 text-sm font-bold">Data Produk</div>
          <div class="mt-0.5 text-xs">Nama, kategori, harga, dan informasi dasar.</div>
        </div>
        <div :class="step === 2 ? 'border-emerald-500 bg-white text-emerald-700 shadow-sm' : 'border-transparent text-slate-400'" class="rounded-xl border p-3">
          <div class="text-xs font-semibold uppercase tracking-wide">Langkah 2</div>
          <div class="mt-1 text-sm font-bold">Gambar & Tagline</div>
          <div class="mt-0.5 text-xs">Tampilan produk dan kalimat promosi singkat.</div>
        </div>
      </div>
    </div>

    <form @submit.prevent="submitForm" class="p-5 sm:p-7">
      <!-- STEP 1 -->
      <template x-if="step === 1">
        <div class="space-y-6">
          <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4 text-sm text-emerald-800">
            <strong>Petunjuk:</strong> isi data yang wajib terlebih dahulu. Harga menggunakan Rupiah dan stok awal dikelola melalui menu <b>Stok</b> setelah produk dibuat.
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
              <label for="nama-produk" class="label">Nama Produk <span class="form-required">*</span></label>
              <input id="nama-produk" type="text" x-model="form.nama_produk" required maxlength="150" class="input" placeholder="Contoh: Beras Merah Pantar">
              <p class="form-help">Gunakan nama yang mudah dikenali pelanggan.</p>
            </div>

            <div>
              <label class="label">Kategori <span class="form-required">*</span></label>
              <select x-gg-select x-model="form.id_kategori" required class="input">
                <option value="">Pilih kategori produk</option>
                <template x-for="kategori in kategoriOptions" :key="kategori.id_kategori">
                  <option :value="kategori.id_kategori" x-text="kategori.nama_kategori"></option>
                </template>
              </select>
              <p class="form-help">Pilih kategori yang paling sesuai dengan produk.</p>
            </div>

            <div>
              <label for="harga-jual" class="label">Harga Jual <span class="form-required">*</span></label>
              <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-slate-400">Rp</span>
                <input id="harga-jual" type="number" min="0" step="1" x-model="form.harga_jual" required class="input pl-10" placeholder="Contoh: 25000">
              </div>
              <p class="form-help">Masukkan harga jual dalam Rupiah tanpa titik atau simbol.</p>
            </div>

            <div>
              <label for="satuan" class="label">Satuan Dasar <span class="form-required">*</span></label>
              <input id="satuan" type="text" x-model="form.satuan_dasar" required maxlength="50" class="input" placeholder="Contoh: kg, pcs, botol">
              <p class="form-help">Tuliskan satuan yang digunakan saat produk dijual.</p>
            </div>

            <div class="md:col-span-2">
              <label for="deskripsi" class="label">Deskripsi</label>
              <textarea id="deskripsi" x-model="form.deskripsi" maxlength="1000" rows="5" class="input resize-y" placeholder="Contoh: Beras merah lokal dari Pantar, cocok untuk konsumsi harian dan dikemas higienis."></textarea>
              <p class="form-help">Jelaskan manfaat, ukuran, rasa, bahan, atau informasi penting lain yang perlu diketahui pelanggan.</p>
            </div>

            <div class="md:col-span-2">
              <label for="asal-produk" class="label">Asal Produk</label>
              <input id="asal-produk" type="text" x-model="form.asal_produk" maxlength="150" class="input" placeholder="Contoh: Kabupaten Alor, NTT">
              <p class="form-help">Opsional. Cantumkan daerah atau produsen asal produk jika diketahui.</p>
            </div>
          </div>

          <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <button type="button" @click="history.back()" class="btn-secondary w-full sm:w-auto">Batal</button>
            <button type="button" @click="step = 2" class="btn-primary w-full sm:w-auto">Lanjut ke Gambar <?= masterIconSvg('arrow-right', 'h-4 w-4') ?></button>
          </div>
        </div>
      </template>

      <!-- STEP 2 -->
      <template x-if="step === 2">
        <div class="space-y-6">
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="text-sm font-semibold text-slate-800">Sebelum menyimpan</div>
            <p class="mt-1 text-sm text-slate-500">Tambahkan gambar yang jelas agar produk mudah dikenali. Tagline bersifat opsional.</p>
          </div>

          <div x-cloak x-show="form.asal_produk != ''">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <label for="tagline" class="label">Tagline <span class="text-xs font-normal text-slate-400">(opsional)</span></label>
                <p class="form-help mt-0">Kalimat singkat yang dapat digunakan sebagai deskripsi promosi di halaman utama.</p>
              </div>
            </div>
            <input id="tagline" type="text" x-model="form.tagline" class="input mt-2" placeholder="Contoh: Manis alami, rasa khas NTT yang autentik." maxlength="150">
          </div>

          <!-- IMAGE UPLOAD -->
          <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center transition hover:border-emerald-400 hover:bg-emerald-50/30">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-600 shadow-sm"><?= masterIconSvg('image', 'h-7 w-7') ?></div>
            <h2 class="mt-4 font-bold text-slate-800">Gambar Produk</h2>
            <p class="mx-auto mt-1 max-w-xl text-sm text-slate-500">Gunakan foto produk yang jelas. Anda dapat memilih file, drag & drop, atau menempel gambar dengan <b>Ctrl + V</b>.</p>
            <label class="btn-outline-primary mt-4 inline-flex w-full cursor-pointer justify-center sm:w-auto">
              Pilih File
              <input type="file" class="hidden" @change="onFileChange($event)" accept="image/jpeg,image/png,image/webp">
            </label>
            <p class="mt-3 text-xs font-medium text-slate-400">JPG, JPEG, PNG, WebP • Maksimal 5MB • Disarankan rasio 1:1</p>
          </div>

          <!-- PREVIEW -->
          <template x-if="previewUrl">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 text-center">
              <p class="text-sm font-semibold text-emerald-800">Gambar baru</p>
              <img :src="previewUrl" alt="Preview gambar produk" class="mx-auto mt-3 h-48 w-48 rounded-xl border border-white bg-white object-cover shadow-sm">
              <p class="mt-2 text-xs text-emerald-700">Gambar ini akan digunakan setelah Anda menyimpan.</p>
            </div>
          </template>

          <!-- CURRENT IMAGE -->
          <template x-if="currentImageUrl && !previewUrl">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center">
              <p class="text-sm font-semibold text-slate-700">Gambar saat ini</p>
              <img :src="currentImageUrl" alt="Gambar produk saat ini" class="mx-auto mt-3 h-48 w-48 rounded-xl border bg-white object-cover shadow-sm">
              <p class="mt-2 text-xs text-slate-500">Pilih gambar baru jika ingin menggantinya.</p>
            </div>
          </template>

          <!-- TIPS -->
          <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
            <div class="font-semibold">Tips foto produk</div>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-blue-700">
              <li>Gunakan foto yang terang, tajam, dan tidak blur.</li>
              <li>Foto persegi (1:1) biasanya lebih mudah tampil konsisten pada katalog.</li>
              <li>Background sederhana membuat produk lebih mudah dikenali.</li>
              <li>PNG transparan dapat digunakan bila sesuai dengan kebutuhan produk.</li>
            </ul>
          </div>

          <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
            <button type="button" @click="step = 1" class="btn-secondary w-full sm:w-auto"><?= masterIconSvg('arrow-left', 'h-4 w-4') ?> Kembali ke Data</button>
            <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="saving" :class="saving ? 'opacity-60 cursor-not-allowed' : ''">
              <span x-show="!saving" x-text="kodeProduk ? 'Simpan Perubahan' : 'Simpan Produk'"></span>
              <span x-show="saving">Menyimpan...</span>
            </button>
          </div>
        </div>
      </template>
    </form>
  </div>
</div>

<script>
  function produkFormPage() {
    return {
      baseUrl: BASE_URL,
      formTitle: <?= json_encode($title) ?>,
      kodeProduk: <?= json_encode($kodeProduk) ?>,
      kategoriOptions: [],
      currentImageUrl: '',
      form: {
        nama_produk: '',
        id_kategori: '',
        deskripsi: '',
        harga_jual: '',
        satuan_dasar: '',
        asal_produk: '',
        tagline: '',
        gambar: null
      },
      step: 1,
      dragging: false,
      previewUrl: '',
      saving: false,

      init() {
        this.loadKategori();
        if (this.kodeProduk) {
          this.loadProduk();
        }
      },

      async loadKategori() {
        try {
          const res = await API.get('/kategori');
          if (res.success) {
            this.kategoriOptions = res.data || [];
          }
        } catch (err) {
          console.error(err);
        }
      },

      async loadProduk() {
        try {
          const res = await API.get('/produk/detail?k=' + encodeURIComponent(this.kodeProduk));
          if (res.success && res.data) {
            const data = res.data;
            this.form.nama_produk = data.nama_produk || '';
            this.form.id_kategori = data.id_kategori || '';
            this.form.deskripsi = data.deskripsi || '';
            this.form.harga_jual = data.harga_jual || '';
            this.form.satuan_dasar = data.satuan_dasar || '';
            this.form.asal_produk = data.asal_produk || '';
            this.form.tagline = data.tagline || '';
            this.currentImageUrl = data.gambar ? BASE_URL + '/uploads/' + data.gambar : '';
          }
        } catch (err) {
          console.error(err);
        }
      },

      onFileChange(event) {
        const file = event.target.files[0];
        if (file) {
          this.form.gambar = file;
          this.previewUrl = URL.createObjectURL(file);
        }
      },

      async submitForm() {
        if (this.saving) return;
        if (!this.form.nama_produk.trim() || !this.form.id_kategori || !this.form.harga_jual || !this.form.satuan_dasar.trim()) {
          Alpine.store('ui')?.toast('Lengkapi semua data wajib pada langkah pertama');
          this.step = 1;
          return;
        }
        this.saving = true;
        try {
          const payload = new FormData();
          payload.append('nama_produk', this.form.nama_produk);
          payload.append('id_kategori', this.form.id_kategori);
          payload.append('deskripsi', this.form.deskripsi || '');
          payload.append('harga_jual', this.form.harga_jual);
          payload.append('satuan_dasar', this.form.satuan_dasar || '');
          payload.append('asal_produk', this.form.asal_produk || '');
          payload.append('tagline', this.form.tagline || '');
          if (this.form.gambar) {
            payload.append('gambar', this.form.gambar);
          }

          let res;
          if (this.kodeProduk) {
            payload.append('kode_produk', this.kodeProduk);
            res = await API.put('/produk', payload);
          } else {
            res = await API.post('/produk', payload);
          }

          if (res.success) {
            Alpine.store('ui')?.toast(this.kodeProduk ? 'Produk berhasil diperbarui' : 'Produk berhasil ditambahkan');
            setTimeout(() => {
              window.history.back();
            }, 1000);
          }
        } catch (err) {
          console.error(err);
        } finally {
          this.saving = false;
        }
      },

      handleDrop(e) {
        this.dragging = false;
        const file = e.dataTransfer.files[0];

        if (file && this.validateImage(file)) {
          this.form.gambar = file;
          this.previewUrl = URL.createObjectURL(file);
        }
      },

      handlePaste(e) {
        const items = e.clipboardData?.items;
        if (!items) return;

        for (let i = 0; i < items.length; i++) {
          const item = items[i];

          if (item.type.indexOf('image') !== -1) {
            const file = item.getAsFile();

            if (file && this.validateImage(file)) {
              this.form.gambar = file;
              this.previewUrl = URL.createObjectURL(file);

              Alpine.store('ui')?.toast('Gambar berhasil ditempel (Ctrl+V)');
            }

            break;
          }
        }
      },

      validateImage(file) {
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!file.type.startsWith('image/')) {
          Alpine.store('ui')?.toast('File harus berupa gambar');
          return false;
        }

        if (file.size > maxSize) {
          Alpine.store('ui')?.toast('Ukuran gambar maksimal 5MB');
          return false;
        }

        return true;
      },


    }
  }
</script>