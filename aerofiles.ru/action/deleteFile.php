<?php
declare(strict_types=1);

$STATUS_REQUEST = ['success'=>'0','error'=>'1'];

session_start();

if(!$_SESSION['login'] && !$_SESSION['idUser'] && !$_SESSION['path_user'] && !$_SESSION['path_storage']){
  exit($STATUS_REQUEST['error']);
}

$fileName = $_POST['filePath'];
$isFile = $_POST['isFile'];
$sizeFile = null;
$dataDeleted = null;
$data = ['status'=>$STATUS_REQUEST['error']];
$SelectedFolders = [];
$folders = [];

if(!$fileName){
  exit(json_encode($data));
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/explorer.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";

if($isFile === 'false'){
  $dataDeleted = $explorerController->deleteFolder($fileName);
  $SelectedFolders = $dataDeleted['folders'];
}
else{
  $dataDeleted = $explorerController->deleteFile($fileName);
}

if(!$dataDeleted['isDeleted']){
  $databaseController->close();
  exit(json_encode($data));
}

if(!empty($SelectedFolders)){
  foreach($SelectedFolders as $folder){
    $deleted = $databaseController->deleteSelectedFolder($_SESSION['idUser'], $folder);
    if($deleted){
      array_push($folders, $folder);
    }
  }
}

$databaseController->subSizeStorage($_SESSION['idUser'], $dataDeleted['sizeFile']);
$freeSizeInPercent = $databaseController->getFreeSizeStorageInPercent($_SESSION['idUser']);
$freeSize = $explorerController->formattedSize($databaseController->getFreeSizeStorage($_SESSION['idUser']));

$databaseController->close();

$data['freeSizePercent'] = $freeSizeInPercent;
$data['freeSize'] = $freeSize;
$data['status'] = $STATUS_REQUEST['success'];
$data['folders'] = $folders;

echo json_encode($data);

?>