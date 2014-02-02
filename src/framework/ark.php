<?php

defined('ARK') or define('ARK', microtime(true));
defined('ARK_VERSION') or define('ARK_VERSION', '1.0.1');
defined('ARK_PATH') or define('ARK_PATH', dirname(__FILE__).'/');

//自动加载缓存
$GLOBALS['__ark_autoload_caches']=array();
$GLOBALS['__ark_autoload_paths']=array(
		ARK_PATH,
		ARK_PATH.'core/',
		ARK_PATH.'dao/',
		ARK_PATH.'i18n/'
);

class Ark{
	
}

function _ark_display_error($e) {
	$html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Server Error</title><style type="text/css">
body,div,p,ul,li,hr{ margin:0; padding:0; font-size:14px;}
body{margin:0 5px; line-height:20px;}
h3{font-size:18px; font-weight:bold; margin-bottom:10px;margin-top:0;}
hr{margin:5px 0; border:none; border-top:1px solid #efefef;}
p{padding:5px;}
div{background:#FF9; padding:5px; margin-bottom:20px;}</style></head><body>
<h3>Server Error</h3><p><b>' . get_class ( $e ) . ':</b> ' . $e->getMessage () . '</p>
<hr /><p><b>Source file：</b>' . $e->getFile () . ' <b>Line：</b>' . $e->getLine () . '<br><br>
<b>Stack trace：</b><br></p><div>';
	$lines=preg_split('/#[0-9]{0,100}\s/', $e->getTraceAsString ());
	$i=count($lines)-1;
	foreach ($lines as $item){
		if(!empty($item)){
			$html.='#'.$i.' '.$item.'<br>';
		}
		$i--;
	}

	$html .= '</div><hr /><b>version infomartion:</b>ArkPHP framework version:' . ARK_VERSION . ' PHP version:' . phpversion () . '</body></html>';

	exit (str_replace(getcwd(), '...', $html) );
}
//捕获所有未处理异常和错误
\error_reporting( E_ALL );
\set_exception_handler(function ($e){
	_ark_display_error($e);
});
\register_shutdown_function(function (){
	$error =  error_get_last();
	if($error && isset($error['type']) && $error['type']==E_ERROR){
		ob_clean();
		$e=new ErrorException($error['message'],$error['type'],$error['type'],$error['file'],$error['line']);
		_ark_display_error($e);
	}
});
\set_error_handler(function (){
	$args=func_get_args();
	//echo var_export($args[4], TRUE);
	$e=new ErrorException($args[1],$args[0],$args[0],$args[2],$args[3]);
	throw $e;
	_ark_display_error($e);
},E_ALL);

function ark_handleFileError() {
	\set_error_handler ( function () {
		$args = func_get_args ();
		throw new ark\FileSystemException ( $args [1] );
	} );
}
	
function ark_unhandleError() {
	\restore_error_handler ();
}
	
function ark_loadFile($filename, $extensions = NULL, $throw = TRUE, &$lookups = array(), $once = FALSE) {
	\ark_handleFileError ();
	$found = FALSE;
	$path = '';
	if ($extensions === NULL) {
		foreach ( $GLOBALS ['__ark_autoload_paths'] as $dir ) {
			if (! file_exists ( $dir . $filename )) {
				$lookups [] = $dir . $filename;
			} else {
				$path = $dir . $filename;
				$found = TRUE;
				break;
			}
		}
	} else {
		foreach ( $GLOBALS ['__ark_autoload_paths'] as $dir ) {
			foreach ( $extensions as $ext ) {
				if (! file_exists ( $dir . $filename . $ext )) {
					$lookups [] = $dir . $filename . $ext;
				} else {
					$path = $dir . $filename . $ext;
					$found = TRUE;
					break;
				}
			}
		}
	}
	if (! $found && $throw) {
		$msg = '指定文件不存在或未找到。文件名:' . $filename . ' 搜索路径：';
		foreach ( $lookups as $item ) {
			$msg .= '<br>' . $item;
		}
		throw new ark\FileSystemException ( $msg );
	} else if ($found) {
		if ($once !== TRUE) {
			include $path;
		} else {
			include_once $path;
		}
	}
	\ark_unhandleError ();
	return $found;
}
function ark_using($type, $checkClass = TRUE) {
	if (isset ( $GLOBALS ['__ark_autoload_caches'] [$type] )) {
		return;
	}
	$path = preg_replace_callback ( '/(^[A-Z]{1,})|(\.[A-Z]{1,})|(\\[A-Z]{1,})/', function ($m) {
		return strtolower ( $m [0] );
	}, $type );
	$path = preg_replace_callback ( '/[A-Z]{1,}/', function ($m) {
		return '_' . strtolower ( $m [0] );
	}, $path );
	$path = str_replace ( '.', '/', $path );
	$path = str_replace ( '\\_', '\\', $path );
	$path = str_replace ( '\ark\core\\', '', $path );
	$path = str_replace ( '\ark\\', '', $path );
	$path = str_replace ( 'ark\core\\', '', $path );
	$path = str_replace ( 'ark\\', '', $path );
	$lookups = array ();
	$result = ark_loadFile ( $path, array (
			'.class.php',
			'.php' 
	), FALSE, $lookups, FALSE );
	if ($result && $checkClass === TRUE) {
		$result = class_exists ( $type );
	}
	if (! $result) {
		$msg = '自动载入类失败。未找到要加载的类文件或类未定义。类名：' . $type . ' 搜索路径：';
		foreach ( $lookups as $item ) {
			$msg .= '<br>' . $item;
		}
		_ark_display_error ( new \Exception ( $msg . '<br>' ) );
	}
}

//注册自动导入类
\spl_autoload_extensions('.class.php,.php');
\spl_autoload_register('ark_using',TRUE);

//include ARK_PATH.'core/event.class.php';
//include ARK_PATH.'core/application.class.php';
include ARK_PATH.'functions.php';


?>