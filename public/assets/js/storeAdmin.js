document.addEventListener('alpine:init', () => {
  Alpine.store('pesananBadge', {
    total: 0,
    interval: null,
    initialized: false,

    async fetch() {
      try {
        const res = await API.get('/transaksi/badge', false, true)
        if (res.success) {

          this.total = res.data.total
          this.initialized = true
        }
      } catch (err) {
        console.error('Badge error:', err)
      }
    },

    start() {
      if (this.interval) return
      this.fetch()
      // polling ringan (5 detik)
      this.interval = setInterval(() => {
        // hanya polling kalau tab aktif
        if (!document.hidden) {
          this.fetch()
        }
      }, 5000)
    },

    stop() {
      if (this.interval) {
        clearInterval(this.interval)
        this.interval = null
      }
    }
  })

})
