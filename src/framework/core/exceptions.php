<?php

/**
 * 
 * @author jun
 *
 */
class ArgumentException extends Exception{
	
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

class FileSystemException extends Exception{

	public function __construct($message=NULL,$innerException=NULL){
		if(!$message){
			$message='文件系统异常。';
		}
		parent::__construct($message, NULL, $innerException);
	}
}

?>