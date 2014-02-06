<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'deny access' );

/**
 * Represents a web application.
 *
 * @author jun你好
 *        
 */
class Application {
	
	/**
	 *
	 * @var Application
	 */
	private static $_instance;
	/**
	 *
	 * @var string
	 */
	private $_appName;
	/**
	 *
	 * @var array
	 */
	private $_settings;
	protected function __construct($appName, $settings) {
		$this->_appName = $appName;
		$this->_settings = $settings;
	}
	
	/**
	 * starts this application.
	 *
	 * @param array $settings        	
	 */
	public static final function run($settings = NULL, $appName = NULL) {
		
		
			
			if (self::$_instance) {
				throw new Exception ( 'Application has already started.' );
			}
			
			if (! $appName) {
				$arr = ark_split ( $_SERVER ['PHP_SELF'], '/', TRUE );
				$arr = ark_split ( end ( $arr ), '.' );
				$appName = $arr [0];
			}
			
			self::$_instance = new Application ( $appName, $settings );
			
			$GLOBALS['__ARK_LANGS']['zh-cn']=include ARK_PATH.'i18n/langs/zh-cn.php';
			
			//var_dump($GLOBALS['__ARK_LANGS']['zh-cn']);
			
			//ark_loadFile ( 'F:\test\test.txt' );
			//throw new \Exception('ff');
			/*
			if(isset($_GET['id'])){
				echo '姓名：张三；证件号码：'.$_GET['id'].' 客户号：3011';
			}
			else 
				echo '空参数';
			*/
			self::$_instance->test();
			
			
			
			try {} catch (\Exception $e ) {
			_ark_display_error ( $e );
		}
		//
		//exit ( 0 );
	}
	
	/**
	 * gets current Application Instance.
	 *
	 * @return Application
	 */
	public static function getInstance() {
		if (! self::$_instance) {
			throw new Exception ( 'Application not started yet.' );
		}
		return self::$_instance;
	}
	
	/**
	 *
	 * @param unknown $dbConfigIndex        	
	 * @param string $newInstance        	
	 */
	public function usingDB($dbConfigIndex, $newInstance = FALSE) {
	}
	
	function test(){
		$c=new \ark\template\Compiler(ARK_PATH.'../apps/default/views/test.tpl.html');
		$text=$c->compileToString();
		echo '编译结果：<br><hr><textarea style="width:100%; height:300px;">';
		echo $text;
		echo '</textarea>';
	}
}

?>