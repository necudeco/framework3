<?php
	function debug($obj, $title = null)
	{
		if ( $title != null ) echo "<h2>$title</h2>";
		echo "<pre>";
		print_r($obj);
		echo "</pre>";
	}
	
?>