<?php

function csrf_token(): string
{
  if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function csrf_header_token(): string
{
  return trim((string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
}

function verify_csrf_token(): bool
{
  $sessionToken = $_SESSION['csrf_token'] ?? '';
  if (!is_string($sessionToken) || $sessionToken === '') return false;

  $provided = csrf_header_token();
  if ($provided === '') {
    $provided = (string)(input('_csrf') ?? '');
  }

  return $provided !== '' && hash_equals($sessionToken, $provided);
}

function require_csrf(): void
{
  if (!verify_csrf_token()) {
    response([
      'success' => false,
      'message' => 'Token keamanan tidak valid atau sesi telah kedaluwarsa. Muat ulang halaman lalu coba lagi.'
    ], 403);
    exit;
  }
}
