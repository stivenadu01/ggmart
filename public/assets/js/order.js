function checkoutPage() {
  return {
    config: null, step: 1,
    form: { metode_bayar: 'transfer', metode_pengiriman: 'ambil', kecamatan: '', zona_id: '', nama_penerima: '', no_hp_penerima: '', alamat_pengiriman: '' },
    async init() {
      const user = Alpine.store('auth').user || {};
      this.form.nama_penerima = user.nama || '';
      this.form.no_hp_penerima = user.no_hp || '';
      this.form.alamat_pengiriman = user.alamat || '';
      try {
        this.config = (await API.get('/checkout/config')).data;
        if (!this.config.metode.transfer) this.form.metode_bayar = 'qris';
      } catch (e) { this.config = null; }
    },
    get kecamatan() { return [...new Set((this.config?.zona || []).map(z => z.kecamatan))]; },
    get kelurahan() { return (this.config?.zona || []).filter(z => z.kecamatan === this.form.kecamatan); },
    changeDistrict() { this.form.zona_id = ''; },
    changeDelivery() {
      if (this.form.metode_pengiriman === 'kirim' && this.form.metode_bayar === 'tunai') this.form.metode_bayar = this.config?.metode.transfer ? 'transfer' : 'qris';
    },
    get zone() { return this.kelurahan.find(z => String(z.id) === String(this.form.zona_id)); },
    get ongkir() { return this.form.metode_pengiriman === 'kirim' ? Number(this.zone?.tarif || 0) : 0; },
    get total() { return Alpine.store('cart').totalHarga + this.ongkir; },
    get ready() { return !!this.config?.metode[this.form.metode_bayar] && !(this.form.metode_bayar === 'tunai' && this.form.metode_pengiriman !== 'ambil') && this.form.nama_penerima.trim().length >= 2 && !!this.form.no_hp_penerima.trim() && (this.form.metode_pengiriman === 'ambil' || (!!this.zone && this.form.alamat_pengiriman.trim().length >= 10)); },
    next() { if (Alpine.store('cart').items.length) { this.step = 2; window.scrollTo({ top: 0, behavior: 'smooth' }); } },
    async checkout() { if (this.step === 2 && this.ready) await Alpine.store('cart').checkout(this.form); }
  };
}

