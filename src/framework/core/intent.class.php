<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'deny access' );

/**
 * 封装当前请求的上下文。
 * @author jun
 *
 */
class Intent{
	
	public function __construct(){
		
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
	
	public function cookie($name){
		if(isset($_COOKIE[$name])){
			return $_COOKIE[$name];
		}
	}
	
	public function session($name){
		if(isset($_SESSION[$name])){
			return $_SESSION[$name];
		}
	}
	
}

?>