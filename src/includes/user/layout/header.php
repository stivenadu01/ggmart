<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?> | GG MART | Toko Pangan</title>
  <link rel="icon" href="<?= ASSETS_URL . '/favicon.ico' ?>" type="image/x-icon">
  <link rel="stylesheet" href="<?= ASSETS_URL . '/css/app.css' ?>">
  <link rel="manifest" href="<?= BASE_URL . '/manifest.json' ?>">
  <script src="<?= ASSETS_URL . '/js/cdn.min.js' ?>" defer></script>
</head>

<body x-data="navbar()" x-init="init()" class="h-[100dvh] flex flex-col bg-gray-50 text-gray-800">
  <?php include INCLUDES_PATH . '/user/layout/navbar.php'; ?>

  <main class="flex-1 overflow-y-auto pt-4 pb-20 px-4 lg:px-10">