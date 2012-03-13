<?php
	global $config;
	
	$config = array();
	
	$config['modules']['default'] = 'public';
	
	$config['database'] = array(
						'server' => 'localhost',
						'database' => 'database',
						'user' => 'root',
						'password' => '',
						'driver'=>'mysqli',
						'charset' => 'utf8'
							);
							
	
	$config['baseURL'] = 'http://localhost/application/';
	
	$lang = @$_SESSION['lang']['Name'];
	if ( $lang == '' ) $lang = 'he';
	
	$config['lang'] = $lang;
	
	$config['image_small_width'] = 50;
	$config['image_small_height'] = 50;
	
	$config['image_medium_width'] = 220;
	$config['image_medium_height'] = 220;
	
	$config['root']="C:\\wamp\\www\\application\\"; 
	$config['uploads_root'] = $_SERVER['DOCUMENT_ROOT']."app/files/uploads/";
	$config['uploads_url'] =  $_SERVER['SERVER_NAME']."/uploads/";



?>