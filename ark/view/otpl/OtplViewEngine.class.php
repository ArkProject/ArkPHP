<?php
namespace ark\view\otpl;
defined ( 'ARK' ) or exit ( 'access denied' );

class OtplViewEngine extends \ark\view\ViewEngine{
	
	public function __construct($app){
		parent::__construct($app);
		
	}
	
	public function render($view,$content,$isFile=TRUE){
		if(!$isFile || !(ark_endWith($content, '.tpl') || ark_endWith($content, '.tpl.html') || 
				ark_endWith($content, '.otpl') || ark_endWith($content, '.otpl.html')
		)){
			return FALSE;
		}
		
		$entry_id='';
		$state=&$this->getCompiledState($content);
		$this->checkChange($state);
		if($state['compiled'] && $state['compiled']==$state['changed']){
			
			$entry_id=$state['uuid'];
		}
		else{
			$comliler=new OtplCompiler($this, $state);
			if(!$comliler->compile()){
				throw new \Exception('编译失败');
			}
			$entry_id=$comliler->entry();
			if(!$entry_id){
				throw new \Exception('禁止访问');
			}
			//$entry_id=APP_ROOT.'data/temp/c/'.$entry_id.'.php';
			//$state=$this->getCompiledState($content);
		}
		$view->initInternal();
		$view->mapInclude($state['inc_maps']);
		
		include ark_combine(APP_ROOT,'data/.ark/tpl/'. $entry_id .'.php');
	}
	
	public function &loadState(){
		$filename=ark_combine(APP_ROOT,'data/.ark/state.php');
		$state=NULL;
		if(@file_exists($filename)){
			$str=file_get_contents($filename);
			if($str){
				$state=unserialize($str);
				if(!is_array($state)){
					$state=NULL;
				}
			}
		}
		
		if(!$state){
			$state= array();
		}
		
		return $state;
	}
	
	public function saveState(&$state){
		if(!$state){
			throw new \Exception('错误的状态集');
		}
		$str=serialize($state);
		$filename=ark_combine(APP_ROOT,'data/.ark/state.php');
		file_put_contents($filename, $str);
	}
	
	private $state;
	public function &load(){
		if(!$this->state){
			$this->state=$this->loadState();
		}
		return $this->state;
	}
	
	public function saveCompiledState($state){
		$this->state['compiled_state']['refs'][$state['file']]=$state['uuid'];
		$this->state['compiled_state']['items'][$state['uuid']]=$state;
		$this->saveState($this->state);
	}
	
	public function checkChange(&$state){
		$target=ark_combine(APP_ROOT,'data/temp/c/'.$state['uuid'].'.php');
		if(!@file_exists($target) || !@file_exists($state['file'])){
			$state['changed']=NULL;
			return ;
		}
		
		$ltime=@filemtime($state['file']);
		if(!$ltime){
			throw new \Exception('获取文件时间错误');
		}
		if($ltime!=$state['changed']){
			$state['changed']=NULL;
			return ;
		}
	}
	
	public function &getCompiledState($filename){
		// 文件编译名称：compiled_id //不包含路径
		// 文件编译时间：complied_time
		// 模板原名称：orgin_filename //全路径
		// 是否是布局文件：is_master
		// 内容页地址（指针）：sub_complied_id
		// 是否是子页：is_sub
		// 父级页地址（指针）：master_compiled_id
		// 是否可作为部分页使用：allow_include
		
		$this->load();
		
		if(!isset($this->state['compiled_state'])){
			$this->state['compiled_state']=array();
		}
		if(!isset($this->state['compiled_state']['refs'])){
			$this->state['compiled_state']['refs']=array();
		}
		
		if(isset($this->state['compiled_state']['refs'][$filename])){
			$id=$this->state['compiled_state']['refs'][$filename];
			if(!isset($this->state['compiled_state']['items'][$id])){
				unset($this->state['compiled_state']['refs'][$filename]);
			}
			else{
				return $this->state['compiled_state']['items'][$id];
			}
		}
		
		$item=array();
		$item['uuid']=NULL;
		$item['file']=$filename;
		$item['compiled']=FALSE;
		$item['changed']=NULL;
		$item['entry_id']=NULL;
		$item['layout_id']=NULL;
		$item['inc_maps']=array();
		return $item;
	}
}


?>