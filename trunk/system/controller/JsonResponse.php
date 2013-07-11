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
				

				$response['code'] = 'OK';
				$response['response'] = array();//$aux;
				
				if ( is_array($r) &&  isset($r['count'] )){
					$response['response'] = $r;
				}else{
					$response['response']['count'] = count($r);
					$response['response']['data'] = $r;
				}
				die(jsonEncode($response));
				
			}catch(Exception $e)
			{
				
				$this->error($e);
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