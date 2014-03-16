<?php
namespace ark;


class Set implements \ArrayAccess,\Iterator{
	
	protected $array;
	
	public function __construct($array=NULL) {
		if(!$array){
			$array=array();
		}
		$this->array =$array;
	}
	
	public function count(){
		return count($this->array);
	}
	
	
	/* ***************************************** magic members ***************************************** */
	
	public function __set($index,$value){
		$this->array[$index]=$value;
	}
	
	public function __get($index){
		if(isset($this->array[$index])){
			return $this->array[$index];
		}
	}
	
	public function __isset($index){
		return isset($this->array[$index]);
	}
	
	public function __unset($index){
		unset($this->array[$index]);
	}
	
	
	/* ***************************************** Iterator members ***************************************** */
	
	public function current () {
		return current($this->array);
	}
	
	public function next () {
		return next($this->array);
	}
	
	public function key () {
		return key($this->array);
	}
	
	public function valid () {
		return !is_null($this->key());
	}
	
	public function rewind () {
		return reset($this->array);
	}
	
	/* ***************************************** Iterator members ***************************************** */
	/**
	 * @param offset
	 */
	public function offsetExists ($offset) {
		return $this->__isset($offset);
	}
	
	/**
	 * @param offset
	 */
	public function offsetGet ($offset) {
		return $this->__get($offset);
	}
	
	/**
	 * @param offset
	 * @param value
	 */
	public function offsetSet ($offset, $value) {
		$this->__set($offset, $value);
	}
	
	/**
	 * @param offset
	 */
	public function offsetUnset ($offset) {
		$this->__unset($offset);
	}
	
	
}

class MapEntry{
	public $key;
	public $value;
}

class Map extends Set{
	protected $entry;
	public function __construct($array=NULL){
		parent::__construct($array);
		$this->entry=new MapEntry();
	}
	
	function current() {
		$this->entry->key=$this->key();
		$this->entry->value=$this->__get($this->entry->key);
		return $this->entry;
	}
	
}

/*

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
	
	public function __set($key,$value){
		$this->_container[$key]=$value;
	}
	
	public function __get($key){
		return $this->_container[$key];
	}
	
	public function __isset($key){
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
*/
?>