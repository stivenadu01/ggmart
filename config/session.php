<?php
// 🔒 Hindari konfigurasi session untuk CLI (misal saat jalankan seeder)
if (php_sapi_name() !== 'cli') {
  session_name($_ENV['SESSION_NAME']);
  session_start([
    'cookie_lifetime' => (int)$_ENV['SESSION_LIFETIME'] ?? 43200,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'use_strict_mode' => true,
  ]);
}
