<?php
namespace ark\view\otpl;
defined ( 'ARK' ) or exit ( 'access denied' );

class StreamReader{
	private $_filename;
	private $_handle;
	public function __construct($filename){
		$this->_filename=$filename;
	}
	
	public function open(){
		$this->_handle=fopen($this->_filename, 'r');
	}
	
	public function seek($offset,$whence = null){
		return fseek($this->_handle, $offset,$whence);
	}
	
	public function read(&$buffer,$offset=NULL,$length=NULL){
		$temp=fgets($this->_handle,$length);
	}
	
	function readLine(&$buffer,$offset=NULL) {
		$count=0;
		while (!feof($this->_handle)){
			$char=fread($this->_handle, 1);
			if($char===chr(10)){
				break;
			}
			else if($char===chr(13)){
				if(!feof($this->_handle)){
					$char=fread($this->_handle, 1);
					if($char!==chr(10)){
						fseek($this->_handle, -1,SEEK_CUR);
					}
				}
				
				break;
			}
			else{
				$count++;
				if($offset){
					$buffer[$offset++]=$char;
				}
				else{
					$buffer[]=$char;
				}
			}
		}
		return $count;
	}
	
	
	public function endOfStream(){
		return feof($this->_handle);
	}
	
}

abstract class Parser{
	
	/**
	 * 处理完成，可以获取结果。
	 * @var int
	 */
	const PARSE_DONE=1;
	/**
	 * 未处理，请继续。
	 * @var int
	 */
	const PARSE_CONTINUE=2;
	/**
	 * 处理失败。
	 * @var int
	 */
	const PARSE_FAILED=3;
	
	/**
	 * @var Compiler
	 */
	private $_compiler;
	protected $result;
	function __construct($compiler){
		$this->_compiler=$compiler;
	}
	
	/**
	 * 获取处理结果。
	 * @return string
	 */
	public function getResult(){
		return $this->result;
	}
	
	/**
	 * 获取当前编译器。
	 * @return Compiler
	 */
	public function getCompiler(){
		return $this->_compiler;
	}
	
	/**
	 * 解析标记表达式。
	 * @param 要解析的表达式。
	 * @return int
	 */
	public function parse($expr){
		return self::PARSE_CONTINUE;
	}
	
	public function toBuffer($expr){
		return ark_strToArray($expr);
	}
}


class VarParser extends Parser{
	
	function parse($expr){
		if(!preg_match('/^(\$|\#)/', $expr)){
			return self::PARSE_CONTINUE;
		}
		$compiler=$this->getCompiler();
		//$expr=ark_substr($expr, 0,ark_strlen($expr)-1);
		$expr=preg_replace('/\s*\|\s*(\w+)/', '@@@@$1', $expr);
		
		$arr=preg_split('/@@@@/', $expr);
		
		if(count($arr)==1){
			$buffer=$this->toBuffer($arr[0]);
			$this->result= '<?php echo '. $compiler->parseExpr($buffer) .'; ?>';
		}
		else if(count($arr)==2){
			$buffer=$this->toBuffer($arr[1]);
			$params=$compiler->getParams($buffer);
			$buffer=$this->toBuffer($arr[0]);
			if(count($params)>=1){
				$name=key($params);
				$this->result= '<?php echo $view->format(\''.$name.'\','.$params[$name].','. $compiler->parseExpr($buffer) .'); ?>';
			}
			else{
				$this->result= '<?php echo '. $compiler->parseExpr($buffer) .'; ?>';
			}
		}
		else{
			return self::PARSE_CONTINUE;
		}
		
		return parent::PARSE_DONE;
	}
}

class IfParser extends Parser{
	
