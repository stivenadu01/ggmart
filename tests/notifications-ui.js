const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const {webcrypto} = require('node:crypto');
const source = fs.readFileSync('public/assets/js/notifications.js', 'utf8');
async function fixture({role = 'pelanggan', supported = true} = {}) {
  let init, permissionCalls = 0, nativeCalls = [], toasts = [], scheduled = [], listeners = {};
  const stores = {ui: {toast: (...args) => toasts.push(args)}};
  const user = {id_pengguna: 15, role};
  const storage = new Map([['ggmart-notifikasi-sesi', 'session-a']]);
  let feed = {items: [], events: [], more: false, next: 0, unread: 0, latest_id: 0, user_id: 15, session: 'session-a'};
  let won = [], deferred = null;
  let displayed = [];
  const worker = {active: {}, showNotification: async (...args) => nativeCalls.push(args), getNotifications: async () => displayed};
  const Notification = function(title, options) { nativeCalls.push([title, options]); };
  Notification.permission = 'default';
  Notification.requestPermission = async () => { permissionCalls++; Notification.permission = 'granted'; return 'granted'; };
  const context = {
    window: {__USER__: user, NOTIFICATION_SESSION: 'session-a', CSRF_TOKEN: 'csrf', isSecureContext: supported, addEventListener: (key, handler) => {listeners[key] = handler;}, focus: () => {}},
    document: {hidden: false, addEventListener: (key, handler) => {if (key === 'alpine:init') init = handler;}},
    Alpine: {store: (key, value) => value ? (stores[key] = value) : stores[key]},
    BASE_URL: 'http://localhost/ggmart', Notification, crypto: webcrypto,
    navigator: {serviceWorker: {register: async () => worker, ready: Promise.resolve(worker), getRegistration: async () => worker}},
    location: {search: '', pathname: '/ggmart/produk', hash: '', assign: () => {}, reload: () => {}},
    history: {replaceState: () => {}, state: null},
    localStorage: {getItem: key => storage.get(key) || null, setItem: (key, value) => storage.set(key, value)},
    URL, URLSearchParams, setTimeout: callback => {scheduled.push(callback); return scheduled.length;}, clearTimeout: () => {},
    fetch: async (url, options) => {
      if (deferred && !options.body) await deferred;
      const data = options.body ? {ids: won, session: 'session-a', user_id: 15} : feed;
      return {ok: true, status: 200, json: async () => ({success: true, data})};
    }
  };
  vm.runInNewContext(source, context); init();
  for (let i = 0; i < 30; i++) await Promise.resolve();
  return {
    store: stores.notifications, storage, nativeCalls, toasts, scheduled, listeners, Notification,
    get permissionCalls() {return permissionCalls;},
    set feed(value) {feed = {...feed, ...value};}, set won(value) {won = value;}, set deferred(value) {deferred = value;}, context,
    set displayed(value) {displayed = value;},
  };
}
(async () => {
  const app = await fixture();
  assert.equal(app.permissionCalls, 0, 'Loading a page must not prompt for notification permission');
  assert.equal(app.scheduled.length, 1, 'Polling starts even when permission has not been granted');
  await app.store.enableBrowser();
  assert.equal(app.permissionCalls, 1);
  assert.equal(app.store.browserActive, true);
  const item = {id: 42, judul: 'Pesanan siap diambil', pesan: 'Ambil di GGmart.', tautan: '/pembayaran/GG-TEST', jenis: 'siap_diambil', tanggal: '2026-10-13 01:00:00', dibaca_pada: null};
  app.feed = {items: [item], events: [item], unread: 1, latest_id: 42}; app.won = [42];
  app.context.document.hidden = true;
  await app.store.refresh();
  assert.equal(app.nativeCalls.length, 1, 'A background tab can display a browser notification');
  assert.equal(app.nativeCalls[0][1].data.url, 'http://localhost/ggmart/pembayaran/GG-TEST?notifikasi=42');
  assert.equal(app.store.unread, 1, 'Displaying a notification leaves it unread');
  app.won = []; await app.store.refresh();
  assert.equal(app.nativeCalls.length, 1, 'A losing tab does not repeat the popup');
  app.store.disableBrowser();
  app.context.document.hidden = false; app.won = [42]; await app.store.refresh();
  assert.equal(app.nativeCalls.length, 1);
  assert.equal(app.toasts.length, 1, 'Disabled browser notifications still have an in-app fallback');
  const read = {tag: 'ggmart:15:42', closed: false, close() {this.closed = true;}};
  const arriving = {tag: 'ggmart:15:50', closed: false, close() {this.closed = true;}};
  const another = {tag: 'ggmart:99:40', closed: false, close() {this.closed = true;}};
  app.displayed = [read, arriving, another]; app.won = [];
  await app.store.readAll();
  assert.equal(read.closed, true, 'Reading a notification also closes its native popup');
  assert.equal(arriving.closed, false, 'Mark-all must not close a newly arriving notification');
  assert.equal(another.closed, false, 'Read cleanup is scoped to the current recipient');
  app.Notification.permission = 'denied'; app.store.permission = 'denied';
  assert.match(app.store.browserStatus, /diblokir/);
  app.feed = {events: [], user_id: 99}; await app.store.refresh();
  assert.equal(app.store.items.length, 0, 'A changed account clears the old inbox');
  assert.match(app.store.error, /Sesi berubah/);
  const old = await fixture();
  let resolve; old.deferred = new Promise(done => {resolve = done;});
  old.feed = {items: [item], events: [item], unread: 1}; old.won = [42];
  const pending = old.store.refresh(); old.store.stop(true); resolve(); await pending;
  assert.equal(old.store.items.length, 0, 'Logout ignores an in-flight response');
  assert.equal(old.nativeCalls.length, 0, 'Logout cannot produce a late notification');
  const leader = await fixture({role: 'pimpinan'});
  assert.equal(leader.store.available, false); assert.equal(leader.scheduled.length, 0);
  const insecure = await fixture({supported: false});
  assert.equal(insecure.store.supported, false); await insecure.store.enableBrowser();
  assert.equal(insecure.permissionCalls, 0, 'An insecure origin retains the inbox without prompting');
  console.log('PASS: notifications UI permission, background delivery, deduplication, unread state, logout and role isolation');
})().catch(error => {console.error(error); process.exitCode = 1;});
