<?php
/*
  * Smarty plugin
  * -------------------------------------------------------------
  * File:      function.widget.php
  * Type:      function
  * Name:      widget
  * Purpose:   crea un control para el formulario
  * -------------------------------------------------------------
  */
function smarty_function_mydebug($params, $smarty){
	if ( empty($params['item'] ) ){
		$smarty->triggerError("item: missing 'item' parameter");
		return ;
	}
	$w = $params['item'][0];

	debug($w);
}
?>
