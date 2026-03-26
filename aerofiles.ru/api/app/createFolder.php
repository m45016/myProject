<?php

declare(strict_types=1);
session_start();

$response = ['data'=>[],'error'=>null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    exit("Сессия недоступна");
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";
  
  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $dataFolder = $explorer->createFolder();
  $data = $dataFolder['data'];
  $status = $dataFolder['status'];

  if (!$status) {
    throw new ErrorException('Не удалось создать папку');
  }

  $response['data'] = $data;
  echo json_encode($response);

} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
