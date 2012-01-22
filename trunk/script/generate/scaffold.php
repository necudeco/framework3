<?php
// Script encargado de generar el Form base


$moduleName = $argv[1];
$modelName = $argv[2];
$tableName = $argv[3];

unset($argv[3]);
unset($argv[2]);
unset($argv[1]);
unset($argv[0]);

$fields = "";

foreach ( $argv as $a){
	$fields .= " $a ";
}

exec("php script/generate/model.php $modelName $tableName");
exec("php script/generate/controller.php $moduleName $modelName");
exec("php script/generate/form.php $moduleName $modelName $fields");
exec("php script/generate/list.php $moduleName $modelName $fields");


?>
