<?php

include("system/controller/Response.php");

class ActionResponse extends Response{
	
	public function Run($method){
		if ( array_key_exists('__debug',$_REQUEST) )
		{
			if ( class_exists("ORMBase")  )
			{
				ORMConnection::debug(true);
				unset($_REQUEST['__debug']);
			}
		} 
		
		try{
			
			$args = ( $this->obj->args['params'] );
			$methodName = $method."Action";
			

			if ( ! ACL::access(get_class($this->obj), $method, $this->obj) ) throw new ControllerForbiddenException();

			if ( ! method_exists($this->obj, $methodName) ) throw new ControllerNotFoundException();
			
			 
			
			$response = call_user_func_array(array($this->obj, $methodName), $args);
				
				if ( $response == false ){
					$this->obj->display();
				}

			}catch(ControllerNotFoundException $e){ throw new ControllerNotFoundException(); }
			catch(ControllerForbiddenException $e){ throw new ControllerForbiddenException(); }
			catch(Exception $e)
			{	
				$this->error($e);
			}		
	}
	
	public function error($e){
		global $config;
		$this->obj->view->clearAllAssign();
		$this->obj->view->assign('error',$e);
		
		$infotrace = $e->getTrace();
		$this->obj->view->assign('traces',array_reverse($infotrace));
		$this->obj->view->assign('errormessage',$e->getMessage());		

		if ( file_exists("app/views/error.html") ){
			die($this->obj->view->fetch('error.html'));	
		}else{			
			$path= $config['root'];				
			$urlicono = $config['baseURL']."system/css/images/error.png";			
			$this->obj->view->assign('icono',$urlicono);				
			die($this->obj->view->fetch("file:".$path."system/error.html"));	
		}


		
	}
	
}
?>