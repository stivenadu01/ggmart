<?php
// Jalankan via CLI setiap menit menggunakan cron / Task Scheduler.
if (PHP_SAPI !== 'cli') {
  http_response_code(403);
  exit("CLI only\n");
}

require_once __DIR__ . '/../app/config/bootstrap.php';

try {
  $count = expireDeliveredOrders(200);
  echo date('Y-m-d H:i:s') . " - Pesanan diselesaikan otomatis: {$count}\n";
} catch (Throwable $e) {
  error_log('Gagal menjalankan penyelesaian pesanan otomatis: ' . $e->getMessage());
  fwrite(STDERR, "Gagal menjalankan tugas otomatis. Periksa log aplikasi.\n");
  exit(1);
}

if (function_exists('db')) db()->close();
