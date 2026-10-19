<?php if (in_array($_SESSION['user']['role'] ?? '',['pelanggan','admin','kasir'],true)): ?>
<div x-data x-cloak x-show="$store.notifications.open" class="notification-overlay" @keydown.escape.window="$store.notifications.closePanel()" @click.self="$store.notifications.closePanel()" x-effect="if ($store.notifications.open) $nextTick(() => $refs.closeNotifications.focus())">
  <section role="dialog" aria-modal="true" aria-labelledby="notification-title" class="notification-panel" @keydown.tab="$store.notifications.trapFocus($event)">
    <header class="notification-panel-heading">
      <div><h2 id="notification-title">Notifikasi</h2><p><span x-text="$store.notifications.unread"></span> belum dibaca</p></div>
      <button x-ref="closeNotifications" type="button" class="user-icon-button" @click="$store.notifications.closePanel()" aria-label="Tutup notifikasi"><?= masterIconSvg('close','h-5 w-5') ?></button>
    </header>
    <div class="notification-preferences">
      <p x-text="$store.notifications.browserStatus"></p>
      <button type="button" class="notification-enable" x-show="$store.notifications.supported && $store.notifications.permission !== 'denied' && !$store.notifications.browserActive" :disabled="$store.notifications.enabling" @click="$store.notifications.enableBrowser()" x-text="$store.notifications.enabling ? 'Mengaktifkan…' : 'Aktifkan notifikasi browser'"></button>
      <button type="button" class="notification-enable" x-show="$store.notifications.browserActive" @click="$store.notifications.disableBrowser()">Nonaktifkan di perangkat ini</button>
    </div>
    <div class="notification-toolbar"><span>Pemberitahuan terbaru</span><button type="button" :disabled="!$store.notifications.unread || $store.notifications.busy" @click="$store.notifications.readAll()">Tandai semua dibaca</button></div>
    <div class="notification-list">
      <p class="notification-empty" x-show="$store.notifications.loading">Memuat notifikasi…</p>
      <p class="notification-error" role="status" x-show="$store.notifications.error" x-text="$store.notifications.error"></p>
      <p class="notification-empty" x-show="!$store.notifications.loading && !$store.notifications.items.length && !$store.notifications.error">Belum ada notifikasi.</p>
      <template x-for="item in $store.notifications.items" :key="item.id">
        <button type="button" class="notification-item" :class="{'is-unread': !item.dibaca_pada}" @click="$store.notifications.visit(item)">
          <span class="notification-dot" aria-hidden="true"></span>
          <span class="min-w-0"><span class="notification-item-title" x-text="item.judul"></span><span class="notification-item-body" x-text="item.pesan"></span><span class="notification-item-time" x-text="$store.notifications.time(item.tanggal)"></span><span class="sr-only" x-show="!item.dibaca_pada">Belum dibaca</span></span>
        </button>
      </template>
      <button type="button" class="notification-more" x-show="$store.notifications.more" :disabled="$store.notifications.busy" @click="$store.notifications.loadMore()">Muat lebih banyak</button>
    </div>
  </section>
</div>
<?php endif; ?>
