<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Kernel\Access;

/**
 * Access
 */
class AccessServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$Access = new Access();
		//$Access->setLogin(1);
		return $Access;
	}
}
