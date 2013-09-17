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
		$this->obj->view->clearAllAssign();
		$this->obj->view->assign('error',$e);
		die($this->obj->view->fetch('error.html'));	
	}
	
}
?>