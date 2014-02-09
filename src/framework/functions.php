<?php
use ark\Runtime;
if (! defined ( 'ARK' )) {
	exit ( 'deny access' );
}
function ark_version() {
	return '1.0.1';
}

//use ark;

/**
 * gets Application instance.
 * 
 * @return Application
 */
function ark_app() {
	return Runtime::getApplication();
}

function _ark_randHex($start, $end, $len) {
	$r = dechex(mt_rand($start, $end)) . '';
	//echo strlen($r);
	while (strlen($r) < $len) {

		$r = $r . dechex(mt_rand($start, $end));
	}
	return substr($r, 0, $len);
}
/**
 * 获取一个新的 UUID ，关于UUID请参考：百科。
 * @param boolean $short 是否去掉连接短线，默认为 true.
 * @return String
 */
function ark_uuid($short = true) {
	$uuid = dechex(microtime(true));
	if (!$short) {
		$uuid .='-';
	}
	$uuid .=_ark_randHex(1000000, 4000000, 4);
	if (!$short) {
		$uuid .='-';
	}
	$uuid .=_ark_randHex(4000000, 8000000, 4);
	if (!$short) {
		$uuid .='-';
	}
	$uuid .=_ark_randHex(8000000, 12000000, 4);
	if (!$short) {
		$uuid .='-';
	}
	$uuid .=_ark_randHex(12000000, 16000000, 12);

	return $uuid;
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

function ark_substr($str, $start, $length = NULL){
	if($length===NULL){
		return \substr($str,$start);
	}
	return \substr($str,$start,$length);
}

function ark_indexOf($str,$substr) {
	return strpos($str,$substr);
}

function ark_lastIndexOf($str,$substr){
	return strrpos($str, $substr);
}

function ark_streq($left, $right, $ignoreCase = TRUE) {
}

/**
 * gets string length.
 * 
 * @param string $value        	
 * @param
 *        	boolean 计算之前是否清除两端的空白字符，默认为 TRUE。
 * @param
 *        	boolean 是否使用字符模式，默认为 FALSE.
 * @param
 *        	string 如果使用了字符模式，该参数将确定使用何种编码进行计算。
 * @return integer
 */
function ark_strlen($value, $trimWhitespace = TRUE, $useCharacterMode = FALSE, $encoding = NULL) {
	if (NULL===$value || gettype($value)!='string') {
		return 0;
	}
	
	if ($trimWhitespace === TRUE) {
		$value = trim ( $value );
	}
	
	if ($useCharacterMode === FALSE) {
		
		preg_match_all ( '/./us', $value, $match );
		return count ( $match [0] );
	} else if($encoding!==NULL){
		return mb_strlen ( $value, $encoding );
	}
	else {
		return mb_strlen ( $value);
	}
}

function ark_startWith($str, $prefix) {
	if (ark_indexOf($str, $prefix) === 0) {
		return true;
	}
	return false;
}
function ark_endWith($str,$suffix){
	if (ark_substr($str, ark_strlen($str,FALSE,TRUE) - ark_strlen($suffix,FALSE,TRUE)) === $suffix) {
		return true;
	}
	return false;
}

?>