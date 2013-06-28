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
				return $this->obj->$method();	
			}catch(Exception $e)
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