<?php
namespace Kernel;

class Service {

	/**
	 *
	 * @var Service
	 */
	private static $_service = array();

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		self::prepare();
	}

	/**
	 * Start Service
	 *
	 * @return void
	 */
	public function initialize() {
		foreach (self::$_service as $_serviceKey => $_serviceInfo) {
			$classname = "Kernel\Service\\" . $_serviceInfo['filename'];
			self::$_service[$_serviceKey]['object'] = new $classname();
			self::$_service[$_serviceKey]['object']->start();
		}
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
