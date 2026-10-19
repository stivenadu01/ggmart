// Hanya menampilkan notifikasi yang diminta halaman aktif; tidak menyimpan
// halaman, bukti bayar, maupun respons API dalam cache.
self.addEventListener('install', event => event.waitUntil(self.skipWaiting()));
self.addEventListener('activate', event => event.waitUntil(self.clients.claim()));
self.addEventListener('notificationclick', event => {
  event.notification.close();
  const url = new URL(event.notification.data?.url || self.registration.scope, self.registration.scope);
  if (url.origin !== self.location.origin || !url.href.startsWith(self.registration.scope)) return;
  event.waitUntil((async () => {
    const tabs = await self.clients.matchAll({type: 'window', includeUncontrolled: true});
    const tab = tabs.find(item => item.url.startsWith(self.registration.scope));
    if (tab) { await tab.navigate(url.href); await tab.focus(); }
    else await self.clients.openWindow(url.href);
  })());
});
