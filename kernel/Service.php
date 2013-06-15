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
		require_once KERNEL_PATH . '/Service/ServiceClass.php';
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
	 * Get Service Info
	 *
	 * @return Array $_service
	 */
	public function getService() {
		return self::$_service;
	}
}
