<?php

/**
*
*/

class RouteBase {

	public static function getController($url_alias, $module=""){


		global $config;
		$url_alias = strtolower($url_alias);

		if ( $url_alias == 'index' ) return false;

		$className = "c".ucfirst($url_alias);
		$filename = strtolower("$className.php");

		if ( $module == "" ){
			$module = $config['module']['default'];
		}

		$path = $config['root'];
		$fullPath = false;


		if ( file_exists($path."app/controllers/$module/".$filename) ){
			$fullPath = $path."app/controllers/$module/".$filename;

		}

		if ( $fullPath === false ) return false;

		require_once($fullPath);

		return $className;




	}

}

?>
