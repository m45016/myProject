<?php

declare (strict_types=1);
session_status();

$pathUser = $_SESSION['path_user'];
$pathStorage = $_SESSION['path_storage'];

require_once "{$_SERVER['DOCUMENT_ROOT']}/model/explorerModel.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/controller/explorercontroller.php";

$explorerModel = new ExplorerModel($pathUser, $pathStorage);
$explorerController =  new ExplorerController($explorerModel);

?>