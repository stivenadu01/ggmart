const settingPage = () => ({
  loading: false,
  currentTab: 0,
  tabs: [
    { name: "sistem", label: "Sistem" },
    { name: "email", label: "Email" },
  ],
  settings: {},

  async fetchSettings() {
    try {
      this.loading = true;
      const res = await fetch(`${baseUrl}/api/setting`);
      const data = await res.json();
      if (data.success) {
        this.settings = data.data;
      } else {
        showFlash(data.message, 'warning');
      }
    } catch (err) {
      showFlash("Gagal memuat pengaturan", "error");
      console.error(err);
    } finally {
      this.loading = false;
    }
  },

  async simpanSettings() {
    try {
      this.loading = true;
      const res = await fetch(`${baseUrl}/api/setting`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(this.settings)
      });
      const data = await res.json();
      if (data.success) {
        showFlash("Pengaturan berhasil disimpan");
      } else {
        showFlash(data.message, 'error');
      }
    } catch (err) {
      console.error(err);
      showFlash("Terjadi kesalahan saat menyimpan", "error");
    } finally {
      this.loading = false;
    }
  }
});
