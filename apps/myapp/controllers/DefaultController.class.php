<?php
namespace myapp\controllers;
use ark\Uri;
use ark\Controller;
use ark\view\otpl\OtplCompiler;

class View{
	public $data=array();
	public function bag($name){
		return $this->data[$name];
	}
	public function format($type,$format,$value){
		$type=trim($type);
		$timestamp=intval($value);
		if($timestamp<1){
			$timestamp=ark_datetimeParse($value);
		}
		if($type=='datetime'){
			return ark_datetimeFormat($timestamp, $format);
		}
		return $type.':'.$format.':'.$value;
	}
}

class DefaultController extends Controller{
	
	function onAction($action){
		//befor
		parent::onAction($action);
		//after
	}
	
	protected function action_index() {
		//ob_clean();
		//var_dump(get_included_files(),"<br>");
		//throw new Exception('gg');
		//die('fdgd');
		
		//$u=new Uri('http://usr:pwd@dom.com:8868/index.php?abc=u#f#f');
		//echo $u->toString();
		
		//$this->test();
		
		$this->view->hello='hello world';
		
		return  $this->view('test');
	}
	
	function test(){
		
		$view=new View();
		$view->data['now']=time();
		$view->data['hello']='hello world';
		
		$c=new OtplCompiler(null,APP_ROOT.'apps/myapp/views/test.tpl.html');
		$text=$c->compileToString();
		$path=APP_ROOT.'data/temp/c/1.php';
		file_put_contents($path, $text);
		include $path;
		return ;
		echo '编译结果：<br><hr><textarea style="width:100%; height:300px;">';
		echo $text;
		echo '</textarea>';
	
		echo $_SERVER['QUERY_STRING'];
	}
}