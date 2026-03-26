<?php

declare(strict_types=1);
session_start();

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  $response['debug'] = $json;

  if (
    !property_exists($json, 'oldPath') ||
    !property_exists($json, 'newPath') ||
    !property_exists($json, 'fileName') ||
    !property_exists($json, 'fileType')
  ) {
    throw new ErrorException('Не корректная структура данных');
  }

  $oldPath = $json->oldPath;
  $newPath = $json->newPath;
  $file = [$json->fileName, $json->fileType];

  if (
    gettype($oldPath) !== 'string' ||
    gettype($newPath) !== 'string' ||
    gettype($file[0]) !== 'string' ||
    gettype($file[1]) !== 'string' ||
    strlen($oldPath) === 0 ||
    strlen($newPath) === 0 ||
    strlen($file[0]) === 0
  ) {
    throw new ErrorException('Не корректные данные');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";
  
  $explorer = new ExplorerController($_SESSION['pathUser'],$_SESSION['pathStorage']);

  $pathFile = "{$oldPath}{$file[0]}{$file[1]}";

  $sizeFile = $explorer->getSizeFile($pathFile);

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  if ($sizeFile !== 0) {
    $isUpdate = $database->addSizeStorage($_SESSION['idUser'], $sizeFile);

    if (!$isUpdate) {
      throw new ErrorException('В хранилище недостаточно места :(');
    }
  }

  $dataCopy = $explorer->copyFile($oldPath, $newPath, $file);

  if (!$dataCopy['status']) {
    throw new ErrorException('Не удалось скопировать файл');
  } 

  $storageInfo = $database->getStorageInfo($_SESSION['idUser']);
  $freeSizeInPercent = $storageInfo['freeSizeStorageInPercent'];
  $freeSize = $explorer->shortSizeFile($storageInfo['freeSizeStorage']);

  $database->close();

  $dataCopy['freeSizePercent'] = $freeSizeInPercent;
  $dataCopy['freeSize'] = $freeSize;

  $response['data'] = $dataCopy;

  echo json_encode($response);

} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
