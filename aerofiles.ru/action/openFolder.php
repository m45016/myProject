<?php

declare(strict_types=1);
session_start();

if(!$_SESSION['login'] && !$_SESSION['idUser'] && !$_SESSION['path_user'] && !$_SESSION['path_storage']){
  exit("Сессия недоступна");
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/explorer.php";

if($_POST['path'] === ''){
  exit('Файл отсутсвует');
}

$path = $_POST['path'];

$data = null;

$pathStorage = $explorerController->getPathStorage();
$fullPathFolder = "{$pathStorage}{$path}";
$pathFolder = $path;

if (!is_dir($fullPathFolder)) {
  
  require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";
  
  $nameFolder = basename($path);

  $isSelected = $databaseController->isSelectedFolder($_SESSION['idUser'], $nameFolder, $pathFolder);
  $isDeleted = null;

  if($isSelected){
    $isDeleted = $databaseController->deleteSelectedFolder($_SESSION['idUser'], $pathFolder);
  }

  $data = [
    'isExists'=>false,
    'isSelected'=>$isSelected,
    'isDeleted'=>$isDeleted
  ];

  $databaseController->close();

  exit(json_encode($data));

}

$explorerController->openFolder($path);

$content = $explorerController->getFilesFromCurrentPath();

$elements = $content['elements'];
$countFiles = $content['length'];
$pathUser = $content['path'];


$emptyStorage = false;

if (empty($countFiles)) {
  $emptyStorage = true;
}

$data = [
  'elements' => $elements,
  'countFiles' => $countFiles,
  'pathUser' => $pathUser,
  'emptyStorage' => $emptyStorage,
  'isExists' => true
];

$_SESSION['path_user'] = $pathUser;

echo json_encode($data);
