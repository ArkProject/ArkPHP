<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'access denied' );

//http://www.php.net/manual/en/function.parse-url.php
class Uri{
	private $_pathInfo = array (
			'scheme' => 'http',
			'user' => '',
			'pass' => '',
			'host' => '',
			'port' => 80,
			'path' => '',
			'fragment' => '' 
	);
	private $_queryString=array();
	public function __construct($url=NULL){
		if($url===NULL){
			return ;
		}
		$this->_pathInfo=parse_url($url);
		$this->query(isset($this->_pathInfo['query'])?$this->_pathInfo['query']:'');
		var_dump($this->_pathInfo);
		
	}
	
	public function scheme($value=NULL){
		if($value===NULL){
			return $this->_pathInfo['scheme'];
		}
		if(ark_strlen($value)==0){
			throw new \ark\ArgumentException('value', ark_lang('__ARK_ARGUMENT_NULL_EXCEPTION'));
		}
		$this->_pathInfo['scheme']=$value;
	}
	public function username($value=NULL){
		if($value===NULL){
			return $this->_pathInfo['user'];
		}
		if(ark_strlen($value)==0){
			throw new \ark\ArgumentException('value', ark_lang('__ARK_ARGUMENT_NULL_EXCEPTION'));
		}
		$this->_pathInfo['user']=$value;
	}
	public function password($value=NULL){
		if($value===NULL){
			return $this->_pathInfo['pass'];
		}
		if(ark_strlen($value)==0){
			throw new \ark\ArgumentException('value', ark_lang('__ARK_ARGUMENT_NULL_EXCEPTION'));
		}
		$this->_pathInfo['pass']=$value;
	}
	public function host($value=NULL){
		if($value===NULL){
			return $this->_pathInfo['host'];
		}
		if(ark_strlen($value)==0){
			throw new \ark\ArgumentException('value', ark_lang('__ARK_ARGUMENT_NULL_EXCEPTION'));
		}
		$this->_pathInfo['host']=$value;
	}
	public function port($value=NULL){
		if($value===NULL){
			return $this->_pathInfo['port'];
		}
		$value=intval($value);
		if($value<=0){
			throw new \ark\ArgumentException('value');
		}
		$this->_pathInfo['port']=$value;
	}
	
	public function path($value=NULL){
		if($value===NULL){
			return $this->_pathInfo['path'];
		}
		if(ark_strlen($value)==0){
			$value='/';
		}
		$this->_pathInfo['path']=$value;
	}
	public function query($value=NULL){
		if($value===NULL){
			return $this->_queryString;
		}
		$items=array();
		foreach (ark_split($value, '&') as $item){
			$arr=ark_split($item, '=');
			if(count($arr)!=2){
				continue; // TODO: throw e?
			}
			$items[$arr[0]]=$item[1];
		}
		$this->_queryString=$items;
	}
	public function fragment($value=NULL){
		if($value===NULL){
			return $this->_pathInfo['fragment'];
		}
		$this->_pathInfo['fragment']=$value;
	}
	
	public static function current(){
		$url='';
		
		if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on'){
			$url.="https://";
		}
		else{
			$url.= "http://";
		}
		
		$url.=$_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
		return new \ark\Uri($url);
	}
	
	public function toString(){
		$url='';
		$port=intval($this->port());
		if($this->scheme()){
			$url.=$this->scheme().'://';
		}
		
		if($this->username()){
			$url.=$this->username().':'.$this->password().'@';
		}
		
		if($this->host()){
			$url.=$this->host();
		}
		if(isset($this->_pathInfo['port'])){
			$port=intval($this->_pathInfo['port']);
		}
		if((strtolower($this->scheme())=='http' && $port==80) || (strtolower($this->scheme())=='https' && $port!=443)) {

		}
		else{
			$url.=':'.$port;
		}
		
		if($this->path()){
			$url.=$this->path();
		}
		else {
			$url.='/';
		}
		
		if(count($this->_queryString)>0){
			$url.='?';
			$arr=array();
			foreach ($this->_queryString as $name=>$val){
				$arr[]=$name. '='.$val;
			}
			$url.=join('&', $arr);
		}
		
		if($this->fragment()){
			
			$url.=ark_startWith($this->fragment(), '#')?'':'#'.$this->fragment();
		}
		
		return $url;
	}
	
	public function __get($name){
		if(isset($this->_queryString[$name])){
			return $this->_queryString[$name];
		}
	}
	
	public function __set($name,$value){
		$this->_queryString[$name]=$value;
	}
}
?>