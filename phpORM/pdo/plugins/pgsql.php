<?php

class ORMPDOPGSQL extends ORMPDO{
	
	protected $pdo;
	protected $driver;
	
	static $DRIVERS = array('pgsql');
	
	public function __construct(){
//		if ( ! in_array($driver , ORMPDO::$DRIVERS ) ) throw new Exception("DRIVER NOT FOUND");
		
		$this->driver = 'pgsql';
	}
	
	private function generateDSN($host,$dbname){
		$dsn = "";
		switch($this->driver){
			case 'pgsql':
				$dsn = "pgsql:host=$host;dbname=$dbname";
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
		//$sql = "SELECT * FROM information_schema.columns WHERE table_name = '$tablename' AND table_catalog = '$database'";
		
		$sql="SELECT column_name, column_default,
					(SELECT CASE WHEN pg_get_serial_sequence(table_name,column_name) is null THEN '' ELSE 'auto_increment'END) as extra,
 					is_nullable, data_type, character_maximum_length as CHARACTER_MAXIMUM_LENGTH,
					(select tc.constraint_type as COLUMN_KEY from INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc WHERE 
					tc.table_name = table_name AND tc.table_catalog = table_catalog and tc.constraint_name = column_name 
					limit 1 )
			 FROM information_schema.columns 
			 WHERE table_name = '$tablename' AND table_catalog = '$database'
			 order by ordinal_position asc;";
		
		$rs =  $this->Execute($sql);
		$meta = array();
		foreach( $rs as $c ){
			$aux = array();
			$aux['type'] = $c['data_type'];//ok
			$aux['primary_key'] = ( $c['column_key'] == 'PRIMARY KEY')?true:false;//OK
			$aux['auto_increment'] = ( $c['extra'] == 'auto_increment')?true:false;//ok
			$aux['name'] = $c['column_name']; //ok
			$aux['max_length'] = $c['character_maximum_length'];//ok
			
			if($aux['auto_increment']){
				$aux['has_default'] = false;
			}else{
				$aux['has_default'] = (is_null($c['column_default']))?false:true;				
			}
			$aux['default_value'] = $c['column_default'];

			//$aux['has_default'] = ( is_null($c['COLUMN_DEFAULT']) )?false:true;
			//$aux['default_value'] = $c['COLUMN_DEFAULT'];
						
			$meta[$c['column_name']] = $aux;
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
		return $this->pdo->lastInsertId($idColumn);

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
		$sql .= "  limit $i offset $offset "; 
		
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
