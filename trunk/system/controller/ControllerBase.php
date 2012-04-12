<?php

//Autor: Necudeco
//Email: necudeco@gmail.com
//Web  : http://www.ncdcsolutions.com
//Licencia: GPL3
//Version: 040309-01

global $path;

include_once("ACL.php");
include_once("system/libs/JSON.php");
include_once("system/libs/FException.php");

abstract class ControllerBase
{

	protected $defaultaction = 'index';
	protected $action = null;
	protected $template="index.html";
	
	protected $model = null;

	protected $route = array();
						// array(<action>=><ClassName>)
						
	protected $autoRoute = false;
	
	public static $debug = true;
	protected $display = null;
	
	protected $error_level = E_ALL;
	
	public function __construct(&$args)
	{
		$this->args = $args;
		$this->module = $args['module'];
		 
		
		global $smarty;

		$this->view = &$smarty;
		$this->view->assign('baseURL',$args['baseURL']);
		
		
		$this->init();
		
		
	}

	protected function init()
	{	
		// Codigo de Inicializacion del Controlador
		
	}

	protected function getParameter() { return array_shift($this->args["params"]); }


	public function callMethod($method,$caller='Action')
	{	
		if ( array_key_exists('__debug',$_REQUEST) )
		{
			if ( class_exists("ORMBase")  )
			{
				ORMConnection::debug(true);
				unset($_REQUEST['__debug']);
			}
		} 
		
		try{
				return $this->$method();	
			}catch(Exception $e)
			{
				global $smarty;
				$err = $caller."Error";

				$this->$err($e);
			}
		
	}

	private function ActionError($e)
	{
		global $smarty;
		$smarty->clear_all_assign();
		$smarty->assign('nroerror',$e->getCode());
		die($smarty->fetch('error.html'));		
	}
	
	private function JsonError($e)
	{
		global $smarty;
		
		$response = array();
		$response['code'] = "ERROR";
			
//		$smarty->assign('nroerror',$e->getCode());
			
		$response['message'] = $e->getMessage();//$smarty->fetch('error.html');
		$response['log'] = $e->getCode();
		if ( get_class($e) == 'FException' )
			$response['params'] = $e->getParams();

		
		die(json_encode($response));
	}
	
	private function XmlError($e)
	{
		header("Content-type:text/xml");

		$dom = new DOMDocument();
		$err = $dom->createElement("error");
		$dom->appendChild($err);
					
		$err->setAttribute("code",0);
		$err->setAttribute("message",$e->getMessage());
					
		die($dom->saveXML());
	}
	
	public function ActionRun($actionName)
	{
		error_reporting($this->error_level);
	
		$response = $this->callMethod($actionName);
		if ( $response == false ){
		  global $smarty;
		  $smarty->display($this->template);
		}
	}
	
	public function JsonRun($actionName)
	{	
		// No mostrar errores menores que puedan afectar al JSON
		error_reporting(E_ERROR);
		
		if ( !isset($_REQUEST['__debug']) )
			header("Content-type: text/json");
		
		$response = array();
		try{
			$aux = $this->callMethod($actionName,'Json');  

			$response['code'] = 'OK';
			$response['response'] = array();//$aux;
			
			if ( is_array($aux) &&  isset($aux['count'] )){
				$response['response'] = $aux;
			}else{
				$response['response']['count'] = count($aux);
				$response['response']['data'] = $aux;
			}

			

			die(jsonEncode($response));
		}
		catch(Exception $e){
			$this->JsonError($e);
		}
		
		return true;		
	}
	
