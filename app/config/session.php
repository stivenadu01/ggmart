<?php

$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

session_name('ggmart_session');
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'domain' => '',
  'secure' => $isHttps,
  'httponly' => true,
  'samesite' => 'Lax',
]);

session_start();

// Idle timeout: 2 jam. Tidak mengganggu sesi aktif, tetapi membatasi sesi yang ditinggalkan.
$now = time();
$lastActivity = (int)($_SESSION['_last_activity'] ?? 0);
if ($lastActivity > 0 && ($now - $lastActivity) > 7200) {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  session_destroy();
  session_start();
}
$_SESSION['_last_activity'] = $now;

// Rotasi ID sesi berkala untuk memperkecil risiko session fixation/hijacking.
$lastRegenerated = (int)($_SESSION['_last_regenerated'] ?? 0);
if ($lastRegenerated === 0 || ($now - $lastRegenerated) > 1800) {
  session_regenerate_id(true);
  $_SESSION['_last_regenerated'] = $now;
}
