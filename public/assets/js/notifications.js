// Pusat notifikasi memakai API penerima. Polling tetap berjalan pada tab latar
// (frekuensinya dapat dibatasi browser); tidak menggunakan push saat semua tab ditutup.
document.addEventListener('alpine:init', () => {
  const user = window.__USER__;
  const available = !!user && ['pelanggan', 'admin', 'kasir'].includes(user.role);
  const readStorage = key => { try { return localStorage.getItem(key); } catch { return null; } };
  const writeStorage = (key, value) => { try { localStorage.setItem(key, value); } catch { } };
  let device = readStorage('ggmart-notifikasi-perangkat');
  if (!/^[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}$/.test(device || '')) {
    device = crypto.randomUUID ? crypto.randomUUID() : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
      const byte = crypto.getRandomValues(new Uint8Array(1))[0] % 16;
      return (c === 'x' ? byte : (byte & 3) | 8).toString(16);
    });
    writeStorage('ggmart-notifikasi-perangkat', device);
  }
  const enabledKey = 'ggmart-notifikasi-aktif:' + (user?.id_pengguna || '');
  const session = window.NOTIFICATION_SESSION || '';
  let timer = null, worker = null, stopped = !available, backoff = 10000, trigger = null;
  const nativeNotifications = new Set();
  async function request(path, data = null) {
    const response = await fetch(BASE_URL + '/api/notifikasi' + path, {
      method: data === null ? 'GET' : 'POST', credentials: 'same-origin', cache: 'no-store',
      headers: { Accept: 'application/json', ...(data === null ? {} : { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN || '' }) },
      ...(data === null ? {} : { body: JSON.stringify({ ...data, sesi: session }) })
    });
    const body = await response.json().catch(() => ({}));
    if (!response.ok || !body.success) {
      const error = new Error(body.message || 'Notifikasi belum dapat dimuat.');
      error.status = response.status; throw error;
    }
    if (body.data?.session && (body.data.session !== session || Number(body.data.user_id) !== Number(user.id_pengguna))) {
      const error = new Error('Sesi berubah. Silakan muat ulang halaman.'); error.status = 401; throw error;
    }
    return body.data;
  }
  async function prepareWorker() {
    if (!('serviceWorker' in navigator) || !window.isSecureContext) return null;
    try {
      worker = await navigator.serviceWorker.register(BASE_URL + '/notification-worker.js', { scope: BASE_URL + '/' });
      if (!worker.active) await navigator.serviceWorker.ready;
      return worker;
    } catch { return null; }
  }
  async function closeBrowserNotifications() {
    if (!('serviceWorker' in navigator)) return;
    try {
      const registration = await navigator.serviceWorker.getRegistration(BASE_URL + '/');
      const notifications = registration ? await registration.getNotifications() : [];
      for (const item of notifications) if (item.tag.startsWith('ggmart:')) item.close();
    } catch { }
  }
  async function dismissReadNotifications(upto = Infinity, exact = false) {
    const prefix = 'ggmart:' + user.id_pengguna + ':';
    const matches = item => item.tag.startsWith(prefix) && (exact ? Number(item.tag.slice(prefix.length)) === upto : Number(item.tag.slice(prefix.length)) <= upto);
    for (const item of nativeNotifications) if (matches(item)) { item.close(); nativeNotifications.delete(item); }
    if (!('serviceWorker' in navigator)) return;
    try {
      const registration = worker || await navigator.serviceWorker.getRegistration(BASE_URL + '/');
      for (const item of registration ? await registration.getNotifications() : []) if (matches(item)) item.close();
    } catch { }
  }
  Alpine.store('notifications', {
    available, open: false, items: [], unread: 0, latest: 0, more: false, next: 0,
    loading: false, busy: false, error: '', enabling: false,
    supported: typeof Notification !== 'undefined' && !!window.isSecureContext,
    permission: typeof Notification === 'undefined' ? 'unsupported' : Notification.permission,
    enabled: readStorage(enabledKey) === '1',
    get browserActive() { return this.enabled && this.permission === 'granted'; },
    get browserStatus() {
      if (!this.supported) return 'Notifikasi browser belum tersedia di perangkat atau alamat ini. Pemberitahuan tetap tersimpan di sini.';
      if (this.permission === 'denied') return 'Notifikasi browser diblokir. Anda dapat mengizinkannya melalui pengaturan situs di browser.';
      return this.browserActive ? 'Notifikasi browser aktif di perangkat ini.' : 'Aktifkan notifikasi browser untuk menerima pemberitahuan pesanan.';
    },
    async start() {
      if (!available) return;
      const previous = readStorage('ggmart-notifikasi-sesi');
      if (previous !== session) await closeBrowserNotifications();
      writeStorage('ggmart-notifikasi-sesi', session);
      if (this.browserActive) prepareWorker();
      const params = new URLSearchParams(location.search);
      const notification = Number(params.get('notifikasi'));
      if (Number.isSafeInteger(notification) && notification > 0) {
        try { await request('/baca', { id: notification }); } catch { }
        params.delete('notifikasi');
        history.replaceState(history.state, '', location.pathname + (params.toString() ? '?' + params : '') + location.hash);
      }
      const tick = async () => {
        if (stopped) return;
        await this.refresh();
        if (!stopped) timer = setTimeout(tick, backoff);
      };
      tick();
    },
    stop(logout = false) {
      stopped = true; clearTimeout(timer); this.open = false; this.items = []; this.unread = 0;
      for (const item of nativeNotifications) item.close();
      nativeNotifications.clear();
      if (logout) {
        writeStorage('ggmart-notifikasi-sesi', 'logout:' + Date.now());
        closeBrowserNotifications();
      }
    },
    async refresh() {
      if (stopped || this.busy) return;
      this.busy = true;
      if (!this.items.length) this.loading = true;
      try {
        const result = await request('?perangkat=' + encodeURIComponent(device));
        if (stopped) return;
        this.unread = result.unread; this.latest = result.latest_id;
        const first = new Map(result.items.map(item => [Number(item.id), item]));
        this.items = result.items.length ? [...result.items, ...this.items.filter(item => !first.has(Number(item.id)) && Number(item.id) < Number(result.items.at(-1).id))] : [];
        if (this.items.length <= 20) { this.more = result.more; this.next = result.next; }
        this.error = ''; backoff = 10000;
        this.permission = this.supported ? Notification.permission : 'unsupported';
        if (!result.unread && result.latest_id) await dismissReadNotifications(result.latest_id);
        if (result.events.length) {
          const claim = await request('/klaim', { perangkat: device, sesi: session, ids: result.events.map(item => Number(item.id)) });
          if (stopped) return;
          const won = new Set(claim.ids);
          for (const item of result.events) {
            if (!won.has(Number(item.id))) continue;
            const shown = this.browserActive && await this.showBrowser(item);
            if (!shown && !document.hidden) Alpine.store('ui').toast(item.judul, 'success', 5000);
          }
        }
      } catch (error) {
        if (error.status === 401 || error.status === 403 || error.status === 409) {
          this.stop(); this.error = 'Sesi berubah. Muat ulang halaman untuk melihat notifikasi.';
        } else { this.error = error.message; backoff = Math.min(backoff * 2, 60000); }
      } finally { this.busy = false; this.loading = false; }
    },
    async openPanel(button = null) {
      trigger = button;
      this.open = true;
      await this.refresh();
    },
    closePanel() { this.open = false; trigger?.focus(); },
    async enableBrowser() {
      if (!this.supported || this.enabling) return;
      this.enabling = true;
      try {
        // Permintaan izin hanya akibat tombol yang diklik pengguna.
        this.permission = await Notification.requestPermission();
        this.enabled = this.permission === 'granted';
        writeStorage(enabledKey, this.enabled ? '1' : '0');
        if (this.enabled) await prepareWorker();
      } catch { this.error = 'Notifikasi browser belum dapat diaktifkan.'; }
      finally { this.enabling = false; }
    },
    disableBrowser() { this.enabled = false; writeStorage(enabledKey, '0'); closeBrowserNotifications(); },
    async showBrowser(item) {
      if (stopped) return false;
      const url = new URL(BASE_URL + item.tautan);
      url.searchParams.set('notifikasi', item.id);
      const options = {
        body: item.pesan, icon: BASE_URL + '/assets/logo.png',
        tag: 'ggmart:' + user.id_pengguna + ':' + item.id,
        data: { url: url.href, recipient: Number(user.id_pengguna) },
      };
      try {
        const registration = worker || await prepareWorker();
        if (stopped) return false;
        if (registration?.active) await registration.showNotification(item.judul, options);
        else {
          const notification = new Notification(item.judul, options);
          nativeNotifications.add(notification);
          notification.onclose = () => nativeNotifications.delete(notification);
          notification.onclick = () => { notification.close(); window.focus(); location.assign(url.href); };
        }
        return true;
      } catch { return false; }
    },
    async readAll() {
      if (!this.latest || this.busy) return;
      try {
        const upto = this.latest;
        await request('/baca', { sampai: upto });
        await dismissReadNotifications(upto);
        for (const item of this.items) if (Number(item.id) <= upto) item.dibaca_pada = new Date().toISOString();
        await this.refresh();
      } catch (error) { this.error = error.message; }
    },
    async visit(item) {
      try {
        await request('/baca', { id: Number(item.id) });
        await dismissReadNotifications(Number(item.id), true);
        location.assign(BASE_URL + item.tautan);
      } catch (error) { this.error = error.message; }
    },
    async loadMore() {
      if (!this.more || this.busy || stopped) return;
      this.busy = true;
      try {
        const result = await request('?perangkat=' + encodeURIComponent(device) + '&sebelum=' + this.next);
        const ids = new Set(this.items.map(item => Number(item.id)));
        this.items.push(...result.items.filter(item => !ids.has(Number(item.id))));
        this.more = result.more; this.next = result.next;
      } catch (error) { this.error = error.message; }
      finally { this.busy = false; }
    },
    time(value) {
      const date = new Date(String(value).replace(' ', 'T'));
      return Number.isNaN(date.getTime()) ? '' : date.toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
    },
    trapFocus(event) {
      const panel = event.currentTarget;
      const targets = [...panel.querySelectorAll('button,a,[tabindex="0"]')].filter(el => !el.disabled && el.getClientRects().length);
      const first = targets[0], last = targets.at(-1);
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
      else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    }
  });
  const store = Alpine.store('notifications');
  window.addEventListener('storage', event => {
    if (event.key === 'ggmart-notifikasi-sesi' && event.newValue !== session) store.stop();
    if (event.key === enabledKey) store.enabled = event.newValue === '1';
  });
  window.addEventListener('pagehide', () => store.stop());
  window.addEventListener('pageshow', event => { if (event.persisted) location.reload(); });
  store.start();
});
