<?php

include("../../app/config.php");


if ( @$config['upgrade'] !== @$_REQUEST['upgrade'] ) die('wrong auth key');


function cp($source, $dest, $dontcopy = array()){

	print("<hr />Processing $source<br />");
	
	if ( ! is_dir($dest) ) 
		if ( ! mkdir($dest) ) die("we cannt create dir $dest <br />");
	
	system("cp $source $dest -rf ");
	
	return array();
	

	
	$problem = array();
		
	foreach( glob("$source/*", GLOB_MARK) as $filename){
		$fileNameOut = basename($filename);
		
		if ( in_array($fileNameOut, $dontcopy ) ) continue;
		
		if ( is_dir($filename) ) {
			array_merge($problem, cp($filename, "${dest}${fileNameOut}/"));
			continue;
		}
		
		
		print("Copying $filename to ${dest}${fileNameOut} <br />");
		$b = copy($filename, "$dest/$fileNameOut");
		if ( !$b ) $problem[] = $fileNameOut;
		
	}
	
	return $problem;
}


$dir = $config['root'];

$problem = array();

array_merge($problem, cp("${dir}system/js/*", "${dir}files/js/libs/" ) );
array_merge($problem, cp("${dir}system/css/*", "${dir}files/css/libs/" ) );

die("Upgrade HTML DONE");


?>
