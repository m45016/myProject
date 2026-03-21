<?php

declare(strict_types=1);

$STATUS_REQUEST = ['success'=>'0', 'addError'=>'1'];

session_start();

if(!$_SESSION['login'] && !$_SESSION['idUser'] && !$_SESSION['path_user'] && !$_SESSION['path_storage']){
  exit("Сессия недоступна");
}

if(strlen($_POST['nameFolder'])===0 || strlen($_POST['pathFolder'])===0){
  exit($STATUS_REQUEST['addError']);
}

$nameFolder = trim($_POST['nameFolder']);
$pathFolder = $_POST['pathFolder'];

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";

$isAdd = $databaseController->addSelectedFolder($_SESSION['idUser'], $nameFolder, $pathFolder);

$databaseController->close();

if(!$isAdd){
  exit($STATUS_REQUEST['addError']);
}

echo $STATUS_REQUEST['success'];

?>