function paymentPanel() {
  return {
    code: window.location.pathname.split('/').filter(Boolean).pop(), trx: null, loading: true, busy: false, error: '', note: '', proof: null, timer: null, checking: false, disposed: false, redirecting: false,
    get role() { return Alpine.store('auth').user?.role; },
    get isAdmin() { return this.role === 'admin'; },
    get counterFlow() { return this.trx?.sumber_transaksi === 'kasir' && ['admin', 'kasir'].includes(this.role) && window.location.pathname.includes('/pembayaran/'); },
    get canPay() { return !!this.trx && this.trx.status === 'pending' && ['belum_bayar', 'ditolak'].includes(this.trx.status_bayar); },
    get canUpload() { return this.canPay && (this.role === 'pelanggan' || this.trx.sumber_transaksi === 'kasir' && ['admin', 'kasir'].includes(this.role)); },
    get printRequested() { return new URLSearchParams(window.location.search || '').get('cetak') === '1'; },
    eventLabel(event) { return ({ checkout: 'Pesanan dibuat', diproses: 'Pesanan diproses', siap_diambil: 'Siap diambil', dikirim: 'Pesanan dikirim', selesai: 'Pesanan selesai', dibatalkan: 'Pesanan dibatalkan', pembayaran_belum_bayar: 'Menunggu pembayaran', pembayaran_menunggu_verifikasi: 'Menunggu verifikasi pembayaran', pembayaran_lunas: 'Pembayaran diterima', pembayaran_ditolak: 'Bukti pembayaran ditolak', pembayaran_kedaluwarsa: 'Batas pembayaran berakhir', pembayaran_perlu_refund: 'Pengembalian dana diperlukan', pembayaran_dikembalikan: 'Dana dikembalikan' })[event] || event; },
    paymentLabel(status) { return ({ belum_bayar: 'Belum dibayar', menunggu_verifikasi: 'Menunggu verifikasi', lunas: 'Lunas', ditolak: 'Bukti ditolak', kedaluwarsa: 'Kedaluwarsa', perlu_refund: 'Menunggu pengembalian dana', dikembalikan: 'Dana dikembalikan' })[status] || status; },
    async init() { await this.load(); },
    destroy() { this.disposed = true; clearTimeout(this.timer); },
    returnToCounter() {
      if (this.redirecting) return true;
      if (this.counterFlow && this.trx.status === 'selesai' && this.trx.status_bayar === 'lunas') {
        this.redirecting = true; clearTimeout(this.timer);
        window.location.replace(BASE_URL + (this.printRequested ? '/struk/' + encodeURIComponent(this.code) + '?auto=1' : '/admin/kasir')); return true;
      }
      if (this.role === 'pelanggan' && new URLSearchParams(window.location.search || '').get('checkout') === '1' && (this.trx?.status === 'diproses' || this.trx?.status_bayar === 'menunggu_verifikasi')) {
        this.redirecting = true; clearTimeout(this.timer); window.location.replace(BASE_URL + '/transaksi?tahap=' + (this.trx.status_bayar === 'menunggu_verifikasi' ? 'menunggu_verifikasi' : 'diproses')); return true;
      }
      return false;
    },
    scheduleCheck(delay = 5000) {
      clearTimeout(this.timer);
      if (!this.disposed && !this.redirecting && this.trx && ['pending', 'diproses', 'dikirim'].includes(this.trx.status)) this.timer = setTimeout(() => this.refreshStatus(), delay);
    },
    async refreshStatus() {
      if (this.disposed || this.checking || this.busy || document.hidden) { this.scheduleCheck(); return; }
      await this.load(true);
    },
    async load(quiet = false) {
      if (!quiet) this.loading = true; this.error = '';
      try { this.trx = (await API.get('/transaksi/detail?k=' + encodeURIComponent(this.code), false, quiet)).data; this.$dispatch('payment-updated', this.trx); if (!this.returnToCounter()) this.scheduleCheck(); }
      catch (e) { this.error = e.message; this.scheduleCheck(15000); }
      finally { this.loading = false; }
    },
    async action(endpoint, extra = {}) {
      if (this.busy) return;
      this.busy = true;
      try { await API.post(endpoint, { kode_transaksi: this.code, ...extra }); await this.load(); }
      catch (e) { this.error = e.message; }
      finally { this.busy = false; }
    },
    async upload() {
      if (!this.proof || this.busy) return;
      this.busy = true;
      const data = new FormData(); data.append('kode_transaksi', this.code); data.append('bukti', this.proof);
      try {
        await API.post('/pembayaran/bukti', data); this.proof = null; await this.load();
        if (this.counterFlow && this.role === 'kasir' && !this.printRequested) { this.redirecting = true; clearTimeout(this.timer); window.location.replace(BASE_URL + '/admin/kasir'); }
      }
      catch (e) { this.error = e.message; }
      finally { this.busy = false; }
    },
    async verify(action) {
      if (action === 'tolak' && this.note.trim().length < 5) { Alpine.store('ui').toast('Isi alasan penolakan minimal 5 karakter.', 'error'); return; }
      if (!await Alpine.store('ui').confirm(action === 'terima' ? 'Pastikan uang telah masuk ke rekening toko. Terima pembayaran ini?' : 'Tolak bukti transfer ini?')) return;
      await this.action('/pembayaran/verifikasi', { aksi: action, catatan: this.note });
      if (!this.error && !this.redirecting) {
        this.redirecting = true; clearTimeout(this.timer);
        window.location.replace(BASE_URL + (this.trx.sumber_transaksi === 'kasir' ? '/admin/kasir' : '/admin/pesanan?tahap=' + (action === 'terima' ? 'diproses' : 'menunggu_pembayaran')));
      }
    },
    async refund() {
      if (this.note.trim().length < 5) { Alpine.store('ui').toast('Isi catatan pengembalian dana.', 'error'); return; }
      if (!await Alpine.store('ui').confirm('Pastikan dana sudah dikembalikan kepada pelanggan. Catat pengembalian ini?')) return;
      await this.action('/pembayaran/refund', { catatan: this.note });
      if (!this.error) { this.redirecting = true; clearTimeout(this.timer); window.location.replace(BASE_URL + '/admin/transaksi?status=dibatalkan'); }
    },
    waLink() {
      let phone = String(this.trx?.no_hp_penerima || this.trx?.user_phone || '').replace(/\D/g, '');
      if (phone.startsWith('0')) phone = '62' + phone.slice(1);
      if (!/^628\d{7,12}$/.test(phone)) return null;
      return 'https://wa.me/' + phone + '?text=' + encodeURIComponent('Halo ' + (this.trx.nama_penerima || this.trx.user || '') + ', kami dari GGmart ingin menghubungi Anda tentang pesanan ' + this.code + '.');
    }
  };
}

