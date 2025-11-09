const riwayatTransaksiPage = () => ({
  filter: {
    search: '',
    start: '',
    end: '',
    metode: '',
    show: false
  },
  totalSummary: {
    pokok: 0,
    jual: 0,
    laba: 0,
  },
  modalDetail: false,
  detail: [],

  cetakUlang(kode) {
    // this.printTransaksi(data.data)
    // window.location.href = `${baseUrl}/admin/transaksi/print?k=${data.data}`;
    window.open(`${baseUrl}/admin/transaksi/print?k=${kode}`, '_blank');
  },

  pagination: {
    page: 1,
    limit: 10,
    total: 0,
    total_pages: 1
  },
  loading: false,
  loadingDetail: false,
  transaksi: [],

  async fetchTransaksi() {
    try {
      this.loading = true;
      const params = new URLSearchParams({
        halaman: this.pagination.page,
        limit: this.pagination.limit,
        start: this.filter.start,
        end: this.filter.end,
        search: this.filter.search,
        metode: this.filter.metode
      })
      const url = `${baseUrl}/api/transaksi?${params}`
      const res = await fetch(url);
      const data = await res.json();

      if (data.success) {
        this.transaksi = data.data;
        this.pagination = data.pagination;
        this.totalSummary = data.totalSummary;
      } else {
        showFlash(data.message, 'warning')
      }
    } catch (error) {
      console.error(error);
    } finally {
      this.loading = false;
    }
  },

  applyFilter() {
    this.filter.show = false;
    this.pagination.page = 1
    this.fetchTransaksi();
  },

  async lihatDetail(kode) {
    try {
      this.loadingDetail = true;
      this.modalDetail = true
      const res = await fetch(`${baseUrl}/api/transaksi?mode=detail&k=${kode}`)
      const data = await res.json();
      if (data.success) {
        this.detail = data.data;
        console.log(this.detail);
      } else {
        showFlash(data.message, 'warning')
      }
    } catch (error) {
      console.error(error);
    } finally {
      this.loadingDetail = false;
    }
  },

  resetFilter() {
    this.filter.show = false;
    this.filter.search = '';
    this.filter.start = '';
    this.filter.end = '';
    this.filter.metode = '';
    this.pagination.page = 1
    this.fetchTransaksi();
  },

  goPage(n) {
    this.pagination.page = n;
    this.fetchTransaksi();
  },

  prevPage() {
    if (this.pagination.page > 1) {
      this.pagination.page -= 1;
      this.fetchTransaksi();
    }
  },
  nextPage() {
    if (this.pagination.page < this.pagination.total_pages) {
      this.pagination.page += 1;
      this.fetchTransaksi();
    }
  }
})