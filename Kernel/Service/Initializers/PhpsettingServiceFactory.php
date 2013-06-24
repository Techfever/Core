<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Phpsetting.
 */
class PhpsettingServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$config = $serviceLocator->get('Config');
		$phpSettings = $config['phpSettings'];
		if ($phpSettings) {
			foreach ($phpSettings as $key => $value) {
				ini_set($key, $value);
			}
		}
		return true;
	}
}
