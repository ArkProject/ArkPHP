<?php
namespace ark\view;
defined ( 'ARK' ) or exit ( 'access denied' );
//http://msdn.microsoft.com/zh-cn/library/system.linq.expressions(v=vs.110).aspx
class TemplateBase{
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
	
	function isDigital($char){
		return $char>=chr(48) && $char<=chr(57);
	}
	
	function isLetter($char){
		return ($char>=chr(65) && $char<=chr(90) || $char>=chr(97) && $char<=chr(122));
	}
	
	function isAlphanumeric($char){
		return $this->isDigital($char) || $this->isLetter($char);
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
	function skipScope(&$input,&$pos,$length,$char=self::T_SQUOTE, $breakLineToken=TRUE){

		while ($pos<$length){
			if($input[$pos]===self::T_BSLASH){
				$pos++;
			}
			else if($input[$pos]===$char){
				return $pos;
			}
			else if($breakLineToken===TRUE && ($input[$pos]===self::T_CR || $input[$pos]===self::T_LF)){
				return -1;
			}
			$pos++;
		}
		return -1;
	}
	
	
	
	/**
	 * 跳过空白
	 * @param unknown $input
	 * @param unknown $pos
	 * @param unknown $length
	 */
	function skipWhitespace(&$input,&$pos,$length){
		while ($pos<$length){
			if (!($input[$pos]===self::T_SPACE || $input[$pos]===self::T_TAB || $input[$pos]===self::T_CR || $input[$pos]===self::T_LF)){
				return ;
			}
			$pos++;
		}
	}
	
	/**
	 * 跳过空白
	 * @param unknown $input
	 * @param unknown $pos
	 * @param unknown $length
	 */
	function skipWhitespaceRight(&$input,&$length){
		while ($length>0){
			if (!($input[$length]===self::T_SPACE || $input[$length]===self::T_TAB || $input[$length]===self::T_CR || $input[$length]===self::T_LF)){
				return ;
			}
			$length--;
		}
	}
	
	
	/**
	 * 读取标识符
	 * @param unknown $input
	 * @param unknown $pos
	 * @param unknown $length
	 * @return boolean
	 */
	function readIdentifier(&$input,&$pos,$length){
		
		$id='';
		$this->skipWhitespace($input, $pos, $length);
		while ($pos<$length){
			if($this->isAlphanumeric($input[$pos]) || $input[$pos]==='_'){
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
		return $id;
	}
	
	function peek(&$input,$tag,$pos,$length){
		return ark_strcomBC($input, $tag,$pos,$length);
	}
	
	function lastPeek(&$input,$tag,$length){
		return ark_strcomBC($input, $tag,$length-ark_strlenBC($tag),$length);
	}
	
}

class Parser extends TemplateBase{

	/**
	 *
	 * @var Compiler
	 */
	public $compiler;

	public function __construct($compiler){
		$this->compiler=$compiler;
	}

	/**
	 * Lexer
	 * @param \ark\view\Lexer $current
	 * @param string $input
	 * @param unknown $pos
	 * @param unknown $length
	 * @param string $allToken
	 * @throws TemplateCompileException
	 * @return \ark\view\Lexer
	 */
	public function parse(&$input,$start,$length,$allToken=FALSE){
		$code='';
		$pos=$start;
		$this->skipWhitespace($input, $pos, $length);
		$this->skipWhitespaceRight($input, $length);
		if ($this->peek ( $input, '//', $pos, $length )) {
			return NULL;
			return new Lexer ( Lexer::L_COMMENT );
		}
		else if ($this->peek ( $input, '/*', $pos, $length )) {
			return new Lexcomment ($this->compiler, $pos, $length-($pos-$start),'comment');
		}
		else if ($this->lastPeek ( $input, '*/', $pos+$length )) {
			return new Lexcloser($this->compiler, $pos, $length-($pos-$start),'comment');
		}
		else if ($this->peek ( $input, '$', $pos, $length )) {
			return new Lexprint($this->compiler, $pos, $length-($pos-$start));
		}
		else if ($this->peek ( $input, '@', $pos, $length )) {
			return new Lexfunc($this->compiler, $pos+1, $length-($pos-$start)-1);
		}
		else if ($this->peek ( $input, '/', $pos, $length )) {
			//block end
			return new Lexprint($this->compiler, $pos, $length-($pos-$start),TRUE);
		}
		die('herex:'.$length.htmlspecialchars(ark_substrBC($input, $pos,2)));
		$id=$this->readIdentifier($input, $pos, $length);
		if(!$id){
			$err= new TemplateCompileException('syntax error,illegal identifier "'. $input[0] .'".');
			$err->setFile($this->compiler->filename);
			$err->setLine($this->compiler->lineno);
		}
		if($this->peek($input, '.', $pos, $length)){
			return new Lextempvar($this->compiler,$id,$pos, $length-($pos-$start),TRUE);
		}
		else if($this->peek($input, '[', $pos, $length)){
			return new Lextempvar($this->compiler,$id,$pos, $length-($pos-$start),TRUE);
		}
		else if($this->peek($input, '(', $pos, $length)){
			return new Lextempvar($this->compiler,$id,$pos, $length-($pos-$start),TRUE);
		}
			
		$lex=$this->compiler->lexs[$id];
		return new $lex($this->compiler,$id,$pos, $length-($pos-$start),TRUE);
		//只能是变量，不允许运行时标签函数
		return new Lextempvar($this->compiler,$id,$pos, $length-($pos-$start),TRUE);
		//{call name='myfunc' arg='' arg=''} //动态函数
	}

	//{$var.mem (arg) | format}
	public function parseTempvar($current, &$input,$pos,$length){
		$current->append();
		//val lex => var->mem('arg')
		//format lex =>call {arg=>['arg',func=val]}
		//=>echo format('func',val,'y')
		//KEY(文本|输出模板变量|输出临时变量|输出函数调用|函数调用|标签函数|条件|循环|)
		//
	}
}

class Lexer{
	/**
	 * @var Compiler
	 */
	public $compiler;
	public $lineno;
	/**
	 * @var Lexblock
	 */
	public $container;
	public $start;
	public $length;
	public $name;
	//public $code;
	
	public function __construct($compiler,$start,$length,$name){
		$this->compiler=$compiler;
		$this->lineno=$compiler->lineno;
		$this->start=$start;
		$this->length=$length;
		$this->name=$name;
		//$this->input=substr($compiler->input, $start,$length);
	}
	
	public function setContainer($block){
		$this->container=$block;
	}
	
	protected function parse(){
	
	}
	
	public function compile(){
		
	}
}


class Lexblock extends Lexer{
	public $vars=array();
	protected $items=array();
	public function copyTo($block){
		
	}
	public $closed;
	
	public function append($lex){
		if($lex instanceof Lexcloser && $lex->name==$this->name){//
			$this->closed=TRUE;//die('here go:'.$this->name);
			return $this->container;
		}
		if($this->closed){
			$this->container->append($lex);
			return $this->container;
		}
		else{
			$lex->container=$this;
			$this->items[]=$lex;
			return $lex instanceof Lexblock ? $lex :$this;
		}
	}
	
	public function compile(){
		$code='';
		foreach ($this->items as $lex){
			$code.=$lex->compile();
		}
		return $code;
	}
}

class Lexspan extends Lexer{
	
}

class Lexroot extends Lexblock{
	
}

class Lexcomment extends Lexblock{
	public function compile(){
		return '';
	}
}

class Lexcloser extends Lexspan{
	
	function compile(){
		return '';
	}
}

/**
 * 纯文本。
 * @author jun
 *
 */
class Lexplan extends Lexspan{
	function compile(){
		return ark_substrBC($this->compiler->input, $this->start,$this->length);
	}
}

/**
 * 打印。
 * @author jun
 *
 */
class Lexprint extends Lexspan{
	private $code;
	function __construct($compiler,$start,$length,$identifier=NULL){
		parent::__construct($compiler, $start, $length,$identifier);
		
		$this->parse();
	}
	
	function parse(){
		
	}
	public function compile(){
		die(1);
		return '<?php echo '.$this->code.' ?>';
	}
}


class Lexfunc{
	public $func;
	public $arguments;
	
	function compile(){
		$code='';
		$code+=$this->func->compile();
		
		$arr=array();
		foreach ($this->arguments as $lex){
			$arr[]=$lex->compile();
		}
		
		$code+='('.join(',',$arr).')';
		return  $code;
	}
}
/*

class GenNode extends SpanNode{
	
	public function parse(){
		$pos=0;
		$this->skipWhitespace($this->input, $pos, $this->length);
		
		switch ($this->input[0]){
			case self::T_DOLLAR :{	//模板变量
				$pos++;
				$id=$this->readIdentifier($this->input, $pos, $this->length);
				if($id===FALSE){
					throw new TemplateCompileException('非法标识符');
				}
				echo 'idddddd:'.$id.'<br>';
				//readIndentfier($this->text)
				//.attr
				//(fun
				//[index
				//error
				break;
			}
			default:{
				//?
			}
		}
		
		return true;
	}
}
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

class Compiler extends TemplateBase{
	public $ldelimiter='{{';
	public $rdelimiter='}}';
	public $input;
	public $pos=0;
	public $length;
	public $lineno=1;
	public $filename;
	public $_isComment;
	public $parser;
	public $encoding='utf-8';
	/**
	 * 根节点
	 * @var Lexroot
	 */
	private $rootLex;
	/**
	 * 当前
	 * @var Lexblock
	 */
	private $currentLex;
	
	function open($filename){
		$this->input=file_get_contents($filename);
		//ark_removeUTF8Bom($this->input); //移除所有  UTF-8 BOM
		$this->length=ark_strlenBC($this->input);
		$this->filename=$filename;
		$this->parser=new Parser($this);
		$this->currentLex= $this->rootLex=new Lexroot($this, 0, 0,'root');
	}
	
	public function append($lex){
		
		$this->currentLex=$this->currentLex->append($lex);
		return ;
	}
	
	function parseMarkup($start,$length,$type){
		if($length<1){
			return ;
		}
		$lex=NULL;
		if($type=='PLAN'){
			$lex=new Lexplan($this,$start,$length,'plan');
		}
		else {
			//$input=substr($this->input, $start,$length);
			$lex=$this->parser->parse($this->input, $start, $length,TRUE);
			//$node=new GenNode($this,$start,$length);
			//$node->parse();
			//$this->appendNode($node);
		}
		if($lex){
			$this->append($lex);
		}
		return ;
		echo 'markup item['.$type.']['.$this->lineno.'] start:'.$start.' lenght:'.$length.chr(10).chr(13);
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
	 *开始处理
	 * @param Compiler $compiler
	 */
	function parse(){
		//die($this->input);
		$pos=$start=$this->pos;
		
		$this->skipWhitespace($input, $pos, $this->length);
		
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
						die(ark_substrBC($this->input, $begin));
						$err= new TemplateCompileException('syntax error,missing close tag "'. $this->rdelimiter .'".');
						$err->setFile($this->filename);
						$err->setLine($this->lineno);
						throw $err;
					}
					$this->pos=$last;
					//die( 'plan:('.$start.','.($begin-$start).') ='.substr($this->input, $start,$begin-$start));
					$this->parseMarkup($start,$begin-$start,'PLAN');
					//die( 'code:('.$last.','.($last-$lastIndex).') ='.substr($this->input, $index,$lastIndex-$index));
					$this->parseMarkup($index,$lastIndex-$index,'CODE');
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
			$this->parseMarkup($start,$pos-$start,'PLAN');
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
		$this->parse();
		var_dump($this->rootLex); die();
		echo $this->rootLex->compile();
	}
}


?>