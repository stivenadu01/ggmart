<?php

function uploadImageGeneral($file, $folder, $filename = null, $maxSizeMB = 10)
{
  if (!isset($file) || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    throw new Exception('File tidak valid atau rusak', 400);
  }

  if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxSizeMB * 1024 * 1024) {
    throw new Exception("Ukuran gambar maksimum {$maxSizeMB}MB", 413);
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($file['tmp_name']);
  $allowedMime = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
  ];
  if (!isset($allowedMime[$mime])) {
    throw new Exception('Format gambar tidak didukung', 422);
  }

  $folder = trim($folder, '/\\');
  if ($folder === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $folder)) {
    throw new Exception('Folder upload tidak valid', 500);
  }

  $baseDir = ROOT_PATH . '/public/uploads/' . $folder;
  if (!is_dir($baseDir) && !mkdir($baseDir, 0755, true) && !is_dir($baseDir)) {
    throw new Exception('Direktori upload tidak dapat dibuat', 500);
  }

  $baseName = $filename ?: strtoupper($folder) . '_' . bin2hex(random_bytes(8));
  $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
  // Nama unik mencegah file lama tertimpa sebelum transaksi DB berhasil.
  $storedName = $baseName . '_' . bin2hex(random_bytes(4)) . '.' . $allowedMime[$mime];
  $dest = $baseDir . '/' . $storedName;

  if (!move_uploaded_file($file['tmp_name'], $dest)) {
    throw new Exception('Gagal menyimpan file', 500);
  }

  @chmod($dest, 0644);
  return '/' . $folder . '/' . $storedName;
}

function deleteUploadedFile(?string $dbPath): void
{
  if (!$dbPath) return;
  $uploadsRoot = realpath(ROOT_PATH . '/public/uploads');
  if (!$uploadsRoot) return;

  $relative = ltrim(str_replace('\\', '/', $dbPath), '/');
  $candidate = ROOT_PATH . '/public/uploads/' . $relative;
  $real = realpath($candidate);
  if ($real && str_starts_with($real, $uploadsRoot . DIRECTORY_SEPARATOR) && is_file($real)) {
    @unlink($real);
  }
}
