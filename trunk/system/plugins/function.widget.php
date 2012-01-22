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
function smarty_function_widget($params, $smarty){
	if ( empty($params['widget'] ) ){
		$smarty->triggerError("widget: missing 'widget' parameter");
		return ;
	}
	$w = $params['widget'];

	$_fn = "widget_".$w.type;
	$widget = $_fn($w);
	
	$row = <<<ROW
	<div class="widget field" >
		<label>{$w.label}</label>
		$widget
	</div>

ROW;

}

function widget_image($w){
	$widget = <<<EOD
			<div id="${w.name}-uploader" class="fileuploader" input="name[${w.name}]" 
						action="${w.action}" preview="#preview-${w.name}"></div>
		</div>
		<div id="preview-${w.name}" >
			<img src="" />
EOD;
}

function widget_file($w){
	$widget = <<<EOD
			<div id="${w.name}-uploader" class="fileuploader" input="name[${w.name}]" 
						action="${w.action}" preview="#preview-${w.name}"></div>
			</div>
EOD;
}

function widget_text($w){
	$widget = <<<EOD
			<input type="text" class="{$w.name}" />
EOD;
}

function widget_textarea($w){
	$widget = <<<EOD
			<textarea class="{$w.name}" ></textarea>
EOD;
}

function widget_date($w){
	$widget = <<<EOD
			<input type="text" class="{$w.name} datepicker" />
EOD;
}



?>


