const stokFormPage = () => ({
  produkList: {
    query: '',
    data: [],
    satuan_dasar: '',
    open: false,
    loading: false,

    async fetch() {
      this.loading = true;
      try {
        const res = await fetch(`${baseUrl}/api/produk?mode=dropdown&search=${encodeURIComponent(this.query)}`);
        const result = await res.json();
        this.data = result.success ? result.data : [];
      } catch (err) {
        console.error('Fetch produk gagal:', err);
        this.data = [];
        this.open = false;
      } finally {
        this.loading = false;
      }
    },
  },

  selectProdukList(p) {
    this.produkList.open = false;
    this.produkList.query = p.nama_produk;
    this.form.kode_produk = p.kode_produk;
    this.form.nama_produk = p.nama_produk;
    this.produkList.satuan_dasar = p.satuan_dasar;
  },

  mutasiList: {
    query: '',
    data: [],
    open: false,
    filtered: [],
    change() {
      this.filtered = this.data.filter(i => (
        formatDateTime(i.tanggal).toLowerCase().includes(this.query.toLowerCase())
      ))
    }
  },

  form: {
    kode_produk: "",
    nama_produk: "",
    type: "",
    jumlah: 0,
    total_pokok: 0,
    harga_pokok: 0,
    id_mutasi: "",
    keterangan: ""
  },
  page: 1,
  submitting: false,

  async fetchMutasiList() {
    try {
      const res = await fetch(`${baseUrl}/api/mutasiStok?mode=dropdown&kode=${this.form.kode_produk}`);
      const data = await res.json();
      if (data.success) {
        this.mutasiList.data = data.data;
        this.mutasiList.filtered = data.data;
      };
    } catch (err) {
      console.error(err);
    }
  },

  selectMutasiList(m) {
    this.mutasiList.open = false;
    this.mutasiList.query = formatDateTime(m.tanggal);
    this.form.id_mutasi = m.id_mutasi;
    this.form.harga_pokok = m.harga_pokok;
  },


  syncHargaPokok(source) {
    const j = parseFloat(this.form.jumlah) || 0;
    if (j <= 0) return;
    if (source === 'total') {
      this.form.harga_pokok = this.form.total_pokok / j;
    } else if (source === 'harga') {
      this.form.total_pokok = this.form.harga_pokok * j;
    } else if (source === 'jumlah') {
      this.form.total_pokok = 0;
      this.form.harga_pokok = 0;
    }
  },

  async submitForm() {
    try {
      this.submitting = true;
      const formData = new FormData();
      for (let key in this.form) formData.append(key, this.form[key]);
      if (this.form.type == 'masuk') formData.append('sisa_stok', this.form.jumlah);

      const res = await fetch(`${baseUrl}/api/mutasiStok`, { method: "POST", body: formData });
      const data = await res.json();

      if (data.success) {
        showFlash(data.message);
        setTimeout(() => window.location.href = `${baseUrl}/admin/stok`, 1000);
      } else {
        showFlash(data.message || "Gagal menyimpan stok", "error");
      }
    } catch (error) {
      console.error(error);
    } finally {
      this.submitting = false;
    }
  },

  nextPage() {
    if (this.page === 1 && !this.form.type || !this.form.kode_produk) return showFlash("Pilih Jenis Perubahan & Produk Dulu", "warning");
    if (this.form.type == 'keluar') this.fetchMutasiList();
    this.page++;
  }
});
