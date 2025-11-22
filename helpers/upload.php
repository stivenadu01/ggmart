<?php

/**
 * Upload + konversi + kompres gambar ke direktori tertentu.
 *
 * @param array  $file        $_FILES['...']
 * @param string $folder      contoh: "produk", "hero"
 * @param string $filename    jika kosong → generate otomatis
 * @param int    $maxSizeMB   ukuran maksimal file (default 10MB)
 * @param bool   $forceWebp   true = convert semua ke WebP
 *
 * @return string  path yang disimpan di database (misal: "/produk/PRD_123.webp")
 */
function uploadImageGeneral($file, $folder, $filename = null, $maxSizeMB = 10, $forceWebp = false)
{
  if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
    throw new Exception("File tidak valid atau rusak", 400);
  }

  $allowed = ['jpg', 'jpeg', 'png', 'webp'];
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowed)) {
    throw new Exception("Format gambar tidak didukung", 422);
  }

  if ($file['size'] > $maxSizeMB * 1024 * 1024) {
    throw new Exception("Ukuran gambar maksimum {$maxSizeMB}MB", 413);
  }

  // Lokasi upload
  $baseDir = ROOT_PATH . "/public/uploads/";
  if (!file_exists($baseDir . $folder)) {
    mkdir($baseDir . $folder, 0777, true);
  }

  // Penamaan file
  if (!$filename) {
    $filename = strtoupper($folder) . "_" . time() . "_" . rand(1000, 9999);
  }

  // Jika $forceWebp dan GD tersedia dan file >1MB, kompres ke WebP
  if ($forceWebp && function_exists("imagewebp") && $file['size'] > 1024 * 1024) {
    // Output WebP
    $dbPath = "/{$folder}/{$filename}.webp";
    $dest = $baseDir . $dbPath;

    switch ($ext) {
      case 'jpg':
      case 'jpeg':
        $image = imagecreatefromjpeg($file['tmp_name']);
        break;

      case 'png':
        $image = imagecreatefrompng($file['tmp_name']);
        $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefill($bg, 0, 0, $white);
        imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagedestroy($image);
        $image = $bg;
        break;

      case 'webp':
        $image = imagecreatefromwebp($file['tmp_name']);
        break;

      default:
        throw new Exception("Tipe gambar tidak dikenali", 415);
    }

    if (!$image) {
      throw new Exception("Gagal membaca gambar", 500);
    }

    if (!imagewebp($image, $dest, 85)) {
      throw new Exception("Gagal mengonversi gambar ke WebP", 500);
    }

    imagedestroy($image);
  } else {
    // Upload biasa jika GD tidak ada / file kecil / forceWebp false
    $dest = "{$baseDir}{$folder}/{$filename}.{$ext}";
    $dbPath = "/{$folder}/{$filename}.{$ext}";

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
      throw new Exception("Gagal menyimpan file", 500);
    }
  }

  return $dbPath;
}
