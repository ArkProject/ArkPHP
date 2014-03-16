<?php
/*
 * Ark PHP Framework.
 * Ark 标准库。
 * 
 * $ID$
 * 
 * */

define ( 'ARK', microtime ( true ) );
define ( 'ARK_VERSION', '1.0.1' );
define ( 'ARK_PATH', dirname ( __FILE__ ) . '\\' );
defined ( 'APP_ROOT' ) or define ( 'APP_ROOT', realpath ( dirname ( __FILE__ ) . '/..' ) . '\\' );

/**
 * @var integer 表示外部向过程内部单向传值。
 */
define ( 'ARK_PARAM_IN', 1 );

/**
 * @var integer 表示过程内部向外部单向传值。
 */
define ( 'ARK_PARAM_OUT', 2 );

/**
 * @var integer 表示调用双方均可传值（双向）。
 */
define ( 'ARK_PARAM_INOUT', 3 );

/**
 * @var bool ARK 是否运行在windows 系统上。
 */
define('ARK_ON_WIN', DIRECTORY_SEPARATOR == '\\');

// ================================Ark  标准库========================================//

use ark\Runtime;
use ark\Application;

/**
 * 启动ARK运行时。
 * @param array $config 配置参数。
 */
function ark_start($config) {
	// 自动加载类
	spl_autoload_register ( function ($class) {
		ark_import ( $class . '.class.php' );
	}, TRUE );
	
	// 捕获所有未处理异常和错误
	error_reporting ( E_ALL );
	set_exception_handler ( function ($e) {
		Runtime::handleError($e);
	} );
	register_shutdown_function ( function () {
		$error = error_get_last ();//($error ['type'] == E_ERROR || $error ['type'] == E_PARSE)
		if ($error && isset ( $error ['type'] ) && gettype($error ['type'])=='integer') {

			ob_clean ();
			$e= new ErrorException ( $error ['message'], $error ['type'], $error ['type'], $error ['file'], $error ['line'] );
			Runtime::handleError($e);
		}
		
	} );
	set_error_handler ( function () {
		$args = func_get_args ();
		// echo var_export($args[4], TRUE);
		throw new ErrorException ( $args [1], $args [0], $args [0], $args [2], $args [3] );
	}, E_ALL );
	
	Runtime::start ( $config );
}

/**
 * 获取当前应用程序实例。
 * 
 * @return \ark\Application
 */
function ark_app() {
	return Runtime::getApplication();
}

function ark_handleFileError() {
	\set_error_handler ( function () {
		$args = func_get_args ();
		throw new ark\FileSystemException ( $args [1] );
	} );
}

function ark_unhandleError() {
	\restore_error_handler ();
}

function ark_lang($var,$_){
	//class_exists('ark.Culture',TRUE);
	//return \ark\i18n\Culture::getLocalString(func_get_args());
	//ark_using('ark\i18n\Culture');
	//return call_user_func_array(array(ark_app()->getCulture(),'getLocalString'), func_get_args());
	//return \ark\i18n\Culture::getLocalString(func_get_args());
	return call_user_func_array('\ark\i18n\Culture::getLocalString', func_get_args());
}

/**
 * 获取一个新的 UUID ，关于UUID请参考：百科。
 * @param boolean $short 是否去掉连接短线，默认为 true.
 * @return string
 */
function ark_uuid($short = true) {
	return Runtime::uuid($short);
}

function ark_datetimeParse($str){

	$str=str_replace('年', '-', $str);
	$str=str_replace('月', '-', $str);
	$str=str_replace('日', '-', $str);
	$str=str_replace('时', ':', $str);
	$str=str_replace('分', ':', $str);
	$str=str_replace('秒', '.', $str);
	$str=str_replace('毫秒', '', $str);
	$str=str_replace('上午', 'AM', $str);
	$str=str_replace('下午', 'PM', $str);
	
	//Wed, 22 Jul 2009 16:24:33 GMT
	
	$str=trim($str,'.-');

	return strtotime($str);
}

