<?php

declare(strict_types=1);
$STATUS_REQUEST = ['success' => '0', 'userError' => '1', 'emailError' => '2', 'dataError' => '3'];

if (empty($_POST['reg'])) {

  exit($STATUS_REQUEST['dataError']);
}

$login = $_POST['login'];
$pass = $_POST['password'];
$rep_pass = $_POST['rep_password'];
$email = $_POST['email'];


if (
  $pass !== $rep_pass ||
  strlen($login) < 2 ||
  !preg_match('/(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}/', $pass) ||
  strlen($rep_pass) == 0 || 
  strlen($email) == 0 ||
  strpos($email, '@') === false ||
  $email[strlen($email) - 1] === '@'
) {
  exit($STATUS_REQUEST['dataError']);
}


require_once "{$_SERVER['DOCUMENT_ROOT']}/model/ExplorerModel.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/controller/explorerController.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";

$expModel = new ExplorerModel("/", $_SERVER['DOCUMENT_ROOT'] . '/assets/storages');
$explorerController = new ExplorerController($expModel);

$result = $databaseController->regUser($login, $pass, $email);

if ($result !== true) {
  if (strpos($result, $login)) {
    $databaseController->close();
    exit($STATUS_REQUEST['userError']);
  } else if (strpos($result, $email)) {
    $databaseController->close();
    exit($STATUS_REQUEST['emailError']);
  }
}

$explorerController->createStorage($login);

$databaseController->close();

echo $STATUS_REQUEST['success'];
