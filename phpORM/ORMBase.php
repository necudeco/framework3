<?php
/*
 *      ORMBase.php
 *      
 *      Copyright 2008 necudeco <necudeco@arthas>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software;

 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */

// Version 	3.0.0
// Fecha:	10/04/2011
// Author:	necudeco@gmail.com




include_once("ORMConnection.php");
include_once("ORMMetaData.php");
include_once("ORMCollection.php");
include_once("ORMException.php");


global $phpORM_debug;


/**
* ORMBase
* Clase Abstracta que implementa el acceso a los campos de un
* registro de una tabla en particular
*/
abstract class ORMBase implements Iterator, Countable 
{
	/**
	* Nombre de la tabla que sera controlada por esta clase.
	*/
	protected $tablename;
	
	/**
	* Campos obtenidos por defecto de la base de datos
	* Caracteristica aun no implementada.
	* NO MODIFICAR
	*/
	protected $selectFields = "*";
	
        //protected $searchFields = array();
	
	protected $fields;
	protected $primarykeys;	
	protected $metainfo;
	
	protected $className;
	
	protected $bModified;
	protected $bNull;

	protected $sql = null;

	static public $searchFields = null;

	protected $hasone=array();
	protected $hasmany=array();
	
	// Se encarga de almacenar temporalmente los resultados de las peticiones hasone y hasmany
	private $cache = array();

	// Establece las rutas a concatenar cuando se serializa el objeto
	protected $follow = array();

	// me permite definir nombres de campos equivalentes en las tablas
	// Solo se usa en caso de ser necesario cuando tenemos hasone o hasmany
	protected $translator = null;
	
	private $__friends = array("ORMCollection","ORMBase");
	 
	protected $autoreplaceNull = false;

	protected $exceptions = true;
	
	public function rewind() { reset($this->fields); }
	public function current() { return current($this->fields); }
	public function key() { return key($this->fields); }
	public function next() { return next($this->fields); }
	public function valid() { return ($this->current() !== false ); }
	
	/**
	* Devuelve el nombre de la tabla que maneja este objeto.
	*/
	public function getTableName() {return $this->tablename; }
	
	public function count(){
		return 1;	
	}


	/*
		Esta funcion se encarga de obtener informacion acerca de la 
		estructura de la tabla. Es llamada automaticamente por el 
		constructor
	*/

	public function _getMetadata()
	{	
		// Verificamos si ya se ha leido la metadata anteriormente
		if ( $this->metainfo !== null ) 
		{
			$response['fields'] = $this->fields;
			$response['primarykeys'] = $this->primarykeys;
			$response['metainfo'] = $this->metainfo;
		}else{
		
			// Sino, verificamos si se ha creado un archivo de configuracion de metadata
			$filename = "models/metadata/$this->tablename.php";
			if (file_exists($filename) )
			{
				include($filename);

				return $metadata;
			}
			
			// Como ultima opcion se accede a la base de datos para conseguirla.
			$conn = ORMConnection::getConnection(); 
			$rs = $conn->MetaColumns($this->tablename,False);

			foreach($rs as $item)
			{	
				$fields[$item['name']]=null;
				$metainfo[$item['name']]= (array)$item; //array("type"=>$item->type,"length"=>$item->max_length);
				if ( $item['primary_key'] === true )
				{
					$primarykeys[$item['name']] = ($item['auto_increment']===true)?-1:0;
				}
			}
			$response["fields"] = &$fields;
			$response["primarykeys"] = &$primarykeys;
			$response["metainfo"] = &$metainfo;
		}
		
		return $response;
	}
	 

/*
 * 
 * name: __construct
 * @param
 		$args:  * parametro recibido por find
 * @return:		* El objeto relacionado con un registro de la base de datos
 				* El objeto sin relacion con ningun registro de la base de datos
 */
	 

	/**
	*	Crea un objeto tipo ORMBase, obteniendo un registro de la base de datos
	*   cuyas claves PK coincidan con el argumento $args
	*   Internamente llama a $this->find
	*/
	public function __construct($args=null)
	{		
		$this->className = get_class($this);
		
		$metadata = ORMMetadata::getMetadata($this);
		$this->fields = $metadata["fields"];
		$this->primarykeys =  $metadata["primarykeys"];
		$this->metainfo = $metadata["metainfo"];

		if ( is_null($args) )
		 {	
			$this->bNull = True;
			return ;
		 }
		  
		$this->find($args);
	}

	//Me permite indentificar el tipo de dato de una propiedad
  public function type($property)
  {
	if ( ! isset($this->metainfo[$property])) return null;
	return $this->metainfo[$property]["type"];
  }


