<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'access denied' );
/**
 * 
 * @author jun
 *
 */
class ArgumentException extends \Exception{
	
	public function __construct($paramName=NULL,$message=NULL,$innerException=NULL){
		if(!$message){
			$message=ark_lang('__ARK_ARGUMENT_EXCEPTION');
		}
		if($paramName){
			$message .=ark_lang('__ARK_ARGUMENT_EXCEPTION_NAME', $paramName);
		}
		parent::__construct($message, NULL, $innerException);
	}
}
?>