function storeSettingsPage() {
  return {
    tab: 'akun', rekening: { bank_nama: '', bank_rekening: '', bank_pemilik: '' }, zona: [], filter: '', district: '', busy: false, loaded: false, error: '',
    get districts() { return [...new Set(this.zona.map(z => z.kecamatan))]; },
    get filtered() { return this.zona.filter(z => (!this.district || z.kecamatan === this.district) && z.kelurahan.toLowerCase().includes(this.filter.toLowerCase())); },
    async init() {
      try {
        const d = (await API.get('/pengaturan/toko')).data;
        this.rekening = d.rekening; this.zona = d.zona.map(z => ({ ...z, tarif: Number(z.tarif), aktif: Number(z.aktif) === 1 })); this.loaded = true; this.error = '';
      } catch (e) { this.loaded = false; this.error = e.message; }
    },
    async save(section) {
      if (this.busy || !this.loaded) return;
      this.busy = true;
      this.error = '';
      try { await API.put('/pengaturan/' + section, section === 'pembayaran' ? { rekening: this.rekening } : { zona: this.zona }); }
      catch (e) { this.error = e.message; }
      finally { this.busy = false; }
    }
  };
}

utils.customerWhatsApp = function (trx) {
  if (trx.sumber_transaksi === 'kasir') return null;
  let phone = String(trx.no_hp_penerima || trx.user_phone || '').replace(/\D/g, '');
  if (phone.startsWith('0')) phone = '62' + phone.slice(1);
  if (!/^628\d{7,12}$/.test(phone)) return null;
  return 'https://wa.me/' + phone + '?text=' + encodeURIComponent('Halo ' + (trx.nama_penerima || trx.user || '') + ', kami dari GGmart ingin menghubungi Anda tentang pesanan ' + trx.kode_transaksi + '.');
};

utils.orderStatus = function (trx) {
  if (trx.status === 'pending') return trx.status_bayar === 'menunggu_verifikasi' ? 'Menunggu verifikasi' : trx.status_bayar === 'lunas' ? 'Diproses' : 'Menunggu pembayaran';
  if (trx.status === 'diproses' && Number(trx.siap_diambil)) return 'Siap diambil';
  return ({ diproses: 'Diproses', dikirim: 'Dikirim', selesai: 'Selesai', dibatalkan: 'Dibatalkan' })[trx.status] || trx.status;
};
utils.orderAction = function (trx) {
  if (trx.status === 'diproses' && trx.metode_bayar === 'tunai' && trx.status_bayar === 'belum_bayar' && trx.metode_pengiriman === 'ambil' && trx.sumber_transaksi === 'online') {
    return Number(trx.siap_diambil) ? { endpoint: 'tunai', label: 'Terima tunai & serahkan', question: 'Pastikan uang tunai sudah diterima dan barang diserahkan kepada pelanggan. Selesaikan pesanan?' } : { endpoint: 'siap-ambil', label: 'Siap diambil', question: 'Pesanan sudah siap diambil pelanggan?' };
  }
  if (trx.status_bayar !== 'lunas') return null;
  if (trx.status === 'pending') return { endpoint: 'proses', label: 'Proses', question: 'Mulai menyiapkan pesanan ini?' };
  if (trx.status === 'diproses' && trx.metode_pengiriman === 'kirim') return { endpoint: 'kirim', label: 'Kirim pesanan', question: 'Barang sudah diserahkan kepada pengantar?' };
  if (trx.status === 'diproses' && trx.sumber_transaksi === 'online' && !Number(trx.siap_diambil)) return { endpoint: 'siap-ambil', label: 'Siap diambil', question: 'Pesanan sudah siap diambil pelanggan?' };
  if (['diproses', 'dikirim'].includes(trx.status)) return { endpoint: 'konfirmasi', label: 'Selesaikan', question: 'Pastikan barang sudah diterima pelanggan. Selesaikan pesanan ini?' };
  return null;
};
utils.orderEndpoint = action => action.endpoint === 'tunai' ? '/pembayaran/tunai' : '/transaksi/' + action.endpoint;

// getRandomValues tersedia juga ketika laptop membuka toko melalui HTTP jaringan lokal.
utils.checkoutKey = function () {
  if (globalThis.crypto.randomUUID) return globalThis.crypto.randomUUID();
  return Array.from(globalThis.crypto.getRandomValues(new Uint8Array(16)), n => n.toString(16).padStart(2, '0')).join('');
};
