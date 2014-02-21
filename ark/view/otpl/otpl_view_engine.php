<?php
namespace ark\view\otpl;
defined ( 'ARK' ) or exit ( 'access denied' );

class OtplViewEngine extends \ark\view\ViewEngine{
	
	public function __construct($app){
		parent::__construct($app);
		
	}
	
	public function render($filename){
		if(!(ark_endWith($filename, '.tpl') || ark_endWith($filename, '.tpl.html') || 
				ark_endWith($filename, '.otpl') || ark_endWith($filename, '.otpl.html')
		)){
			return FALSE;
		}
		
		
		
		$comliler=new Compiler($filename);
		
		
	}
}


?>