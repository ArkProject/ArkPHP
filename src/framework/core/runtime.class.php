<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'deny access' );

/**
 * ARK 框架运行时。
 * @author jun
 *
 */
final class Runtime{
	private static $_instance;
	private static $_app;
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
		/*
		if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on')
		{
		    echo "HTTPS";
		}else{
		    echo "HTTP";
		}
		*/
		$routing=false;
		if(isset($config['routing'][$domain])){
			$routing=$config['routing'][$domain];
		}
		else if(isset($config['routing']['*'])){
			$routing=$config['routing']['*'];
		}
		else{
			throw new \Exception('未找到路由配置');
		}
		
		if(isset($_GET['_'])){
			$routing['app']=$_GET['_'];
		}
		else if(isset($_POST['_a'])){
			$routing['app']=$_POST['_'];
		}
		
		if($routing['app']=='phpinfo'){
			if(isset($config['debug']) && $config['debug']=='false'){
				die( 'Welcome use ARKPHP framework!');
			}
			$_SERVER['ARKPHP']=ARK_VERSION;
			phpinfo();
			exit(0);
		}
		
		$appPath=ROOT_DIR.'apps/'.$routing['app'].'/';
		if(!@file_exists($appPath)){
			die( 'Welcome use ARKPHP framework!'.$appPath);
		}
		
		//clears all buffer
		if(!self::debug()){
			ob_start(function (){
				
			});
		}
		
		$filename=$appPath.'app.class.php';
		if(@file_exists($filename)){
			include $filename;
			self::$_app=new \App($appPath,$config,$routing);
		}
		else{
			self::$_app=new Application($appPath,$config,$routing);
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
	
	/**
	 * 获取一个值，指示当前是否启用 DEBUG 模式。
	 * @return boolean
	 */
	public static function debug(){
		return self::$_debug;
	}
	
	
	
}