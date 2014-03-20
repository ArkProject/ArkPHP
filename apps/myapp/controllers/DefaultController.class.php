<?php
namespace myapp\controllers;
use ark\Uri;
use ark\Controller;
use ark\view\otpl\OtplCompiler;

class DefaultController extends Controller{
	
	function onAction($action){
		//befor
		parent::onAction($action);
		//after
	}
	function filter($value){
		$value=mysql_real_escape_string($value);
		//echo $value;
		return $value;
	}
	
	
	
	protected function action_index() {
		header('Content-Type:text/html;charset=utf-8');
		
		$c=new \ark\view\Compiler();//plain
		$c->open('E:\repositories\ArkPHP\src\ArkPHP\apps\myapp\views\default\test.tpl.html');
		//$l=new \ark\view\Lexer();
		$c->compile();
		
		return '<br>end结束:'.ARK_ON_WIN;
		
		
		
		
		$this->view->hello='ff';
		return $this->view('test');
		
		$db=new \ark\dao\MysqlConnection(array (
						'provider' => 'ark\dao\mysql\MysqlProvider',
						'dbhost' => '127.0.0.1',
						'dbname' => 'test',
						'username' => 'root',
						'passwd' => 'rootroot',
						'encoding' => 'utf-8' 
				));
		$db->connect();
		$cmd=$db->insert('ark_test');
		$cmd->bind('name', 'ccxx\'');
		$cmd->bind('age', 18,FALSE);
		//$cmd->where('id=:id');
		//$cmd->bind('id', 4);
		var_dump($cmd->exec());
		//$hd=$db->connect();
		
		return  "<br>over";
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