<?php
declare(strict_types=1);
session_start();

if(!$_SESSION['login'] && !$_SESSION['idUser'] && !$_SESSION['path_user'] && !$_SESSION['path_storage']){
  exit("Сессия недоступна");
}

if(!isset($_POST['fileName'])){
  exit("Файл отсутствует");
}

$fileName = $_POST['fileName'];
$token = $_POST['t'] ?? null;

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/explorer.php";

if($token===null){
  $token = $explorerController->downloadFile($fileName);
  exit($token);
}

$explorerController->downloadFile($fileName);

?>