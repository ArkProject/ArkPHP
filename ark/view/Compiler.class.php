<?php
namespace ark\view;
defined ( 'ARK' ) or exit ( 'access denied' );

/**
 * 表示模板编译异常。
 * @author jun
 *
 */
class TemplateCompileException extends \Exception{
	public function __construct($message = NULL, $previous = NULL,$code=NULL){
		parent::__construct($message, $code=NULL, $previous);
	}

	public function setLine($lineno){
		$this->line=$lineno;
	}
	public function setFile($filename){
		$this->file=$filename;
	}
}

//http://msdn.microsoft.com/zh-cn/library/system.linq.expressions(v=vs.110).aspx
abstract class Parser{
	/**
	 * @var char 单引号
	 */
	const T_SQUOTE			="'";
	/**
	 * @var char 转义号
	 */
	const T_BSLASH			="\\";
	/**
	 * @var char 双引号
	 */
	const T_DQUOTE			='"';
	/**
	 * @var char 井号
	 */
	const T_SHARP			='#';
	/**
	 * @var char 点
	 */
	const T_DOT				='。';
	/**
	 * @var char 冒号
	 */
	const T_COLON			=':';
	/**
	 * @var char 逗号
	 */
	const T_COMMA			=',';
	/**
	 * @var char 美元符号
	 */
	const T_DOLLAR			='$';
	/**
	 * @var char AT符号
	 */
	const T_AT				='@';
	/**
	 * @var char 问号
	 */
	const T_QMARK			='?';
	/**
	 * @var char AND符号
	 */
	const T_AND				='&';
	/**
	 * @var char 分号
	 */
	const T_SEMICOLON		=';';
	/**
	 * @var char 星号
	 */
	const T_START			='*';
	/**
	 * @var char 竖线符号
	 */
	const T_VERT			='|';
	/**
	 * @var char 等于号
	 */
	const T_EQUAL			='=';
	/**
	 * @var char 空格号
	 */
	const T_SPACE			=' ';
	/**
	 * @var char 水平制表符
	 */
	const T_TAB				="\t";
	/**
	 * @var char 换行/新行
	 */
	const T_CR				="\r";
	/**
	 * @var char 回车 
	 */
	const T_LF				="\n";
	
	/**
	 * @var char 惊叹号，非
	 */
	const T_NOT				='!';
	/**
	 * @var char 减号
	 */
	const T_MINUS			='-';
	/**
	 * @var char 小于，尖左括号
	 */
	const T_LT				='<';
	/**
	 * @var char 大于，尖右括号
	 */
	const T_GT				='>';
	/**
	 * @var char 左大括号
	 */
	const T_OBRACE			='{';
	/**
	 * @var char 右大括号
	 */
	const T_CBRACE			='}';
	/**
	 * @var char 左方括号
	 */
	const T_OBRACKET		='[';
	/**
	 * @var char 右方括号
	 */
	const T_CBRACKET		=']';
	/**
	 * @var char 左括号
	 */
	const T_OPAREN			='(';
	/**
	 * @var char 右括号
	 */
	const T_CPAREN			=')';
	/**
	 * @var char 斜线
	 */
	const T_SLASH			='/';
	protected $lineno;
	/**
	 * @var Compiler 关联的编译器
	 */
	public $compiler;
	
	protected $locvars=array();
	protected $tmpvars=array();
	protected function error($msg){
		$err= new TemplateCompileException($msg);
		$err->setFile($this->compiler->filename);
		$err->setLine($this->lineno);
		throw $err;
	}
	
	protected function getInternalFunc($id){
		$funcs=array(
			'str'=>'str',
			'format'=>'$view->format'
		);
		if(isset($funcs[$id])){
			return $funcs[$id];
		}
		return FALSE;
	}
	
	/**
	 * 是否是数字
	 * @param unknown $char
	 * @return boolean
	 */
	function isDigital($char){
		//$char='a';
		//die('z:'.($char>=chr(48) && $char<=chr(57)));
		//$char=;
		//return $char>=48 && $char<=57;
		return ($char>=chr(48) && $char<=chr(57))===TRUE;
	}
	
