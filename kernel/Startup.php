<?php
namespace Kernel;
class Startup {

	/**
	 *
	 * @var Service
	 */
	private static $Service = array();

	/**
	 *
	 * @var Config
	 */
	private static $Config = array();

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
	public function initialize() {
		self::initConfig();
		self::initService();
	}

	/**
	 * Initialize Service
	 *
	 * @return void
	 */
	public function initService() {
		require_once KERNEL_PATH . '/Service.php';
		$Service = new Service();
		$Service->initialize();
		self::$Service = $Service->getService();
	}

	/**
	 * Get Service
	 *
	 * @return Array String
	 */
	public function getService($name, $option = null) {
		if (!empty($name)) {
			if (!empty($option)) {
				return self::$Service[$name][$option];
			} else {
				return self::$Service[$name];
			}
		}
		return self::$Service;
	}

	/**
	 * Initialize Config
	 *
	 * @return void
	 */
	public function initConfig() {
		require_once KERNEL_PATH . '/Config.php';
		$Config = new Config();
		$Config->initialize();
		self::$Config = $Config->getConfig();
	}

	/**
	 * Get Config
	 *
	 * @return Array String
	 */
	public function getConfig($name, $key = null) {
		if (!empty($name)) {
			if (!empty($key)) {
				return self::$Config[$name][$key];
			} else {
				return self::$Config[$name];
			}
		}
		return self::$Config;
	}

	/**
	 * Magic Call Function
	 *
	 * @return Object
	 */
	public function __call($function, $variable = null) {
		if (array_key_exists($function, self::$Service)) {
			return self::$Service[$function]['object'];
		}
		return False;
	}
}