function ark_datetimeFormat($timestamp,$format){
	//http://www.php.net/manual/en/function.date-parse-from-format.php
	//http://www.php.net/manual/en/function.strftime.php
	$date = new DateTime("1899-12-31");
	$parts=array('%','a','A','d','e','j','u','w','U','V','W','b','B','h','m','C','g','G','y','Y','H','l','M','p','P','r','R','S','T','X','z','Z','c','D','F','s','x','n','t');
	
	foreach ($parts as $part){
		$format=str_replace($part, '%'.$part, $format);
	}
	return strftime($format,$timestamp);
}

/**
 * 合并两个路径。
 * @param unknown $path1
 * @param unknown $path2
 * @param string $glue
 * @return string
 */
function ark_combine($path1,$path2,$glue='\\'){
	if(!ark_endWith($path1, $glue)){
		$path1.=$glue;
	}
	if(ark_startWith($path2, $glue)){
		$path2=ark_substr(ark_strlen($glue), $path2);
	}
	return $path1.$path2;
}

/**
 * 导入一个文件
 * @param unknown $relativePath
 * @param string $throw
 * @return boolean
 */
function ark_import($relativePath,$throw=TRUE){

	if(!isset($GLOBALS['__ARK_INC_PATH'])){
		$GLOBALS['__ARK_INC_PATH']=array(APP_ROOT,ark_combine(APP_ROOT, 'apps'),ARK_PATH);
	}
	
	$result=FALSE;
	$filename='';
	$relativePath=str_replace('/', '\\', $relativePath);
	$lookups=array();
	foreach ($GLOBALS['__ARK_INC_PATH'] as $base){
		$filename=ark_combine($base, $relativePath);
		$result=@file_exists($filename);
		if($result){
			break;
		}
		else {
			$lookups[]=$filename;
		}
	}
	if(!$result){
		$filename=$relativePath;
		$result=@file_exists($filename);
	}
	if($result){
		include_once $filename;
		return TRUE;
	}
	else if($throw){
		trigger_error('failed to import: Not found file '.$relativePath.'. lookup paths:<br>'.join('<br>', $lookups),E_USER_ERROR);
	}
	else {
		return FALSE;
	}
}

/**
 * 注册一个搜索路径
 * @param unknown $path
 */
function ark_regsisterIncludePath($path){
	if(!isset($GLOBALS['__ARK_INC_PATH'])){
		$GLOBALS['__ARK_INC_PATH']=array(ARK_PATH,APP_ROOT,ark_combine(APP_ROOT, 'apps'));
	}
	if(!in_array($path,$GLOBALS['__ARK_INC_PATH'])){
		$GLOBALS['__ARK_INC_PATH'][]=$path;
	}
}

// ================================字符串处理函数========================================//

function &ark_removeUTF8Bom(&$str){
	$bom = pack('H*','EFBBBF');
	$str = preg_replace("/^$bom/", '', $str);
	return $str;
}

/**
 * 字符串比较
 * @param string $str
 * @param string $substr
 * @param int $pos
 * @param int $length
 * @param string $ic
 * @return boolean
 */
function ark_strCompare(&$str,$substr,$pos=0,$length=0,$ic=FALSE){
	if($length<=0){
		$length=ark_strlen($str);
	}
	$len=ark_strlen($substr);
	if($len+$pos>$length){
		return FALSE;
	}
	else if($len===1){
		return $str[$pos]===$substr[0];
	}
	for($i=0;$i<$len;$i++){
		if($str[$pos+$i]!==$substr[$i]){
			return FALSE;
		}
	}
	return TRUE;
}

/**
 * 将一个字符串分割为字符数组返回。
 * @param string $str 要处理的字符串。
 * @param string $encoding 字符串编码。如果为 NULL 则会按字符个数分割(包含中文等宽字符集)，否则将会返回按编码处理后的数组。默认为 NULL.
 * @return array 
 */
