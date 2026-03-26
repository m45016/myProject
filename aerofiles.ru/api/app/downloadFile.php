<?php

declare(strict_types=1);
session_start();

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  if (!isset($_POST['fileName']) && !isset($_POST['t'])) {

    $json = json_decode(file_get_contents('php://input'));

    if (!property_exists($json, 'fileName')) {
      throw new ErrorException('Не корректная структура данных');
    }

    $fileName = $json->fileName;
    $token = $json->t ?? null;

    if (
      gettype($fileName) !== 'string' ||
      $fileName === ''
    ) {
      throw new ErrorException('Данные не корректные');
    }

    require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

    $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

    if ($token === null) {
      $token = $explorer->downloadFile($fileName);
      $response['data'] = $token;
      exit(json_encode($response));
    }
  } else {

    $fileName = $_POST['fileName'];
    $token = $_POST['t'] ?? null;

    $response['debug'] = [$fileName, $token];

    if (
      gettype($fileName) !== 'string' ||
      $fileName === ''
    ) {
      throw new ErrorException('Данные не корректные');
    }

    require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

    $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

    $explorer->downloadFile($fileName);

  }
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
