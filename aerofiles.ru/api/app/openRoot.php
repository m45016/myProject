<?php

declare(strict_types=1);
require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";

$response = ['data'=>[], 'error'=>null];

try {
  session_start();

  if(!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])){
    throw new ErrorException('Сессия не активна');
  }

  $_SESSION['pathUser'] = '/';

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);
  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $content = $explorer->getFilesFromCurrentPath();

  $response['data']['elements'] = $content['elements'];
  $response['data']['countFiles'] = $content['length'];
  $response['data']['pathUser'] = $content['path'];

  $emptyStorage = false;

  if (empty($response['data']['countFiles'])) {
    $emptyStorage = true;
  }

  $response['data']['emptyStorage'] = $emptyStorage;

  $response['data']['selectedFolders'] = $database->getSelectedFolders($_SESSION['idUser']); 
  $response['data']['storageInfo'] = $database->getStorageInfo($_SESSION['idUser']);

  $response['data']['storageInfo']['freeSizeStorage'] = $explorer->shortSizeFile($response['data']['storageInfo']['freeSizeStorage']);
  $response['data']['storageInfo']['maxSizeStorage'] = $explorer->shortSizeFile($response['data']['storageInfo']['maxSizeStorage']);

  $database->close();

  echo json_encode($response);

} catch (Exception $e) {
  $response['error'] = "Ошибка: {$e->getMessage()}!";
  echo json_encode($response);
}

?>