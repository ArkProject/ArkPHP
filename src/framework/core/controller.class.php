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
	 * 获取当前视图。
	 * @var \ark\Intent
	 */
	protected $intent;
	
	/**
	 * 初始化一个新的  Controller 实例。 
	 * @param \ark\Application $app
	 */
	public final function __construct($app){
		$this->app=$app;
		$this->intent=\Ark::getIntent();
		$routing=$this->app->getRouting();
		$action = '';
		
		//先检查是否存在指定请求的 action
		$prefix=$this->intent->getRequestMethod(TRUE);
		if (method_exists($this, $prefix.'_' . $routing['action'])) {
			$action = $prefix.'_' . $routing['action'];
		} else {
			$action = 'action_' . $routing['action'];
		}
		$this->onAction($action);
	}
	
	/**
	 * 执行  action 之前调用。
	 * @param string $action 要调用 action 方法名称。
	 * @throws \Exception
	 */
	protected function onAction($action){
		if (!method_exists($this, $action)) {
			throw new \Exception('未找到 action 。');
		}
		
		$result=$this->$action();
		
		if(!\Ark::debug()){
			ob_end_flush();
		}
		
		if(gettype($result)=='string'){
			echo $result;
		}
	}
	
	protected function View(){
		//ViewResult
	}
	
	
	
}