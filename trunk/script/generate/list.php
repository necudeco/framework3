<?php
// Script encargado de generar el Form base


$moduleName = $argv[1];
$modelName = $argv[2];

$filename = strtolower($modelName);
$controllerName = $filename;

unset($argv[0]);
unset($argv[1]);
unset($argv[2]);

$dataTypes = array('text', 'password', 'date');

$fields = array();
foreach ( $argv as $a){
	$aux = split(':', $a);
	$fieldName = $aux[0];
	$dataType = @$aux[1];
	if ( $dataType == "" ) $dataType = "text";
	if ( ! in_array($dataType, $dataTypes ) ) $dataType = 'text';
	$fields[$fieldName] = $dataType;
}

//include("app/models/$filename.php");

//$o = new $modelName();

include("script/models/list_datatable.php");

//echo $PHP;

exec("mkdir app/views/$moduleName/$filename -p");

file_put_contents("app/views/$moduleName/$filename/__list.html",$PHP);
?>
