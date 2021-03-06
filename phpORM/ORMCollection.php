<?php

/*
 *  Esta clase contiene una lista de objetos ORMBase
 *  obtenidos generalmente por medio de getAll.
 *  Sin embargo los objetos no son obtenidos de la base de datos,
 *  sino hasta que realmente son requeridos
 * */

 include_once("ORMCondition.php");
 
class ORMCollection implements  Iterator, SeekableIterator, Countable
{
	
	public static $OFFSET=null;
	
	protected $data=array();
	private $begin = null;
	private $offset = 50;
	
	private $pointer = 0;
	
	protected $whereCondition = null;
	protected $params = array();
	
	public function get($i)
	{	
		$this->count();
		$offset = (is_null(self::$OFFSET))?$this->offset:self::$OFFSET;
		if ( $offset < 1 ) $offset = 1;

		if ( $this->begin === null  || $i >= $this->begin+$offset )
		{	
			$conn = ORMConnection::getConnection();
			$this->generateSQL();
			$rs = $conn->SelectLimit($this->sql,$offset,$i,$this->params);
			$this->data = array(); 
			foreach($rs as $item)
			{ 
				$this->data[] = $item;
			}
			
			$this->begin = $i;
		}
		
		$obj = new $this->class();
		if ( count($this->data) < 2 )
		{
			if ( count($this->data) > 0 )
				$obj->__setFields($this->data[0], false);
		}else
		{
			$obj->__setFields($this->data[$i-$this->begin], false);
		}
		
		return $obj;
	}
	

	
	public function __construct($class)
	{
		$this->class = $class;
		$obj = new $class();
		$this->tablename = $obj->getTableName();
		$this->sql = null;
	}
	
	public function WhereAnd($property,$value=null)
	{
		if ( $this->whereCondition == null ) // No existe condiciones creadas
		{ 
			$this->WhereCondition($property,$value);
		}else{ // Agregar condiciones con And
		 
			$this->whereCondition = $this->whereCondition->andCondition($property,$value);
		}
		return $this;
	}
	
	public function WhereOr($property,$value=null)
	{
		if ( $this->whereCondition == null ) // No existe condiciones creadas
		{ 
			$this->WhereCondition($property,$value);
		}else{ // Agregar condiciones con And
		 
			$this->whereCondition = $this->whereCondition->orCondition($property,$value);
		}
		return $this;
	}
	
	public function addJoin($className){
	
	}
	
	public function Orderby($property,$asc=null)
	{
		if ( is_array($property) )
			foreach($property as $key => $value )
				$this->orderby[$key]=$value;	
		else
			$this->orderby[$property] = $asc;

		$this->sql = null;
		return $this;
	}
	
	public function GroupBy($property)
	{
		if ( is_array($property) )
			foreach($property as $key )
				$this->groupby[] = $key;	
		else
			$this->groupby[] = $property;

		$this->sql = null;
		
		return $this;
	}
	
	public function AddSelect($property)
	{
		
	}
	
	private function generateSQL()
	{
		if ( $this->sql != null ) return $this->sql;
	
		$groupby = "";
		if ( isset( $this->groupby ) )
		{
			$groupby = array();
			foreach($this->groupby as $group)
			{	$groupby[] = $group; }
		
			$groupby = " GROUP BY ".join(",", $groupby);
		}
		
		$orderby = "";
		if ( isset($this->orderby) )
		{
			$orderby = array();
			foreach($this->orderby as $key=>$value)
				$orderby[$key] = ($value==true)?" $key ASC " :" $key DESC ";
			
			$orderby = "ORDER BY ".join(",",$orderby);
		}
		
		$this->params = array();
		
		$where = $this->_whereCondition();
		
		$sql = " $this->tablename $where $orderby $groupby";
		$this->from = $sql;
		$this->sql = "SELECT * from $this->tablename $where $orderby $groupby";
		$this->countsql = "SELECT count(*) from $this->tablename $where $orderby $groupby"; 
		return $sql;
	}
	
	public function rewind() { $this->pointer = 0; }
	public function current() { return $this->get($this->pointer); }
	public function key() { return $this->pointer; }
	public function next() { $this->pointer++; }
	public function valid() { return ($this->pointer < $this->count() ); }
	public function seek($pos) { $this->pointer = ($this->count() > $pos)?$pos:$this->count() - 1; }
	
