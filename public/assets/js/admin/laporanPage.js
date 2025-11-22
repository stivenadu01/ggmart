function laporanPage() {
  return {
    // === Data utama ===
    jenis: 'transaksi',
    tipe: 'harian',
    metode: '',
    tanggal: new Date().toISOString().slice(0, 10),
    bulan: new Date().toISOString().slice(0, 7),
    tahun: new Date().getFullYear(),

    // === Penjualan / stok ===
    produk: '',
    query: '',
    produkList: [],
    showDropdown: false,
    tanggalMulai: '',
    tanggalSelesai: new Date().toISOString().slice(0, 10),

    // === Ambil produk untuk dropdown ===
    async fetchProduk() {
      try {
        const res = await fetch(`${baseUrl}/api/produk?mode=dropdown&search=${encodeURIComponent(this.query)}`);
        const data = await res.json();
        this.produkList = data.data || [];
      } catch (err) {
        console.error("Gagal ambil produk:", err);
      }
    },

    pilihProduk(p) {
      if (!p) {
        this.produk = '';
        this.query = '';
      } else {
        this.produk = p.kode_produk;
        this.query = p.nama_produk;
      }
      this.showDropdown = false;
    },

    // === Status loading ===
    loading: false,
    formatProses: '', // untuk tahu tombol mana yang sedang proses (pdf/excel)

    // === Cetak Laporan ===
    async cetakLaporan(format) {
      try {
        this.loading = true;
        this.formatProses = format;

        let url = `${baseUrl}/laporan/${this.jenis}_${format}?`;
        if (this.jenis === 'transaksi') {
          url += `tipe=${this.tipe}&metode=${this.metode}`;
          if (this.tipe === 'harian') url += `&tanggal=${this.tanggal}`;
          if (this.tipe === 'bulanan') url += `&bulan=${this.bulan}`;
          if (this.tipe === 'tahunan') url += `&tahun=${this.tahun}`;
        }
        if (this.jenis === 'penjualan_produk' || this.jenis === 'mutasi_stok') {
          url += `mulai=${this.tanggalMulai}&selesai=${this.tanggalSelesai}`;
          if (this.produk) url += `&produk=${this.produk}`;
        }

        // HEAD request untuk cek file dan ambil nama file
        const headRes = await fetch(url, { method: 'HEAD' });
        if (!headRes.ok) throw new Error(`File tidak tersedia, status ${headRes.status}`);

        // validasi content type jika bukan pdf dan bukan excel
        const contentType = headRes.headers.get('Content-Type') || '';
        if (!contentType.includes('pdf') && !contentType.includes('spreadsheet')) {
          throw new Error('File Gagal dimuat Atau Tidak Tersedia');
        }

        // aambil nama file dari server
        let fileName = format === 'pdf' ? 'laporan.pdf' : 'laporan.xlsx'; // default
        const cd = headRes.headers.get('Content-Disposition');
        if (cd && cd.includes('filename=')) {
          fileName = cd.split('filename=')[1].replace(/["']/g, '');
        }

        // Link download langsung
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

      } catch (err) {
        showFlash(err.message || 'Gagal Cetak Laporan', 'error');
        console.error(err);
      } finally {
        this.loading = false;
        this.formatProses = '';
      }
    }

  };
}
