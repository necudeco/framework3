<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
chdir('../..');

require_once('app/config.php');

require_once('system/controller/App.php');

$app = new App();

?>
