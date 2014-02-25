<?php
define ( 'APP_ROOT', realpath(dirname(__FILE__).'/..') );
require '../ark/ark.php';

//echo 'time:'.strftime ("%d" ,date('2012-4-6'));
//$d=strptime('2012-4-6','%Y-%m-%d');
//echo 'time:'.'gf';


//date_parse_from_format

//var_dump(ark_datetimeParse('2014年5月4日'));
function function_name() {
 var_dump(debug_backtrace());//http://www.kuqin.com/php5_doc/function.memory-get-usage.html
}
//function_name();
//die();

//application starting
ark_start(include APP_ROOT.'/data/config.php');

?>