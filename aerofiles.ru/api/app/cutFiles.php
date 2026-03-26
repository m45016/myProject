<?php

declare(strict_types=1);
session_start();

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

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
  $isFile = true;

  $response['debug'] = $json;

  if (
    gettype($oldPath) !== 'string' ||
    gettype($newPath) !== 'string' ||
    gettype($file[0]) !== 'string' ||
    gettype($file[1]) !== 'string' ||
    $oldPath === '' ||
    $newPath === '' ||
    $fileName === ''
  ) {
    throw new ErrorException('Данные не корректны');
  }

  if ($oldPath === $newPath) {
    throw new ErrorException('Пути для перемещения одинаковые!');
  }

  $filePath = "{$_SESSION['pathStorage']}{$oldPath}{$file[0]}{$file[1]}";

  if ($file[1] === '') {
    $isFile = false;
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $dataCopy = $explorer->copyFile($oldPath, $newPath, $file);

  if (!$dataCopy['status']) {
    throw new ErrorException('Не удалось скопировать файлы');
  }

  $dataCopy['selectedFolders'] = null;

  if (!$isFile) {
    $newPathSelectedFolders = $dataCopy['newPathFile'];
    $oldPathSelectedFolders = $dataCopy['oldPathFile'];
    $newNameFolder = basename($newPathSelectedFolders);
    $oldNameFolder = basename($oldPathSelectedFolders);

    require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
    require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

    $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

    $dataDeleted = $explorer->deleteFolder($filePath, true);

    if ($newNameFolder !== $oldNameFolder) {
      $isUpdate = $database->updateNameSelectedFolderForPath($_SESSION['idUser'], $oldNameFolder, $newNameFolder, $oldPathSelectedFolders);
    }

    $database->updatePathSelectedFolders($_SESSION['idUser'], $oldPathSelectedFolders, $newPathSelectedFolders);

    $selectedFolders = $database->getSelectedFolders($_SESSION['idUser']);

    if (!is_null($selectedFolders)) {
      $dataCopy['selectedFolders'] = $selectedFolders;
    }

    $database->close();

  } else {
    $explorer->deleteFile($filePath, true);
  }
  $response['data'] = $dataCopy;
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
