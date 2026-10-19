<?php

function model($name)
{
  $name = ucfirst($name);
  require_once ROOT_PATH . "/app/models/{$name}.php";
}

function view($page, $data = [], $layout = 'user')
{
  extract($data);

  ob_start();
  require ROOT_PATH . "/app/views/pages/$page.php";
  $content = ob_get_clean();

  require ROOT_PATH . "/app/views/layouts/$layout.php";
}


require_once __DIR__ . '/request_helper.php';
require_once __DIR__ . '/response_helper.php';

require_once __DIR__ . '/csrf_helper.php';
require_once __DIR__ . '/master_icon_helper.php';
require_once __DIR__ . '/order_helper.php';
require_once __DIR__ . '/notification_helper.php';
