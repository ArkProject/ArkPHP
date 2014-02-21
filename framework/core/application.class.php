<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'access denied' );

/**
 * Represents a web application.
 *
 * @author jun
 *        
 */
class Application {
	
	/**
	 *
	 * @var string
	 */
	private $_appPath;
	private $_controllerPath;
	private $_dataPath;
	private $_viewPath;
	private $_modelPath;
	/**
	 *
	 * @var array
	 */
	protected $settings;
	protected $routing;
	public final function __construct($appPath, &$settings,&$routing) {
		$this->_appPath = $appPath;
		$this->_controllerPath=$appPath.'controllers/';
		$this->_viewPath=$appPath.'views/';
		$this->_modelPath=$appPath.'models/';
		$this->settings = $settings;
		$this->routing=$routing;
		$this->init();
	}
	
	/**
	 * starts this application.
	 *
	 * @param array $settings        	
	 
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
			
			if(isset($_GET['id'])){
				echo '姓名：张三；证件号码：'.$_GET['id'].' 客户号：3011';
			}
			else 
				echo '空参数';
			
			self::$_instance->test();
			
			
			
			try {} catch (\Exception $e ) {
			_ark_display_error ( $e );
		}
		//
		//exit ( 0 );
	}
	*/
	
	public function getView(){
		return new View();
	}
	
	protected function init(){
		if(isset($_GET['_c'])){
			$this->routing['controller']=$_GET['_c'];
		}
		else if(isset($_POST['_c'])){
			$this->routing['controller']=$_POST['_c'];
		}
		
		if(isset($_GET['_a'])){
			$this->routing['action']=$_GET['_a'];
		}
		else if(isset($_POST['_a'])){
			$this->routing['action']=$_POST['_a'];
		}
		//$data=serialize($this->settings);
		//file_put_contents(ROOT_DIR.'data/temp/c/1.php', $data);
		$controller=ucfirst($this->routing['controller']).'Controller';
		$filename=$this->_controllerPath.strtolower($this->routing['controller']).'.controller.php';
		
		if(!@file_exists($filename)){
			throw new \Exception('[404]未找到指定 Controller。Controller:'. $filename);
		}
		include $filename;
		
		$instance=new $controller($this);
		
		
	}
	
	public function getRouting(){
		return $this->routing;
	}
	
	
	/**
	 *
	 * @param unknown $dbConfigIndex        	
	 * @param string $newInstance        	
	 */
	public function usingDB($dbConfigIndex, $newInstance = FALSE) {
	}
	
	
	public function getAppPath(){
		return $this->_appPath;
	}
	
	/**
	 * 映射一个相对于应用程序的服务器路径。
	 * @param string $path
	 */
	public function mapPath($path){
		return $this->getAppPath().$path;
	}
	
	
	
}

?>