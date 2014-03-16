<?php
namespace ark\view;
defined ( 'ARK' ) or exit ( 'access denied' );

class ViewResult extends ActionResult{
	private $_filename;
	/**
	 * 
	 * @param \ark\view\ViewContext $view
	 * @param string $appName
	 * @param string $controllerName
	 * @param string $actionName
	 */
	public function __construct($view, $appName,$controllerName,$actionName){
		parent::__construct($view);
		$app=$this->getView()->app();
		$this->_filename=ark_combine(APP_ROOT, 'apps');
		$this->_filename=ark_combine($this->_filename, $appName);
		$this->_filename=ark_combine($this->_filename, 'views');
		$this->_filename=ark_combine($this->_filename, $controllerName);
		$this->_filename=ark_combine($this->_filename, $actionName);
		
		foreach (array('.tpl','.tpl.html','.otpl','.otpl.html','.php') as $ext){
			$file=$this->_filename.$ext;
			if(@file_exists($file)){
				$this->_filename=$file;
				return ;
			}
		}
		throw new \Exception('Open template file Failed,filename:'.$this->_filename);
	}
	
	public function Execute(){
		$engines=array('ark\view\otpl\OtplViewEngine');
		$app=$this->getView()->app();
		foreach ($engines as $engineClass){
			$engine=new $engineClass($app);
			if($engine instanceof \ark\view\ViewEngine){
				if($engine->render($this->getView(), $this->_filename,TRUE)){
					break;
				}
			}
		}
	}
}

?>