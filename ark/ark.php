<?php

defined ( 'ARK' ) or define ( 'ARK', microtime ( true ) );
defined ( 'ARK_VERSION' ) or define ( 'ARK_VERSION', '1.0.1' );
defined ( 'ARK_PATH' ) or define ( 'ARK_PATH', dirname ( __FILE__ ) . '/' );
defined ( 'SECURITY_DIR' ) or define ( 'SECURITY_DIR', dirname ( __FILE__ ) );
defined ( 'ROOT_DIR' ) or define ( 'ROOT_DIR', ARK_PATH.'../' );

final class Ark{
	private static $_instance;
	private static $_app;
	private static $_intent;
	private static $_debug=FALSE;
	
	/**
	 * 单例启动。
	 */
	private function __construct(){
	
	}
	
	public static function start($config){
	
		if($config && isset($config['debug'])){
			self::$_debug=$config['debug']==='true';
		}
	
		if(self::$_instance){
			throw new Exception ( 'Runtime has already started.' );
		}
		self::$_instance=new Ark();
	
		$domain=$_SERVER['HTTP_HOST'];
		if(!($_SERVER["SERVER_PORT"]==80 || $_SERVER["SERVER_PORT"]==23)){
			$domain.=':'.$_SERVER["SERVER_PORT"];
		}
	
		$routing=array();
		if(isset($config['routing'][$domain])){
			$routing=$config['routing'][$domain];
		}
		else if(isset($config['routing']['*'])){
			$routing=$config['routing']['*'];
		}
		self::$_intent=new \ark\Intent($routing);
	
	
		if (self::$_intent->getApplicationName () == 'phpinfo') {
			if (isset ( $config ['debug'] ) && $config ['debug'] == 'false') {
				die ( 'Welcome use ARKPHP framework!' );
			}
			$_SERVER ['ARKPHP'] = ARK_VERSION;
			phpinfo ();
			exit ( 0 );
		}
	
		$appPath=realpath(ROOT_DIR.'apps/'. self::$_intent->getApplicationName());
		if(!$appPath || !@file_exists($appPath)){
			die( 'Welcome use ARKPHP framework!');
		}
	
		//clears all buffer
		if(!self::debug()){
			ob_start(function (){
	
			});
		}
	
		$filename=$appPath.'/app.class.php';
		if(@file_exists($filename)){
			include $filename;
			if(!class_exists('\App')){
				throw new \Exception('未定义 类 \App');
			}
			self::$_app=new \App($appPath.'/',$config,$routing);
			//if(class_parents(\App))
		}
		else{
			self::$_app=new \ark\Application($appPath.'/',$config,$routing);
		}
	
		exit(0);
	}
	
	/**
	 *
	 * @throws \Exception
	 * @return \ark\Application 返回当前应用程序唯一实例。
	 */
	public static function getApplication() {
		if (! self::$_app) {
			throw new \Exception ( 'Application not started yet.' );
		}
		return self::$_app;
	}
	
	public static function getIntent(){
		return self::$_intent;
	}
	
	/**
	 * 获取一个值，指示当前是否启用 DEBUG 模式。
	 * @return boolean
	 */
	public static function debug(){
		return self::$_debug;
	}
}


// 自动加载缓存
$GLOBALS ['__ark_autoload_caches'] = array ();
$GLOBALS ['__ark_autoload_paths'] = array (
		ARK_PATH,
		ARK_PATH . 'core/',
		ARK_PATH . 'dao/',
		ARK_PATH . 'i18n/' 
);

function _ark_display_error($e) {
	$html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Server Error</title><style type="text/css">
body,div,p,ul,li,hr{ margin:0; padding:0; font-size:14px;}
body{margin:0 5px; line-height:20px;}
h3{font-size:18px; font-weight:bold; margin-bottom:5px;margin-top:10px;}
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
	@ob_end_clean();
	if(@ob_get_length()){
		@ob_end_flush();
	}
	
	exit (str_replace(SECURITY_DIR, '~', $html) );
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
		$msg ='';
		foreach ( $lookups as $item ) {
			$msg .= '<br>' . $item;
		}
		throw new ark\FileSystemException ( ark_lang('__ARK_LOOKUP_FILE_FAIL',$filename,$msg) );
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

function ark_using($fullname, $instantiable = TRUE) {
	$orign=$fullname;
	if (isset ( $GLOBALS ['__ark_autoload_caches'] [$orign] )) {
		return;
	}
	
	if(ark_startWith($fullname,'\\')){
		$fullname = ark_substr($fullname, 1);
	}
	else if(ark_startWith($fullname,'ark.')){
		$fullname = str_replace ( 'ark.', 'ark\\', $fullname );
	}
	$fullname = str_replace ( '.', '\\', $fullname );
	
	if ($instantiable === TRUE && class_exists ( $fullname,FALSE)) {
		return ;
	}
	
	$path = preg_replace_callback ( '/(^[A-Z]{1,})|(\.[A-Z]{1,})|(\\[A-Z]{1,})/', function ($m) {
		return strtolower ( $m [0] );
	}, $fullname );
	$path = preg_replace_callback ( '/[A-Z]{1,}/', function ($m) {
		return '_' . strtolower ( $m [0] );
	}, $path );
	
	$path = str_replace ( '\\_', '\\', $path );
	$path = str_replace ( 'ark\core\\', '', $path );
	$path = str_replace ( 'ark\\', '', $path );
	
	$lookups = array ();
	$extensions=array('.class.php');
	if($instantiable!==TRUE){
		$extensions[]='.php';
	}
	$result = ark_loadFile ( $path,$extensions, FALSE, $lookups, TRUE );
	if ($result && $instantiable === TRUE) {
		$result = class_exists ( $fullname );
	}
	if (! $result) {
		$msg ='';// '自动载入类失败。未找到要加载的类文件或类未定义。类名：' . $type . ' 搜索路径：';
		foreach ( $lookups as $item ) {
			$msg .= '<br>' . $item;
		}
		_ark_display_error ( new \Exception ( ark_lang('__ARK_AUTOCLASS_FAIL',$fullname,$msg) ) );
	}
	$GLOBALS ['__ark_autoload_caches'] [$orign]=TRUE;
}

//注册自动导入类
\spl_autoload_extensions('.class.php,.php');
\spl_autoload_register('ark_using',TRUE);

//include ARK_PATH.'core/event.class.php';
//include ARK_PATH.'core/application.class.php';

function ark_lang($val,$_){
	return call_user_func_array('ark\i18n\Culture::getLocalString', func_get_args());
}

/**
 * 启动ARK运行时。
 * @param array $config 配置参数。
 */
function ark_start($config){
	
	Ark::start($config);
	//ark\Application::run($config);
}

include ARK_PATH.'functions.php';


?>