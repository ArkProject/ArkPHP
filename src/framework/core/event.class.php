<?php
namespace ark;

/**
 * 用于支持事件的基类。
 * 
 * @author jun
 *
 */
abstract class Event{
	/**
	 * @var array
	 */
	private $_events=array(); 
	
	/**
	 * 添加一个监听事件。
	 * 
	 * @param string 事件类型
	 * @param Closure 回调函数.函数原型: callback(object $sender,object $arg1,object $arg2...,object $argn)
	 * @throws ArgumentException
	 */
	public function on($eventType,$callback){
		
		if(!is_callable($callback)){
			throw new ArgumentException('callback', '不是一个有效的回调函数.');
		}
		
		if(!isset($this->_events[$eventType])){
			$this->_events[$eventType]=array();
		}
		$this->_events[$eventType][]=$callback;
	}
	
	/**
	 * 移除一个监听事件。
	 * 
	 * @param string 事件类型
	 * @param Closure 回调函数.函数原型: callback(object $sender,object $arg1,object $arg2...,object $argn)
	 */
	public function un($eventType,$callback=NULL){
		if(!isset($this->_events[$eventType])){
			return ;
		}
		if($callback!==NULL){
			for ($i=0;i<count($this->_events[$eventType]);$i++){
				if($this->_events[$eventType][$i]==$callback){
					unset($this->_events[$eventType][$i]);
				}
			}
		}
		else{
			unset($this->_events[$eventType]);
		}
	}
	
	/**
	 * 触发一个事件。
	 * 
	 * @param string 事件类型
	 * @param array/object[option] 参数
	 * @throws Exception
	 */
	protected function notify($eventType,$args=NULL){
		if(!isset($this->_events[$eventType])){
			throw new Exception('not a function');
		}
		foreach ($this->_events[$eventType] as $callback){
			if(is_callable($callback)){
				if(!$args){
					$args=array($this);
				}
				else{
					if(!is_array($args)){
						$args=array($this,$args);
					}
					else {
						$args=array_merge(array($this),$args);
					}
				}
				call_user_func_array($callback, $args);
			}
		}
	}
}
?>