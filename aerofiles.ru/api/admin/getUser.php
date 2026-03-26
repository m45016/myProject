<?php

declare(strict_types=1);

$response = ['data' => [], 'error' => null];

try {

  session_start();

  if (!isset($_SESSION['isAdmin']) || !isset($_SESSION['login']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Вы не являетесь администратором сайта');
  }

  $json = json_decode(file_get_contents('php://input'));

  if (!property_exists($json, 'login')) {
    throw new ErrorException('Не корректная структура данных');
  }

  $login = $json->login;

  if (
    gettype($login) !== 'string' ||
    strlen($login) === 0
  ) {
    throw new ErrorException('Данные не корректны');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);
  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $response['data'] = $database->getUserByLogin($login);

  if (is_null($response['data'])) {
    throw new ErrorException('Пользователь не найден');
  }

  $storageInfo = $database->getStorageInfoByLogin($login);

  if ($response['data']['isAdmin']) {
    $response['data']['isAdminCheckBox'] = 'checked';
    $response['data']['isAdminText'] = 'Да';
  } else {
    $response['data']['isAdminCheckBox'] = '';
    $response['data']['isAdminText'] = 'Нет';
  }
  unset($response['data']['password']);
  $response['data']['freeSize'] = $explorer->shortSizeFile($storageInfo['freeSizeStorage']);
  $response['data']['sizeStorage'] = $explorer->shortSizeFile($response['data']['sizeStorage']);
  $response['data']['maxSizeStorage'] = $explorer->shortSizeFile($response['data']['maxSizeStorage']);

  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