function ark_strToArray($str,$encoding=NULL){
	if(!$encoding){
		$arr=preg_split('//us', $str);
		
		//移除首尾两个空白
		array_splice($arr,0,1);
		array_splice($arr,count($arr)-1,1);
		return $arr;
	}
	else{
		$arr=array();
		for ($i=0;$i<mb_strlen ( $str ,$encoding);$i++){
			$arr[]=$str[$i];
		}
		return $arr;
	}
}

function ark_split($str, $delimiter, $removeEmptyItem = FALSE) {
	if ($removeEmptyItem === TRUE) {
		$result = array ();
		foreach ( explode ( $delimiter, $str ) as $item ) {
			if (! empty ( $item )) {
				$result [] = $item;
			}
		}
		return $result;
	} else {
		return explode ( $delimiter, $str );
	}
}

function ark_substr(&$str, $start, $length = NULL,$encoding = NULL){
	
	//return mb_substr($str, $start,$length,$encoding);
	
	
	$result='';
	if($length===NULL){
		$length=ark_strlen($str,$encoding);
	}
	while ($start<$length){
		$result.=$str[$start];
		$start++;
	}
	return $result;
	return \substr($str,$start,$length);
}

function ark_indexOf($str,$substr) {
	return mb_strpos($str,$substr);
}

function ark_lastIndexOf($str,$substr){
	return mb_strrpos($str, $substr);
}

/**
 * 获取一个字符串的长度。
 * 
 * @param string $str 要计算的字符串。
 * @param string $encoding 该参数将确定使用何种编码进行计算。
 * @return integer
 */
function ark_strlen($str, $encoding = NULL) {
	if (NULL===$str || gettype($str)!='string') {
		throw new Exception('argument $str is required');
	}
	
	if($encoding!==NULL){
		return mb_strlen ( $str, $encoding );
	}
	else {
		return mb_strlen ( $str);
	}
	
	// 	if ($trimWhitespace === TRUE) {
	// 		$value = trim ( $value );
	// 	}
		
	// 	if ($useCharacterMode === FALSE) {
	// 		preg_match_all ( '/./us', $value, $match );
	// 		return count ( $match [0] );
	// 	} else if($encoding!==NULL){
	// 		return mb_strlen ( $value, $encoding );
	// 	}
	// 	else {
	// 		return mb_strlen ( $value);
	// 	}
}

function ark_startWith($str, $prefix) {
	if (ark_indexOf($str, $prefix) === 0) {
		return true;
	}
	return false;
}

function ark_endWith($str,$suffix){
	return ark_strCompare($str, $suffix,ark_strlen($str)-ark_strlen($suffix));
}




function ark_strlenBC(&$str){
	$len=0;
	while (TRUE){
		if(isset($str[$len])){
			$len++;
		}
		else{
			break;
		}
	}
	return $len;
}
function ark_substrBC(&$str,$start,$length=NULL){
	$substr='';
	if(!$length || gettype($length)!='integer'){
		
		while (TRUE){
			if(isset($str[$start])){
				$substr.=$str[$start];
				$start++;
			}
			else{
				break;
			}
		}
		return $substr;
	}
	$length += $start;
	while ($start<$length){
		$substr.=$str[$start];
		$start++;
	}
	return $substr;
}

/**
 * 字符串比较
 * @param string $str
 * @param string $substr
 * @param int $pos
 * @param int $length
 * @param string $ic
 * @return boolean
 */
function ark_strcomBC(&$str,$substr,$pos=0,$length=NULL){
	
	$slen=ark_strlenBC($substr);
	if(!gettype($length)=='integer'){
		$length=$slen;
	}
	for($i=0;$i<$slen && $i<$length;$i++){
		if(isset($str[$pos+$i]) && $str[$pos+$i]===$substr[$i]){
			continue;
		}
		else {
			return FALSE;	
		}
	}
	return TRUE;
}





?>