<?php

declare(strict_types=1);

$response = ['data' => [], 'error' => null];

try {

  $json = json_decode(file_get_contents('php://input'));

  if (
    !property_exists($json, 'resPass') ||
    !property_exists($json, 'password') ||
    !property_exists($json, 'email')
  ) {
    throw new ErrorException('Не корректная структура данных');
  }

  if (!$json->resPass) {
    throw new ErrorException('Форма не действительна');
  }

  $email = $json->email;
  $password = $json->password;

  if (
    gettype($email) !== 'string' ||
    gettype($password) !== 'string' ||
    strlen($email) === 0 ||
    !preg_match('/(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}/', $password) ||
    strpos($email, '@') === false ||
    $email[strlen($email) - 1] === '@'
  ) {
    throw new ErrorException('Данные не корректны');
  }


  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $isReset = $database->resetPasswordUser($email, $password);

  if (!$isReset) {
    throw new ErrorException('Пользователь не найден');
  }

  $response['data'] = true;

  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
