<?php

require_once('app/config.php');

$domain = $_SERVER['HTTP_HOST'];
if ( file_exists("app/config.$domain.php") ){
	require("app/config.$domain.php");
}

global $config;

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

include_once("system/libs/debug.php");
include_once("system/smarty/SmartyML.class.php");
include_once("system/controller/ControllerBase.php");
include_once("system/phpORM/ORMBase.php");


$error = "stdout";
if ( array_key_exists('error',$config) ){
	$error = $config['error'];
}
ini_set('display_errors',$error);

ini_set('html_errors',"On");

ini_set('error_prepend_string','<div class="php_error" style="padding: 0 20px 20px;  border: 1px #FF00CC dotted;">');
ini_set('error_append_string','</div >');

class App{

	public function __construct($modulo=""){
		global $smarty;
		global $config;
		
		
		$path = getcwd()."/";		
		$args = App::breakURL();



		if ( ! is_writable("${path}cache") ){
			throw new Exception("Directorio ${path}cache no Existe o no tiene permisos de escritura");
		}
		
		//$config['lang'] = 'en';
		$smarty = new SmartyML($config['lang']);
		$smarty->allow_php_tag = true;
		$smarty->error_reporting = 2;
		$smarty->compile_dir = "cache";
		$smarty->template_dir = "app/views";
		
		//$smarty->addPluginsDir($config['root']."app". DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR );
		$smarty->addPluginsDir($config['root']."app". DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR );
		
		$smarty->assign("baseURL",$config['baseURL']);	
		
		$modulo = "";
		if ( count($args['params']) > 0 ){
			$modulo = $args['params'][0];
			unset($args['params'][0]);
		}
		try{
			
			$cindex_filename = "${path}app/controllers/${modulo}/cindex.php";
			 
			if (! file_exists($cindex_filename)){
				if ( $modulo != "" ) array_unshift($args['params'],$modulo);
				
				$modulo = $config['modules']['default'];
				$cindex_filename = "${path}app/controllers/${modulo}/cindex.php";
				if (! file_exists($cindex_filename)){
					throw new FException("NOT_MODULE");
				}	 
				
			}else{
				
			} 
			
			$args['module'] = $modulo;
			require_once($cindex_filename); // || die("MODULE $cindex_filename NOT EXISTS. Please Config your 404.html page");

			$_Controller = new cIndex($args); 
			$_Controller->Run();

		}catch(Exception $e){
			//die("PROBLEMA MAYOR ".$e->getMessage());
			throw $e;
		}
		
		 
	}
	
	static public function breakURL()
	{


		global $config;

		$_REQUEST = array_merge($_GET,$_POST);
	
		$params = @$_SERVER['PATH_INFO'];
		$params = explode("/",$params);
		if ( @$params[0] == "" ) array_shift($params);

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
