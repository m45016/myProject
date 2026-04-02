<?php

declare(strict_types=1);

session_start();

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'nameFolder'=>(object)['type'=>'string'],
    'pathFolder'=>(object)['type'=>'string']
  ],
  'required' => ['nameFolder','pathFolder'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data'=>[],'error'=>null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $nameFolder = trim($json->nameFolder);
  $pathFolder = trim($json->pathFolder);

  if (strlen($nameFolder) === 0 || strlen($pathFolder) === 0) {
    throw new ErrorException('Данные состоят только из пробелов');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $isAdd = $database->addSelectedFolder($_SESSION['idUser'], $nameFolder, $pathFolder);

  $database->close();

  if (!$isAdd) {
    throw new ErrorException('Не удалось добавить папку в избранное');
  }

  $response['data'] = true;

  echo json_encode($response);
} catch (InvalidValue $e){
  $response['error']='Данные не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error']=$e->getMessage();
  echo json_encode($response);
}
