<?php

/**
*	
*/

class RouteBase {

	public static function getController($url_alias){
	
		if ( $url_alias == 'index' ) return false;
	
		$url_alias = strtolower($url_alias);
		$class = "c".ucfirst($url_alias);
		return $class;
	}

}

?>