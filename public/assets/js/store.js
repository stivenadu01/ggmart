document.addEventListener('alpine:init', () => {

  // 🔐 AUTH STORE
  Alpine.store('auth', {
    user: window.__USER__ ?? null,

    async logout() {
      const ok = await Alpine.store('ui').confirm("Yakin ingin logout?");
      if (!ok) return;

      try {
        const base = window.BASE_URL || '';
        const res = await fetch(base + '/api/auth/logout', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN || '' },
          credentials: 'same-origin'
        });

        const payload = await res.json().catch(() => ({}));
        if (payload.message) {
          Alpine.store('ui').toast(payload.message, payload.success ? 'success' : 'error');
        }
      } catch (error) {
        console.error('Logout failed:', error);
      } finally {
        Alpine.store('notifications')?.stop(true);
        this.user = null;
        window.location.assign((window.BASE_URL || '') + '/login');
      }
    },

    async refresh() {
      if (!this.user) return;
      const res = await API.get('/auth/me', false);
      if (res.success) {
        this.user = res.data;
      }
    }

  });

  // 🎨 UI STORE
  Alpine.store('ui', {
    loading: false,
    loadingCount: 0,

    startLoading() {
      this.loadingCount++
      setTimeout(() => {
        if (this.loadingCount > 0) this.loading = true
      }, 150)
    },

    stopLoading() {
      this.loadingCount--
      if (this.loadingCount <= 0) {
        this.loading = false
        this.loadingCount = 0
      }
    },

    toastMessage: '',
    toastType: 'success',
    openSearch: false,
    openUserMenu: false,

    // 🔔 TOAST
    toast(msg, type = 'success', duration = 3000) {
      this.toastMessage = msg;
      this.toastType = type;

      setTimeout(() => {
        this.toastMessage = '';
      }, duration);
    },

    // ❓ CONFIRM MODAL SYSTEM
    confirmMessage: '',
    confirmShow: false,
    confirmResolve: null,

    confirm(msg) {
      this.confirmMessage = msg;
      this.confirmShow = true;

      return new Promise((resolve) => {
        this.confirmResolve = resolve;
      });
    },

    confirmYes() {
      this.confirmShow = false;
      if (this.confirmResolve) this.confirmResolve(true);
    },

    confirmNo() {
      this.confirmShow = false;
      if (this.confirmResolve) this.confirmResolve(false);
    },

    flyToCart(imgEl) {
      return new Promise((resolve) => {
        const cart = document.getElementById('cart-icon')
        if (!cart || !imgEl) {
          resolve()
          return
        }

        const imgRect = imgEl.getBoundingClientRect()
        const cartRect = cart.getBoundingClientRect()

        const clone = document.createElement('img')
        clone.src = imgEl.src

        clone.style.position = 'fixed'
        clone.style.top = imgRect.top + 'px'
        clone.style.left = imgRect.left + 'px'
        clone.style.width = imgRect.width + 'px'
        clone.style.height = imgRect.height + 'px'
        clone.style.zIndex = 9999
        clone.style.pointerEvents = 'none'
        clone.style.borderRadius = '12px'
        clone.style.objectFit = 'cover'

        // initial state
        clone.style.opacity = '0'
        clone.style.transform = 'scale(1)'

        document.body.appendChild(clone)

        // ⏱ FRAME 1 → langsung tampil
        requestAnimationFrame(() => {
          clone.style.transition = 'opacity 0.1s ease'
          clone.style.opacity = '1'
        })

        // ⏱ FRAME 2 → semua animasi SEKALIGUS (ini kunci smooth)
        setTimeout(() => {
          const cartCenterX = cartRect.left + (cartRect.width / 2)
          const cartCenterY = cartRect.top + (cartRect.height / 2)

          const cloneWidth = clone.offsetWidth
          const cloneHeight = clone.offsetHeight

          clone.style.transition = `
            transform 0.8s cubic-bezier(.22,1,.36,1),
            top 0.8s cubic-bezier(.22,1,.36,1),
            left 0.8s cubic-bezier(.22,1,.36,1),
            opacity 0.6s ease
          `

          // 🔥 langsung gabung semua:
          clone.style.transform = 'scale(0.2)'
          clone.style.left = (cartCenterX - cloneWidth / 2) + 'px'
          clone.style.top = (cartCenterY - cloneHeight / 2) + 'px'
          clone.style.opacity = '0.2'

        }, 80) // kecil banget delay → terasa instant

        // ⏱ END
        setTimeout(() => {
          clone.remove()
          resolve()
        }, 900)
      })
    }
  });

  // UTILS
  Alpine.store('utils', {

    formatRupiah(angka, prefix = true) {
      const number = Number(angka) || 0;

      const formatted = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(number);

      return prefix ? 'Rp' + formatted : formatted;
    },

    formatDate(dateStr) {
      const date = new Date(dateStr);
      return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },

    formatDateTime(dateStr) {
      const date = new Date(dateStr);
      return date.toLocaleString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    storageHelper(key, action, value = null) {
      let data;

      try {
        data = JSON.parse(localStorage.getItem(key)) || [];
      } catch {
        data = [];
      }

      switch (action) {
        case 'load':
          return data;

        case 'save':
          if (!value) return data;
          data.push(value);
          localStorage.setItem(key, JSON.stringify(data));
          return data;

        case 'set':
          if (!Array.isArray(value)) return data;
          localStorage.setItem(key, JSON.stringify(value));
          return value;

        case 'remove':
          data = data.filter((_, i) => i !== value);
          localStorage.setItem(key, JSON.stringify(data));
          return data;

        case 'clear':
          localStorage.removeItem(key);
          return [];

        default:
          console.warn('storageHelper: action tidak dikenal ->', action);
          return data;
      }
    }
  });

  // 🛒 CART
  Alpine.store('cart', {
    items: [],
    checkingOut: false,
    async init() {
      const user = Alpine.store('auth').user;

      if (!user || user.role !== 'pelanggan') {
        this.items = [];
        return;
      }


      await this.load();
    },

    async load() {
      const res = await API.get('/keranjang?u=' + Alpine.store('auth').user.id_pengguna, false);
      if (res.success) {
        this.items = res.data || [];
      }
    },

    async add(item, imgEl = null) {
      if (this.checkingOut) return;
      this.checkoutKey = null;
      if (!Alpine.store('auth').user) {
        const ok = await Alpine.store('ui').confirm('Harus login dulu untuk tambah keranjang');
        if (ok) window.location.href = BASE_URL + '/login';
        return;
      }
      if (item.jumlah > item.stok) {
        Alpine.store('ui').toast(`Stok ${item.nama_produk} tidak cukup`, 'error');
        return;
      }

      try {
        const res = await API.post('/keranjang', {
          ...item,
          id_pengguna: Alpine.store('auth').user.id_pengguna
        }, false);
        if (res.success) {
          if (imgEl) await Alpine.store('ui').flyToCart(imgEl);
          await this.load();
        }
      } catch (err) {
        console.log(err);
      }
    },

    async remove(id_keranjang) {
      if (this.checkingOut) return;
      this.checkoutKey = null;
      try {
        const payload = {
          id_pengguna: Alpine.store('auth').user.id_pengguna,
          id_keranjang: id_keranjang
        }
        await API.delete('/keranjang', payload, false);
        await this.load();
      } finally {
      }
    },

    async update(item) {
      if (this.checkingOut) return;
      this.checkoutKey = null;
      try {
        if (item.jumlah > item.stok) {
          Alpine.store('ui').toast(`Stok ${item.nama_produk} tidak cukup`, 'error');
          return;
        }
        item.id_pengguna = Alpine.store('auth').user.id_pengguna
        await API.put('/keranjang', item, false);
      } finally {
        await this.load();
      }
    },

    async clear() {
      if (this.checkingOut) return;
      this.checkoutKey = null;
      const res = await API.delete('/keranjang/clear', { id_pengguna: Alpine.store('auth').user.id_pengguna }, false);
      if (res.success) {
        this.items = [];
      }
    },

    get totalQty() {
      return this.items.reduce((s, i) => s + (i.jumlah || 1), 0);
    },

    get totalHarga() {
      return this.items.reduce((s, i) => s + (i.harga_jual * (i.jumlah || 1)), 0);
    },

    checkoutKey: null,
    async checkout(form) {
      if (this.checkingOut || !this.items.length) return;
      this.checkingOut = true;
      this.checkoutKey ||= utils.checkoutKey();
      try {
        const payload = {
          ...form,
          checkout_key: this.checkoutKey,
          total_harga: this.totalHarga,
          detail: this.items.map(i => ({ kode_produk: i.kode_produk, jumlah: i.jumlah }))
        };
        const res = await API.post('/transaksi/user', payload);
        this.items = [];
        this.checkoutKey = null;
        window.location.href = BASE_URL + '/pembayaran/' + encodeURIComponent(res.data) + '?checkout=1';
      } catch (err) {
        await this.load().catch(() => {});
      } finally {
        this.checkingOut = false;
      }
    }

  });
});
