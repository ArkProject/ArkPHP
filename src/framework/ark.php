<?php
defined('ARK') or define('ARK', microtime(true));
defined('ARK_PATH') or define('ARK_PATH', dirname(__FILE__).'/');

//error_reporting( E_ALL );
set_error_handler(function (){
	$args=func_get_args();
	//echo var_export($args[4], TRUE);
	$e=new ErrorException($args[1],$args[0],$args[0],$args[2],$args[3]);
	display_error($e);
	//$last_handler;
});

$class_mappings=array();
$class_mappings['ark.Application']='core/application.class.php';
$class_mappings['ark.Request']='core/request.class.php';
$class_mappings['ark.dao.DB']='dao/db.class.php';

include ARK_PATH.'core/exceptions.php';
include ARK_PATH.'core/event.class.php';
include ARK_PATH.'core/application.class.php';
include ARK_PATH.'functions.php';


?>