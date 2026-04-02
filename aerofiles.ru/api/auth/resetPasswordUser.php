<?php

declare(strict_types=1);

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'resPass' => (object)['type' => 'boolean', 'enum' => [true]],
    'password' => (object)['type' => 'string', 'minLength' => 8, 'pattern' => '^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}$'],
    'email'=>(object)['type'=>'string', 'format'=>'email']
  ],
  'required' => ['resPass', 'password','email'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data' => [], 'error' => null];

try {

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $email = $json->email;
  $password = $json->password;

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $isReset = $database->resetPasswordUser($email, $password);

  if (!$isReset) {
    throw new ErrorException('Пользователь не найден');
  }

  $response['data'] = true;

  echo json_encode($response);
} catch (InvalidValue $e){
  $response['error']='Данные формы не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
