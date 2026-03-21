<?php

declare(strict_types=1);

$STATUS_REQUEST = ['success' => '0', 'emailError' => '1', 'dataError' => '2'];

if (empty($_POST['resPass'])) {
  exit($STATUS_REQUEST['dataError']);
}

session_start();

$email = $_POST['email'];
$password = $_POST['password'];

if (
  strlen($email) === 0 ||
  !preg_match('/(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}/', $password) ||
  strpos($email, '@') === false ||
  $email[strlen($email) - 1] === '@'
) {
  exit($STATUS_REQUEST['dataError']);
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";

$isReset = $databaseController->resetPasswordUser($email, $password);

if (!$isReset) {
  exit($STATUS_REQUEST['emailError']);
}

echo $STATUS_REQUEST['success'];
