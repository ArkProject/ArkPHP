<?php
namespace ark\view;
defined ( 'ARK' ) or exit ( 'deny access' );

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
	 * 
	 * @param string $filename
	 */
	abstract public function render($filename);
	
}