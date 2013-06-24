<?php
namespace Kernel;

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service;

class Startup {
	/**
	 * Prepare
	 *
	 * @return void
	 */
	public static function prepare() {

		// Run the application!
		//Mvc\Application::init(require CORE_PATH . '/config/application.config.php')->run();
		$configuration = require CORE_PATH . '/config/application.config.php';
		$smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
		$serviceManager = new ServiceManager(new Service\ServiceManagerConfig($smConfig));
		$serviceManager->setService('ApplicationConfig', $configuration);
		$serviceManager->get('ModuleManager')->loadModules();

		ServiceLocator::setServiceManager($serviceManager);
	}

	/**
	 * Bootstrap
	 *
	 * @return void
	 */
	public static function bootstrap() {
		$serviceManager = ServiceLocator::getServiceManager();
		return $serviceManager->get('Application')->bootstrap()->run();
	}
}
