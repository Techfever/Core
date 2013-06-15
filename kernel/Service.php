<?php
namespace Kernel;

use Kernel\Startup;

class Service {

	/**
	 *
	 * @var Service
	 */
	private static $_service = array();
	private static $_config = array();

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct($_config) {
		self::$_config = $_config;
		self::prepare();
	}

	/**
	 * Start Service
	 *
	 * @return void
	 */
	public static function initialize() {
		foreach (self::$_service as $_serviceKey => $_serviceInfo) {
			$classname = __NAMESPACE__ . "\Service\\" . $_serviceInfo['filename'];
			$service = ucfirst($_serviceInfo['filename']);
			$option = (array_key_exists($service, self::$_config) ? self::$_config[$service] : null);
			self::$_service[$_serviceKey]['object'] = new $classname($option);
			self::$_service[$_serviceKey]['object']->start();
		}
	}

	/**
	 * UnInitialize Service
	 *
	 * @return void
	 */
	public static function uninitialize() {
		self::$_service = null;
	}

	/**
	 * Verify & Prepare Service
	 *
	 * @return void
	 */
	public function prepare() {
		$_servicePath = KERNEL_PATH . '/Service/';
		if (file_exists($_servicePath)) {
			$_serviceFileRaw = scandir($_servicePath);
			foreach ($_serviceFileRaw as $_serviceFile) {
				$_serviceFilePath = $_servicePath . $_serviceFile;
				$_serviceFileInfo = pathinfo($_serviceFilePath);
				if (file_exists($_serviceFilePath) && $_serviceFileInfo['extension'] == 'php' && $_serviceFileInfo['filename'] != 'ServiceClass') {
					$_serviceFileInfo['path'] = $_serviceFilePath;
					self::$_service[$_serviceFileInfo['filename']] = $_serviceFileInfo;
				}
			}
		}
	}

	/**
	 * Get Service
	 *
	 * @return Array String
	 */
	public function getService($name = null, $option = null) {
		if (!empty($name)) {
			if (!empty($option)) {
				return self::$_service[$name][$option];
			} else if (array_key_exists($name, self::$_service)) {
				return self::$_service[$name];
			}
		}
		return self::$_service;
	}
}
