<?php
namespace ark;
if (! defined ( 'ARK' )) {
	exit ( 'deny access' );
}

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
			// echo var_dump($_GET);
			
			// echo '<br>Welcome use ArkPHP Framework v1.0.1.<br>';
			// todo: //URL hook logics in here...
			// ark_strIndexOf();
			// ark_strLength();
			// ark_errorHandle();
			ark_handleFileError();
			ark_loadFiles ( 'F:\test\test.txt' );
			ark_unhandleError();
			//throw new \Exception('ff');
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
}

?>