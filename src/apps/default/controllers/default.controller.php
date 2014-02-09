<?php

class DefaultController extends ark\Controller{
	
	function onAction($action){
		//befor
		parent::onAction($action);
		//after
	}
	
	protected function action_index() {
		//ob_clean();
		//var_dump(get_included_files());
		//throw new Exception('gg');
		
		$this->test();
		
		
		return  'hello word';
	}
	
	function test(){
		$c=new \ark\template\Compiler(ARK_PATH.'../apps/default/views/test.tpl.html');
		$text=$c->compileToString();
		echo '编译结果：<br><hr><textarea style="width:100%; height:300px;">';
		echo $text;
		echo '</textarea>';
	
		echo $_SERVER['QUERY_STRING'];
	}
}