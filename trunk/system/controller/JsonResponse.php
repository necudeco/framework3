<?php

include("system/controller/Response.php");

class JsonResponse extends Response{
	
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
				$r =  $this->obj->$method();	
				return $r;
			}catch(Exception $e)
			{
				
				$this->$err($e);
			}		
	}
	
	public function error($e){
		
		$response = array();
		$response['code'] = "ERROR";
			
		$response['message'] = $e->getMessage();//$smarty->fetch('error.html');
		$response['log'] = $e->getCode();
		
		if ( get_class($e) == 'FException' )
			$response['params'] = $e->getParams();
	
		die(jsonEncode($response));	
	}
	
	
}
?>