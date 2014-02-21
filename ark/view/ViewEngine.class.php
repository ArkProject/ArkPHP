<?php
namespace ark\view;
defined ( 'ARK' ) or exit ( 'access denied' );

/**
 * 
 * @author jun
 *
 */
abstract class ViewEngine{
	protected $app;
	/**
	 * 
	 * @param \ark\Application $app
	 */
	public function __construct($app){
		$this->app=$app;
	}
	
	/**
	 * 向客户端渲染内容。
	 * @param \ark\view\ViewContext $view 视图上下文。
	 * @param string $content 内容。注意：如果 $isFile 为 TRUE 则该参数值应为绝对文件名。
	 * @param boolean $isFile 是否是文件。
	 */
	abstract public function render($view,$content,$isFile=TRUE);
	
}