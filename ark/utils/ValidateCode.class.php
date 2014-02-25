<?php

namespace ark\utils;

defined ( 'ARK' ) or exit ( 'access denied' );
class ValidateCode {
	private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789'; // 随机因子
	private $code; // 验证码
	private $codelen = 4; // 验证码长度
	private $width = 100; // 宽度
	private $height = 40; // 高度
	private $img; // 图形资源句柄
	private $font; // 指定的字体
	private $fontsize = 20; // 指定字体大小
	private $fontcolor; // 指定字体颜色
	                    // 构造方法初始化
	public function __construct() {
		if (! function_exists ( 'imagecreatetruecolor' )) {
			throw new \Exception ( '未开启 DB 扩展。' );
		}
		$this->font = ark_combine ( ARK_PATH, 'resources\fonts\elephant.ttf' );
	}
	
	// 生成随机码
	private function genCode() {
		$_len = strlen ( $this->charset ) - 1;
		for($i = 0; $i < $this->codelen; $i ++) {
			$this->code .= $this->charset [mt_rand ( 0, $_len )];
		}
	}
	
	// 生成背景
	private function genBg() {
		$this->img = imagecreatetruecolor ( $this->width, $this->height );
		$color = imagecolorallocate ( $this->img, mt_rand ( 157, 255 ), mt_rand ( 157, 255 ), mt_rand ( 157, 255 ) );
		imagefilledrectangle ( $this->img, 0, $this->height, $this->width, 0, $color );
	}
	private $_fontColors=array();
	// 生成文字
	private function genFont() {
		$_x = $this->width / $this->codelen;
		for($i = 0; $i < $this->codelen; $i ++) {
			$color = imagecolorallocate ( $this->img, mt_rand ( 0, 156 ), mt_rand ( 0, 156 ), mt_rand ( 0, 156 ) );
			$this->_fontColors[]=$color;
			imagettftext ( $this->img, $this->fontsize, mt_rand ( - 30, 30 ), $_x * $i + mt_rand ( 1, 5 ), $this->height / 1.4, $color, $this->font, $this->code [$i] );
		}
	}
	
	private function &genRandColors() {
		$arr = array (
				mt_rand ( 0, 255 ),
				mt_rand ( 0, 255 ),
				mt_rand ( 0, 255 ) 
		);
		
		return $arr;
	}
	
	// 生成线条、雪花
	private function genLine() {
		// 线条
		for($i = 0; $i < 6; $i ++) {
			imageline ( $this->img, mt_rand ( 0, $this->width ), mt_rand ( 0, $this->height ), mt_rand ( 0, $this->width ), mt_rand ( 0, $this->height ), $this->_fontColors[mt_rand ( 0, 3 )] );
		}
		// 雪花
		for($i = 0; $i < 20; $i ++) {
			$color = imagecolorallocate ( $this->img, mt_rand ( 200, 255 ), mt_rand ( 200, 255 ), mt_rand ( 200, 255 ) );
			imagestring ( $this->img, mt_rand ( 5, 50 ), mt_rand ( 0, $this->width ), mt_rand ( 0, $this->height ), $this->charset [mt_rand ( 0, 26 )], $color );
		}
	}
	
	/**
	 * 生成验证码，并返回图片资源。
	 * 
	 * @param string $code
	 *        	返回全小写的验证码字串。
	 * @param int $width
	 *        	图片宽度
	 * @param int $height
	 *        	图片高度
	 * @param string $bgColor
	 *        	背景颜色
	 * @return resource
	 */
	public function &generate(&$code, $width = 100, $height = 40, $bgColor = '') {
		$this->genBg ();
		$this->genCode ();
		$this->genFont();
		$this->genLine ();
		$code = strtolower ( $this->code );
		return $this->img;
	}
}

?>