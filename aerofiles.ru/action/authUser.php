<?php
declare(strict_types=1);

$STATUS_REQUEST = ['success'=>'0','userError'=>'1','passwordError'=>'2','dataError'=>'3'];

session_start();

if(empty($_POST['auth'])){

  exit($STATUS_REQUEST['dataError']);

}

$login = $_POST['login'];
$pass = $_POST['password'];

if(strlen($login) == 0 && strlen($pass) == 0){

  exit($STATUS_REQUEST['dataError']);

}

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";

$user = $databaseController->authUser($login, $pass);

$databaseController->close();

if(is_null($user['data'])){
  exit($STATUS_REQUEST['userError']);

}
else if(!empty($user['error'])){
  exit($STATUS_REQUEST['passwordError']);
}

$_SESSION['idUser'] = $user['data']['id_user'];
$_SESSION['login'] = $user['data']['login'];
$_SESSION['isAdmin'] = $user['data']['isAdmin'];
$_SESSION['path_storage'] = $_SERVER['DOCUMENT_ROOT'] . "/assets/storages/" . $user['data']['login'];
$_SESSION['path_user'] = '/';

echo $STATUS_REQUEST['success'];

?>