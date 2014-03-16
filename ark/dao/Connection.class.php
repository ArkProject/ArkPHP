<?php
namespace ark\dao;
use ark\Map;
defined ( 'ARK' ) or exit ( 'access denied' );


class DaoException extends \Exception{
	public function __construct($message = null, $code = null, $previous = null){
		parent::__construct($message, $code, $previous);
	}
}

interface IConnectionHelper{
	/**
	 * @return \ark\dao\Connection
	 */
	public function getConnection();
	
	public function executeQuery($query,&$params=NULL);
	
	function &getHandle();
}

abstract class Command{
	/**
	 * @var \ark\dao\IConnectionHelper
	 */
	protected $helper;
	
	/**
	 * @var \ark\Map
	 */
	protected $params;
	
	/**
	 * 
	 * @param \ark\dao\IConnectionHelper $helper
	 */
	public function __construct($helper){
		$this->helper=$helper;
		$this->params=new Map();
	}
	
	/**
	 * 绑定参数。
	 * @param string $name
	 * @param any $value
	 * @param mixed $filter 对对数值进行过滤。 如果 为 TRUE 将使用默认的方式过滤；如果为 回调函数则将调用该回调函数过滤（回调函数可以使用下面两句形式：callback_name,array(object,callback_name)）.
	 */
	public function bind($name,$value,$filter=TRUE,$direction=ARK_PARAM_IN){
		$name=self::filterName($name);
		if($filter===TRUE){
			header('content-type','text/htm;charset=utf-8');
			
			$value=mysql_real_escape_string($value);
			//$value=str_replace('\'', '\\\'', $value);
			//die($value);htmlspecialchars
		}
		else if(is_callable($filter)){
			$value=call_user_func($filter, $value);
		}
		else if($filter){
			throw new \Exception('parameter $filter to be a invalid callback');
		}
		//mysql_real_escape_string
		
		$this->params->__set($name, array('name'=>$name,'value'=>$value,'direction'=>$direction,'type'=>'object'));
		return $this;
	}
	
	public static function  filterName($name){
		return mysql_real_escape_string($name);
	}
	
	public static function  filterValue($value,$type=NULL){
		return mysql_real_escape_string($value);
	}
	
	protected abstract function prepare($commandText);
	
	protected abstract function executeRender();

	protected abstract function executeArray();

	protected abstract function executeScalar();
	
	protected abstract function executeNonQuery();
}

abstract class Connection{
	
	/**
	 * @var \mysqli 数据库连接实例。|resource
	 */
	protected $handle;
	/**
	 * @var array 数据库配置。
	 */
	protected $config;
	
	public final function __construct($config){
		$this->config=$config;
	}
	
	final function __destruct(){
		$this->close();
		unset($this->handle);
		$this->handle=NULL;	
	}
	
	/**
	 * 
	 * @param unknown $config
	 * @param string $open
	 * @return resource
	 */
	public abstract function connect($open=TRUE);
	
	/**
	 * 
	 */
	public abstract function open();
	
	public abstract function close();
	
	public abstract function reportError();
	 
	
	public function query($table){
		//$this->db->use('db');
		
		//$this->db->begin();
		//$query=$this->db->query('table')->where($condition)->set('abc','123')->count();
		//$query->list(10,80);
		//$query->get();
		//$query->obj();
		//$this->db->commit();
		//$this->db->rollback();
		
		//$query=$this->db->insert('table');
		//$query->set('a','b');
		//$query->exec();
		
		//$query=$this->db->call('function :arg');
		//$query->set('a','b');
		//$query->exec();
		
	}
	
	public abstract function insert($table);
	public abstract function update($table);
	
	
}
?>