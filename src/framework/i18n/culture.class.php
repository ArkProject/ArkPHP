<?php
namespace ark\i18n;
defined ( 'ARK' ) or exit ( 'deny access' );
/**
 * 
 * @author jun
 *
 */
class Culture{
	public static $langs=array('zh-cn','zh','en');
	public static function getDefaultLang(){
		return 'zh-cn';
	}
	
	public static function getCurrentLang(){
		if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			return self::getDefaultLang();
		}
		$lang='';
		$str = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4);
		if (preg_match("/zh-c/i", $str)){
			$lang='zh-cn';
		}
		else if (preg_match("/zh/i", $str)){
			$lang='zh'; //繁體中文
		}
		else if (preg_match("/en/i", $str)){
			$lang='en'; //English
		}
		
		if(!$lang){
			return self::getDefaultLang();
		}
		if(in_array($lang,self::$langs)){
			return $lang;
		}
		return self::getDefaultLang();
	}
	
	private static function format($str,$args,$start=1){
		for($i=$start;$i<count($args);$i++){
			$str=str_replace('{'.($i>0?$i-1:$i).'}', $args[$i], $str);
		}
		//var_dump($args);
		//die();
		$str=preg_replace_callback('/\{[0-9]{0,}\}/', function ($m){
			return '';
		}, $str);
		
		return $str;
	}
	
	public static function getLocalString($var,$_){
		$lang = self::getCurrentLang ();
		$def_lang = self::getDefaultLang ();
		if (! isset ( $GLOBALS ['__ARK_LANGS'] )) {
			$GLOBALS ['__ARK_LANGS'] = array ();
		}
		if (isset ( $GLOBALS ['__ARK_LANGS'] [$lang] ) && isset ( $GLOBALS ['__ARK_LANGS'] [$lang] ['__ARK_LANGS'] ) && isset ( $GLOBALS ['__ARK_LANGS'] [$lang] ['__ARK_LANGS'] [$var] )) {
			return self::format ( $GLOBALS ['__ARK_LANGS'] [$lang] ['__ARK_LANGS'] [$var], func_get_args (), 1 );
		} else if (isset ( $GLOBALS ['__ARK_LANGS'] [$def_lang] ) && isset ( $GLOBALS ['__ARK_LANGS'] [$def_lang] ) && isset ( $GLOBALS ['__ARK_LANGS'] [$def_lang] [$var] )) {
			return self::format ( $GLOBALS ['__ARK_LANGS'] [$def_lang] [$var], func_get_args (), 1 );
		}
		return $var;
	}
	
}
?>