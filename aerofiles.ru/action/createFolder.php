<?php

declare(strict_types=1);
$STATUS_REQUEST = ['success'=>'0','createError'=>'2'];

session_start();

if(!$_SESSION['login'] && !$_SESSION['idUser'] && !$_SESSION['path_user'] && !$_SESSION['path_storage']){
  exit("Сессия недоступна");
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/explorer.php";

$dataFolder = $explorerController->createFolder();
$data = $dataFolder['data'];
$status = $dataFolder['status'];

$message = ['data'=>$data, 'status'=>null];

if(!$status){
  $message['status'] = $STATUS_REQUEST['createError'];
  exit(json_encode($message));
}

$message['status'] = $STATUS_REQUEST['success'];

echo json_encode($message);

?>