	/**
	 * 是否是字母
	 * @param unknown $char
	 * @return boolean
	 */
	function isLetter($char){
		return ($char>=chr(65) && $char<=chr(90) || $char>=chr(97) && $char<=chr(122));
	}
	/**
	 * 是否是字母或数字
	 * @param unknown $char
	 * @return boolean
	 */
	function isAlphanumeric($char){
		return $this->isDigital($char) || $this->isLetter($char);
	}
	/**
	 * 是否是空白
	 * @param unknown $char
	 * @return boolean
	 */
	function isWhitespace($char){
		if($char===self::T_SPACE || $char===self::T_TAB){//
			return TRUE;
		}
		return FALSE;
	}
	
	
	/**
	 * 跳过指定范围
	 * @param unknown $input
	 * @param unknown $pos
	 * @param unknown $length
	 * @param unknown $token
	 * @param string $breakLineToken
	 * @return unknown|number
	 */
	function skipScope(&$input,&$pos,&$length,$char=self::T_SQUOTE, $breakLineToken=TRUE){
		
		while ($length>0 && isset($input[$pos])){
			if($input[$pos]===self::T_BSLASH){
				$pos++;
				$length--;
			}
			else if($input[$pos]===$char){
				return $pos;
			}
			else if($breakLineToken===TRUE && ($input[$pos]===self::T_CR || $input[$pos]===self::T_LF)){
				return -1;
			}
			$pos++;
			$length--;
		}
		
		return -1;
	}
	
	
	
	/**
	 * 跳过空白
	 * @param unknown $input
	 * @param unknown $pos
	 * @param unknown $length
	 */
	function skipWhitespace(&$input,&$pos,&$length){
		while ($length>0 && isset($input[$pos])){
			if (!$this->isWhitespace($input[$pos])){
				return ;
			}
			$pos++;
			$length--;
		}
	}
	
	/**
	 * 跳过左右的空白
	 * @param unknown $input
	 * @param unknown $pos
	 * @param unknown $length
	 */
	function trimWhitespace(&$input,&$pos,&$length){
		while ($length>0 && isset($input[$pos])){
			if (!$this->isWhitespace($input[$pos])){
				break ;
			}
			$length--;
		}
		$this->skipWhitespace($input, $pos, $length);
	}
	
	function getSomething(&$input,$pos,$length){
		$result='';
		while ($length>0 && isset($input[$pos])){
			if($this->isWhitespace($input[$pos])){
				break;
			}
			$result.=$input[$pos];
			$pos++;
			$length--;
		}
		return $result;
	}
	
	/**
	 * 读取标识符
	 * @param unknown $input
	 * @param unknown $pos
	 * @param unknown $length
	 * @return boolean
	 */
	function readIdentifier(&$input,&$pos,&$length){
		$id='';
		$this->skipWhitespace($input, $pos, $length);
		while ($length>0 && isset($input[$pos])){
			if($this->isAlphanumeric($input[$pos]) || $input[$pos]==='_'){
				$id.=$input[$pos];
			}
			else{
				break;
			}
			$pos++;
			$length--;
		}
		//die('x:'.$id);
		if(!$id || $id==='' || !$this->isLetter($id[0])){
			//die('input['.$input[$pos].']');
			return FALSE;
		}
		return $id;
	}
	function readNumber(&$input,&$pos,&$length){
		$num='';
		$this->skipWhitespace($input, $pos, $length);
		while ($length>0 && isset($input[$pos])){
			if($this->isDigital($input[$pos])){
				$num.=$input[$pos];
			}
			else if($this->assert($input, '.', $pos, $length)){
				$pos++;
				$length--;
				if(!$this->isDigital($input[$pos])){
					return FALSE;
				}
				$num.=$input[$pos];
			}
			else if($this->isWhitespace($input[$pos]) || in_array($input[$pos],array('<','>','(',')','|','=','+','-','*','/','%','&'))){
				break;
			}
			else{
				return FALSE;
			}
			$pos++;
			$length--;
		}
		return $num==''?FALSE:$num;;
	}
	function readScope(&$input,&$pos,&$length,$open,$close){
		$refs=0;
		$start=$pos;
		$this->skipWhitespace($input, $pos, $length);
		while ($length>0 && isset($input[$pos])){
			if($input[$pos]===self::T_BSLASH){
				$pos++;
				$length--;
			}
			else if($open===$close && $input[$pos]===$open){
				$refs++;
				if($refs==2){
					return $pos-$start;
				}
			}
			else if($input[$pos]===$open){
				$refs++;
			}
			else if($input[$pos]===$close){
				$refs--;
				if($refs==0){
					return $pos-$start;
				}
			}
			$pos++;
			$length--;
		}
		return -1;
	}
	
