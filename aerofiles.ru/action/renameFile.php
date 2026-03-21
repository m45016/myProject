<?php

declare(strict_types=1);

$STATUS_REQUEST = ['success' => '0', 'renameError' => '1'];

session_start();

if (!$_SESSION['login'] && !$_SESSION['idUser'] && !$_SESSION['path_user'] && !$_SESSION['path_storage']) {
  exit("Сессия недоступна");
}

if ($_POST['oldName'] === '' || $_POST['newName'] === '' || $_POST['isFile'] === '' || $_POST['isSelectedFolder'] === '') {
  exit($STATUS_REQUEST['remaneError']);
}

$oldName = $_POST['oldName'];
$newName = trim($_POST['newName']);
$isFile = $_POST['isFile'];
$isSelecetdFolder = $_POST['isSelectedFolder'];

$data = ['data' => null, 'isRenamed' => null, 'selectedFolders'=>null];

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/explorer.php";

$isRenamed = $explorerController->renameFile($oldName, $newName);

if (!$isRenamed) {
  $data['isRenamed'] = $STATUS_REQUEST['renameError'];
  exit(json_encode($data));
}

$data['isRenamed'] = $STATUS_REQUEST['success'];

if ($isFile !== 'true') {

  require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";

  $oldpathFolder = "{$explorerController->getPathUser()}{$oldName}/";
  $newPathFolder = "{$explorerController->getPathUser()}{$newName}/";

  if ($isSelecetdFolder === 'true') {
    $update = $databaseController->updateSelectedFolder($_SESSION['idUser'], $oldName, $newName, $oldpathFolder, $newPathFolder);
    if (!is_null($update)) {
      $data['data'] = $update;
    }
  }

  $selectedFolders = $databaseController->updateSelectedFolders($_SESSION['idUser'], $oldpathFolder, $newPathFolder);

  if (!is_null($selectedFolders)) {
    $data['selectedFolders'] = $selectedFolders;
  }

  $databaseController->close();
}
echo json_encode($data);
