<?php
define('SECURITY_DIR',dirname(__FILE__));
require './framework/ark.php';
//namespace ark;

//const x=9;

/*
function __autoload($class_name) {
	//die( './freamwork/'.$class_name . '.class.php');
	include dirname(__FILE__).'/framework/core/application.class.php';
}


$x='AbcDFv.Dxx\Xz';
echo $x.'<br>';
$x=preg_replace_callback('/(^[A-Z]{1,})|(\.[A-Z]{1,})|([\A-\Z]{1,})/', function($m){
	echo 'xx['.$m[0].']<br>';
	return strtolower($m[0]);
}, $x);
$x=preg_replace_callback('/[A-Z]{1,}/', function($m){
	echo 'yy['.$m[0].']<br>';
	return '_'.strtolower($m[0]);
}, $x);

echo $x;

die();*/
//gweb_using('core.Application');
//gweb_import('core/application.class.php','./framework');

// starting app...include ARK_PATH.'../app_data/config.php'
ark\Application::run(array());
//new xxxx();
//die('end');
//echo $_GET['name'].'gfhf';

?>