<?php

declare(strict_types=1);
session_start();

if (!$_SESSION['login'] && !$_SESSION['idUser'] && !$_SESSION['path_user'] && !$_SESSION['path_storage']) {
  exit("Сессия недоступна");
}

$STATUS_REQUEST = ['success' => '0', 'error' => '1', 'sizeError' => '2'];

if (strlen($_POST['oldPath']) === 0 || strlen($_POST['newPath']) === 0 || strlen($_POST['fileName']) === 0) {
  exit($STATUS_REQUEST['error']);
}

$oldPath = $_POST['oldPath'];
$newPath = $_POST['newPath'];
$file = [$_POST['fileName'], $_POST['fileType']];

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/explorer.php";

$pathFile = "{$oldPath}{$file[0]}{$file[1]}";

$sizeFile = $explorerController->getSizeFile($pathFile);

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";

if ($sizeFile !== 0) {
  $isUpdate = $databaseController->addSizeStorage($_SESSION['idUser'], $sizeFile);
  
  if (!$isUpdate) {
    $status['status'] = $STATUS_REQUEST['sizeError'];
    $status['textError'] = "В хранилище недостаточно места :(";
    $databaseController->close();
    exit(json_encode($status));
  }
}


$dataCopy = $explorerController->copyFile($oldPath, $newPath, $file);

if (!$dataCopy['status']) {
  exit($STATUS_REQUEST['error']);
}

$freeSizeInPercent = $databaseController->getFreeSizeStorageInPercent($_SESSION['idUser']);
$freeSize = $explorerController->formattedSize($databaseController->getFreeSizeStorage($_SESSION['idUser']));

$databaseController->close();

$dataCopy['freeSizePercent'] = $freeSizeInPercent;
$dataCopy['freeSize'] = $freeSize;

echo json_encode($dataCopy);
