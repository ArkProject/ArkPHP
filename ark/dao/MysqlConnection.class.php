<?php
namespace ark\dao;
defined ( 'ARK' ) or exit ( 'access denied' );

class MysqlConnection extends \ark\dao\Connection implements \ark\dao\IConnectionHelper{
	private $_closed;
	private function support($throw=TRUE,$checkVersion=FALSE,$handle=NULL){
		$e=NULL;
		if(!class_exists('\mysqli',FALSE)){
			$e=new DaoException('Unable to MySQLi class for MysqlProvider.');
		}
		
		if($checkVersion && $handle->client_version<50000){
			$e=new DaoException('Not support MySQLi Client Version. must be 50000 or later.');
		}

		if($throw && $e){
			throw $e;
		}
		else if($e){
			return FALSE;
		}
		return TRUE;
	}
	
	//检查并报告SQL错误
	public function reportError(){
        if ($this->handle->errno) {
            throw new DaoException('MySQL Error: ['.$this->handle->errno.']'.$this->handle->error);
        }
	}
	
	
	/* ***************************************** Connection members ***************************************** */
		
	//连接数据库
	function connect($open=TRUE){
		$this->support();
		$handle=NULL;
		try{
			$this->handle=\mysqli_connect($this->config['dbhost'],$this->config['username'],$this->config['passwd'],$this->config['dbname']);
		}
		catch (\Exception $e){
			throw new DaoException('Unable connect to MySQL Server. Error Message: '.$e->getMessage(),$e->getCode(),$e);
		}
		if($handle && $this->handle->connect_error){
			throw new DaoException('Unable connect to MySQL Server. Error Message: ['.$this->handle->connect_errno.']'.$this->handle->connect_error);
		}
		else if(!$this->handle){
			throw new DaoException('Unable connect to MySQL Server.');
		}
		
		if($open){
			$this->open();
		}
		
	}

	//打开数据库
	function open(){
		if(isset($this->config['charset'])){
			$charset=$this->config['charset'];
		}
		else{
			$charset='utf8';
		}
		$this->handle->query('SET NAMES ' . $charset);
		$this->reportError();
	}
	
	//关闭连接
	function close(){
		if($this->_closed){
			return;
		}
		$this->handle->close();
		$this->_closed=true;
	}
	
	function query($table){
		return new MysqlQueryCommand($this,$table);
	}
	
	public function insert($table){
		return new MysqlInsertCommand($this,$table);
	}
	
	public function update($table){
		return new MysqlUpdateCommand($this,$table);
	}
	
	/* ***************************************** IConnectionHelper members ***************************************** */
	function getConnection(){
		return $this;
	}
	
	function executeQuery($query,&$params=NULL){
		$result= $this->handle->query($query);
		$this->reportError();
		return $result;
	}
	
	function &getHandle(){
		return $this->handle;
	}
}

class MysqlCommand extends Command{

	/**
	 * @var \ark\dao\Connection
	 */
	protected $connection;
	protected $cmdText;
	public function __construct($helper){
		parent::__construct($helper);
		$this->connection=$helper->getConnection();
	}
	
	private function matchToken($input) {
		$name = $input[1];
		$param=$this->params->__get($name);
		
		return '\'' . $param['value'] . '\'';
	}
	
	function prepare($commandText){
		return preg_replace_callback('/\:([0-9a-zA-Z_]+)/', array(&$this, 'matchToken'), $commandText);
	}
	
	function getCommandText(){
		
	}
	
	function executeRender() {
		
	}

	function executeArray() {
		
	}
	
	function executeNonQuery(){
		
		if($this->cmdText && is_string($this->cmdText) && $result= $this->helper->executeQuery($this->cmdText)){
			$this->connection->reportError();
			$handle=&$this->helper->getHandle();
			return $handle->affected_rows;
		}
	}

	function executeScalar() {
		
		if($this->cmdText && is_string($this->cmdText) && $result= $this->helper->executeQuery($this->cmdText)){
			$this->connection->reportError();
			if($row=$result->fetch_row()){
				$this->connection->reportError();
				$result->free();
				return $row[0];
			}
			$result->free();
		}
	}
}

