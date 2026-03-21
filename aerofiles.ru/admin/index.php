<?php
// панель администратора

declare(strict_types=1);

session_start();

header('Content-Security-Policy: default-src \'self\'');

if (!$_SESSION['isAdmin'] && !$_SESSION['login']) {
  exit('Вы не являетесь администратором сайта');
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/explorer.php";

$user = $databaseController->getUserOnLogin($_SESSION['login']);

if (is_null($user['error'])) {
  if ($user['data']['isAdmin']) {
    $user['data']['isAdminCheckBox'] = 'checked';
    $user['data']['isAdminText'] = 'Да';
  } else {
    $user['data']['isAdminCheckBox'] = '';
    $user['data']['isAdminText'] = 'Нет';
  }
  $user['data']['freeSize'] = $explorerController->formattedSize($user['data']['freeSize']);
  $user['data']['sizeStorage'] = $explorerController->formattedSize((int) $user['data']['sizeStorage']);
  $user['data']['maxSizeStorage'] = $explorerController->formattedSize((int) $user['data']['maxSizeStorage']);
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/view/adminView.php";
