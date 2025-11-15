function carousel() {
  return {
    current: 0,
    interval: null,
    loading: false,

    hero: [],

    init() {
      this.start();
      this.fetchHero();
    },

    start() {
      this.stop();
      this.interval = setInterval(() => {
        this.next();
      }, 5000);
    },

    stop() {
      if (this.interval) {
        clearInterval(this.interval);
      }
    },

    next() {
      this.current = (this.current + 1) % this.hero.length;
    },

    goTo(index) {
      this.current = index;
      this.start();
    },

    async fetchHero() {
      try {
        this.loading = true;
        const res = await fetch(`${baseUrl}/api/landingHero`);
        const data = await res.json();
        if (data.success) {
          this.hero = data.data;
          console.log(data.data);

        }
      } catch (err) {
        console.error(err);
      } finally {
        this.loading = false;
      }
    },
  };
}
