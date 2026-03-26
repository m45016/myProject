<?php

declare(strict_types=1);

session_start();

$response = ['data'=>[],'error'=>null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['isAdmin']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Вы не являетесь администратором сайта');
  }

  $json = json_decode(file_get_contents('php://input'));

  if(!property_exists($json, 'idUser') || !property_exists($json, 'maxStorage') || !property_exists($json, 'isAdmin')){
    throw new ErrorException('Не корректная структура данных');
  }

  $idUser = $json->idUser;
  $maxStorage = $json->maxStorage;
  $isAdmin = $json->isAdmin;

  if(
    gettype($idUser) !== 'integer' || 
    gettype($maxStorage) !== 'string' ||
    gettype($isAdmin) !== 'boolean' ||
    $idUser < 0 ||
    strlen($maxStorage) === 0
  ){
    throw new ErrorException('Данные не корректны');
  }

  if (!$isAdmin) {
    $isAdmin = 0;
  } else {
    $isAdmin = 1;
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $isUpdate = $database->updateUserData($idUser, $maxStorage, $isAdmin);

  if (!$isUpdate) {
    throw new ErrorException('Данные не изменены');
  }

  $database->close();

  $response['data'] = true;

  echo json_encode($response);

} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
