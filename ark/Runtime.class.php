<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'deny access' );



final class Runtime{
	private static $_instance;
	private static $_app;
	private static $_debug=FALSE;
	private static $_culture;
	private static $_items=array();
	
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
			throw new \Exception ( 'Runtime has already started.' );
		}
		self::$_instance=new Runtime();
	
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
		
		//replacing app name
		if(isset($_GET['_'])){
			$routing['app']=$_GET['_'];
		}
		else if(isset($_POST['_'])){
			$routing['app']=$_POST['_'];
		}
		else if(!isset($routing['app'])){
			$routing['app']='myapp';
		}
		
		if(!preg_match('/^\w+$/', $routing['app'])){
			throw new \Exception('非法的应用程序名称。参数名：_'.$routing['app']);
		}
		
		//replacing controller name
		if(isset($_GET['_c'])){
			$routing['controller']=$_GET['_c'];
		}
		else if(isset($_POST['_c'])){
			$routing['controller']=$_POST['_c'];
		}
		else if(!isset($routing['controller'])){
			$routing['controller']='default';
		}
		
		if(!preg_match('/^\w+$/', $routing['controller'])){
			throw new \Exception('非法的controller名称。参数名：_'.$routing['controller']);
		}
		
		//replacing action name
		if(isset($_GET['_a'])){
			$routing['action']=$_GET['_a'];
		}
		else if(isset($_POST['_c'])){
			$routing['action']=$_POST['_a'];
		}
		else if(!isset($routing['action'])){
			$routing['action']='index';
		}
		
		if(!preg_match('/^\w+$/', $routing['action'])){
			throw new \Exception('非法的action名称。参数名：_'.$routing['action']);
		}
		
		//self::$_culture=new \ark\Culture();
		if ($routing['app'] == 'phpinfo') {
			if (isset ( $config ['debug'] ) && $config ['debug'] == 'false') {
				die ( 'Welcome use ARKPHP framework!' );
			}
			$_SERVER ['ARKPHP'] = ARK_VERSION;
			phpinfo ();
			exit ( 0 );
		}
	
		//$GLOBALS['__ARK_LANGS']['zh-cn']=include ARK_PATH. 'i18n/langs/zh-cn.php';
		//die(ark_combine(APP_ROOT,'apps\\'). $routing['app']);
		$appPath=realpath(ark_combine(APP_ROOT,'apps\\'). $routing['app']);
		if(!$appPath || !@file_exists($appPath)){
			die( 'Welcome use ARKPHP framework!'.$appPath);
		}
		$routing['app_path']=$appPath;
		
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
			self::$_app=new \App($config,$routing);
			//if(class_parents(\App))
		}
		else{
			self::$_app=new \ark\Application($config,$routing);
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
	
	public static function getCulture(){
		return self::$_culture;
	}
	
	/**
	 * 获取一个值，指示当前是否启用 DEBUG 模式。
	 * @return boolean
	 */
	public static function debug(){
		return self::$_debug;
	}
	
	
	private static function randHex($start, $end, $len) {
		$r = dechex(mt_rand($start, $end)) . '';
		//echo strlen($r);
		while (strlen($r) < $len) {
	
			$r = $r . dechex(mt_rand($start, $end));
		}
		return substr($r, 0, $len);
	}
	
	/**
	 * 获取一个新的 UUID ，关于UUID请参考：百科。
	 * @param boolean $short 是否去掉连接短线，默认为 true.
	 * @return String
	 */
	public static function uuid($short = TRUE) {
		
		$uuid = dechex(microtime(TRUE));
		if (!$short) {
			$uuid .='-';
		}
		$uuid .=self::randHex(1000000, 4000000, 4);
		if (!$short) {
			$uuid .='-';
		}
		$uuid .=self::randHex(4000000, 8000000, 4);
		if (!$short) {
			$uuid .='-';
		}
		$uuid .=self::randHex(8000000, 12000000, 4);
		if (!$short) {
			$uuid .='-';
		}
		$uuid .=self::randHex(12000000, 16000000, 12);
	
		return $uuid;
	}
	
	public static function handleError($e){
		$html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Server Error</title><style type="text/css">
body,div,p,ul,li,hr{ margin:0; padding:0; font-size:14px;}
body{margin:0 5px; line-height:20px;}
h3{font-size:18px; font-weight:bold; margin-bottom:5px;margin-top:10px;}
hr{margin:5px 0; border:none; border-top:1px solid #efefef;}
p{padding:5px;}
div{background:#FF9; padding:5px; margin-bottom:20px;}</style></head><body>
<h3>Server Error</h3><p><b>' . get_class ( $e ) . ':</b> ' . str_replace(ARK_PATH, '~', str_replace(APP_ROOT, '~', $e->getMessage ())) . '</p>
<hr /><p><b>Source file：</b>' . str_replace(ARK_PATH, '~', str_replace(APP_ROOT, '~', $e->getFile ())) . ' <b>Line：</b>' . $e->getLine () . '<br><br>
<b>Stack trace：</b><br></p><div>';
		$lines=preg_split('/#[0-9]{0,100}\s/', $e->getTraceAsString ());
		$i=count($lines)-1;
		foreach ($lines as $item){
			if(!empty($item)){
				$html.='#'.$i.' '.str_replace(ARK_PATH, '~', str_replace(APP_ROOT, '~', $item)).'<br>';
			}
			$i--;
		}
		
		$html .= '</div><hr /><b>Version infomartion:</b>ArkPHP framework version:' . ARK_VERSION . ' PHP version:' . phpversion () . '</body></html>';
		@ob_end_clean();
		if(@ob_get_length()){
			@ob_end_flush();
		}
		
		exit ($html );
	}
}







/*
// 自动加载缓存
$GLOBALS ['__ark_autoload_caches'] = array ();
$GLOBALS ['__ark_autoload_paths'] = array (
		ARK_PATH,
		ARK_PATH . 'core/',
		ARK_PATH . 'dao/',
		ARK_PATH . 'i18n/'
);
	
function ark_loadFile($filename, $extensions = NULL, $throw = TRUE, &$lookups = array(), $once = FALSE) {
	\ark_handleFileError ();
	$found = FALSE;
	$path = '';
	if ($extensions === NULL) {
		foreach ( $GLOBALS ['__ark_autoload_paths'] as $dir ) {
			$path = $dir . $filename;
			if (! file_exists ($path )) {
				$lookups [] = $path;
			} else {
				
				$found = TRUE;
				break;
			}
		}
	} else {
		foreach ( $GLOBALS ['__ark_autoload_paths'] as $dir ) {
			
			foreach ( $extensions as $ext ) {
				$path = $dir . $filename . $ext;
				if (! file_exists ( $dir . $filename . $ext )) {
					$lookups [] = $path;
				} else {
					$found = TRUE;
					break;
				}
			}
			if($found){
				break;
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
	$path = preg_replace_callback ( '/([0-9a-z]+)([A-Z]+)/', function ($m) {
		return $m [1].'_'.strtolower ( $m [2] );
	}, $fullname );
	$path=strtolower($path);
	
	
	$path = str_replace ( 'ark\core\\', '', $path );
	$path = str_replace ( 'ark\\', '', $path );
	
	$lookups = array ();
	$extensions=array('.class.php');
	if($instantiable!==TRUE){
		$extensions[]='.php';
	}
	$result = ark_loadFile ( $path,$extensions, FALSE, $lookups, TRUE );
	if ($result && $instantiable === TRUE) {
		//die($fullname.'here');
		$result = class_exists ( $fullname );
		
		
	}
	if (! $result) {
		$msg ='';// '自动载入类失败。未找到要加载的类文件或类未定义。类名：' . $type . ' 搜索路径：';
		foreach ( $lookups as $item ) {
			$msg .= '<br>' . $item;
		}
		if(ark_indexOf($fullname, 'view')){
			var_dump($lookups);
			die($fullname);
		}
		_ark_display_error ( new \Exception ( ark_lang('__ARK_AUTOCLASS_FAIL',$fullname,$msg) ) );
	}
	$GLOBALS ['__ark_autoload_caches'] [$orign]=TRUE;
	
	
}
*/
//注册自动导入类
//\spl_autoload_extensions('.class.php,.php');
//\spl_autoload_register('ark_using',TRUE);

//include ARK_PATH.'core/event.class.php';
//include ARK_PATH.'i18n/culture.class.php';





//include ARK_PATH.'functions.php';


?>