	function parse(&$input,&$pos,&$length){
		$code=NULL;
		$astart=$pos;
		$alen=$length;
		//die('l:'.($alen-($pos-$astart)).'/'.($length).'/'.$input);
		while ($length>0 && isset($input[$pos])){
			$this->skipWhitespace($input, $pos, $length);
			if($this->assert($input, '$', $pos, $length)){//本地变量
				$pos++;
				$length--;
				$id =$this->readIdentifier($input, $pos, $length);
				if(!$id){
					$this->error('syntax error,illegal identifier "'. $input[$pos] .'".');
				}
				$this->locvars[]=$id;
				$code.='$'.$id;
				if($this->assert($input, '(', $pos, $length)){
					$begin=$pos+1;
					$slen=$this->readScope($input, $pos, $length, '(', ')');
					if($slen<0){
						$this->error('syntax error,miss close characters ")".');
					}
					$cc=$this->parse($input, $begin, $slen);
					if($cc===FALSE){
						$this->error('syntax error,illegal arguments "'. $input[$begin] .'".');
					}
					$code.='('.$cc.')';
				}
				continue;
			}
			else if($this->assert($input, '.', $pos, $length)){//属性
				$pos++;
				$length--;
				$id =$this->readIdentifier($input, $pos, $length);
				if(!$id){
					$this->error('syntax error,illegal identifier "'. $input[$pos] .'".');
				}
				$code.='->'.$id;
				if($this->assert($input, '(', $pos, $length)){
					$begin=$pos+1;
					$slen=$this->readScope($input, $pos, $length, '(', ')');
					if($slen<0){
						$this->error('syntax error,miss close characters ")".');
					}
					$cc=$this->parse($input, $begin, $slen);
					if($cc===FALSE){
						$this->error('syntax error,illegal arguments "'. $input[$begin] .'".');
					}
					$code.='('.$cc.')';
				}
			}
			else if($this->assert($input, '[', $pos, $length)){ //索引
				$begin=$pos+1;
				$slen=$this->readScope($input, $pos, $length, '[', ']');
				if($slen<0){
					$this->error('syntax error,miss close characters "]".');
				}
				$cc=$this->parse($input, $begin, $slen);
				if($cc===FALSE){
					$this->error('syntax error,illegal index "[".');
				}
				$code.='['.$cc.']';
			}
			else if($this->assert($input,self::T_SQUOTE, $pos, $length)){ //字符串
				
				$begin=$pos;
				$slen=$this->readScope($input, $pos, $length, self::T_SQUOTE, self::T_SQUOTE);
				
				if($slen<0){
					$this->error('syntax error,illegal strings "\'".');
				}
				$code.=self::T_SQUOTE. ark_substrBC($input, $begin+1,$slen-1) .self::T_SQUOTE;
				//die('here:'.$length);
			}
			else if($this->assert($input,'++', $pos, $length)){ //逻辑？
				$code.='++';
			}
			else if($this->assert($input,'--', $pos, $length)){ //逻辑？
				$code.='--';
			}
			else if($this->assert($input,'||', $pos, $length)){ //逻辑？
				$code.='||';
			}
			else if($this->assert($input,'&&', $pos, $length)){ //逻辑？
				$code.='&&';
			}
			else if($this->assert($input,'==', $pos, $length)){ //逻辑？
				$pos++;
				$length--;
				$code.='==';
			}
			else if($this->assert($input,'!=', $pos, $length)){ //逻辑？
				$code.='!=';
			}
			else if($this->assert($input,'<=', $pos, $length)){ //逻辑？
				$code.='<=';
			}
			else if($this->assert($input,'<', $pos, $length)){ //逻辑？
				$code.='<';
			}
			else if($this->assert($input,'>=', $pos, $length)){ //逻辑？
				$code.='>=';
			}
			else if($this->assert($input,'>', $pos, $length)){ //逻辑？
				$code.='>';
			}
			else if($this->assert($input,'|', $pos, $length) || $this->assert($input,';', $pos, $length)){ //语句分界
				break;
			}
			else if($this->isDigital($this->peek($input, $pos, $length))){ //数字
				$begin=$pos;
				$num=$this->readNumber($input, $pos, $length);
				if(!$num){
					$this->error('syntax error,illegal characters2 "'. ($this->peek($input, $begin, $length)) .'".');
				}
				$code.=$num;
				continue;
			}
			else if($this->isLetter($this->peek($input, $pos, $length))){ //字母
				//
				$begin=$pos;
				$alen=$length;
				$id=$this->readIdentifier($input, $pos, $length);
				if(!$id){
					$this->error('syntax error,illegal identifier "'. $input[$begin] .'".');
				}
				
				if($this->assert($input, '==', $pos, $length)){ //如果遇到参数分界
					$this->tmpvars[]=$id;
					$code.='$view->data[\''.$id.'\']';//die('here:'.$length);;
					continue;
				}
				else if($this->assert($input, '=', $pos, $length)){ //如果遇到参数分界
					
					$pos=$begin;
					$length=$alen;
					break;
				}
				if($this->assert($input, '(', $pos, $length)){
					$begin=$pos+1;
					$slen=$this->readScope($input, $pos, $length, '(', ')');
					if($slen<0){
						$this->error('syntax error,miss close characters ")".');
					}
					$cc=$this->parse($input, $begin, $slen);
					if($cc===FALSE){
						$this->error('syntax error,illegal arguments "'. $input[$begin] .'".');
					}
					//检查函数
					$func=$this->getInternalFunc($id);
					if($func){
						$code.=$func;
					}
					else{
						$this->tmpvars[]=$id;
						$code.='$view->data[\''.$id.'\']';
					}
					$code.='('.$cc.')';
				}
				else {
					$this->tmpvars[]=$id;
					$code.='$view->data[\''.$id.'\']';
				}
			}
			else if($length>0){
				$this->error('syntax error,illegal characters3 "'. $input[$pos] .'".');
			}
			if($length>0){
				$pos++;
				$length--;
			}
		}
		return $code;
	}
	function parseValue(&$input,&$pos,$length){
		$tlen=$pos+$length;
		$this->skipWhitespace($input, $pos, $length);
		while ($pos<$tlen){
			if($this->assert($input, '$', $pos, $length)){
				$id.=$input[$pos];
			}
			else{
				break;
			}
			$pos++;
		}
		if(!$id || $id==='' || !$this->isLetter($id[0])){
			//die('input['.$input.']');
			return FALSE;
		}
	}
	
