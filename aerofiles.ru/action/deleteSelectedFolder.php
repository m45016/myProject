<?php

declare(strict_types=1);
$STATUS_REQUEST = ['success'=>'0','error'=>'1'];

session_start();

if(!$_SESSION['login'] && !$_SESSION['idUser'] && !$_SESSION['path_user'] && !$_SESSION['path_storage']){
  exit("Сессия недоступна");
}

if($_POST['nameFolder'] === '' || $_POST['pathFolder'] === ''){
  exit($STATUS_REQUEST['error']);
}

$nameFolder = $_POST['nameFolder'];
$pathFolder = $_POST['pathFolder'];

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";

$isDeleted = $databaseController->deleteSelectedFolder($_SESSION['idUser'], $pathFolder);

$databaseController->close();
if(!$isDeleted){
  exit($STATUS_REQUEST['error']);
}


echo $STATUS_REQUEST['success'];

?>