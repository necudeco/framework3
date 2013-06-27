<?php
	global $config;
	

	date_default_timezone_set("America/Lima");
	
	$config['modules']['default'] = 'public';
	
	$config['database'] = array(
						'server' => 'localhost',
						'database' => '',
						'user' => '',
						'password' => '',
						'driver'=>'mysqli', //pgsql
						'charset' => 'utf8',						
							);
	

	$config['baseURL'] = 'http://localhost/';
	$config['index'] = 'index.php';
	
	$lang = @$_SESSION['lang']['Name'];
	if ( $lang == '' ) $lang = 'en';
	
	$config['lang'] = $lang;
	$config['logging'] = 1;
	
	$config['root']="/var/www/app";
	
	$config['root_views']=$config['root']."app/views/";
	
	
	$config['check_enviroment'] = true; // Si esta en false, no realiza verificaciones de comprobacion
	
	$config['log']['apache_log'] = true;
	
	$config['error'] = E_ERROR | E_WARNING | E_PARSE ;
	
?>