	function assert(&$input,$tag,$pos,$length){
		return ark_strcomBC($input, $tag,$pos,$length);
	}
	
	function lastAssert(&$input,$tag,$length){
		return ark_strcomBC($input, $tag,$length-ark_strlenBC($tag),$length);
	}
	
	function peek(&$input,$pos,$length){
		if($length>0 && isset($input[$pos])){
			return $input[$pos];
		}
		return NULL;
	}
	
	protected function parseAll(){
	
	}
	
	public function compile(){
	
	}
}

// class Parser extends TemplateBase{

// 	/**
// 	 *
// 	 * @var Compiler
// 	 */
// 	public $compiler;

// 	public function __construct($compiler){
// 		$this->compiler=$compiler;
// 	}

// 	/**
// 	 * Lexer
// 	 * @param \ark\view\Lexer $current
// 	 * @param string $input
// 	 * @param unknown $pos
// 	 * @param unknown $length
// 	 * @param string $allToken
// 	 * @throws TemplateCompileException
// 	 * @return \ark\view\Lexer
// 	 */
// 	public function parse(&$input,$start,$length,$allToken=FALSE){
		
// 	}

// 	//{$var.mem (arg) | format}
// 	public function parseTempvar($current, &$input,$pos,$length){
// 		$current->append();
// 		//val lex => var->mem('arg')
// 		//format lex =>call {arg=>['arg',func=val]}
// 		//=>echo format('func',val,'y')
// 		//KEY(文本|输出模板变量|输出临时变量|输出函数调用|函数调用|标签函数|条件|循环|)
// 		//
// 	}
// }

/**
 * 表示一个词条
 * @author jun
 *
 */
abstract class Lexer extends Parser{

	/**
	 * @var Lexblock 当前词条的容器。
	 */
	public $container;
	protected $start;
	protected $length;
	public $name;
	//public $code;
	
