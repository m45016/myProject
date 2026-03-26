<?php

declare(strict_types=1);

$response = ['data' => [], 'error' => null];

try {

  $json = json_decode(file_get_contents('php://input'));

  if (
    !property_exists($json, 'reg') ||
    !property_exists($json, 'login') ||
    !property_exists($json, 'pass') ||
    !property_exists($json, 'rep_pass') ||
    !property_exists($json, 'email')
  ) {
    throw new ErrorException('Не корректная структура данных');
  }

  if (!$json->reg) {
    throw new ErrorException('Форма не действительна');
  }

  $login = $json->login;
  $pass = $json->pass;
  $rep_pass = $json->rep_pass;
  $email = $json->email;

  if (
    gettype($login) !== 'string' ||
    gettype($pass) !== 'string' ||
    gettype($rep_pass) !== 'string' ||
    gettype($email) !== 'string' ||
    $pass !== $rep_pass ||
    strlen($login) < 2 ||
    !preg_match('/(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}/', $pass) ||
    strlen($rep_pass) == 0 ||
    strlen($email) == 0 ||
    strpos($email, '@') === false ||
    $email[strlen($email) - 1] === '@'
  ) {
    throw new ErrorException('Данные не корректны');
  }

  require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $explorer = new ExplorerController('/', "{$_SERVER['DOCUMENT_ROOT']}/assets/storages");
  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $result = $database->regUser($login, $pass, $email);

  $explorer->createStorage($login);

  $database->close();

  $response['data'] = true;

  echo json_encode($response);
} catch (Exception $e) {
  $error = $e->getMessage();
  if (isset($login) && strpos($error, $login)) {
    $response['error'] = "Такой пользователь уже существует.\nИзмените логин!";
  } else if (isset($email) && strpos($error, $email)) {
    $response['error'] = "Такой пользователь уже существует.\nИзмените email!";
  } else {
    $response['error'] = $error;
  }

  echo json_encode($response);
}
