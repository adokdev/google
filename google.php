<?php
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', TRUE);
/**
 * BTECH
 *
 * Provides functionality to interact with Google data APIs
 * @category BTECH
 * @package  Google
 * @author   Chernov Aleksandr <adok@ukr.net>
 */
class Google 
{
  /**
   * Google api base URL
   */
	const ENDPOINT = "https://www.googleapis.com";

  /**
   * Instance of Google api library
   */
	private static $instance = null;

  /**
   * Global configuration array
   *
   * @var array
   */
	private static $configs  = array();

  /**
   * Required elements of configuration array
   *
   * @var array
   */
  private static $required_config_items  = array("Email","Passwd");

  /**
   * Defult configuration array
   *
   * @var array
   */
  private static $default_config = array(
  	"Email"    => "admin@gmail.com",
  	"Passwd" => "password"
  );

  /**
   * Array of Google_Auth instances
   *
   * @var array
   */
  private static $auths  = array();

  /**
   * Constructs a Google library
   */
	private function __construct(){
		define("GROOT", dirname(__FILE__)."/");
		$path = '.'.PATH_SEPARATOR.GROOT.PATH_SEPARATOR.get_include_path();
		set_include_path($path);
		spl_autoload_register(array($this, 'loader'));
	}

  /**
   * Retrieves the default google instance and set the auth config.
   *
   * @return Google
   */
	public static function instance($config = null){
		self::$instance = self::$instance ? self::$instance : new self;
		$config = is_null($config) ? self::$default_config : $config;
		self::set_config($config);
		return self::$instance;
	}

  /**
   * @return Google api base URL
   */
	public static function url()
	{
		return static::ENDPOINT;
	}

  /**
   * Authorize in google servise.
   *
   * @return Google
   */
	public function auth(){
		foreach(self::$configs as $config){
			$id = spl_object_hash((object)$config);
			if(!isset(self::$auths[$id])){
				self::$auths[$id] = new Google_Auth($config);
			}
		}
		return $this;
	}

  /**
   * Return the auth instances
   *
   * @return Google_Auth
   */
  public function auths(){
  	return self::$auths;
  }

  /**
   * Set global configuration options
   *
   * @param array $config
   * @throws Google_Exception
   */
	private static function set_config(array $config){
		$missed = array();
		$valid  = true;

		if(!is_array($config)){
			throw new Google_Exception("Config is not an Array");
		}

		foreach (self::$required_config_items as $key) {
			if(!in_array($key, array_keys($config))){
				array_push($missed, $key);
				$valid  = false;
			}
		}
		if($valid){
			array_push(self::$configs, $config);
		}
		else{
			throw new Google_Exception("Config not contains required data: ".implode(",", $missed));
		}
		
	}

  /**
   * @return Google api base URL
   * @throws Exception
   */
	private function loader($class){
		$file = strtolower(implode("/", explode("_", $class)));
		
		$path = GROOT . $file . '.php';
		if(file_exists($path)){
			require_once($path);   
		}
		else{
			throw new Exception("Called class {$class} file {$path} not exist");
			
		}
	}
}

?>