	public function __construct($compiler,$start,$length,$name){
		$this->compiler=$compiler;
		$this->lineno=$compiler->lineno;
		$this->start=$start;
		$this->length=$length;
		$this->name=$name;
		
		$this->parseAll();
	}
	
	
}

/**
 * 表示一个块词条
 * @author jun
 *
 */
abstract class Lexblock extends Lexer{
	public $vars=array();
	protected $items=array();
	public function copyTo($block){
		
	}
	public $closed;
	protected $accpteCloseLexerNames=array();
	
	public function append($lex,$direct=FALSE){
// 		if($lex->name=='endif'){
// 			var_dump($this->accpteCloseLexerNames);
// 			die();
// 		}
		if(!$direct && in_array($lex->name, $this->accpteCloseLexerNames)){//
			
			$this->closed=TRUE;//die('here go:'.$this->name);!$direct && 
			return $this->container->append($lex,TRUE);
		}
// 		if($lex instanceof Lexcloser && $lex->name==$this->name){//
// 			$this->closed=TRUE;//die('here go:'.$this->name);
// 			return $this->container;
// 		}
// 		if($this->closed){
// 			$this->container->append($lex);
// 			return $this->container;
// 		}
		else{
			
			$lex->container=$this;
			$this->items[]=$lex;
			return $lex instanceof Lexblock ? $lex :$this;
		}
	}
	
	public function compile(){
		
		$code='';
		foreach ($this->items as $lex){//die('here:'.count($this->items));
			$code.=$lex->compile();
			//echo $code;
		}
		return $code;
	}
}
/**
 * 表示一个单词条
 * @author jun
 *
 */
abstract class Lexspan extends Lexer{
	
}
/**
 * 表示根词条
 * @author jun
 *
 */
class Lexroot extends Lexblock{
	function __construct($compiler){
		parent::__construct($compiler, 0, 0,'root');
	}
}
/**
 * 表示一个块注释
 * @author jun
 *
 */
class Lexcomment extends Lexblock{
	function __construct($compiler, $start, $length, $name){
		$this->accpteCloseLexerNames=array('endcomment');
		parent::__construct($compiler, $start, $length, $name);
	}
	public function compile(){
		return '';
	}
}
/**
 * 表示一个块的结束
 * @author jun
 *
 */
class Lexcloser extends Lexspan{
	
	function compile(){
		return '<?php } ?>';
	}
}

/**
 * 纯文本。
 * @author jun
 *
 */
class Lexplain extends Lexspan{
	function compile(){
		//die('bababqab:'.ark_substrBC($this->compiler->input, $this->start,$this->length));
		return ark_substrBC($this->compiler->input, $this->start,$this->length);
	}
}

/**
 * 打印。
 * @author jun
 *
 */
class Lexprint extends Lexspan{
	private $_code;
	private $identifier;
	private $_lazyChecks=array();
	private $_lazyCheckSysfuncs=array();
	function __construct($compiler,$start,$length,$identifier=NULL){
		$this->identifier=$identifier;
		parent::__construct($compiler, $start, $length,$identifier);
	}
	
	function parseAll(){
		$astart=$pos=$this->start;
		$alen=$length=$this->length;
		$this->_code=$this->parse($this->compiler->input, $pos, $length);//die('kkk:'. ($length-($pos-$astart)));
		
		//$pos++;
		$this->skipWhitespace($this->compiler->input, $pos, $length);
		
		//格式化参数
		if($this->assert($this->compiler->input, '|', $pos, $length)){
			$pos++;
			$length--;
			$this->skipWhitespace($this->compiler->input, $pos, $length);
			//格式化参数
			
			$id=$this->readIdentifier($this->compiler->input, $pos, $length);
			
			if(!$id || empty($id)){
				$err= new TemplateCompileException('syntax error,illegal identifier2 "'. $this->compiler->input[$pos] .'".');
				$err->setFile($this->compiler->filename);
				$err->setLine($this->compiler->lineno);
				throw $err;
			}
			//die('debug:'.$id);
			$this->_code ='$view->format(\''.$id.'\','.$this->_code;
			
			$this->skipWhitespace($this->compiler->input, $pos, $length);
			if($this->assert($this->compiler->input, '=', $pos, $length)){
				$pos++;
				$length--;
				
				$this->skipWhitespace($this->compiler->input, $pos, $length);
				
				$cc=$this->parse($this->compiler->input, $pos, $length);
				if(!$cc){
					$this->error('syntax error,forgot set a values? near "=".');
				}
				$this->_code.=','.$cc;
			}
			$this->_code.=')';
			
		}
		$this->skipWhitespace($this->compiler->input, $pos, $length);
		if($this->peek($this->compiler->input,$pos, $length)){
			$err= new TemplateCompileException('syntax error,unidentified characters "'. $this->compiler->input[$pos] .'".');
			$err->setFile($this->compiler->filename);
			$err->setLine($this->compiler->lineno);
			throw $err;
		}
		$this->_code.=';';
	}
	