	private function toXML($rs)
	{
    	$dom = new DOMDocument('1.0');
    
    	$xreport = $dom->createElement('xml');
    	$dom->appendChild($xreport);

    	if ( count($rs) > 0 )
    	{
    		foreach( $rs as $item )
    		{
	        	$xrow = $dom->createElement('row');
        		foreach( $item as $key => $data)
        		{
            		$xcol = $dom->createElement($key);

		            $text = $dom->createTextNode($data);
    		        $xcol->appendChild($text);

        		    $xrow->appendChild($xcol);
        		}
        		$xreport->appendChild($xrow);
    		}
    	}
		return $dom;
	}
	
	
	public function XmlRun($actionName)
	{
			$response = array();
				try
				{
					$aux = $this->callMethod($actionName,'Xml'); 
					if ( $aux instanceof ORMBase ) $aux = $aux->toXML();
					if ( $aux instanceof ORMCollection ) $aux = $aux->toXML();
					if ( !( $aux instanceof DOMDocument ) ) $aux = $this->toXML($aux);
					
					
					header("Content-type:text/xml");
					die($aux->saveXML());
					
				}
				catch(Exception $e)
				{
					header("Content-type:text/xml");

					$dom = new DOMDocument();
					$err = $dom->createElement("error");
					$dom->appendChild($err);
					
					$err->setAttribute("code",0);
					$err->setAttribute("message",$e->getMessage());
					
					die($dom->saveXML());
				
				}
				return true;		
	}
	
	
	public function Run()
	{	

		$this->event = "200";	
		$this->action = ( ($this->action = array_shift($this->args["params"])) == null ) ? $this->defaultaction: $this->action;
		$this->type 	= ( isset($_REQUEST['ajax']) )?($_REQUEST['ajax']=='xml')?'xml':'json':'Action';
		$this->ftype 	= ( isset($_REQUEST['ajax']) )?'Ajax':'Action';
		
		$actionName = $this->action.$this->ftype; 

		$this->actionName = $actionName;

		if ( ! ACL::access(get_class($this), $this->action, $this) ) {
			$this->event = "403";
		}else{
			$this->event = "200";
			if ( array_key_exists($this->action, $this->route) ){
				$className = $this->route[$this->action];
				global $path;
				$filename = strtolower("$className.php");

				//require_once("${path}app/controllers/$module/$filename");
				
				$module = $this->module;
				if ( $module == "" ){
					global $config;
					$module = $config['module']['default'];
				}
				if ( $module != "" ) $module .="/";
				require_once("${path}app/controllers/${module}${filename}");
				

				$mod = new $className($this->args);
				$mod->parent = get_class($this);

				return $mod->Run();

			}else{  
				if ( method_exists($this, $actionName) or method_exists($this,"__call")){
					$execute = $this->type."Run";
					return $this->$execute($actionName);
				}else{ 
					$this->event = "404";	//debug($this);
					return cIndex::__404();
				}
			}

			return cIndex::__404();
		}
		return cIndex::__403();
	}

	
	protected function __404()
	{
		//if ( file_exists("app/views/$this->module/404.html")){
		if ( file_exists("app/views/errors/404.html")){
			$this->view->assign('heading', 'Page Not Found');
			$this->view->assign('message', '<p>Lo sentimos la p&aacute;gina no se encuentra en este servidor.</p>');			
			if ( file_exists("app/views/$this->module/index.html")){
				$this->view->assign("content","errors/404.html");
				$this->view->display("$this->module/index.html");
			}else{
				$this->view->display("errors/404.html");
			}
		}else{
			die("URL dont exists");
		}
	}
	
	protected function __403($message=null)
	{
		if ( file_exists("app/views/errors/403.html")){
			$this->view->assign('heading', 'Forbidden');
			$this->view->assign('message', '<p>Lo sentimos pero no tiene permisos para acceder al recurso solicitado.</p>');			
			if ( file_exists("app/views/$this->module/index.html")){
				$this->view->assign("content","errors/403.html");
				$this->view->display("$this->module/index.html");
			}else{
				$this->view->display("errors/403.html");
			}
		}else{
			die("No tiene permisos");
		}
	}
	
	// Establece si el usuario actual tiene acceso al modulo solicitado
	public function rights()
	{
		return true;
	}

