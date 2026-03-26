<?php

declare(strict_types=1);

session_start();

$request = ['data'=>[],'error'=>null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  if(!property_exists($json,'nameFolder') || !property_exists($json,'pathFolder')){
    throw new ErrorException('Не корректная структура данных');
  }

  $nameFolder = trim($json->nameFolder);
  $pathFolder = $json->pathFolder;

  if (
    gettype($nameFolder) !== 'string' ||
    gettype($pathFolder) !== 'string' ||
    strlen($nameFolder) === 0 || 
    strlen($pathFolder) === 0) {
    throw new ErrorException('Данные не корректны');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $isAdd = $database->addSelectedFolder($_SESSION['idUser'], $nameFolder, $pathFolder);

  $database->close();

  if (!$isAdd) {
    throw new ErrorException('Не удалось добвить папку в избранное');
  }

  $request['data'] = true;

  echo json_encode($request);
} catch (Exception $e) {
  $request['error']=$e->getMessage();
  echo json_encode($request);
}
