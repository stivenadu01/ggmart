<?php if (in_array($_SESSION['user']['role'] ?? '',['pelanggan','admin','kasir'],true)): ?>
<button type="button" x-data class="notification-bell user-icon-button relative shrink-0" @click="$store.notifications.openPanel($event.currentTarget)" :aria-label="'Notifikasi, ' + $store.notifications.unread + ' belum dibaca'" :aria-expanded="$store.notifications.open" aria-haspopup="dialog">
  <?= masterIconSvg('bell','h-5 w-5') ?>
  <span class="notification-count" x-cloak x-show="$store.notifications.unread > 0" x-text="$store.notifications.unread > 99 ? '99+' : $store.notifications.unread"></span>
</button>
<?php endif; ?>
