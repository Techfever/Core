<?php
namespace Techfever\Kernel;
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
		$_configPath = KERNEL_PATH . '/Config/';
		if (file_exists($_configPath)) {
			$_configFileRaw = scandir(KERNEL_PATH . '/Config/');
			foreach ($_configFileRaw as $_configFile) {
				$_configFilePath = $_configPath . $_configFile;
				$_configFileInfo = pathinfo($_configFilePath);
				if (file_exists($_configFilePath) && $_configFileInfo['extension'] == 'php') {
					$_configFileInfo['path'] = $_configFilePath;
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
