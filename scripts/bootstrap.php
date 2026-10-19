<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();
require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/helper.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
