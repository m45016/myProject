<?php

// страница о приложении

declare(strict_types=1);
session_start();

header('Content-Security-Policy: default-src \'self\'');

require_once "{$_SERVER['DOCUMENT_ROOT']}/view/tutorialView.php";

?>