<?php

declare(strict_types=1);
session_start();
require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";

use Swaggest\JsonSchema\Exception\Error;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'balance' => (object)['type' => 'number', 'min'=>10, 'max'=>30000],
  ],
  'required' => ['balance'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data'=>[],'error'=>null];

try{

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $balance = $json->balance;

  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/paymentServiceController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);
  $paymentService = new PaymentServiceController();

  $isSuccess = $paymentService->sendPayment([
    'amount'=>$balance,
    'currency'=>'RUB',
    'description'=>'Пополнение баланса'
  ]);

  if(!$isSuccess){
    throw new ErrorException('Отказ в платеже');
  }

  $balanceInfo = $database->addBalance($_SESSION['idUser'], $balance);

  if(!$balanceInfo['isSuccess']){
    throw new ErrorException('Баланс не пополнен');
  }

  $balance = $balanceInfo['balance'];

  $response['data']['success'] = true;
  $response['data']['balance'] = $balance;
  echo json_encode($response);
}
catch(InvalidValue $e){
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
}
catch(Exception $e){
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}

?>