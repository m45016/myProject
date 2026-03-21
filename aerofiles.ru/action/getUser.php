<?php

declare(strict_types=1);

session_start();

if (!$_SESSION['isAdmin'] && !$_SESSION['login']) {
  exit('Вы не являетесь администратором сайта');
}

if(!isset($_POST['login'])){
  exit(['data'=>null,'error'=>'Данные не корректны']);
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/explorer.php";

$user = $databaseController->getUserOnLogin($_POST['login']);

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

echo json_encode($user);

?>