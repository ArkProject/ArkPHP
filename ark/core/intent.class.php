<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'access denied' );

/**
 * 封装当前请求的上下文。
 * @author jun
 *
 */
final class Intent{
	
	private $_routeData=array();
	private $_appName;
	public function __construct($routeData){
		$this->_routeData=$routeData;
	}
	
	/**
	 * 获取当前程序名称。
	 */
	public function getApplicationName(){
		if(!$this->_appName){
			if(isset($_GET['_'])){
				$this->_appName=$_GET['_'];
			}
			else if(isset($_POST['_'])){
				$this->_appName=$_POST['_'];
			}
			else if(isset($this->_routeData['app'])){
				$this->_appName=$this->_routeData['app'];
			}
			else{
				$this->_appName='default';
			}
			if(!preg_match('/^\w+$/', $this->_appName)){
				throw new \Exception('非法的应用程序名称。参数名：_'.$this->_appName);
			}
		}
		return $this->_appName;
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
}

?>