<p x-show="tab !== 'akun' && !loaded" class="text-sm text-slate-500">Memuat pengaturan toko...</p>
<div x-show="error && tab !== 'akun'" x-cloak class="rounded-xl bg-red-50 p-4 text-sm text-red-700" role="alert"><span x-text="error"></span><button x-show="!loaded" @click="init()" class="ml-2 underline" type="button">Coba lagi</button></div>
<section id="panel-pembayaran" role="tabpanel" aria-labelledby="tab-pembayaran" x-show="tab === 'pembayaran' && loaded" x-cloak class="admin-card space-y-5 p-5 sm:p-6">
  <div><h3 class="text-lg font-semibold">Pembayaran</h3><p class="form-help">Isi rekening toko agar pelanggan dapat menggunakan transfer manual.</p></div>
  <div class="grid gap-4 sm:grid-cols-3">
    <label class="label">Nama bank<input class="input mt-2 w-full" x-model.trim="rekening.bank_nama" maxlength="120" placeholder="Nama bank"></label>
    <label class="label">Nomor rekening<input class="input mt-2 w-full" x-model.trim="rekening.bank_rekening" maxlength="40" inputmode="numeric" placeholder="Nomor rekening toko"></label>
    <label class="label">Pemilik rekening<input class="input mt-2 w-full" x-model.trim="rekening.bank_pemilik" maxlength="120" placeholder="Nama sesuai rekening"></label>
  </div>
  <div class="rounded-xl bg-slate-50 p-4 text-sm"><p class="font-semibold">QRIS tetap tersedia</p><p class="mt-1 text-slate-600">Menggunakan gambar QRIS tetap dari aset toko. Pembayaran diverifikasi admin berdasarkan bukti pembayaran.</p></div>
  <button type="button" class="admin-action-primary" @click="save('pembayaran')" :disabled="busy"><span x-text="busy ? 'Menyimpan...' : 'Simpan pembayaran'"></span></button>
</section>
<section id="panel-pengiriman" role="tabpanel" aria-labelledby="tab-pengiriman" x-show="tab === 'pengiriman' && loaded" x-cloak class="admin-card space-y-5 p-5 sm:p-6">
  <div><h3 class="text-lg font-semibold">Pengiriman</h3><p class="form-help">Ambil di tempat gratis. Tarif baru berlaku untuk pesanan berikutnya. Nonaktifkan kelurahan yang belum dilayani.</p></div>
  <div class="grid gap-3 sm:grid-cols-2">
    <label class="label">Kecamatan<select x-gg-select class="input mt-2 w-full" x-model="district"><option value="">Semua kecamatan</option><template x-for="name in districts" :key="name"><option :value="name" x-text="name"></option></template></select></label>
    <label class="label">Cari kelurahan<input type="search" class="input mt-2 w-full" x-model="filter" placeholder="Nama kelurahan..."></label>
  </div>
  <div class="max-h-96 overflow-auto rounded-xl border border-slate-200"><table class="table"><thead><tr><th>Kecamatan</th><th>Kelurahan</th><th>Ongkir (Rp)</th><th>Dilayani</th></tr></thead><tbody><template x-for="z in filtered" :key="z.id"><tr><td x-text="z.kecamatan"></td><td x-text="z.kelurahan"></td><td><input type="number" min="0" max="1000000" step="1" class="input w-32" x-model.number="z.tarif" :aria-label="'Tarif ' + z.kelurahan"></td><td><input type="checkbox" x-model="z.aktif" :aria-label="'Layani ' + z.kelurahan"></td></tr></template><tr x-show="filtered.length === 0"><td colspan="4" class="text-center text-slate-500">Kelurahan tidak ditemukan.</td></tr></tbody></table></div>
  <button type="button" class="admin-action-primary" @click="save('pengiriman')" :disabled="busy"><span x-text="busy ? 'Menyimpan...' : 'Simpan pengiriman'"></span></button>
</section>
