<?php

declare(strict_types=1);
session_start();

if (!$_SESSION['login'] && !$_SESSION['idUser'] && !$_SESSION['path_user'] && !$_SESSION['path_storage']) {
  exit("Сессия недоступна");
}

if ($_POST['oldPath'] === '' || $_POST['newPath'] === '' || $_POST['fileName'] === '') {
  exit("Некорректные данные");
}

$oldPath = $_POST['oldPath'];
$newPath = $_POST['newPath'];
$file = [$_POST['fileName'], $_POST['fileType']];
$isFile = true;


if($oldPath === $newPath){
  exit (json_encode(['status'=>1, 'textError'=>'Пути для перемещения одинаковые!']));
}


$filePath = "{$_SESSION['path_storage']}{$oldPath}{$file[0]}{$file[1]}";

if ($file[1] === '') {
  $isFile = false;
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/explorer.php";

$dataCopy = $explorerController->copyFile($oldPath, $newPath, $file);

if (!$dataCopy['status']) {
  exit(json_encode($dataCopy));
}
$dataCopy['selectedFolders'] = null;

if (!$isFile) {
  $newPathSelectedFolders = $dataCopy['newPathFile'];
  $oldPathSelectedFolders = $dataCopy['oldPathFile'];
  $newNameFolder = basename($newPathSelectedFolders);
  $oldNameFolder = basename($oldPathSelectedFolders);

  require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";

  $dataDeleted = $explorerController->deleteFolder($filePath, true);

  if($newNameFolder !== $oldNameFolder){
    $isUpdate = $databaseController->updateNameSelectedFolderForPath($_SESSION['idUser'], $oldNameFolder, $newNameFolder, $oldPathSelectedFolders);
  }

  $selectedFolders = $databaseController->updateSelectedFolders($_SESSION['idUser'], $oldPathSelectedFolders, $newPathSelectedFolders);

  if (!is_null($selectedFolders)) {
    $dataCopy['selectedFolders'] = $selectedFolders;
  }

  $databaseController->close();
} else {
  $explorerController->deleteFile($filePath, true);
}
echo json_encode($dataCopy);