	function parse($expr){
		$compiler=$this->getCompiler();
		if(preg_match('/^if\s*\(/', $expr)){
			$compiler->openBlock('if');
			$buffer=$this->toBuffer($expr);
			$this->result= '<?php '. $compiler->parseExpr($buffer) .'{ ?>';
			return parent::PARSE_DONE;
		}
		else if(preg_match('/^if\s/', $expr)){
			$compiler->openBlock('if');
			$expr=ark_substr($expr, 3);
			$buffer=$this->toBuffer($expr);
			$this->result= '<?php if( '. $compiler->parseExpr($buffer) .'){ ?>';
			return parent::PARSE_DONE;
		}
		else if(preg_match('/^elif\s*\(/', $expr)){
				
			if($compiler->getBlockCount('if')<=0){
				throw new \Exception('elif 必须与 if 配对使用');
			}
			$expr=preg_replace('/^elif\s/', '', $expr);
			$buffer=$this->toBuffer($expr);
			$this->result= '<?php else if '. $compiler->parseExpr($buffer) .'{ ?>';
			return parent::PARSE_DONE;
		}
		else if(preg_match('/^elif\s/', $expr)){
			
			if($compiler->getBlockCount('if')<=0){
				throw new \Exception('elif 必须与 if 配对使用');
			}
			$expr=preg_replace('/^elif\s/', '', $expr);
			$buffer=$this->toBuffer($expr);
			$this->result= '<?php }else if( '. $compiler->parseExpr($buffer) .'){ ?>';
			return parent::PARSE_DONE;
		}
		else if(preg_match('/^else\s*$/', $expr)){
				
			if($compiler->getBlockCount('if')<=0){
				throw new \Exception('elif 必须与 if 配对使用');
			}
				
			$this->result= '<?php }else{ ?>';
			return parent::PARSE_DONE;
		}
		else if(preg_match('/^\/\s*if/', $expr)){
				
			if($compiler->getBlockCount('if')<=0){
				throw new \Exception('/if 必须与 if 配对使用');
			}
			$compiler->closeBlock('if');
				
			$this->result= '<?php }?>';
			return parent::PARSE_DONE;
		}
		return parent::PARSE_CONTINUE;
	}
}

class ForParser extends Parser{

	function parse($expr){
		$compiler=$this->getCompiler();
		if(preg_match('/^for\s*\:/', $expr)){
			$compiler->openBlock('for');
			$expr=trim(preg_replace('/^for\s*\:/', '', $expr));
			
			$var='';
			$val='';
			if(preg_match('/^\w\s*\=/', $expr)){
				$buffer=$this->toBuffer($expr);
				$params=$compiler->getParams($expr);
				//var_dump($expr);
				//die();
				if(count($params)<2){
					throw new \Exception('for 表达式必须有2个变量');
				}
				$var=key($params);
				$val=$params[$var];
				
				if(!isset($params['step'])){
					$params['step']=1;
				}
				$this->result= '<?php $__iteration__'.$var.'=0; for($'.$var.'='.$val.';$'. $var .'<'.$params['max'].';'.$var.'+='.$params['step'].'){$__iteration__'.$var.'+=1; ?>';
				
			}
			else{
				$index=0;
				$buffer=$this->toBuffer($expr);
				$var=$compiler->getIdentifier($buffer,$index);
				$expr=trim(ark_substr($expr, $index+1));
				$params=$compiler->parseParams($expr);
				if(!isset($params['max'])){
					throw new \Exception('for 表达式未设置最大值。');
				}
				if(!isset($params['step'])){
					$params['step']=1;
				}
				$this->result= '<?php $__iteration__'.$var.'=0; for(;$'. $var .'<'.$params['max'].';'.$var.'+='.$params['step'].'){$__iteration__'.$var.'+=1; ?>';
			}
			
			return parent::PARSE_DONE;
		}
		else if(preg_match('/^\/\s*for/', $expr)){

			if($compiler->getBlockCount('for')<=0){
				throw new \Exception('/for 必须与  for 配对使用');
			}
			$compiler->closeBlock('for');

			$this->result= '<?php }?>';
			return parent::PARSE_DONE;
		}
		return parent::PARSE_CONTINUE;
	}
}
// extends \ark\view\Compiler
class OtplCompiler{
	private $_tplPath;
	private $_ifCount=0;
	protected $line=0;
	private $_literal=FALSE;
	private $_blocks=array();
	protected $result='';
	protected $state;
	protected $engine;
	public function __construct($engine, $state){
		$this->engine=$engine;
		$this->state=$state;
		$this->_tplPath=$state['file'];
		if(!$this->state['compiled'] || !$this->state['uuid']){
			$this->state['uuid']=ark_uuid();
		}
		
		$this->state['compiled']=@filemtime($this->_tplPath);
		if(!$this->state['compiled']){
			throw new \Exception('获取文件时间错误');
		}
	}
	
