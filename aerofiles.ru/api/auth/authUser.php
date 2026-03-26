<?php

declare(strict_types=1);

$response = ['data' => [], 'error' => null];

try {

  $json = json_decode(file_get_contents('php://input'));

  if (!property_exists($json, 'auth') || !property_exists($json, 'login') || !property_exists($json, 'password')) {
    throw new ErrorException('Не корректная структура данных');
  }

  if (!$json->auth) {
    throw new ErrorException('Форма не действительна');
  }

  $login = $json->login;
  $pass = $json->password;

  if (
    gettype($login) !== 'string' ||
    gettype($pass) !== 'string' ||
    strlen($login) == 0 ||
    strlen($pass) == 0
  ) {
    throw new ErrorException('Форма имеет пустые поля');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $user = $database->authUser($login, $pass);

  $database->close();

  session_start();

  $_SESSION['idUser'] = $user['id_user'];
  $_SESSION['login'] = $user['login'];
  $_SESSION['isAdmin'] = $user['isAdmin'];
  $_SESSION['pathStorage'] = $_SERVER['DOCUMENT_ROOT'] . "/assets/storages/" . $user['login'];
  $_SESSION['pathUser'] = '/';

  $response['data'] = true;

  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