  public function length($property)
  {
	if ( ! isset($this->metainfo[$property])) return null;
	return $this->metainfo[$property]["length"];
  }

/*
 * 
 * name: find
 * @param :		* lista columnas PK y sus respectivos valores
 * @return		* El objeto relacionado con un registro de la base de datos
 */
	public function find($args)
	{	
		if ( $args == null ) throw new Exception("Se requiere un argumento para $this->className::find");
	
		if ( ! is_array($args) ){
			$_args = array();
			$pk = $this->getPK(); 
			if ( count($pk) != 1 ) throw new Exception("Se requiere un argumento tipo array para la clave primaria");
			
			$pk = array_keys($pk);
			$_args[$pk[0]] = $args;

			$args = $_args;
		}
	
		$sql = "select $this->selectFields from $this->tablename where ";
		$where = ""; 
		$whereargs = array(); 
		foreach ($this->primarykeys as $key=>$value)
		{
			if ( ! isset($args[$key]) )	
				 throw new ORMMissingPrimaryKey("Falta argumento '$key' en la clave primaria ($this->className)");
			 
			$whereargs["$key=?"] = $args[$key];
		 }

		$where = join(" and ",array_keys($whereargs));
		$sql = "$sql $where"; 

		$conn = ORMConnection::getConnection();
		  
		$rs = $conn->GetRow($sql,$whereargs); 
		if ( sizeof($rs) === 0 ) throw new ORMRecordNotFound("No existe el registro ($this->className)");
		
		$this->__setFields($rs);
		$this->bModified = false;
		$this->bNull = false;
	}

	
	public function getPK() { return $this->primarykeys; }
	public function getColumns() { return array_keys($this->fields); }


	// Se encarga de traducir el nombre de las propiedades en las relaciones HasOne y HasMany
	protected function translate($class,$key)
	{	
		if ( $this->translator === null ) return $key; 
		if ( isset($this->translator[$class]))
		{ 
			if (isset($this->translator[$class][$key] ))
			{	
				return $this->translator[$class][$key];
			}
		}
		
		return $key;
	}


	// Devuelve al attributo $attr, o llama a una funcion formateadora de existir
	private function getField($attr)
	{
		if ( method_exists($this, "_get_$attr") )
		{
			$method = "_get_$attr";
			return ORMConnection::unquote($this->$method());
		}else{
			return ORMConnection::unquote($this->fields[$attr]);
		}		
	}
		
	
	// Obtiene un objeto ORMBase a partir de una relacion hasOne
	private function getOne($attr)
	{	
		$className = $this->hasone[$attr];

		$filename = strtolower($className);
		if ( ! class_exists($className,false) )
			include_once("models/$filename.php");

		$aux = new $className();
		$pk = $aux->getPK(); 
		if ( $this->translator === null )
			$pk = $this->fields;
		else
		{
			foreach ($pk as $key => $value )
			{
				$keyname = $this->translate($attr,$key);
				$pk[$key]=$this->fields[$keyname]; 
			}
		}

        try{
            $aux->find($pk);
        }catch(Exception $e)
        {
            if ( $this->exceptions == true) throw $e;
            return null;
        }
		return $aux;
	
	}
	// Devuelve un objeto ORMCollection a partir de una relacion hasMay
	private function getMany($attr)
	{	
		$className = $this->hasmany[$attr];

		$filename = strtolower($className);
		if ( ! class_exists($className,false) )
			include_once("models/$filename.php");
		
	

		$aux = new $className();
		$pk = $this->getPK();
		$pk2 = array();
		$response =	$aux->getAll();
		foreach ($pk as $key => $value )
		{
			$key2 = $this->translate($className,$key);
			$pk2["$key2 ="]=$this->fields[$key];
			$response = $response->WhereAnd("$key2 = ",$this->fields[$key]);
		}
		
		return $response;		
	}


