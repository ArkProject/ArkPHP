<?php
namespace ark\dao;
defined ( 'ARK' ) or exit ( 'access denied' );



/**
 * 
 * @author jun
 *
 */
abstract class Provider{
	
	protected abstract function support($checkVersion=FALSE,$throw=TRUE);
	
	/**
	 * 
	 * @param unknown $config
	 * @param string $open
	 * @return resource
	 */
	public abstract function connect($config,$open=TRUE);
	
	public abstract function open($handle);
	
	public abstract function fetch($handle);

}




?>