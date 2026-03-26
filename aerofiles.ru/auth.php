<?php
// страница аутентификации

declare(strict_types=1);
session_start();

header('Content-Security-Policy: default-src \'self\'');

require_once "{$_SERVER['DOCUMENT_ROOT']}/views/authView.php";

?>