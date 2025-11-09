<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="<?= ASSETS_URL . 'favicon.ico' ?>" type="image/x-icon">
  <title><?= $pageTitle ?> | GG MART</title>
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
  <script src="<?= ASSETS_URL ?>/js/cdn.min.js" defer></script>
  <link rel="manifest" href="<?= BASE_URL . '/manifest.json' ?>">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50"
  x-data="{ navOpen: false }">

  <?php include INCLUDES_PATH . '/user/layout/navbar.php'; ?>