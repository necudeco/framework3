<?php

/*
require_once('adodb/adodb.inc.php');
include_once('adodb/adodb-exceptions.inc.php');
//include_once('adodb/toexport.inc.php');
*/

require_once("pdo/pdo.php");

class ORMConnection{

	public static $debug = false;

	public static function getConnectionParamaters(){
		global $config;
		return $config['database'];
	}
	
	public static function debug($debug = true){
		ORMConnection::$debug = $debug;
	}

	public static function getConnection(){
			$config_db = ORMConnection::getConnectionParamaters(); 

			$conn = ORMPDO::create("mysql");  
			$conn->connect($config_db);

			if ( $conn == null)
			{
				throw new ORMConnectionError("Error de Conexion");
			}
			
			if ( $config_db['driver'] == 'mysqli' && @$config_db['charset'] != '' )
				$conn->Execute("SET NAMES ".$config_db["charset"]);
				
			//$conn->SetFetchMode(ADODB_FETCH_ASSOC);	
			$conn->debug = ORMConnection::$debug;
				
			return $conn;  
	}
	
	
	public static function Execute($sql, $args=array()){
	
		$conn = ORMConnection::getConnection();
		
		//$args = ORMConnection::quote($args);
		
		$rs =  $conn->Execute($sql,$args);
		$response = array();
		foreach( $rs as $item){
			$response[] = $item;
		}  
		return $response;
	}

	public static function getOne($sql, $args=array()){
		$conn = ORMConnection::getConnection();
		//$args = ORMConnection::quote($args);
		return $conn->getOne($sql,$args);
	}

	public static function getRow($sql, $args=array()){
		$conn = ORMConnection::getConnection();
		//$args = ORMConnection::quote($args);
		return $conn->getRow($sql,$args);
	}
	
	public static function quote($value){
		if ( is_array($value) ){
			foreach ($value as $k => $v) {
				$value[$k] = addslashes($v); 
			}
			
			return $value;
		}
		return	addslashes($value);
	}
	
	public static function unquote($value){
		if ( is_array($value) ){
			foreach ($value as $k => $v) {
				$value[$k] = stripslashes($v); 
			}
			
			return $value;
		}
		return stripslashes($value);
	}
	
	public static function emptyDatabase(){
	    $conn = ORMConnection::getConnection();
	    
	    $conn->emptyDatabase();
	}

}

?>