	function compile(){
		//die(1);
		return '<?php echo '.$this->_code.' ?>';
	}
}


class Lexif extends Lexblock{
	private $_code;
	
	function __construct($compiler, $start, $length, $name){
		$this->accpteCloseLexerNames=array('endif','elseif','else');
		parent::__construct($compiler, $start, $length, $name);
	}
	
	function parseAll(){
		$pos=$this->start;
		$length=$this->length;//die('bb:'.ark_substrBC($this->compiler->input, $pos, $length));
		$expr=$this->parse($this->compiler->input, $pos, $length);
		if(!$expr){
			$this->error('last expr, near '.$this->name.' | '.$expr);
		}
		$this->_code=$expr;
	}
	function compile(){
		$code='<?php if('.$this->_code.'){ ?>';
		foreach ($this->items as $lex){//die('here:'.count($this->items));
			$code.=$lex->compile();
		}
		if(!$this->closed){
			$this->error('未关闭');
		}
		return $code;
	}
}

class Lexelseif extends Lexblock{
	private $_code;

	function __construct($compiler, $start, $length, $name){
		$this->accpteCloseLexerNames=array('endif','elseif','else');
		parent::__construct($compiler, $start, $length, $name);
	}
	function appends($lex,$direct=FALSE){
		if($lex->name=='endif'){
			die('here');
		}
		parent::append($lex,$direct);
		
	}
	function parseAll(){
		$pos=$this->start;
		$length=$this->length;//die('bb:'.ark_substrBC($this->compiler->input, $pos, $length));
		$expr=$this->parse($this->compiler->input, $pos, $length);
		if(!$expr){
			$this->error('last expr, near '.$this->name.' | '.$expr);
		}
		$this->_code=$expr;
	}
	function compile(){
		$code='<?php elseif('.$this->_code.'){ ?>';
		foreach ($this->items as $lex){//die('here:'.count($this->items));
			$code.=$lex->compile();
		}
		if(!$this->closed){
			$this->error('未关闭');
		}
		return $code;
	}
}

class Lexelse extends Lexblock{
	private $_code;

	function __construct($compiler, $start, $length, $name){
		$this->accpteCloseLexerNames=array('endif','endfor');
		parent::__construct($compiler, $start, $length, $name);
	}

	function parseAll(){
		return ;
		$pos=$this->start;
		$length=$this->length;//die('bb:'.ark_substrBC($this->compiler->input, $pos, $length));
		$expr=$this->parse($this->compiler->input, $pos, $length);
		if(!$expr){
			$this->error('last expr, near '.$this->name.' | '.$expr);
		}
		$this->_code=$expr;
	}
	function compile(){
		$code='<?php else{ ?>';
		foreach ($this->items as $lex){//die('here:'.count($this->items));
			$code.=$lex->compile();
		}
		if(!$this->closed){
			$this->error('未关闭');
		}
		return $code;
	}
}

/**
 * 编译器
 * @author jun
 *
 */
class Compiler extends Parser{
	public $ldelimiter='{{';
	public $rdelimiter='}}';
	public $input;
	public $pos=0;
	public $length;
	public $filename;
	public $_isComment;
	public $encoding='utf-8';
	private $compilers=array(
			'if'=>'\ark\view\Lexif',
			'elseif'=>'\ark\view\Lexelseif',
			'else'=>'\ark\view\Lexelse'
	);
	/**
	 * 根节点
	 * @var Lexroot
	 */
	private $root;
	/**
	 * 当前
	 * @var Lexblock
	 */
	private $current;
	
