<?php

class FException extends Exception{

	protected $params;
	
	public function __construct ($message = "", $params="", $code = 0, $previous = NULL ){
		$this->params = $params;
		
		parent::__construct($message, $code, $previoius);
	}
	
	public function getParams(){
		return $this->params;
	}

}

?>
