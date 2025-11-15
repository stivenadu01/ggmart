const pengaturanPage = () => ({
  loading: false,
  currentTab: 0,
  tabs: [
    { name: 'system', label: 'Sistem' },
    { name: 'carousel', label: 'Carousel Landing' }
  ],

  settings: {},
  hero: {},

  init() {
    this.$watch("currentTab", (tab) => {
      if (tab === 0) this.fetchSettings();
      if (tab === 1) this.fetchHero();
    });

    this.fetchSettings();
  },

  // SETTINGS
  async fetchSettings() {
    try {
      this.loading = true;
      const res = await fetch(`${baseUrl}/api/setting`);
      const data = await res.json();
      if (data.success) this.settings = data.data;
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
      const r = await res.json();
      if (r.success) {
        showFlash("Pengaturan berhasil disimpan");
      } else {
        throw new Error(r.message);
      }
    } catch (error) {
      console.log(error);
    } finally {
      this.loading = false;
    }

  },

  // HERO/CAROUSEL
  async fetchHero() {

  }
});
