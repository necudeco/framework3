<?php

class ORMPDOMYSQL extends ORMPDO{
	
	protected $pdo;
	protected $driver;
	
	static $DRIVERS = array('mysql');
	
	public function __construct(){
//		if ( ! in_array($driver , ORMPDO::$DRIVERS ) ) throw new Exception("DRIVER NOT FOUND");
		
		$this->driver = 'mysql';
		
	}
	
	private function generateDSN($host,$dbname){
		$dsn = "";
		switch($this->driver){
			case 'mysql':
				$dsn = "mysql:host=$host;dbname=$dbname";
				break;
		}
		
		return $dsn;
	}
	
	public function connect($config){
		$this->config = $config;
		$this->pdo = null;
		$dsn = $this->generateDSN($config['server'], $config['database']);
		try {
			$this->pdo = new PDO($dsn, $config['user'], $config['password']);
		}catch(Exception $e){
			throw new Exception("CANNT CONNECT DB");
		}

	}


	public function MetaColumns($tablename){
		$database = $this->config['database'];
		$sql = "SELECT * FROM information_schema.columns WHERE table_name = '$tablename' AND table_schema = '$database'";
		
		$rs =  $this->Execute($sql);
		$meta = array();
		foreach( $rs as $c ){
			$aux = array();
			$aux['type'] = $c['DATA_TYPE'];
			$aux['primary_key'] = ( $c['COLUMN_KEY'] == 'PRI')?true:false;
			$aux['auto_increment'] = ( $c['EXTRA'] == 'auto_increment')?true:false;
			$aux['name'] = $c['COLUMN_NAME'];
			$aux['max_length'] = $c['CHARACTER_MAXIMUM_LENGTH'];
			$aux['has_default'] = ( is_null($c['COLUMN_DEFAULT']) )?false:true;
			$aux['default_value'] = $c['COLUMN_DEFAULT'];
			$meta[$c['COLUMN_NAME']] = $aux;
		}
		//$meta = $rs;
		
		return $meta;
	}
	
	private function __prepare($sql, $args=array()){
		$pos = 0;
		foreach( $args as $k => $arg ){

			$pos = strpos($sql, '?', $pos);

			$arg = "'$arg'";
			$sql = substr_replace($sql,$arg, $pos,1);
			$pos += strlen($arg);
		}
		

		
		return $sql;	
	
	}
	
	public function Insert_ID($idColumn){
		return $this->pdo->lastInsertId();
	}
	
	private function __execute($sql,$args=array()){
		if ( ORMConnection::$debug == true ){
			print("<br /> $sql <br />");
		}
		
		$rs = $this->pdo->prepare($sql);
		$rs->execute();
		if ( $rs === false ) throw new Exception($sql);
		$rs->setFetchMode(PDO::FETCH_ASSOC);
		return $rs;
	}
	
	public function Execute($sql,$args=array()){
		$sql = $this->__prepare($sql,$args);
		$rs = $this->__execute($sql);
		$response = array();
		foreach( $rs as $i){
			$response[] = $i;
		}
		//$rs->closeCursor();
		return $response;
	}
	
	public function GetRow($sql,$args=array()){
		$sql = $this->__prepare($sql,$args);
		$rs = $this->__execute($sql);
		$response = Null;
		foreach( $rs as $i ){
			$response =  $i;
		//	$rs->closeCursor();
			return $response;
		}
	}
	
	public function GetOne($sql,$args=array()){
		$sql = $this->__prepare($sql,$args);	
		$rs = $this->Execute($sql);
		$response = null;
		foreach ( $rs as $i )
			foreach ( $i as $j ){
				$response = $j;
		//		$rs->closeCursor();
				return $response;
			}
	}
	
	public function selectLimit($sql,$offset,$i,$params){
		$sql = $this->__prepare($sql,$params); 
		$sql .= "  limit $i,$offset "; 
		
		$rs = $this->__execute($sql);
		$response = array();
		foreach( $rs as $i){
			$response[] = $i;
		}
		//$rs->closeCursor();
		return $response;
	}

	public function emptyDatabase(){
		$sql = "show tables;";
		$tables = $this->__execute();
		foreach( $tables as $table ){
			$this->emptyTable($table);
		}
	}
	
	public function emptyTable($tablename){
		$sql = "truncate $tablename";
		
		$this->__execute($sql);
	}
	
}


?>
