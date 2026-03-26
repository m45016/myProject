<?php

declare(strict_types=1);
session_start();

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) && !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  if (!property_exists($json, 'path')) {
    throw new ErrorException('Не корректная структура данных');
  }

  $path = $json->path;

  if (
    gettype($path) !== 'string' ||
    $path === ''
  ) {
    throw new ErrorException('Не корректные данные');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $pathStorage = $explorer->getPathStorage();
  $fullPathFolder = "{$pathStorage}{$path}";
  $pathFolder = $path;

  if (!is_dir($fullPathFolder)) {

    require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
    require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

    $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

    $nameFolder = basename($path);

    $isSelected = $database->isSelectedFolder($_SESSION['idUser'], $nameFolder, $pathFolder);
    $isDeleted = null;

    if ($isSelected) {
      $isDeleted = $database->deleteSelectedFolder($_SESSION['idUser'], $pathFolder);
    }

    $response['data'] = [
      'isExists' => false,
      'isSelected' => $isSelected,
      'isDeleted' => $isDeleted
    ];

    $database->close();
  } else {

    $explorer->openFolder($path);

    $content = $explorer->getFilesFromCurrentPath();

    $elements = $content['elements'];
    $countFiles = $content['length'];
    $pathUser = $content['path'];


    $emptyStorage = false;

    if (empty($countFiles)) {
      $emptyStorage = true;
    }

    $response['data'] = [
      'elements' => $elements,
      'countFiles' => $countFiles,
      'pathUser' => $pathUser,
      'emptyStorage' => $emptyStorage,
      'isExists' => true
    ];

    $_SESSION['pathUser'] = $pathUser;
    
  }

  echo json_encode($response);

} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
