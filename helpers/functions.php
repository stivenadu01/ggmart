<?php

function url($path = '')
{
  $base_url = rtrim(BASE_URL, '/');
  $path = ltrim($path, '/');
  return $base_url . '/' . $path;
}

function models($model)
{
  require_once ROOT_PATH . '/models/' . $model . '.php';
}

function redirect($url)
{
  header("Location: " . url($url));
  exit;
}

function redirect_back($fallback = '')
{
  $referer = $_SERVER['HTTP_REFERER'] ?? null;
  if ($referer) {
    header("Location: " . $referer);
  } else {
    header("Location: " . url($fallback));
  }
  exit;
}


function date_indo($format = 'j F Y', $tanggal = null)
{
  // Tentukan timestamp
  if (is_null($tanggal)) {
    $timestamp = time(); // default: waktu sekarang
  } elseif (is_numeric($tanggal)) {
    $timestamp = (int)$tanggal; // kalau sudah timestamp
  } elseif (is_string($tanggal)) {
    $timestamp = strtotime($tanggal);
    if ($timestamp === false) $timestamp = time(); // fallback
  } elseif ($tanggal instanceof DateTime) {
    $timestamp = $tanggal->getTimestamp(); // dukung objek DateTime
  } else {
    $timestamp = time();
  }

  // Daftar nama hari dan bulan Indonesia
  $hari_indo = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
  ];

  $bulan_indo = [
    'January' => 'Januari',
    'February' => 'Februari',
    'March' => 'Maret',
    'April' => 'April',
    'May' => 'Mei',
    'June' => 'Juni',
    'July' => 'Juli',
    'August' => 'Agustus',
    'September' => 'September',
    'October' => 'Oktober',
    'November' => 'November',
    'December' => 'Desember'
  ];

  // Format tanggal sesuai input
  $hasil = date($format, $timestamp);

  // Ganti nama hari & bulan Inggris ke Indonesia
  $hasil = strtr($hasil, $hari_indo);
  $hasil = strtr($hasil, $bulan_indo);

  return $hasil;
}



require_once ROOT_PATH . '/helpers/auth_func.php';
require_once ROOT_PATH . '/helpers/api_func.php';
