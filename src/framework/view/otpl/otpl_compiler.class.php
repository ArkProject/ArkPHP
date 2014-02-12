<?php
namespace ark\view\otpl;
defined ( 'ARK' ) or exit ( 'access denied' );

class OtplCompiler extends \ark\view\Compiler{
	private $_tplPath;
	private $_ifCount=0;
	protected $line=0;
	private $_literal=FALSE;
	private $_blocks=array();
	protected $result='';
	public function __construct($engine, $tplPath){
		$this->_tplPath=$tplPath;
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
	public function skipWhitespace(&$expr,&$start=0,$end=-1){

		if($end<0){
			$end=ark_strlen($expr);
		}
		for (;$start<$end;$start++){
		
			if(!$this->isWhitespace($expr[$start])){
				$start--;
				break;
			}
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
	public function getIdentifier(&$expr,&$start=0,$end=-1){
		$result='';
		if($end<0){
			$end=ark_strlen($expr);
		}
	
		for (;$start<$end;$start++){
				
			if(!$this->isIdentifierChar($expr[$start])){
				$start--;
				break;
			}
			else {
				$result.=$expr[$start];
			}
		}
		
		if(ark_strlen($result)===0 || !$this->isIdentifierChar($result[0],7)){
			throw new \Exception('语法错误：非法标识符。');
		}
		
		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \ark\view\Compiler::findPairRight()
	 */
	protected function findPairRight(&$expr,$start,$end,$left,$right){
		
		if($end<0){
			$end=ark_strlen($expr);
		}
		$m=0;
		for (;$start<$end;$start++){
				
			if($expr[$start]==chr(92)){ // \
				$start++;
				continue;
			}
				
			if($expr[$start]==$right && $m==0){
				return $start;
			}
			else if($expr[$start]==$right){
				$m--;
			}
			else if($expr[$start]==$left){
				$m++;
			}
		}
		return -1;
	}
	
	protected function getString(&$expr,&$start=0,$end=-1,$quoteAscii=39){
		
		if($end<0){
			$end=ark_strlen($expr);
		}
		$result='';
		for (;$start<$end;$start++){
				
			if($buffer[$start]==chr(92)){ // \
				$result.=$expr[$start];
				$result.=$expr[$start+1];
				$start++;
				continue;
			}
			else if($expr[$start]==chr($quoteAscii)){ //'单引号 39 双引号"34
				return $result;
			}
			else{
				$result.=$expr[$start];
			}
		}
		throw new \Exception('语法错误：字符串示结束');
	}
	
	/**
	 * 获取子串到空白字符
	 * @param unknown $expr
	 * @param number $start
	 * @param unknown $end
	 * @return string|Ambigous <string, unknown>
	 */
	protected function getToWhitespace(&$expr,&$start=0,$end=-1){

		if($end<0){
			$end=ark_strlen($expr);
		}
		$result='';
		for (;$start<$end;$start++){
			if($this->isWhitespace($expr[$start])){
				$start--;
				return $result;
			}
			else{
				$result.=$expr[$start];
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
	public function getParams(&$expr,&$start=0,$end=-1){
		if($end<0){
			$end=ark_strlen($expr);
		}
		$name='';
		$value='';
		$result=array();
		
		for (;$start<$end;$start++){
			$this->skipWhitespace($expr,$start,$end);
			$name=$this->getIdentifier($expr,$start,$end);
			$this->skipWhitespace($expr,$start,$end);
			if($start<$end && $expr[$start]!=chr(61)){//=
				throw new \Exception('语法错误：错误的参数表达式'.$expr[$index]);
			}
			$start++;
			if($start>=$end){
				break;
			}
			if($expr[$index]==chr(40)){ //(40 )41
				$last=$this->findPairRight($expr,$index+1, chr(40), chr(41),$end);
				if($last===-1){
					throw new \Exception('语法错误：括号不匹配');
				}
				$start++;
				$result[$name]=$this->parseExpr($expr,$start,$last);
			}
			else if($expr[$start]==chr(39) || $expr[$start]==chr(34)){ //'39 " 34
				$ascii=$expr[$start]==chr(39)?39:34;
				$start++;
				$result[$name]=chr($ascii). $this->getString($expr,$start,$end,$ascii) .chr($ascii);
			}
			else {
				$value=$this->getToWhitespace($expr,$start,$end);
				$result[$name]=$this->parseExpr($value);
			}
			$name='';
			$value='';
		}
		return $result;
	}
	
	public function parseExpr(&$expr,&$start=0,$end=-1){
		if($end<0){
			$end=ark_strlen($expr);
		}
		$result='';
		RESTART:
	
		for (;$expr<$end;$start++){
			if($expr[$index]==chr(40)){ //(40 )41
				$last=$this->findPairRight($expr,$index+1, chr(40), chr(41),$end);
				if($last===-1){
					throw new \Exception('语法错误：括号不匹配');
				}
				$start++;
	
				$result .='('.$this->parseExpr($expr,$start,$last-1).')';
				$i=$last;
			}
			else if($buffer[$i]=='['){
				throw new \Exception('未实现');
			}
			else if($expr[$start]==chr(39) || $expr[$start]==chr(34)){ //'39 " 34
				$ascii=$expr[$start]==chr(39)?39:34;
				$start++;
				$result .=chr($ascii). $this->getString($expr,$start,$end,$ascii) .chr($ascii);
			}
			else if($expr[$start]==chr(46)){ //.46
				$start++;
				$this->skipWhitespace($expr,$start,$end);
				$result .='->'.$this->getIdentifier($expr,$start,$end);
			}
			else if($expr[$start]==chr(36)){//$36
				$start++;
				$this->skipWhitespace($expr,$start,$end);
				$result .='$view->data[\''. $this->getIdentifier($expr,$start,$end) .'\']';
			}
			else if($expr[$start]==chr(35)){//#35
				$start++;
				$this->skipWhitespace($expr,$start,$end);
				$result .='$'. $this->getIdentifier($expr,$start,$end);
			}
			else if($expr[$i]=='@'){
				$start++;
				$this->skipWhitespace($expr,$start,$end);
				$result .='$view->call['. $this->getIdentifier($expr,$start,$end) .']';
			}
			else{
				$result.=$expr[$i];
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
	
	public function compileToString(){
	
		//文件编译名称：compiled_id //不包含路径
		//文件编译时间：complied_time
		//模板原名称：orgin_filename //全路径
		//是否是布局文件：is_master
		//内容页地址（指针）：sub_complied_id
		//是否是子页：is_sub
		//父级页地址（指针）：master_compiled_id
		//是否可作为部分页使用：allow_include
	
	
		//$handle = fopen($this->_tplPath, 'r');
		//while (!feof($handle)) {
		//	$buffer = fgets($handle,1024);
		//	$brarray = explode(' ',$buffer);
		//
		//}
	
	
	
	
		$reader=new StreamReader($this->_tplPath);
		$reader->open();
	
		while (!$reader->endOfStream()){
		$buffer=array();
			if($reader->readLine($buffer)==0){
			$this->_line++;
				continue;
			}
			else {
			$this->_line++;
			$this->compileLine(implode('', $buffer));
			}
			}
	
			return $this->result;
	
			$content=file_get_contents($this->_tplPath);
	
			$content=preg_replace_callback('/\{(.*)\}/i', array($this,'foundToken'), $content);
	
			//save
	
					return $content;
	
	}
}
?>