	public function count() 
	{ 
		if ( ! isset($this->count) )
		{
			$this->generateSQL();
			$conn = ORMConnection::getConnection();

		
			$this->count = (int)$conn->GetOne($this->countsql,$this->params);
		}
		return $this->count;
	}
	
	
	// Borra todos los elementos de la collecions
	public function delete()
	{ 
		$sql = "delete from $this->tablename  ".$this->_whereCondition();

		ORMConnection::Execute($sql,$this->params);
	}
	
	public function getArray($i=null,$level=0)
	{		
		if ( $i == null ) $i = $this->count();
		$i = (int) $i;
		if ( $i < 0 ) $i = 0;
		if ( $i > $this->count() ) $i = $this->count();

		$conn = ORMConnection::getConnection();
		$this->generateSQL();
		$rs = $conn->SelectLimit($this->sql,$i,$this->pointer,$this->params);
		$response = array();
		foreach($rs as $item)
		{
			$response[] = $item;
		}
		
		return $response;
		
	}

	public function toArray($i = null){
		if ( is_null($i) ) $i = $this->count();
		if ( $i <= 0) return json_encode(array());
		if ( $i > $this->count()) $i = $this->count();

		$conn = ORMConnection::getConnection();
		$this->generateSQL();
		$rs = $conn->SelectLimit($this->sql, $i, $this->pointer, $this->params);
		
		$response = array();
		foreach($rs as $item)
		{
			$response[] = $item;
		}
		return $response;
	}

	public function toJSON($i=null)
	{
		$response = $this->toArray();
		return json_encode($response);

	}

	// ORMCollection::update
	// Permite hacer actualizaciones a distintos campos de nuestra tabla
	// para todos los objetos seleccionados en el ORMCollection
	public function update($params)
	{
		if ( ! is_array($params) )
		{
				throw new Exception("ORMCollection::update esperaba un array");
		}
		
		foreach( $params as $key => $value)
		{

		}
	}

	
	public function activateFollow($follow)
	{
		if ( is_array($follow) )
			$this->follow = $follow;
		
	}
	
	public function toXML()
	{
		$dom = new DOMDocument('1.0');
		$col = $dom->createElement('ormcollection');
		$dom->appendChild($col);
	
		foreach( $this as $obj )
		{
		
			$obj->activateFollow($this->follow);
			$xml = $obj->toXML();
         
			$xml = $xml->getElementsbyTagName("ormbase")->item(0);
			$xml = $dom->importNode($xml,true);
			$col->appendChild($xml);			
		}
		
		return $dom;
	}	



	public function WhereCondition($key,$value=null)
	{
		if (! is_a($key ,"ORMCondition") )
		{
			$condition = new ORMCondition($key,$value);
		}else
			$condition = $key;
			
		$this->whereCondition = $condition;
		
		return $this;
	}
	
	
	private function _whereCondition()
	{
		$cond = &$this->whereCondition;
		if ( $cond == null ) return "";
		if ( ! is_a($cond,"ORMCondition")) return "";
		
		$where = "WHERE ";
		$this->params = array ();
		$con = $this->_iterCondition($cond);
		
		return "WHERE $con";
	}
	
	
	private function _iterCondition($cond)
	{
		if ( is_a($cond,"ORMCondition")) return $this->_iterCondition($cond->conditions);

		if ( ! is_array($cond)) return "";

		$strcond = "";
		
		if ( ! array_key_exists("op",$cond) )
		{
			foreach( $cond as $c )
				$strcond .= $this->_iterCondition($c);
				
			return " ( $strcond ) ";
		}
		$op = $cond["op"]; // Operador
		if ( array_key_exists("cond",$cond))
			return "$op ".$this->_iterCondition($cond["cond"]);
		else{
			if ( is_array( $cond["value"] )) // Entonces pueden ser un between o un in
			{	
				$this->params = array_merge($this->params,$cond["value"]);
				list($field, $fop )=explode(" ",trim($cond["key"]),2);
				switch (strtolower(trim($fop)))
				{
					case "in":
							$cond["key"] .= " ( ".join(",",array_fill(0,count($cond["value"]),"?"))." ) "; 
							break;
							
					case "between":
							$cond["key"] .= " ? and ? ";
							break;
				}
				return " $op $cond[key] ";
			}else{
				$this->params[] = $cond["value"] ;
				return "$op $cond[key] ? ";
			}
		}
	}

	public function __toSleep()
	{
		$this->generateSQL();
		return ORMConnection::Execute($this->sql, $this->params);
	}
}

?>
