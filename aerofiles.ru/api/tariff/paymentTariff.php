<?php

declare(strict_types=1);
session_start();

$response = ['data'=>[],'error'=>null];

try{

  if(!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['tariff'])){
    throw new ErrorException('Сессия не активна');
  }

  if($_SESSION['tariff'] === 'free'){
    throw new ErrorException("Тариф бесплатный.\nОплата не требуется");
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/datetimeController.php";


  $database = new DataBaseController(DOMAIN,USER,PASSWORD,DB_NAME);
  $datetime = new DateTimeController();

  $isPayment = $datetime->isPaymentTariff($_SESSION['tariffValidTo']);

  if($isPayment){
    throw new ErrorException('Тариф уже оплачен');
  }

  $isSuccess = $database->paymentTariff($_SESSION['idUser'], $_SESSION['tariff']);

  if(!$isSuccess){
    throw new ErrorException("На балансе не достаточно средств.\nПополните баланс");
  }

  $tariffValidTo = $database->updateDatePaymentUser($_SESSION['idUser']);
  
  $datetime->setDateTime($tariffValidTo);
  $datetime->modify('+1 month');
  $_SESSION['tariffValidTo'] = $datetime->getDateTime();
  
  $response['data'] = true;
  echo json_encode($response);
}
catch(Exception $e){
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}






?>