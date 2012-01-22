<?php
/*
  * Smarty plugin
  * -------------------------------------------------------------
  * File:      function.html_head.php
  * Type:      function
  * Name:      html_head
  * Purpose:   crea un control para el formulario
  * -------------------------------------------------------------
  */
function smarty_function_html_widget($params, $smarty){
	
	$styles = generateCSS($smarty);
	$scripts = generateJS($smarty);
	$title = $smarty->vars['title'];
	$head = <<<ROW
	<html>
		<head>
			<title>$title</title>
			$styles
			$scripts
		</head>
	<body>
ROW;
echo $head;
}

function generateCSS($smarty){
	$styles = "";
	foreach( $smarty->vars['styles'] as $style ){
		$styles .= "<link href='$style' rel='stylesheet' type='text/css'/>\n";
	}
	
	return $styles;
}

function generateJS($smarty){
	$scripts = "";
	foreach( $smarty->vars['styles'] as $scripts ){
		$scripts .= "<script src='$scripts' > </script>\n";
	}
	
	return $scritps;
}
?>
