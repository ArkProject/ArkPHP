<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'access denied' );

/**
 * ARK 框架运行时。
 * @author jun
 *
 */
final class Runtime{
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
		self::$_intent=new Intent($routing);
		
		
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
			self::$_app=new Application($appPath.'/',$config,$routing);
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