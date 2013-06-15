<?php
namespace Kernel;

class Startup {

	/**
	 *
	 * @var Config
	 */
	public static $Config = array();

	/**
	 *
	 * @var Service
	 */
	public static $Service = array();

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
		self::$Config = new Config();
		self::$Config->initialize();

		// Initialize Service
		self::$Service = new Service();
		self::$Service->initialize();
	}

	/**
	 * UnInitialize Kernel
	 *
	 * @return void
	 */
	public static function uninitialize() {
		// UnInitialize Config
		self::$Config->uninitialize();
		self::$Config = null;

		// UnInitialize Service
		self::$Service->uninitialize();
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
}
