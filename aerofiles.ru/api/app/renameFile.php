<?php

declare(strict_types=1);

session_start();

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  if (
    !property_exists($json, 'oldFullName') ||
    !property_exists($json, 'newFullName') ||
    !property_exists($json, 'isFile') ||
    !property_exists($json, 'isSelectedFolder')
  ) {
    throw new ErrorException('Не корректная структура данных');
  }

  $oldName = $json->oldFullName;
  $newName = trim($json->newFullName);
  $isFile = $json->isFile;
  $isSelecetdFolder = $json->isSelectedFolder;

  if (
    gettype($oldName) !== 'string' ||
    gettype($newName) !== 'string' ||
    gettype($isFile) !== 'boolean' ||
    gettype($isSelecetdFolder) !== 'boolean' ||
    $oldName === '' || 
    $newName === '' 
  ) {
    throw new ErrorException('Не корректные данные');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $isRenamed = $explorer->renameFile($oldName, $newName);

  if (!$isRenamed) {
    throw new ErrorException('Не удалось переименовать файл или папку');
  }

  $response['data']['isRenamed'] = $isRenamed;

  if (!$isFile) {
    
    require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
    require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

    $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

    $oldpathFolder = "{$explorer->getPathUser()}{$oldName}/";
    $newPathFolder = "{$explorer->getPathUser()}{$newName}/";

    if ($isSelecetdFolder) {
      $update = $database->updateSelectedFolder($_SESSION['idUser'], $oldName, $newName, $oldpathFolder, $newPathFolder);
      if($update){
        $response['data']['isSelectedFolder'] = ['oldname'=>$oldName, 'newName'=>$newName, 'oldPath'=>$oldpathFolder, 'newPath'=>$newPathFolder];
      }
      else{
        $response['data']['isSelectedFolder'] = null;        
      }
    }

    $database->updatePathSelectedFolders($_SESSION['idUser'], $oldpathFolder, $newPathFolder);
    
    $selectedFolders = $database->getSelectedFolders($_SESSION['idUser']);

    if(!is_null($selectedFolders)){
      $response['data']['selectedFolders'] = $selectedFolders;      
    }
    else{
      $response['data']['selectedFolders'] = null;      
    }

    $database->close();
  }
  else{
    $response['data']['selectedFolders'] = null;
    $response['data']['isSelectedFolder'] = null;
  }
  
  echo json_encode($response);

} catch (Exception $e) {
  $response['error']=$e->getMessage();
  echo json_encode($response);
}