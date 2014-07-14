<?php

class FException extends Exception{

	protected $params;
	
	public function __construct ($message = "", $params="", $code = 0, $previous = NULL ){
		$this->params = $params;
		
		if ( @$config['log']['apache_log'] == true ){
			error_log($message);
		}
		parent::__construct($message, $code, $previoius);
	}
	
	public function getParams(){
		return $this->params;
	}

}


class ControllerNotFoundException extends Exception {}
class ControllerForbiddenException extends Exception {}

?>
