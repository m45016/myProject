<?php
// панель администратора

declare(strict_types=1);

session_start();

header('Content-Security-Policy: default-src \'self\' \'unsafe-eval\'');

$user = ['data' => [], 'error' => null];

try {

  if (!$_SESSION['isAdmin'] && !$_SESSION['login'] && !$_SESSION['pathUser'] && !$_SESSION['pathStorage']) {
    exit('Вы не являетесь администратором сайта!');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);
  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $activeUsers = $database->getActiveUsers();

  $user['data'] = $database->getUserById($_SESSION['idUser']);

  if(is_null($user['data'])){
    throw new ErrorException('Пользователь не найден');
  }

  $storageInfo = $database->getStorageInfo($_SESSION['idUser']);

  if ($user['data']['isAdmin']) {
    $user['data']['isAdminCheckBox'] = 'checked';
    $user['data']['isAdminText'] = 'Да';
  } else {
    $user['data']['isAdminCheckBox'] = '';
    $user['data']['isAdminText'] = 'Нет';
  }

  unset($user['data']['password']);
  $user['data']['freeSize'] = $explorer->shortSizeFile($storageInfo['freeSizeStorage']);
  $user['data']['sizeStorage'] = $explorer->shortSizeFile($user['data']['sizeStorage']);
  $user['data']['maxSizeStorage'] = $explorer->shortSizeFile($user['data']['maxSizeStorage']);

  $database->close();

} catch (Exception $e) {
  $user['error'] = "Ошибка: {$e->getMessage()}!";
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/views/adminView.php";
