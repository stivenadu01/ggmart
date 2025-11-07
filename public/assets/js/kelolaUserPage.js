const kelolaUserPage = () => ({
  users: [],
  showFilter: false,
  loading: false,
  submitting: false,
  filter: {
    search: '',
    role: '',
  },
  pagination: {
    page: 1,
    total: 0,
    total_pages: 1,
    limit: 10,
  },

  async fetchUsers(page = 1) {
    this.loading = true;
    this.pagination.page = page;
    const params = new URLSearchParams({
      halaman: page,
      limit: this.pagination.limit,
      search: this.filter.search,
      role: this.filter.role,
    });

    try {
      const res = await fetch(`${baseUrl}/api/user?${params}`);
      const data = await res.json();

      if (data.success) {
        this.users = data.data;
        this.pagination = data.pagination;
      } else {
        showFlash(data.message, "error");
      }
    } catch (err) {
      console.error("Fetch user error:", err);
      showFlash("Gagal memuat data user", "error");
    } finally {
      this.loading = false;
    }
  },

  applyFilter() {
    this.showFilter = false;
    this.fetchUsers(1);
  },

  resetFilter() {
    this.filter.search = "";
    this.filter.role = "";
    this.fetchUsers(1);
  },

  goPage(page) {
    if (page >= 1 && page <= this.pagination.total_pages && page !== this.pagination.page) {
      this.fetchUsers(page);
    }
  },

  prevPage() {
    this.goPage(this.pagination.page - 1);
  },

  nextPage() {
    this.goPage(this.pagination.page + 1);
  },

  async hapusUser(id_user) {
    if (!confirm("Yakin ingin menghapus user ini?")) return;
    this.submitting = true;

    try {
      const res = await fetch(`${baseUrl}/api/user?id=${id_user}`, { method: "DELETE" });
      const data = await res.json();

      if (data.success) {
        showFlash(data.message);
        this.fetchUsers(this.pagination.page);
      } else {
        showFlash(data.message, "error");
      }
    } catch (err) {
      console.error("Hapus user error:", err);
      showFlash("Terjadi kesalahan saat menghapus user", "error");
    } finally {
      this.submitting = false;
    }
  },
});
