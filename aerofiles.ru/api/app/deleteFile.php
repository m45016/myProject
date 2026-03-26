<?php

declare(strict_types=1);

session_start();

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  if (!property_exists($json, 'fileName') || !property_exists($json, 'isFile')) {
    throw new ErrorException('Не корректная структура данных');
  }

  $fileName = $json->fileName;
  $isFile = $json->isFile;
  $sizeFile = null;
  $dataDeleted = null;
  $SelectedFolders = [];
  $folders = [];

  if (
    gettype($fileName) !== 'string' ||
    gettype($isFile) !== 'boolean'
  ) {
    throw new ErrorException('Данные не корректные');
  }


  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);
  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  if (!$isFile) {
    $dataDeleted = $explorer->deleteFolder($fileName);
    $SelectedFolders = $dataDeleted['folders'];
  } else {
    $dataDeleted = $explorer->deleteFile($fileName);
  }

  if (!$dataDeleted['isDeleted']) {
    throw new ErrorException('Не удалось удалить файл или папку');
  }

  if (!empty($SelectedFolders)) {
    foreach ($SelectedFolders as $folder) {
      $deleted = $database->deleteSelectedFolder($_SESSION['idUser'], $folder);
      if ($deleted) {
        array_push($folders, $folder);
      }
    }
  }

  $database->subSizeStorage($_SESSION['idUser'], $dataDeleted['sizeFile']);

  $storageInfo = $database->getStorageInfo($_SESSION['idUser']);
  $freeSizeInPercent = $storageInfo['freeSizeStorageInPercent'];
  $freeSize = $explorer->shortSizeFile($storageInfo['freeSizeStorage']);

  $database->close();

  $response['data']['freeSizePercent'] = $freeSizeInPercent;
  $response['data']['freeSize'] = $freeSize;
  $response['data']['status'] = $STATUS_REQUEST['success'];
  $response['data']['folders'] = $folders;

  echo json_encode($response);
} catch (Exception $e) {
  $response['error']=$e->getMessage();
  echo json_encode($response);
}
