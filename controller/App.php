<?php

	include_once("system/libs/debug.php");

	global $config;

	$config = array();

	include_once("system/config.php");
	include_once('app/config.php');

	$domain = $_SERVER['HTTP_HOST'];
	@include("app/config.$domain.php");



function autoloadModels($className){
	$fileName = strtolower($className);
	$pathName = "app/models/$fileName.php";

	if ( file_exists($pathName) ){
		require_once($pathName);
		return true;
	}


	$fn_autload = spl_autoload_functions();
	$fn_autload[1]($className);
	return false;
}

	spl_autoload_register('autoloadModels', true);


	include_once("system/smarty/SmartyML.class.php");
	include_once("system/controller/ControllerBase.php");
	include_once("system/phpORM/ORMBase.php");

	error_reporting($config['error']);



class App{

	public function __construct($modulo=""){
		global $smarty;
		global $config;


		$path = getcwd()."/";
		//$args = App::breakURL();


		$smarty = new SmartyML($config['lang']);
		$smarty->allow_php_tag = true;
		$smarty->error_reporting = $config['error'];
		$smarty->compile_dir = "cache";
		$smarty->template_dir = "app/views";

		$smarty->addPluginsDir($config['root']."app". DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR );

		$smarty->assign("baseURL",$config['baseURL']);

require_once("app/controllers/public/route.php");

		Route::getController($_SERVER['REQUEST_URI']);

		return false;


	}

	static public function breakURL()
	{
		global $config;

		$_REQUEST = array_merge($_GET,$_POST);



		$baseURL = explode("/",$config['baseURL']);
		unset($baseURL[count($baseURL) -1 ]);
		unset($baseURL[0]);
		unset($baseURL[1]);
		unset($baseURL[2]);


		$params = @$_SERVER['REQUEST_URI'];
		$params = explode("?", $params);
		$params = $params[0];
		$params = explode("/",$params);
		if ( @$params[0] == "" ) array_shift($params);



		foreach( $baseURL as $i=>$l){
			//unset($params[$i]);
			array_shift($params);
		}

		$params = array_values($params);



		$response['params'] = $params;


		$response['serverIP'] = $_SERVER["SERVER_NAME"];
		$response['serverPort'] = $_SERVER["SERVER_PORT"];
		$response['serverProtocol'] = explode("/",$_SERVER["SERVER_PROTOCOL"]);
		$response['serverProtocol'] = $response['serverProtocol'][0];
		$response['clientIP'] = $_SERVER["REMOTE_ADDR"];

		$response['request_uri'] = $_SERVER["REQUEST_URI"];

		$pos = strpos($_SERVER['REQUEST_URI'], @$_SERVER['PATH_INFO'] );
		$pos = ( $pos == false )? strlen($_SERVER['REQUEST_URI']) : $pos;
		$response['baseURL'] = substr($_SERVER['REQUEST_URI'], 0 , $pos );
		$pos = strpos($response['baseURL'], "index.php" );
		$pos = ( $pos == false )?  strlen($response['baseURL']) : $pos;
		$response['baseURL'] = substr($response['baseURL'], 0 , $pos );

		$serverPort = ( $_SERVER['SERVER_PORT'] == "80" )?"":":$_SERVER[SERVER_PORT]";



		//$response['baseURL'] = $response['serverProtocol']."://".$_SERVER['SERVER_NAME']."$serverPort".$response['baseURL'];
		$response['baseURL'] = $config['baseURL'];
		$response['index'] = $config['index'];


		return $response;
	}
}
?>