	function open($filename){
		$this->input=file_get_contents($filename);
		//ark_removeUTF8Bom($this->input); //移除所有  UTF-8 BOM
		$this->length=ark_strlenBC($this->input);
		$this->filename=$filename;
		$this->lineno=1;
		$this->compiler=$this;
		
		$this->current= $this->root=new Lexroot($this);
		
	}
	
	
	/**
	 * 解析标签
	 * @param unknown $start
	 * @param unknown $length
	 * @param unknown $parsable
	 * @return void|\ark\view\Lexfunc|\ark\view\Lexprint|\ark\view\Lextempvar|unknown
	 */
	function parseMarkup($start,$length,$parsable){
		
// 		$tstr='\'abc\'';
// 		$alen=$tlen=ark_strlenBC($tstr);
// 		$astart=$tpos=0;
// 		$tr=$this->parse($tstr, $tpos, $tlen);
// 		die('l:('.$tr.')='.$alen.'/'.($alen-($tpos-$astart)).'/'.$tlen);
		if($length<1){
			return ;
		}
		$lex=NULL;
		if($parsable!==TRUE){
			$lex=new Lexplain($this,$start,$length,'plain');
			goto CHECK_LEX;
		}
		$pos=$start;
		$this->trimWhitespace($this->input, $pos, $length);
		if ($this->assert ( $this->input, '//', $pos, $length )) {
			$lex=NULL; //行注释
			goto CHECK_LEX;
		}
		else if ($this->assert ( $this->input, '/*', $pos, $length )) {
			$lex=new Lexcomment ($this, $pos, $length-($pos-$start),'comment');
			goto CHECK_LEX;
		}
		else if ($this->lastAssert ( $this->input, '*/', $pos+$length )) {
			$lex=new Lexcloser($this, $pos, $length-($pos-$start),'endcomment');
			goto CHECK_LEX;
		}
		else if ($this->assert ( $this->input, '$', $pos, $length )) {
			$lex= new Lexprint($this, $pos, $length-($pos-$start));
			goto CHECK_LEX;
		}
		else if ($this->assert ( $this->input, '@', $pos, $length )) {
			return new Lexfunc($this, $pos+1, $length-($pos-$start)-1);
		}
		else if ($this->assert ( $this->input, '/', $pos, $length )) {
			//block end
			$begin=$pos;
			$pos++;
			$length--;
			$id=$this->readIdentifier($this->input, $pos, $length);
			if(!$id){
				$this->error('syntax error,illegal identifier near "'. $this->getSomething($this->input, $begin, 5) .'".');
			}
			if($id=='if'){
				//die('fghf:'.$this->current->name);
			}
			$lex= new Lexcloser($this, $pos, $length, 'end'.$id);
			//die($lex->name);
			goto CHECK_LEX;
		}
		$begin=$pos;
		$len=$length;
		$id=$this->readIdentifier($this->input, $pos, $length);
		if(!$id){
			$this->error('syntax error,illegal identifier near "'. $this->getSomething($this->input, $begin, 5) .'".');
		}
		if(isset($this->compilers[$id])){
			$lex= new $this->compilers[$id]($this, $pos, $length,$id);
		}
		else{
			$lex= new Lexprint($this, $begin, $len);
		}
		goto CHECK_LEX;
		return ;
		
		CHECK_LEX:
		if($lex){
			$this->current=$this->current->append($lex);
		}
		
	}
	
	function beginComment(){
		$this->_isComment=TRUE;
		return $this;
	}
	function endComment(){
		$this->_isComment=FALSE;
		return $this;
	}
	
