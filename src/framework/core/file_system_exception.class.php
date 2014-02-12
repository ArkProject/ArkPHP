<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'access denied' );
class FileSystemException extends \Exception{

	public function __construct($message=NULL,$filename=NULL,$innerException=NULL){
		if(!$message){
			$message=ark_lang('__ARK_FILESYSTEM_EXCEPTION',$filename);
		}
		parent::__construct($message, NULL, $innerException);
	}
}
?>