class MysqlQueryCommand extends MysqlCommand{
	private $_sql;
	private $_skip=0;
	private $_limit=2000;
	
	public function __construct($helper,$cmdText){
		parent::__construct($helper);
		$this->_sql=$cmdText;
	}
	

	//http://www.php.net/manual/zh/class.mysqli-result.php
	function executeScalar() {
		$this->cmdText=$this->prepare($this->_sql);
		return parent::executeScalar();
	}
	
	function exec(){
		$this->cmdText=$this->prepare($this->_sql);
		
	}
}

class QueryResult{
	/**
	 * 尝试读取下一行
	 */
	function read(){
			
	}
	
	/**
	 * 尝试读取下个结果集
	 */
	function next(){
		
	}
	
	/**
	 * 获取当前行的1列的值。
	 * @param string $index 如果未提供则获取索引为的0的列
	 */
	function get($index=NULL){
		
	}
	
	/**
	 * 将当前行映射为一个字典。
	 * @param string $map
	 */
	function map($map=NULL){
		
	}
	
}


/**
 * 插入数据
 * @author jun
 *
 */
class MysqlInsertCommand extends MysqlCommand{
	
	private $_replace=FALSE;
	private $_table;
	private $_lastId=-1;
	public function __construct($helper,$table,$replace=FALSE){
		parent::__construct($helper);
		$this->_table=\ark\dao\Command::filterName($table);
		$this->_replace=$replace;
	}
	
	function getCommandText(){
		$sql= ($this->_replace===TRUE?'REPLACE':'INSERT'). ' INTO `'.$this->_table.'`(' ;
		$fields=array();
		$values=array();
		foreach ($this->params as $item){
			$fields[]='`'.$item->key.'`';
			$values[]=':'.$item->key.'';
		}
		$sql .=join(',',$fields).') VALUES (';
		$sql .=join(',',$values).');';
		
		return $this->prepare($sql);
	}
	
	/**
	 * 执行并返回影响的行数。
	 */
	public function exec(){
		$this->cmdText=$this->getCommandText();
		$result=parent::executeNonQuery();
		if($result>0){
			$this->cmdText='SELECT LAST_INSERT_ID();';
			$this->_lastId=$this->executeScalar();
		}
		return $result;
	}
	
	public function getLastInsertId(){
		return $this->_lastId;
	}
	
}


/**
 * 更新数据
 * @author jun
 *
 */
class MysqlUpdateCommand extends MysqlCommand{

	private $_table;
	private $_where;
	private $_ignored=array();
	public function __construct($helper,$table){
		parent::__construct($helper);
		$this->_table=\ark\dao\Command::filterName($table);
	}

	/**
	 * 设计条件
	 * @param unknown $condition
	 * @return \ark\dao\MysqlUpdateCommand
	 */
	function where($condition){
		
		if($condition && is_string($condition)){
			$this->_where=$condition;
		}
		//TODO:未实现结构化条件
		
		return $this;
	}
	
	/**
	 * 设置非更新字段
	 * @param unknown $fields
	 * @param unknown $_
	 * @return \ark\dao\MysqlUpdateCommand
	 */
	function ignore($fields,$_) {
		foreach (func_num_args() as $arg){
			if($arg && is_string($arg)){
				$this->_ignored[]=$arg;
			}else if($arg && is_array($arg)){
				foreach ($arg as $a){
					if($a && is_string($a)){
						$this->_ignored[]=$a;
					}
				}
			}
		}
		return $this;
	}
	
	function getCommandText(){
		$sql= ' UPDATE `'.$this->_table.'` SET ' ;
		$fields=array();
		$values=array();
		foreach ($this->params as $item){
			if(in_array($item->key,$this->_ignored)){
				continue;
			}
			$fields[]='`'.$item->key.'`'.'=:'.$item->key;
		}
		$sql .=join(',',$fields).' ';
		if($this->_where){
			$sql.='WHERE '.$this->_where;
		}
		$sql.=';';

		return $this->prepare($sql);
	}

	/**
	 * 执行更新
	 */
	public function exec(){
		$this->cmdText=$this->getCommandText();
		return parent::executeScalar();
	}

}






?>