	public function indexAction(){
		if ( $this->model == '' ) return false;
		
		global $config;
		
		
		global $smarty;
		
		$smarty->assign('content', "__module/__list.html");
		$smarty->display($this->template);
	}
	
	
	protected function uploadFile($uploadDir,$parameter="qqfile"){
	
	global $path;
		
	include_once("${path}system/libs/qqUploadedFile.php");
	
		
	 $file = qqUploadedFile::get($parameter);
	 
	if ( $file !== false ){
        $pathinfo = pathinfo($file->getName()); 
        $filename = $pathinfo['filename'];
        $rand = rand(100, 999);
        $ext = $pathinfo['extension'];

		$path = getcwd();

		$completeFilename = "${filename}${rand}.${ext}";
		$realfilename = "${path}${uploadDir}/$completeFilename";
		
		$file->save($realfilename);
		return $completeFilename;
	}
	else 
		return false;
	}
	
	public function uploadAjax(){
		$filename = $this->uploadFile("upload");
		
		return $filename;
	}
	
	protected function selectAjax()
	{
		if ( $this->model == '' ) return $this->__404();
		
		$className = $this->model;
		$fileName = strtolower($className);
		
		if ( ! class_exists($className) )
			include_once("models/$fileName.php");
			
		$obj = new $className();
		$obj->find($_REQUEST);
		
		return $obj; 
		
	}
	
	public function deleteAjax()
	{
		if ( $this->model == '' ) return $this->__404();
		
		$className = $this->model;
		$obj = new $className();
		$obj->find($_REQUEST);
		
		$obj->delete();
		
		return true;
	}
	
	public function listAjax($postProcess=false)
	{
		if ( $this->model == '' ) return $this->__404();
		
		$offset = @$_REQUEST['o'];
		$limit = @$_REQUEST['l'];
		$term = @$_REQUEST['term'];
		
		if ( ! is_numeric($offset) || $offset < 0 ) $offset = 0;
		if ( ! is_numeric($limit) || $limit < 0 ) $limit = 10;
	
		unset($_REQUEST['o']);
		unset($_REQUEST['l']);
		unset($_REQUEST['term']);
		unset($_REQUEST['ajax']);
		unset($_REQUEST['PHPSESSID']);

		$cond = null;
		if ( count($_REQUEST) > 0 or $term != '')
		{
			$cond = new ORMCondition();
			foreach ( $_REQUEST as $k => $i)
			{
				$cond->andCondition("$k =",$i);
			}
		}

		$className = $this->model;
		
		if ( $term != '' and 
			$className::$searchFields != null){
			foreach( $className::$searchFields as $k => $f ){
				$cond->andCondition("$k like","%$term%");
			}
		}

    	$objs = new ORMCollection($className);    	
    	
    	if ( $cond != null ) $objs = $objs->whereCondition($cond);    	
//		$count = count($objs);

		if ( $postProcess === true ){
			$_REQUEST['o'] = $offset;
			$_REQUEST['l'] = $limit;

			return $objs;
		}else{
			return $this->limit($objs,$offset,$limit);
		}
	}

	public function limit($obj, $o, $l){
		$response = array();
		$response['count'] = $obj->count();

		$obj->seek($o);
		$response['data'] = $obj->getArray($l);


		return $response;
	}

	public function saveAjax()
	{
		if ( $this->model == '' ) return $this->__404();

		$className = $this->model;

		$obj = new $className();

		try{
			$obj->find($_REQUEST);
		}catch(Exception $e)
		{
			$obj->setFields($_REQUEST);
			$obj->create(true);

			return $obj;
		}

		$obj->setFields($_REQUEST);
		$obj->update();

		return $obj;
	}
	
	

	
	
	public function redirect($module=null, $controller=null, $action=null, $params="")
	{	
		$baseURL = 	$this->args['baseURL'];
		
		$url = "${baseURL}";
		
		if ( $module != null ){
			$url .= "${module}";
		}
		
		if ( $controller != null ){
			$url .= "/${controller}";
		}

		if ( $action != null ){
			$url .= "/${action}";
		}
		
		if ( $params != '' ){
			$url .= "?$params"; 	
		}
		
		header("Location: $url");
	}


}

?>
