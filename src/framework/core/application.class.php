<?php
if (! defined ( 'ARK' )) {
	exit ( 'deny access' );
}
class Set implements Iterator {
	
	/**
	 *
	 * @var array
	 */
	protected $items;
	public function __construct($items = NULL) {
		if (! $items) {
			$this->items = array ();
		}
	}
	public function rewind() {
		reset ( $this->items );
	}
	public function current() {
		return current ( $this->items );
	}
	public function key() {
		return key ( $this->items );
	}
	public function next() {
		return next ( $this->items );
	}
	public function valid() {
		return ($this->current () !== false);
	}
}

/**
 * Represents a web application.
 *
 * @author jun
 *        
 */
class Application extends Event {
	
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
		try {
			
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
			ark_importFile ( 'F:\test\test.txt' );
		} catch ( Exception $e ) {
			display_error ( $e );
		}
		//
		exit ( 0 );
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