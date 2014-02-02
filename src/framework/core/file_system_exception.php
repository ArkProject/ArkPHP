<?php
namespace ark;
if (! defined ( 'ARK' )) {
	exit ( 'deny access' );
}
class FileSystemException extends \Exception{

	public function __construct($message=NULL,$innerException=NULL){
		if(!$message){
			$message='文件系统异常。';
		}
		parent::__construct($message, NULL, $innerException);
	}
}
?>