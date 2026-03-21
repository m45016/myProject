<?php

declare(strict_types=1);

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/model/databaseModel.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/controller/databaseController.php";

$databaseModel = new DataBaseModel(DOMAIN, USER, PASSWORD, DB_NAME);
$databaseController = new DataBaseController($databaseModel);

?>