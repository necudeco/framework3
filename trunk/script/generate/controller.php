<?php
// Script encargado de generar el Form base


$moduleName = $argv[1];
$modelName = $argv[2];

$urlname = strtolower($modelName);
$controllerName = "c$modelName";
$filename = strtolower($controllerName);

//include("app/models/$filename.php");

//$o = new $modelName();

include("script/models/controller.php");

//echo $PHP;

exec("mkdir -p app/controllers/$moduleName");

file_put_contents("app/controllers/$moduleName/$filename.php",$PHP);
?>
