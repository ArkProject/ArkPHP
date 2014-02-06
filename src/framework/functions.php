<?php
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
	return Application::getInstance ();
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
	if (! $value) {
		return 0;
	}
	
	if ($trimWhitespace === TRUE) {
		$value = trim ( $value );
	}
	
	if ($useCharacterMode === FALSE) {
		preg_match_all ( '/./us', $value, $match );
		return count ( $match [0] );
	} else {
		return mb_strlen ( $value, $encoding );
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