	/**
	 * 是否是标识符字符
	 * 0-9A-Za-z_
	 * @param unknown $char
	 * @param number $mode
	 * @return boolean
	 */
	protected function isIdentifierChar($char,$mode=0){
		
		if($mode==1){
			return $char>=chr(48) && $char<=chr(57);//0-9
		}
		else if($mode==2){
			return $char>=chr(65) && $char<=chr(90);//A-Z
		}
		else if($mode==3){
			return $char>=chr(97) && $char<=chr(122);//a-z
		}
		else if($mode==4){
			return $char==chr(95);//_
		}
		else if($mode==5){
			//0-9A-Za-z
			return ($char>=chr(48) && $char<=chr(57)) || ($char>=chr(65) && $char<=chr(90)) || ($char>=chr(97) && $char<=chr(122)) ;
		}
		else if($mode==6){
			//A-Za-z
			return ($char>=chr(65) && $char<=chr(90)) || ($char>=chr(97) && $char<=chr(122)) ;
		}
		else if($mode==7){
			//A-Za-z_
			return ($char>=chr(65) && $char<=chr(90)) || ($char>=chr(97) && $char<=chr(122)) || $char==chr(95);
		}
		else{
			//0-9A-Za-z_
			return ($char>=chr(48) && $char<=chr(57)) || ($char>=chr(65) && $char<=chr(90)) || ($char>=chr(97) && $char<=chr(122)) || $char==chr(95) ;
		}
		
		//http://baike.baidu.com/link?url=hkBBhFd4DFKmDIiScGYBGbW_UOKZTMylauon8m-stwWaqN0aoOHneJQnI6w_SUvM
		//return preg_match('/\w{1}/', $char);
	}
	
