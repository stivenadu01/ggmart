<?php
$pageTitle = "Laporan";
include INCLUDES_PATH . "admin/layout/header.php";
?>

<div x-data="laporanPage()" x-init="fetchProduk()" class="bg-gray-50 p-4 lg:p-6 space-y-4">
  <!-- HEADER -->
  <div>
    <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">Laporan</h1>
    <p class="text-sm text-gray-500">Cetak laporan penjualan atau stok produk berdasarkan periode yang diinginkan.</p>
  </div>

  <!-- CARD -->
  <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 space-y-5 max-w-lg">
    <h2 class="text-xl font-bold text-gray-800 border-b pb-3 border-gray-200">Pilih Parameter Laporan</h2>

    <!-- Jenis Laporan -->
    <div>
      <label class="text-sm font-medium text-gray-700 block mb-1">Jenis Laporan</label>
      <select x-model="jenis" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
        <option value="penjualan">Laporan Penjualan</option>
        <option value="stok">Laporan Stok</option>
      </select>
    </div>



    <!-- Form Laporan Penjualan -->
    <template x-if="jenis === 'penjualan'">
      <div class="space-y-4">
        <!-- Meotde Bayar -->
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">Metode Bayar</label>
          <select x-model="metode" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
            <option value="">Semua</option>
            <option value="tunai">Tunai</option>
            <option value="qris">Qris</option>
          </select>
        </div>

        <div class="flex flex-col md:flex-row gap-4 items-end">
          <div>
            <label class="text-sm font-medium text-gray-700 block mb-1">Tipe Laporan</label>
            <select x-model="tipe" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500">
              <option value="harian">Harian</option>
              <option value="bulanan">Bulanan</option>
              <option value="tahunan">Tahunan</option>
            </select>
          </div>

          <div class="flex-1">
            <label class="text-sm font-medium text-gray-700 block mb-1">Pilih Periode</label>
            <template x-if="tipe === 'harian'">
              <input type="date" x-model="tanggal" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" />
            </template>

            <template x-if="tipe === 'bulanan'">
              <input type="month" x-model="bulan" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" />
            </template>

            <template x-if="tipe === 'tahunan'">
              <input type="number" x-model="tahun" min="2020" max="2100" placeholder="Tahun"
                class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" />
            </template>
          </div>
        </div>
      </div>
    </template>

    <!-- Form Laporan Stok -->
    <template x-if="jenis === 'stok'">
      <div class="space-y-4">
        <div class="relative" @click.outside="showDropdown = false">
          <label class="text-sm font-medium text-gray-700 block mb-1">Pilih Produk</label>

          <!-- Input Pencarian -->
          <input type="text" x-model="query" @input.debounce.300ms="fetchProduk()"
            @focus="showDropdown = true"
            placeholder="Default (Semua Produk)"
            class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" />

          <!-- Dropdown hasil pencarian -->
          <template x-if="showDropdown">
            <ul
              class="absolute z-20 mt-1 bg-white w-full border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-auto">
              <li @click="pilihProduk('')" class="px-3 py-2 hover:bg-red-50 cursor-pointer text-gray-700">Semua Produk</li>
              <template x-if="1 > 0">
                <template x-for="p in produkList" :key="p.kode_produk">
                  <li @click="pilihProduk(p)" class="px-3 py-2 hover:bg-red-50 cursor-pointer text-gray-700"
                    x-text="p.nama_produk"></li>
                </template>
              </template>
            </ul>
          </template>
        </div>

        <!-- Tahun -->
        <div>
          <label class="text-sm font-medium text-gray-700 block mb-1">Tahun</label>
          <input type="number" x-model="tahunStok" min="2020" max="2100" placeholder="Masukkan Tahun"
            class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500" />
        </div>
      </div>
    </template>

    <!-- Tombol Aksi -->
    <div class="flex gap-3 pt-3">
      <!-- PDF -->
      <button @click="cetakLaporan('pdf')"
        class="flex items-center px-4 py-2 bg-red-600 hover:bg-red-600/80 text-white rounded-lg shadow-md transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 11V3m0 8l3-3m-3 3l-3-3m0 14a9 9 0 100-18 9 9 0 000 18z" />
        </svg>
        Export PDF
      </button>

      <!-- Excel -->
      <button @click="cetakLaporan('excel')"
        class="flex items-center px-4 py-2 bg-green-600 hover:bg-green-600/80 text-white rounded-lg shadow-md transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4h16v16H4V4zm4 4l8 8m0-8l-8 8" />
        </svg>
        Export EXCEL
      </button>
    </div>
  </div>

  <div class="text-red-500 text-sm font-semibold pt-2">
    <p>Laporan akan mencakup data transaksi atau stok produk dari sistem GGMART.</p>
  </div>
</div>

<script>
  function laporanPage() {
    return {
      jenis: 'penjualan',
      tipe: 'harian',
      metode: '',
      tanggal: new Date().toISOString().slice(0, 10),
      bulan: new Date().toISOString().slice(0, 7),
      tahun: new Date().getFullYear(),
      produk: '',
      query: '',
      produkList: [],
      showDropdown: false,
      tahunStok: new Date().getFullYear(),

      async fetchProduk() {
        try {
          const res = await fetch(`${baseUrl}/api/produk?mode=dropdown&search=${encodeURIComponent(this.query)}`);
          const data = await res.json();
          this.produkList = data.data || [];
        } catch (err) {
          console.error("Gagal mengambil produk:", err);
        }
      },

      pilihProduk(p) {
        if (p == '') {
          this.produk = '';
          this.query = '';
        } else {
          this.produk = p.kode_produk;
          this.query = p.nama_produk;
        }
        this.showDropdown = false;
      },

      cetakLaporan(format) {
        let url = '';

        if (this.jenis === 'penjualan') {
          url = `${baseUrl}/laporan/penjualan_${format}?tipe=${this.tipe}&metode=${this.metode}`;
          if (this.tipe === 'harian') url += `&tanggal=${this.tanggal}`;
          if (this.tipe === 'bulanan') url += `&bulan=${this.bulan}`;
          if (this.tipe === 'tahunan') url += `&tahun=${this.tahun}`;
        } else if (this.jenis === 'stok') {
          if (!this.produk || !this.tahunStok) {
            showFlash('Silakan pilih produk dan isi tahun terlebih dahulu.');
            return;
          }
          url = `${baseUrl}/laporan/stok_${format}?produk=${this.produk}&tahun=${this.tahunStok}`;
        }

        window.open(url, '_blank');
      },
      init() {
        setInterval(() => {
          console.log(this.tanggal)
          console.log(this.bulan)
          console.log(this.tahun)
        }, 500011);
      }

    }
  }
</script>

<?php include INCLUDES_PATH . "admin/layout/footer.php"; ?>