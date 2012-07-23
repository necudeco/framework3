<?php

include("../../app/config.php");

if ( @$config['upgrade'] !== @$_REQUEST['upgrade'] ) die('wrong auth key');

include('../../system/phpORM/ORMConnection.php');



ORMConnection::emptyDatabase($sql);

?>