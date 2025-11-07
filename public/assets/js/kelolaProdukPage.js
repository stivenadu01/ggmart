function kelolaProdukPage() {
  return {
    produk: [],
    showFilter: false,
    filter: {
      search: '',
      sort: 'tanggal_dibuat',
      dir: 'DESC',
    },
    pagination: {
      page: 1,
      limit: 10,
      total: 0,
      total_pages: 1
    },
    loading: false,
    submitting: false,

    async fetchProduk(page = 1) {
      this.loading = true;
      this.pagination.page = page;

      const params = new URLSearchParams({
        halaman: page,
        limit: this.pagination.limit,
        search: this.filter.search,
        sort: this.filter.sort,
        dir: this.filter.dir,
      });

      try {
        const res = await fetch(`${baseUrl}/api/produk?${params}`);
        const data = await res.json();

        if (data.success) {
          this.produk = data.data;
          this.pagination = data.pagination;
        } else {
          showFlash('Gagal memuat data produk', 'error');
        }
      } catch (err) {
        console.error('Fetch produk error:', err);
        showFlash('Terjadi kesalahan koneksi ke server', 'error');
      } finally {
        this.loading = false;
      }
    },

    applyFilter() {
      this.showFilter = false;
      this.fetchProduk(1);
    },

    resetFilter() {
      this.showFilter = false;
      this.filter.search = '';
      this.filter.sort = 'tanggal_dibuat';
      this.filter.dir = 'DESC';
      this.fetchProduk(1);
    },

    goPage(page) {
      if (page >= 1 && page <= this.pagination.total_pages && page !== this.pagination.page) {
        this.fetchProduk(page);
      }
    },
    prevPage() { this.goPage(this.pagination.page - 1); },
    nextPage() { this.goPage(this.pagination.page + 1); },

    async hapusProduk(id) {
      try {
        if (this.submitting) return;
        this.submitting = true;
        if (!confirm("Yakin ingin menghapus produk ini?")) return;

        const res = await fetch(`${baseUrl}/api/produk?k=${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
          showFlash(data.message);
          this.fetchProduk(this.pagination.page);
        } else {
          showFlash(data.message, 'error');
        }
      } catch (err) {
        console.error('Hapus produk error:', err);
        showFlash('Terjadi kesalahan koneksi ke server', 'error');
      } finally {
        this.submitting = false;
      }
    },
  }
}
