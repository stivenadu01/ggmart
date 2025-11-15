function kelolaKategoriPage() {
  return {
    kategori: [],
    search: "",
    loading: false,
    pagination: {
      page: 1,
      total: 0,
      total_pages: 1,
      limit: 10
    },

    async fetchKategori(page = 1) {
      this.loading = true;
      const urlApi = `${baseUrl}/api/kategori?halaman=${page}&limit=${this.pagination.limit}&search=${encodeURIComponent(this.search)}`;
      try {
        let res = await fetch(urlApi);
        res = await res.json();
        if (res.success) {
          this.kategori = res.data;
          this.pagination = res.pagination;
        } else {
          console.error("API error:", res.message);
        }
      } catch (e) {
        console.error("FetchKategori error:", e);
      } finally {
        this.loading = false;
      }
    },

    doSearch() {
      this.pagination.page = 1;
      this.fetchKategori();
    },

    nextPage() {
      if (this.pagination.page < this.pagination.total_pages) {
        this.pagination.page++;
        this.fetchKategori(this.pagination.page);
      }
    },

    prevPage() {
      if (this.pagination.page > 1) {
        this.pagination.page--;
        this.fetchKategori(this.pagination.page);
      }
    },

    goPage(n) {
      this.pagination.page = n;
      this.fetchKategori(n);
    },

    async hapusKategori(id) {
      let ok = await confirm('Yakin ingin menghapus kategori ini?');
      if (!ok) return;
      const res = await fetch(`${baseUrl}/api/kategori?id=${id}`, {
        method: 'DELETE',
      });
      const data = await res.json();
      if (data.success) {
        showFlash('Kategori berhasil dihapus');
        this.fetchKategori();
      } else {
        showFlash('Gagal menghapus kategori: ' + data.message, 'error');
      }
    }
  }
}