	/**
	 * 是否是空白字符 (空格或制表符)
	 * @param unknown $char
	 */
	public function isWhitespace($char){
		if($char==chr(32) || $char==chr(9)){
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * 跳过连续的空白字符 (空格或制表符)
	 * @param unknown $char
	 */
	public function skipWhitespace(&$buffer,&$start=0,$end=-1){

		if($end<0){
			$end=count($buffer);
		}
		while ($start<$end){
			if(!$this->isWhitespace($buffer[$start]) || ($start+1<$end && !$this->isWhitespace($buffer[$start+1]))){
				break;
			}
			$start++;
		}
	}
	
	/**
	 * 获取一个标识符。 \w
	 * @param unknown $expr
	 * @param number $start
	 * @param unknown $end
	 * @throws \Exception
	 * @return Ambigous <string, unknown>
	 */
	public function getIdentifier(&$buffer,&$start=0,$end=-1){
		$result='';
		if($end<0){
			$end=count($buffer);
		}
		$x=$start;
		for (;$start<$end;$start++){
			if(!$this->isIdentifierChar($buffer[$start])){
				break;
			}
			else {
				$result.=$buffer[$start];
			}
		}
		
		if(ark_strlen($result)===0 || !$this->isIdentifierChar($result[0],7)){ //TODO:后面的取第一个字符需要重做
			var_dump($buffer[$x]);
			die('||'.$result);
			throw new \Exception('语法错误：非法标识符。');
		}
		
		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ark\view\Compiler::findPairRight()
	 */
	protected function findPairRight(&$buffer,$start,$end,$left,$right){
		
		if($end<0){
			$end=count($buffer);
		}
		
		$m=0;
		for (;$start<$end;$start++){
				
			if($buffer[$start]==chr(92)){ //转义：\
				$start++;
				continue;
			}
			
			if($buffer[$start]==$right && $m==0){
				return $start;
			}
			else if($buffer[$start]==$right){
				$m--;
			}
			else if($buffer[$start]==$left){
				$m++;
			}
		}
		//var_dump($end);
		//die('m:'.$end);
		return -1;
	}
	
	protected function getString(&$buffer,&$start=0,$end=-1,$quoteAscii=39){
		
		if($end<0){
			$end=count($buffer);
		}
		$result='';
		for (;$start<$end;$start++){
				
			if($buffer[$start]==chr(92)){ // \
				$result.=$buffer[$start];
				$result.=$buffer[$start+1];
				$start++;
				continue;
			}
			else if($buffer[$start]==chr($quoteAscii)){ //'单引号 39 双引号"34
				return $result;
			}
			else{
				$result.=$buffer[$start];
			}
		}
		throw new \Exception('语法错误：字符串示结束'.$result);
	}
	
	/**
	 * 获取子串到空白字符
	 * @param unknown $expr
	 * @param number $start
	 * @param unknown $end
	 * @return string|Ambigous <string, unknown>
	 */
	protected function getToWhitespace(&$buffer,&$start=0,$end=-1){

		if($end<0){
			$end=count($buffer);
		}
		$result='';
		for (;$start<$end;$start++){
			if($this->isWhitespace($buffer[$start])){
				$start--;
				return $result;
			}
			else{
				$result.=$buffer[$start];
			}
		}
		return $result;
	}
	
	/**
	 * 
	 * @param unknown $expr
	 * @param number $start
	 * @param unknown $end
	 * @throws \Exception
	 * @return multitype:string unknown Ambigous <string, unknown>
	 */
	public function getParams(&$buffer,&$start=0,$end=-1){
		if($end<0){
			$end=count($buffer);
		}
		$name='';
		$value='';
		$result=array();
		
		for (;$start<$end;$start++){
			$this->skipWhitespace($buffer,$start,$end);
			
			$name=$this->getIdentifier($buffer,$start,$end);
			
			$this->skipWhitespace($buffer,$start,$end);
			if($start<$end && $buffer[$start]!=chr(61)){//=
				throw new \Exception('语法错误：错误的参数表达式'.$buffer[$start]);
			}
			$start++;
			if($start>=$end){
				break;
			}
			if($buffer[$start]==chr(40)){ //(=40 )=41
				$last=$this->findPairRight($buffer,$index+1,$end, chr(40), chr(41));
				if($last===-1){
					throw new \Exception('语法错误：括号不匹配');
				}
				$start++;
				$result[$name]=$this->parseExpr($buffer,$start,$last);
			}
			else if($buffer[$start]==chr(39) || $buffer[$start]==chr(34)){ //'39 " 34
				$ascii=$buffer[$start]==chr(39)?39:34;
				$start++;
				$result[$name]=chr($ascii). $this->getString($buffer,$start,$end,$ascii) .chr($ascii);
			}
			else {
				$value=$this->getToWhitespace($buffer,$start,$end);
				$result[$name]=$this->parseExpr($value);
				//die('='.$result[$name]);
			}
			$name='';
			$value='';
		}
		return $result;
	}
	
	public function parseExpr(&$buffer,&$start=0,$end=-1){
		if($end<0){
			$end=count($buffer);
		}
		$result='';
		RESTART:
	
		for (;$start<$end;$start++){
			if($buffer[$start]==chr(40)){ //括号：(=40 )=41
				$last=$this->findPairRight($buffer,$start+1,$end, chr(40), chr(41));
				if($last===-1){
					throw new \Exception('语法错误：括号不匹配');
				}
				$start++;
				
				$result .='('.$this->parseExpr($buffer,$start,$last-1).')';
				
				$start=$last;
			}
			else if($buffer[$start]=='['){//方括号：
				throw new \Exception('未实现');
			}
			else if($buffer[$start]==chr(39) || $buffer[$start]==chr(34)){ //'39 " 34
				$ascii=$buffer[$start]==chr(39)?39:34;
				$start++;
				$result .=chr($ascii). $this->getString($buffer,$start,$end,$ascii) .chr($ascii);
			}
			else if($buffer[$start]==chr(46)){ //点：.=46
				$start++;
				$this->skipWhitespace($buffer,$start,$end);
				$result .='->'.$this->getIdentifier($buffer,$start,$end);
			}
			else if($buffer[$start]==chr(36)){//美元号：$=36
				$start++;
				$this->skipWhitespace($buffer,$start,$end);
				$result .='$view->__get(\''. $this->getIdentifier($buffer,$start,$end) .'\')';
				goto RESTART; //不能直接循环，应为循环会做一个 $start++ 操作 
			}
			else if($buffer[$start]==chr(35)){//井号：#=35
				$start++;
				$this->skipWhitespace($buffer,$start,$end);
				$result .='$'. $this->getIdentifier($buffer,$start,$end);
				goto RESTART; //不能直接循环，应为循环会做一个 $start++ 操作
			}
			else if($buffer[$start]=='@'){//地址号：@=
				$start++;
				$this->skipWhitespace($buffer,$start,$end);
				$result .='$view->call['. $this->getIdentifier($buffer,$start,$end) .']';
			}
			else{
				$result.=$buffer[$start];
			}
		}
	
		return $result;
	}
	
	protected function processToken($matches){
		if(!$matches || !isset($matches[1])){
			return '';
		}
	
	
		//var_dump($matches);
		//$result='';
		$expr=trim($matches[1]);
		if($this->_literal===TRUE && preg_match('/\/\s*literal/i',$expr)){
			$this->_literal=FALSE;
			return '';
		}
		else if($this->_literal===FALSE && preg_match('/literal/i',$expr)){
			$this->_literal=TRUE;
			return '';
		}
	
	
		if($this->_literal===TRUE){
			return $matches[0];
		}
	
		$filters=array();
		$filters[]=new VarParser($this);
		$filters[]=new IfParser($this);
		$filters[]=new ForParser($this);
	
		foreach ( $filters as $parser ) {
			$result = $parser->parse ( $expr );
			if ($result == Parser::PARSE_CONTINUE) {
				continue;
			} else if ($result == Parser::PARSE_DONE) {
				return $parser->getResult ();
				break;
			} else {
				throw new \Exception ( '解析：' . $result );
			}
		}
		return ' UNKOWN TOKEN( '. $expr .')';
	}
	
	protected function compileLine($line){
		$this->result.= preg_replace_callback('/\{\s*(.*)\s*\}/i', array($this,'processToken'), $line).chr(13).chr(10);
	}
	
	public function getBlockCount($token){
		if(!isset($this->_blocks[$token])){
			$this->_blocks[$token]=0;
		}
		return $this->_blocks[$token];
	}
	/**
	 * 打开一个块标签
	 * @param unknown $token
	 */
	public function openBlock($token){
		if(!isset($this->_blocks[$token])){
			$this->_blocks[$token]=0;
		}
		$this->_blocks[$token]=$this->_blocks[$token]+1;
	}
	/**
	 * 关闭一个块标签
	 * @param unknown $token
	 */
	public function closeBlock($token){
		if(!isset($this->_blocks[$token])){
			$this->_blocks[$token]=0;
		}
		$this->_blocks[$token]=$this->_blocks[$token]-1;
	}
	
	public function compile(){
		if($this->state['compiled'] && $this->state['compiled']==$this->state['changed']){
			return TRUE;
		}
		$reader = new StreamReader ( $this->_tplPath );
		$reader->open ();
		
		while ( ! $reader->endOfStream () ) {
			$buffer = array ();
			if ($reader->readLine ( $buffer ) == 0) {
				$this->_line ++;
				continue;
			} else {
				$this->_line ++;
				$this->compileLine ( implode ( '', $buffer ) );
			}
		}
		$this->state['changed']=$this->state['compiled'];
		//$this->state['can_entry']=TRUE;
		//$this->state['entry_id']=NULL;
		
		$path=ark_combine(APP_ROOT,'data/.ark/tpl/');
		if(!is_dir($path)){
			if(!mkdir($path,'0755',TRUE)){
				throw new \Exception('创建目录失败');
			}
		}
		
		$path=ark_combine($path,$this->state['uuid'].'.php');
		file_put_contents($path, $this->result);
		$this->engine->saveCompiledState($this->state);
		return TRUE;
	
	}
	
	public function entry($id=NULL){
		if(!$id){
			if($this->state['entry_id']){
				return $this->state['entry_id'];
			}
			return $this->state['uuid'];
		}
		else{
			$this->state['entry_id']=$id;
		}
	}
	
}
?>