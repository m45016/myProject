<?php
// страница регистрации
declare(strict_types=1);
session_start();

header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-eval\'');

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";

require_once "{$_SERVER['DOCUMENT_ROOT']}/views/regView.php";

?>