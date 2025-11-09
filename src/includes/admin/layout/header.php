<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="<?= ASSETS_URL . 'favicon.ico' ?>" type="image/x-icon">
  <title><?= $pageTitle ?> | GG MART | Toko Pangan</title>
  <!-- <script src="https://unpkg.com/alpinejs" defer></script>  -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
  <script src="<?= ASSETS_URL . '/js/cdn.min.js' ?>" defer></script>
  <link rel="manifest" href="<?= BASE_URL . '/manifest.json' ?>">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
</head>

<body class="overflow-y-auto h-[100dvh] flex flex-col bg-gray-100"
  x-data="{ 
    sidebarOpen: false,
    sidebarCollapse: JSON.parse(localStorage.getItem('sidebarCollapse')) || false,
    fullscreen: false,
    toggleSidebar() {
      this.sidebarCollapse = !this.sidebarCollapse;
      localStorage.setItem('sidebarCollapse', JSON.stringify(this.sidebarCollapse));
    } 
  }"
  x-on:keydown.window="
    if($event.key === 'Escape') { fullscreen = false;}
    if ($event.key === 'F11') {
      $event.preventDefault();
      fullscreen = !fullscreen;
    }
  ">

  <?php
  include INCLUDES_PATH . '/admin/layout/navbar.php';

  include INCLUDES_PATH . '/admin/layout/sidebar.php';
  ?>
  <main class="flex-1 overflow-y-auto mb-3"
    :class="fullscreen ? 'm-0 p-0' : (sidebarCollapse ? 'lg:ml-16' : 'lg:ml-64')">