<?php

declare(strict_types=1);

session_start();

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  if (!property_exists($json, 'pathFolder')) {
    throw new ErrorException('Не корректная структура данных');
  }

  $pathFolder = $json->pathFolder;

  if (
    gettype($pathFolder) !== 'string' ||
    $_POST['pathFolder'] === ''
  ) {
    throw new ErrorException('Данные не корректные');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $isDeleted = $database->deleteSelectedFolder($_SESSION['idUser'], $pathFolder);

  $database->close();

  if (!$isDeleted) {
    throw new ErrorException('Не удалось удалить папку из избранного');
  }

  $response['data'] = true;

  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
