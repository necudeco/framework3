<?php

class ORMPDO{
	
	protected $pdo;
	protected $driver;
	
	static $DRIVERS = array('mysql');
	
	public static function create($driver){
		if ( ! in_array($driver , ORMPDO::$DRIVERS ) ) throw new Exception("DRIVER NOT FOUND");
		
		//$this->driver = $driver;
		

		require_once("plugins/$driver.php");
		
		$driverClassName = "ORMPDO".strtoupper($driver);
		
		return new $driverClassName();	
	}
}


?>
