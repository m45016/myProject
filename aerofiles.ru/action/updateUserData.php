<?php

$STATUS_REQUEST = ['success' => '0', 'adminError' => '1', 'updateError' => '2'];

session_start();

if (!$_SESSION['login'] && !$_SESSION['isAdmin']) {
  exit($STATUS_REQUEST['adminError']);
}

if($_POST['idUser']==='' || $_POST['maxStorage']==='' || $_POST['isAdmin']===''){
  exit($STATUS_REQUEST['updateError']);
}

$idUser = $_POST['idUser'];
$maxStorage = $_POST['maxStorage'];
$isAdmin = $_POST['isAdmin'];

if ($isAdmin === 'false') {
  $isAdmin = 0;
}
else{
  $isAdmin = 1;
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";

$isUpdate = $databaseController->updateUserData($idUser, $maxStorage, $isAdmin);

if (!$isUpdate) {
  $databaseController->close();
  exit($STATUS_REQUEST['updateError']);
}

$databaseController->close();

echo $STATUS_REQUEST['success'];
