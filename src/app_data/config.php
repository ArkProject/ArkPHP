<?php
return (function (){
$settings=array();
$settings['debug']='true';
$settings['lang']='zh-CN';
$settings['encoding']='utf8';

$settings['db'][0]['provider']='custom';
$settings['db'][0]['provider_file']='~/modules/mysql/provider.class.php';
$settings['db'][0]['provider_class']='MysqlProvider';
$settings['db'][0]['username']='sa';
$settings['db'][0]['passwd']='sa';
$settings['db'][0]['dbname']='test';
$settings['db'][0]['encoding']='utf-8';

$settings['apps']['path']='~/apps';
$settings['apps']['default']='default';
$settings['controller']['default']='default';
$settings['action']['default']='index';
return $settings;
});
?>