<?php
namespace ark;
if (! defined ( 'ARK' )) {
	exit ( 'deny access' );
}
/**
 * 
 * @author jun
 *
 */
class ArgumentException extends \Exception{
	
	public function __construct($paramName=NULL,$message=NULL,$innerException=NULL){
		if(!$message){
			$message='参数异常。';
		}
		if($paramName){
			$message .=' 参数名：'.$paramName;
		}
		parent::__construct($message, NULL, $innerException);
	}
}
?>