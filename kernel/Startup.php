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
		self::initConfig();
		self::initService();
	}

	/**
	 * Start Kernel
	 *
	 * @return void
	 */
	public static function start() {

	}

	/**
	 * UnInitialize Kernel
	 *
	 * @return void
	 */
	public static function uninitialize() {
		unset(self::$Config);
		unset(self::$Service);
	}

	/**
	 * Verify & Prepare
	 *
	 * @return void
	 */
	public function prepare() {
		if (is_null(self::$Instance)) {
			self::$Instance = new Startup();
		}
		return self::$Instance;
	}

	/**
	 * Initialize Config
	 *
	 * @return void
	 */
	public function initConfig() {
		self::$Config = new Config();
		self::$Config->initialize();

		print_r(self::$Config->getConfig('Database'));
	}

	/**
	 * Initialize Config
	 *
	 * @return void
	 */
	public function initService() {
		self::$Service = new Service();
		self::$Service->initialize();
		print_r(self::$Service->getService('Superglobal', 'object')->getVariable('Global'));
	}
}
