<?php
namespace Kernel;

class Startup {

	/**
	 *
	 * @var Config
	 */
	private static $Config = array();

	/**
	 *
	 * @var Service
	 */
	private static $Service = array();

	public static $Instance = null;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Initialize Kernel
	 *
	 * @return void
	 */
	public static function initialize() {
		// Initialize Config
		self::initConfig();
		// Initialize Service
		self::initService();
	}

	/**
	 * UnInitialize Kernel
	 *
	 * @return void
	 */
	public static function uninitialize() {
		// UnInitialize Config
		self::$Config = null;

		// UnInitialize Service
		self::$Service = null;

		self::$Instance = null;
	}

	/**
	 * Verify & Prepare Kernel
	 *
	 * @return $Instance
	 */
	public static function prepare() {
		if (is_null(self::$Instance)) {
			self::$Instance = new Startup();
		}
		return self::$Instance;
	}

	/**
	 * Render Kernel
	 *
	 * @return void
	 */
	public static function render() {

	}

	/**
	 * Initialize Config
	 *
	 * @return void
	 */
	public static function initConfig() {
		$_configPath = CONFIG_PATH . '/';
		if (file_exists($_configPath)) {
			$_configFileRaw = scandir($_configPath);
			foreach ($_configFileRaw as $_configFile) {
				$_configFilePath = $_configPath . $_configFile;
				$_configFileInfo = pathinfo($_configFilePath);
				if (file_exists($_configFilePath) && substr($_configFileInfo['basename'], -10) == 'config.php') {
					$_configFileInfo['filename'] = substr($_configFileInfo['basename'], 0, -11);
					self::$Config[strtolower($_configFileInfo['filename'])] = require_once $_configFilePath;
				}
			}
		}
	}

	/**
	 * Get Config
	 *
	 * @return Array String
	 */
	public static function getConfig($name = null, $key = null) {
		if (!empty($name)) {
			$name = strtolower($name);
			if (!empty($key)) {
				return self::$Config[$name][$key];
			} else if (array_key_exists($name, self::$Config)) {
				return self::$Config[$name];
			}
		}
		return self::$Config;
	}

	/**
	 * Initialize Service
	 *
	 * @return void
	 */
	public static function initService() {
		$_config = self::getConfig();
		$_servicePath = KERNEL_PATH . '/Service/';
		if (file_exists($_servicePath)) {
			$_serviceFileRaw = scandir($_servicePath);
			foreach ($_serviceFileRaw as $_serviceFile) {
				$_serviceFilePath = $_servicePath . $_serviceFile;
				$_serviceFileInfo = pathinfo($_serviceFilePath);
				if (file_exists($_serviceFilePath) && $_serviceFileInfo['extension'] == 'php' && $_serviceFileInfo['filename'] != 'ServiceInterface') {
					$classname = __NAMESPACE__ . "\Service\\" . $_serviceFileInfo['filename'];
					$service = strtolower($_serviceFileInfo['filename']);
					$_config = (array_key_exists($service, $_config) ? $_config[$service] : null);
					self::$Service[$_serviceFileInfo['filename']]['object'] = new $classname($_config);
					self::$Service[$_serviceFileInfo['filename']]['object']->start();
				}
			}
		}
	}

	/**
	 * Get Service
	 *
	 * @return Array String
	 */
	public static function getService($name = null, $option = null) {
		if (!empty($name)) {
			if (!empty($option)) {
				return self::$Service[$name][$option];
			} else if (array_key_exists($name, self::$Service)) {
				return self::$Service[$name];
			}
		}
		return self::$Service;
	}
}
