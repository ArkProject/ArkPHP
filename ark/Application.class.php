<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'access denied' );

/**
 * Represents a web application.
 *
 * @author jun
 *        
 */
class Application {
	
	private $_dataPath;
	protected $settings;
	protected $routing;
	public final function __construct(&$settings,&$routing) {
		$routing['controller_path']=ark_combine($routing['app_path'], 'controllers\\');
		$routing['view_path']=ark_combine($routing['app_path'], 'views\\');
		$routing['model_path']=ark_combine($routing['app_path'], 'models\\');
		$this->settings = $settings;
		$this->routing=$routing;
		$this->init();
	}
	
	/**
	 * 当应用程序启动时调用。
	 */
	protected function init(){
		
		$controller='\\'.strtolower($this->getApplicationName()).'\controllers\\'.ucfirst($this->getControllerName()).'Controller';
		
		$instance=new $controller($this);
	}
	
	public function getRouting(){
		return $this->routing;
	}
	
	
	/**
	 *
	 * @param unknown $dbConfigIndex        	
	 * @param string $newInstance        	
	 */
	public function usingDB($dbConfigIndex, $newInstance = FALSE) {
	}
	
	
	public function getApplicationPath(){
		return $this->routing['app_path'];
	}
	
	/**
	 * 获取当前程序名称。
	 */
	public final function getApplicationName(){
		return $this->routing['app'];
	}
	
	public final function getControllerName(){
		return $this->routing['controller'];
	}
	
	public final function getActionName(){
		return $this->routing['action'];
	}
	
	public function isPost(){
		return $this->getRequestMethod()==='POST';
	}
	public function isGet(){
		return $this->getRequestMethod()==='GET';
	}
	public function isAdd(){
		return $this->getRequestMethod()==='ADD';
	}
	public function isDelete(){
		return $this->getRequestMethod()==='DELETE';
	}
	
	/**
	 * 获取请求方法名称。
	 * @param boolean $tolower
	 * @return string
	 */
	function getRequestMethod($tolower = FALSE) {
		if ($tolower === TRUE) {
			return isset ( $_SERVER ['REQUEST_METHOD'] ) ? strtolower ( $_SERVER ['REQUEST_METHOD'] ) : 'unknown';
		}
		return isset ( $_SERVER ['REQUEST_METHOD'] ) ? strtoupper ( $_SERVER ['REQUEST_METHOD'] ) : 'UNKNOWN';
	}
	
	/**
	 * 获取当前请求上下文变量。
	 *
	 * @param string $name
	 * @return string
	 */
	public function __get($name){
		if(self::isPost() && isset($_POST[$name])){
			return $_POST[$name];
		}
		if(isset($_GET[$name])){
			return $_GET[$name];
		}
		else if(isset($_POST[$name])){
			return $_POST[$name];
		}
		else if(isset($_COOKIE[$name])){
			return $_COOKIE[$name];
		}
		else if(isset($_REQUEST[$name])){
			return $_REQUEST[$name];
		}
		else if(isset($_SESSION[$name])){
			return $_SESSION[$name];
		}
		else if(isset($_SERVER[$name])){
			return $_SERVER[$name];
		}
	}
	
	public function query($name){
		if(isset($_GET[$name])){
			return $_GET[$name];
		}
	}
	
	public function form($name){
		if(isset($_POST[$name])){
			return $_POST[$name];
		}
	}
	
	/**
	 * 获取post或put提交的文件
	 * @param unknown $nameOrIndex
	 */
	public function file($nameOrIndex){
	
	}
	
	public function server($name){
		if(isset($_SERVER[$name])){
			return $_SERVER[$name];
		}
	}
	
	public function request($name){
		if(isset($_REQUEST[$name])){
			return $_REQUEST[$name];
		}
	}
	
	/**
	 * 获取或设置cookie的值
	 * @param unknown $name
	 * @param string $value
	 * @return void|string
	 */
	public function cookie($name,$value=NULL){
		if($value!=NULL){
			$_COOKIE[$name]=$value;
			return ;
		}
		if(isset($_COOKIE[$name])){
			return $_COOKIE[$name];
		}
	}
	
	/**
	 * 获取或设置session的值
	 * @param unknown $name
	 * @param string $value
	 * @return void|string
	 */
	public function session($name,$value=NULL){
		if($value!=NULL){
			$_SESSION[$name]=$value;
			return ;
		}
		if(isset($_SESSION[$name])){
			return $_SESSION[$name];
		}
	}
	
	/**
	 * 获取当前请求 URI
	 * @return \ark\Uri
	 */
	public function uri(){
		return Uri::current();
	}
	
	
	/**
	 * 映射一个相对于应用程序的服务器路径。
	 * @param string $path
	 */
	public function mapPath($path){
		return $this->getAppPath().$path;
	}
	
	
	
}

?>