	public function __get($attr)
	{	

		if ( array_key_exists($attr,$this->fields)) // Buscamos la propiedad entre las columnas de la tabla
		{	
			$field = $this->getField($attr);
			return $field;
		}
		elseif ( array_key_exists($attr,$this->hasone) ) // Se debe devolver un objeto no un valor
		{
			if ( ! isset($cache["one"][$attr]) )
				$cache["one"][$attr] = $this->getOne($attr);
			return $cache["one"][$attr];
		}
		elseif (array_key_exists($attr,$this->hasmany))		//
		{
			if ( ! isset($cache["many"][$attr]) )
				$cache["many"][$attr] = $this->getMany($attr);
			return $cache["many"][$attr];
		}
		else
			throw new ORMPropertyNotValid("Get: Propiedad $attr No valida");
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function __setFields($fields = array(),$bNull = false)
	{	
		if ( ! is_array($fields) )
		{
			$this->clean();
			return;
		}
		foreach($this->fields as $key=>$value)
		{
			if ( array_key_exists($key,$fields) ){
				$this->fields[$key] = $fields[$key]; //ORMConnection::quote($fields[$key]);
			}
		}	
		$this->bNull = $bNull;
		$this->bModified = true;
	}

	public function setFields($fields = array(),$bNull = true)
	{	
		if ( ! is_array($fields) )
		{
			$this->clean();
			return;
		}
		foreach($this->fields as $key=>$value)
		{
			if ( array_key_exists($key,$fields) ){
				$this->fields[$key] = ORMConnection::quote($fields[$key]); //ORMConnection::quote($fields[$key]);
			}
		}
		$this->bModified = true;	
	}
	 
	public function __set($attr,$value)
	{
		if ( array_key_exists($attr,$this->fields))
		{
			// ponemos el valor en el formato especificado por la base de datos
			
			if ( array_key_exists($attr, $this->metainfo))
			{
				$formatfunction = "__format".$this->metainfo[$attr]["type"];
				if ( method_exists($this, $formatfunction) ) 
					$value = $this->$formatfuncion(ORMConnection::quote($value),$this->metainfo[$attr]);
			}
			
			if ( method_exists($this,"_set_$attr"))
			{
				$method = "_set_$attr";
				$this->$method(ORMConnection::quote($value));
			}else
			{
				$this->fields[$attr] = ORMConnection::quote($value);
				
			}
			$this->bModified = true;
		}
		else
			throw new ORMPropertyNotValid("Set: Propiedad $attr No Valida");
	}
	

	public function save(){
		if ( $this->bNull ) $this->create();
		else $this->update();
	}


	public function update()
	{
		if ( $this->bNull ) throw new ORMNullObject("No se puede actualizar un objeto inexistente");
		 
		if ( $this->bModified )
		{	
			
			$sql = "update $this->tablename set ";
			$set = "";
			$setargs = array();
			$where = ""; 
			$whereargs = array(); 
			foreach($this->fields as $key => $value )
			{ 
				if ( ! array_key_exists($key,$this->primarykeys) )
				{ 
//					$set = "$set $key=?,";
/*					if ( $this->metainfo[$key]["type"] == "varchar" or $this->metainfo[$key]["type"] == "int" )
					{ 
						if ( ! is_null($value) )
							$value = substr($value,0,$this->metainfo[$key]["max_length"]);
					}*/

					$setargs["$key=?"] = $value;
				}else{
//					$where = "$where $key=? and";
					$whereargs["$key=?"]= $value;	
				}
			}

			$where = join(" and " , array_keys($whereargs));
			$set   = join(", " , array_keys($setargs));
			$sql = "$sql $set where $where";

			$args = array_merge($setargs,$whereargs);
			$conn = ORMConnection::getConnection();  

			$conn->Execute($sql,$args); 
			$this->bModified = false;
		}
	}
	
	// Devuelve true en caso que el objeto actual este registrado en la base de datos
	public function exists() { return ! $this->bNull; } 


	// registra al objeto actual en la base de datos
	public function create($force=false)
	{	
	  if ( $force == false )
		if ( ! $this->bNull ) throw new ORMNotNullObject("No se puede crear un objeto ya existente");
		
		$className = get_class($this);
	
		// Verificamos que esten disponibles todos los pk
		$autoinc = null;
		foreach( $this->primarykeys as $key => $value )
		{
			if ( $value === -1 ) // es autonumerico
			{
			  $autoinc = $key;
			  continue;
			 }
			if ( is_null($this->fields[$key])) 
			{
				throw new ORMMissingPrimaryKey("Create: Falta clave primara $key");
			}
		}


		// Seteamos los valores

		$keys = "";
		$values = "";
		$valuesargs = array(); 
		foreach ($this->fields as $key=>$value)
		{
			if ( $key === $autoinc ) continue;
			
			if ( $value === null )
			{	
				// Verificamos si tiene un valor por defecto en la DB
				if ( $this->metainfo[$key]['has_default'] == true )
				{
					$value = $this->metainfo[$key]['default_value'];
					$this->$key = $this->metainfo[$key]['default_value'];
				}
			}

//			$keys = "$keys $key,";
//			$values = "$values ?,"; 
			/*
			if ( $this->metainfo[$key]["type"] == "varchar" or $this->metainfo[$key]["type"] == "int" ) 
			{
				if ( ! is_null($value) )
				$value = substr($value,0,$this->metainfo[$key]["max_length"]);
			}*/
			
			//$valuesargs[] = ($this->autoreplaceNull and is_null($value))?" ":$value;
			$valuesargs[$key] = $value;
		}
			
//		$keys = substr($keys,0,strlen($keys)-1);
//		$values = substr($values,0,strlen($values)-1);
	
		$keys = join(',', array_keys($valuesargs));
		$values = join (',', array_fill(0,count($valuesargs),'?') );
		
		$sql = "insert into $this->tablename ($keys) values ( $values ) "; 

		$conn = ORMConnection::getConnection();

//        $this->sql['sql'] =  $sql;
//        $this->sql['values'] = $valuesargs;
    
		$rs = $conn->Execute($sql,$valuesargs); // Donde se llama a addslahes
		
		$this->bNull = false;
		$this->bModified = false;
		if ( is_null($autoinc) ) // NO se genero autoincremento
		{
			
		}else{
			$this->fields[$autoinc]=$conn->Insert_ID($autoinc);
		}		
	}
	 
	public function clean()
	{
		$this->_getMetadata();
		$this->bNull = true;
	}
	 
	
	public function _getAll()
	{
		$sql = "from $this->tablename ";
		$coll = new ORMCollection($sql,$this);
		
		return $coll;
	}
	
  	// Se encarga de borrar los objetos de la base de datos
  	public function delete()
  	{
  		if ( $this->bNull === false )
  		{
  			
//  			$where = "";
  			$whereargs = array();
  			foreach ($this->primarykeys as $key=>$value)
  			{
//  				$where = "$where $key=? and";
  				$whereargs["$key =?"] = $this->fields[$key];
  			}
//  			$where = substr($where,0,strlen($where)-3);
			$where = join(" and ", array_keys($whereargs));
  			$sql = "delete from $this->tablename where $where";

  			$conn = ORMConnection::getConnection();
  			$conn->Execute($sql,$whereargs);
  		}
  	}

	public function toJSON()
	{
		return json_encode($this->fields);
	}

        
        public function activateFollow($follow)
        {
            if ( is_array($follow) )
                $this->follow = $follow;
        }

	public function toXML($depth=0)
	{
		$dom = new DOMDocument('1.0');
		
		$o = $dom->createElement("ormbase");
		$o->setAttribute("name",get_class($this));
		$dom->appendChild($o);
		
		foreach ( $this->fields as $k=>$f )
		{
			$att = $dom->createElement("attribute");
			$att->setAttribute("name",$k);
			$att->setAttribute("value",$f);
			
			$o->appendChild($att);
		}
		
                foreach ( $this->follow as $fkey => $fvalue)
		{   
                    if ( is_array($fvalue))
                    {
                        if ( array_key_exists($fkey,$this->hasmany) )
                        {
                            $aux = $this->followMany($fkey,$dom);
                            $o->appendChild($aux);
                        }elseif ( array_key_exists($fkey,$this->hasone) )
                            {
                                $aux = $this->followOne($fkey,$dom);
                                $o->appendChild($aux);
                            }
                    }else{
                        if ( array_key_exists($fvalue,$this->hasmany))
                        {  
                            $aux = $this->followMany($fvalue,$dom);
                            $o->appendChild($aux);
                        }elseif ( array_key_exists($fvalue,$this->hasone) )
                            {
                                $aux = $this->followOne($fvalue,$dom);
                                $o->appendChild($aux);
                            }
                    }

		}
		
		return $dom;
	}

        private function followMany($attr,$dom)
        {
        		$aux = $dom->createElement("attribute");
        		$aux->setAttribute("name",$attr);
        		
        		$xml = $this->$attr;
        		$xml->activateFollow($this->follow[$attr]);
        		$xml = $xml->toXML();
        		$xml = $xml->getElementsbyTagName("ormcollection")->item(0);
        		$xml = $dom->importNode($xml,true);
        		
        		$aux->appendChild($xml);
        		
        		return $aux;
        	
        }

      private function followOne($attr,$dom)
      {
          $aux = $dom->createElement("attribute");
          $aux->setAttribute("name",$attr);


          $hm = $this->$attr;

          if ( is_a($hm,'ORMBase'))
          {
              if ( is_array(@$this->follow[$attr]) )
                $hm->activateFollow($this->follow[$attr]);

              $_dom = $hm->toXML();
              $_dom = $_dom->getElementsbyTagName("ormbase")->item(0);
              $_dom = $dom->importNode($_dom,true);
              $aux->appendChild($_dom);

          }

          return $aux;
      }

    public function toArray() { return $this->fields; }

  
    public function __toSleep()
    {
		function stripslashes_deep($value)
		{
			    $value = is_array($value) ?
            	    array_map('stripslashes_deep', $value) :
            	    stripslashes($value);

			    return $value;
		}

		return $menu = stripslashes_deep($this->fields);	
    	
    }
}


?>
