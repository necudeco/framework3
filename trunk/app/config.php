<?php
	global $config;
	
	$config = array();
	
	$config['modules']['default'] = 'public';
	
	$config['database'] = array(
						'server' => 'localhost',
						'database' => 'daver',
						'user' => 'root',
						'password' => '',
						'driver'=>'mysqli',
						'charset' => 'utf8'
							);
							
	
	$config['baseURL'] = 'http://localhost/daberbo/';
	
	$lang = @$_SESSION['lang']['Name'];
	if ( $lang == '' ) $lang = 'he';
	
	$config['lang'] = $lang;
	
	$config['image_small_width'] = 50;
	$config['image_small_height'] = 50;
	
	$config['image_medium_width'] = 220;
	$config['image_medium_height'] = 220;
	
	$config['root']="C:\\wamp\\www\\daberbo\\"; 
	$config['uploads_root'] = $_SERVER['DOCUMENT_ROOT']."app/files/uploads/";
	$config['uploads_url'] =  $_SERVER['SERVER_NAME']."/uploads/";

	$config['daysName'][0] = 'אי';
	$config['daysName'][1] = 'בי';
	$config['daysName'][2] = 'גי';
	$config['daysName'][3] = 'די';
	$config['daysName'][4] = 'הי';
	$config['daysName'][5] = 'וי';
	$config['daysName'][6] = 'שי';
	
	$config['monthNames']['01'] = "ינואר";
	$config['monthNames']['02'] = "פברואר";
	$config['monthNames']['03'] = "מרץ";
	$config['monthNames']['04'] = "אפריל";
	$config['monthNames']['05'] = "מאי";
	$config['monthNames']['06'] = "יוני";
	$config['monthNames']['07'] = "יולי";
	$config['monthNames']['08'] = "אוגוסט";
	$config['monthNames']['09'] = "ספטמבר";
	$config['monthNames']['10'] = "אוקטובר";
	$config['monthNames']['11'] = "נובמבר";
	$config['monthNames']['12'] = "דצמבר";
	
	$config['years'] = range(11, intval(date("y", time()))+2);

?>