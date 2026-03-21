<?php

declare(strict_types=1);
$STATUS_REQUEST = ['success' => '0', 'sessionError' => '1', 'fileError' => '2', 'sizeError' => '3'];
$status = ['dataFile' => null, 'status' => null, 'textError' => null];

session_start();

if (empty($_SESSION['login']) && empty($_SESSION['path_user'])) {
  $status['status'] = $STATUS_REQUEST['sessionError'];
  $status['textError'] = 'Ваша сессия не активна :(';
  exit(json_encode($status));
}

if ($_FILES['file']['error']) {
  $status['status'] = $STATUS_REQUEST['fileError'];
  $status['textError'] = "Файл загружен с ошибкой. Попробуйте позже.";
  exit(json_encode($status));
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/explorer.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";

$isUpdate = $databaseController->addSizeStorage($_SESSION['idUser'], $_FILES['file']['size']);

if (!$isUpdate) {
  $status['status'] = $STATUS_REQUEST['sizeError'];
  $status['textError'] = "В хранилище недостаточно места :(";
  $databaseController->close();
  exit(json_encode($status));
}

$isUpload = $explorerController->uploadFile($_FILES['file']);

if (!$isUpload['isMoved']) {
  $status['status'] = $STATUS_REQUEST['fileError'];
  $status['textError'] = "Файл не найден";
  $databaseController->close();
  exit(json_encode($status));
}

$dataFile = $explorerController->getStatFile($isUpload['nameFile']);

$status['status'] = $STATUS_REQUEST['success'];
$status['dataFile'] = $dataFile;

$freeSizeInPercent = $databaseController->getFreeSizeStorageInPercent($_SESSION['idUser']);
$freeSize = $explorerController->formattedSize($databaseController->getFreeSizeStorage($_SESSION['idUser']));

$status['freeSizePercent'] = $freeSizeInPercent;
$status['freeSize'] = $freeSize;

$databaseController->close();

echo json_encode($status);
