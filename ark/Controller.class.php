<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'access denied' );

abstract class Controller{
	/**
	 * 获取当前应用程序。
	 * @var \ark\Application
	 */
	protected $app;
	/**
	 * 获取当前视图上下文。
	 * @var \ark\view\ViewContext
	 */
	protected $view;
	/**
	 * 初始化一个新的  Controller 实例。 
	 * @param \ark\Application $app
	 */
	public final function __construct($app){
		$this->app=$app;
		$action = '';
		
		//先检查是否存在指定请求的 action
		$prefix=$this->app->getRequestMethod(TRUE);
		if (method_exists($this, $prefix.'_' . $this->app->getActionName())) {
			$action = $prefix.'_' . $this->app->getActionName();
		} else {
			$action = 'action_' . $this->app->getActionName();
		}
		
		$this->view=new \ark\view\ViewContext($app);
		
		$this->onAction($action);
	}
	
	/**
	 * 执行  action 之前调用。
	 * @param string $action 要调用 action 方法名称。
	 * @throws \Exception
	 */
	protected function onAction($action){
		if (!method_exists($this, $action)) {
			throw new \Exception('未找到 action 。'.$action);
		}
		
		$result=$this->$action();
		
		if(!\ark\Runtime::debug()){
			ob_end_flush();
		}
		
		if($result instanceof \ark\view\ActionResult){
			$result->Execute();
		}
		else if(is_scalar($result)){
			echo $result;
		}
	}
	
	protected function view($actionName=NULL,$controllerName=NULL,$appName=NULL){
		if(!$actionName){
			$actionName=$this->app->getActionName();
		}
		if(!$controllerName){
			$controllerName=$this->app->getControllerName();
		}
		if(!$appName){
			$appName=$this->app->getApplicationName();
		}
		return new \ark\view\ViewResult($this->view,$appName, $controllerName, $actionName);
	}
	
	protected function file($filename,$include=FALSE,$mime=NULL,$charset=NULL){
		
	}
	
	protected function content($text,$mime=NULL,$charset=NULL){
		
	}
	
	protected function json($data=NULL,$charset=NULL){
		
	}
	
	protected function xml($data=NULL,$charset=NULL){
	
	}
	
	protected function javascript($script,$charset=NULL){
		
	}
	
	protected function redirect($url){
		
	}
	
	protected function redirectToAction($actionName,$controllerName=NULL,$appName=NULL){
		
	}
}

/*下面的类未按规定存放是为了 提高PHP解释效率*/
namespace ark\view;

final class ViewContext{
	private $_app;
	private $_viewData;
	private $_viewFuncs;
	public function __construct($app){
		$this->_app=$app;
		$this->_viewData=array();
		$this->_viewFuncs=array();
	}
	/**
	 * @return \ark\Application
	 */
	public function app(){
		return $this->_app;
	}
	
	/**
	 * 内部初始化，主要是向上下初始化一个默认视图变量，如:now等。
	 * 需要在render前调用该方法。
	 */
	public function initInternal(){
		$this->_viewData['now']=time();
	}
	
	public function __get($name){
		if(in_array($name,array('app','view'))){
			return ;
		}
		return $this->_viewData[$name];
		
		if(isset($this->_viewData[$name])){
			return $this->_viewData[$name];
		}
	}
	
	public function __set($name,$value){
		if(in_array($name,array('app','view'))){
			throw new \Exception('索引不能为关键字.');
		}
		$this->_viewData[$name]=$value;
	}
	
	public function mapInclude(){
		
	}
	
	public function format($type,$format,$value){
		$type=trim($type);
		$timestamp=intval($value);
		if($timestamp<1){
			$timestamp=ark_datetimeParse($value);
		}
		if($type=='datetime'){
			return ark_datetimeFormat($timestamp, $format);
		}
		return $type.':'.$format.':'.$value;
	}
}

abstract class ActionResult{
	private $view;
	/**
	 * 
	 * @param \ark\view\ViewContext $view
	 */
	public function __construct($view){
		$this->view=$view;
	}
	
	/**
	 * @return \ark\view\ViewContext
	 */
	public function getView() {
	 	return $this->view;
	}
	
	public abstract function Execute();
}


