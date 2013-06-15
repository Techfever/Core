<?php
namespace Kernel;

class Config {

	/**
	 *
	 * @var Config
	 */
	private static $_config = array();

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		self::prepare();
	}

	/**
	 * Start Config
	 *
	 * @return void
	 */
	public function initialize() {
		foreach (self::$_config as $_configKey => $_configInfo) {
			self::$_config[$_configKey]['data'] = require_once $_configInfo['path'];
		}
	}

	/**
	 * Verify & Prepare Config
	 *
	 * @return void
	 */
	public function prepare() {
		$_configPath = CORE_PATH . '/config/';
		if (file_exists($_configPath)) {
			$_configFileRaw = scandir($_configPath);
			foreach ($_configFileRaw as $_configFile) {
				$_configFilePath = $_configPath . $_configFile;
				$_configFileInfo = pathinfo($_configFilePath);
				if (file_exists($_configFilePath) && substr($_configFileInfo['basename'], -10) == 'config.php') {
					$_configFileInfo['path'] = $_configFilePath;
					$_configFileInfo['extension'] = substr($_configFileInfo['basename'], -10);
					$_configFileInfo['filename'] = ucfirst(substr($_configFileInfo['basename'], 0, -11));
					self::$_config[$_configFileInfo['filename']] = $_configFileInfo;
				}
			}
		}
	}

	/**
	 * Get Config Info
	 *
	 * @return Array $_config
	 */
	public function getConfig() {
		return self::$_config;
	}
}