	/**
	 *开始解析
	 * @param Compiler $compiler
	 */
	function parseAll(){
		//die($this->input);
		$pos=$start=$this->pos;
		
		$this->skipWhitespace($this->input, $pos, $this->length);
		
		while ($pos<$this->length){
			if($this->input[$pos]===self::T_LT || $this->input[$pos]===self::T_SLASH || $this->input[$pos]===$this->ldelimiter[0]){
				
				$begin=0;
				$index=$this->findLeftDelimiter($this->input, $pos, $this->length, $this->input[$pos],$begin);
				//echo ( '|plan:('.$begin.','.($index-$begin).') ='.ark_substrBC($this->input, $begin,$index-$begin));
				if($index>-1){
					$lastIndex=0;
					$last=$this->findRightDelimiter($this->input, $index, $this->length, $this->input[$pos],$lastIndex);
					//die('last:'.$last);
					if($last<0){
						///die(ark_substrBC($this->input, $begin));
						$err= new TemplateCompileException('syntax error,missing close tag "'. $this->rdelimiter .'".');
						$err->setFile($this->filename);
						$err->setLine($this->lineno);
						throw $err;
					}
					$this->pos=$last;
					//die( 'plan:('.$start.','.($begin-$start).') ='.substr($this->input, $start,$begin-$start));
					$this->parseMarkup($start,$begin-$start,FALSE);
					//die( 'code:('.$last.','.($last-$lastIndex).') ='.substr($this->input, $index,$lastIndex-$index));
					$this->parseMarkup($index,$lastIndex-$index,TRUE);
					$pos=$start=$this->pos; //获取解析程序更改后的值（可能）
					continue;
				}
			}
			else if($this->input[$pos]===self::T_CR){
				// 忽略
			}else if($this->input[$pos]===self::T_LF){
				$this->lineno++;
			}
			$pos++;
		}
		
		//die( 'plan:('.$start.','.($pos-$start).') ='.substr($this->input, $start));//mb_substr($str, $start),$pos-$start
		//die($pos.'-'.$start.'='.($pos-$start).'/'.$this->length);
		if($pos-$start>0){
			$this->parseMarkup($start,$pos-$start,FALSE);
		}
	}
	
	
	/**
	 * 查找左边的分界符
	 * @param unknown $input
	 * @param unknown $pos
	 * @param unknown $length
	 * @param unknown $char
	 * @param number $start
	 * @return number
	 */
	function findLeftDelimiter(&$input,$pos,$length,$char,&$start=0){
		
		if($char===self::T_LT){
			$start=$pos;
			if(!ark_strcomBC($input, '!--',$pos+1,$length)){
				return -1;
			}
			$pos+=5;
			$this->skipWhitespace($input, $pos, $length);
		}
		else if($char===self::T_SLASH){
			$start=$pos;
			if(!ark_strcomBC($input, '*',$pos+1,$length)){
				return -1;
			}
			$pos+=2;
			$this->skipWhitespace($input, $pos, $length);
		}
		else if($char===$this->ldelimiter[0]){
			$start=$pos;
		}
		else{
			return -1;
		}
		
		if(!ark_strcomBC($input, $this->ldelimiter,$pos,$length)){
			return -1;
		}
		$pos+=ark_strlenBC($this->ldelimiter);
		//die($this->ldelimiter.'=='.substr($input,$pos,1).'>'.ark_strlenBC($this->ldelimiter));
		return $pos;
		
		
	}
	
	/**
	 * 查找右边的分界符
	 * @param unknown $input
	 * @param unknown $pos
	 * @param unknown $length
	 * @param unknown $char
	 * @param number $start
	 * @return number|unknown
	 */ 
	function findRightDelimiter(&$input,$pos,$length,$char,&$start=0){
		
		while ($pos<$length){
			if($input[$pos]===self::T_BSLASH){			// 转义符
				$pos++;
			}
			else if($input[$pos]===self::T_SQUOTE){
				$pos++;
				$this->skipScope($input, $pos, $length,self::T_SQUOTE); //跳过字符串
			}
			else if($input[$pos]===self::T_CR || $input[$pos]===self::T_LF){
				return -1;
			}
			else if($input[$pos]===$this->rdelimiter[0]){
				$start=$pos;
				if(!ark_strcomBC($input, $this->rdelimiter,$pos,$length)){
					$start=0;
					return -1;
				}
				
				$pos+=ark_strlenBC($this->rdelimiter);
				$this->skipWhitespace($input, $pos, $length);
				if($char===self::T_LT && ark_strcomBC($input, '-->',$pos,$length)){
					return $pos+3;
				}
				else if($char===self::T_SLASH && ark_strcomBC($input, '*/',$pos,$length)){
					return $pos+2;
				}
				return $pos;
			}
			$pos++;
		}
	}
	
	function text(&$lex){
		
	}
	
	
	function compile(){
		$this->parseAll();
		//var_dump($this->rootLex); die();
		echo $this->root->compile();
	}
}


?>