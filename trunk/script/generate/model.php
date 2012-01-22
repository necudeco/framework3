<?php
// Script encargado de generar el Modelo base

//echo getcwd();

//print_r($argv);

$modelName = $argv[1];
$tablename = $argv[2];

$filename = strtolower($modelName);

include("script/models/model.php");

//echo $PHP;

exec("mkdir app/models/ -p");


file_put_contents("app/models/$filename.php",$PHP);
?>
