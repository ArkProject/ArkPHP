<?php
if (! defined ( 'ARK' )) {
	exit ( 'deny access' );
}
function ark_version() {
	return '1.0.1';
}




/**
 * gets Application instance.
 * 
 * @return Application
 */
function ark_app() {
	return Application::getInstance ();
}
function display_error($e) {
	$html = '
<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Server Error</title><style type="text/css">
body,div,p,ul,li,hr{ margin:0; padding:0; font-size:14px;}
body{margin:0 5px; line-height:20px;}
h3{font-size:18px; font-weight:bold; margin-bottom:10px;margin-top:0;}
hr{margin:5px 0; border:none; border-top:1px solid #efefef;}
p{padding:5px;}
div{background:#FF9; padding:5px; margin-bottom:20px;}</style></head><body>
<h3>Server Error</h3><p><b>' . get_class ( $e ) . ':</b> ' . $e->getMessage () . '</p>
<hr /><p><b>Source file：</b>' . $e->getFile () . ' <b>Line：</b>' . $e->getLine () . '<br><br>
<b>Stack trace：</b><br></p><div>';
	$lines=preg_split('/#[0-9]{0,100}\s/', $e->getTraceAsString ());
	$i=count($lines)-1;
	foreach ($lines as $item){
		if(!empty($item)){
			$html.='#'.$i.' '.$item.'<br>';
		}
		$i--;
	}
	
	$html .= '</div><hr /><b>version infomartion:</b>ArkPHP framework version:' . ark_version () . ' PHP version:' . phpversion () . '</body></html>';
	
	exit ( $html );
}

function ark_handleFileError(){
	set_error_handler(function (){
		$args=func_get_args();
		throw new FileSystemException($args[1]);
	});
}

function ark_unhandleError(){
	restore_error_handler ();
}

function ark_importFile($filename,$once=FALSE) {
	ark_handleFileError();
	if (! file_exists ( $filename )) {
		throw new FileSystemException ( '指定文件不存在或未找到。文件名:' . $filename );
	}
	if($once===FALSE){
		include $filename;
	}
	else{
		include_once $filename;
	}
	ark_unhandleError ();
	file($filename);
}

function ark_using($ns) {
	$items=ark_split($ns,'|',TRUE);
	foreach ($items as $item){
		
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

function ark_substr($str, $start, $length = NULL){
	return substr($str,$start,$length);
}

function ark_indexOf($str,$substr) {
	return strpos($str,$substr);
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
function endWith($str,$suffix){
	if (ark_substr($str, ark_strlen($str,FALSE,TRUE) - ark_strlen($suffix,FALSE,TRUE)) === $suffix) {
		return true;
	}
	return false;
}

?>