<?php
namespace ark;

class MapEntry{
	public $key;
	public $value;
}

class Map implements \ArrayAccess,\Iterator{
	private $_container = array();
	private $_mapEntry;
	public function __construct($map=NULL) {
		if(!$map){
			$map=array();
		}
		$this->_container =$map;
		$this->rewind();

		$this->_mapEntry=new MapEntry();
		
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->_container[] = $value;
		} else {
			$this->_container[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->_container[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->_container[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->_container[$offset]) ? $this->_container[$offset] : null;
	}


	function rewind() {
		return reset($this->_container);
	}

	function current() {
		$this->_mapEntry->key=$this->key();
		$this->_mapEntry->value=$this->_container[$this->_mapEntry->key];
		return $this->_mapEntry;
		//return current($this->_container);
	}

	function key() {
		return key($this->_container);
	}

	function next() {
		return next($this->_container);
	}

	function valid() {
		return key($this->_container) !== null;
	}

	
	public function set($key,$value){
		$this->_container[$key]=$value;
	}
	
	public function remove($key){
		unset($this->_container[$key]);
	}
	
	public function &getArray(){
		return $this->_container;
	}
	
	public function count(){
		return count($this->_container);
	}
	
}

?>