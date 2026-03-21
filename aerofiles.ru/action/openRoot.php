<?

session_start();

$_SESSION['path_user'] = '/';

$data = [];

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/database.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/explorer.php";

$content = $explorerController->getFilesFromCurrentPath();

$data['elements'] = $content['elements'];
$data['countFiles'] = $content['length'];
$data['pathUser'] = $content['path'];

$emptyStorage = false;

if (empty($data['countFiles'])) {
  $emptyStorage = true;
}

$data['emptyStorage'] = $emptyStorage;

$data['selectedFolders'] = $databaseController->getSelectedFolders($_SESSION['idUser']);

$data['freeSizeInPercent'] = $databaseController->getFreeSizeStorageInPercent($_SESSION['idUser']); // заменить три запроса на один
$data['freeSize'] = $explorerController->formattedSize($databaseController->getFreeSizeStorage($_SESSION['idUser'])); 
$data['maxSize'] = $explorerController->formattedSize($databaseController->getMaxSizeStorage($_SESSION['idUser']));

$databaseController->close();

echo json_encode($data);


?>