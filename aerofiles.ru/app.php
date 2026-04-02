<?php
// Cтраница приложения

declare(strict_types=1);
session_start();

header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-eval\'');

if(!$_SESSION['login']){
  exit('Ошибка: Сессия не активна.');
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/views/appView.php";

?>