<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Kernel\User\Access as UserAccess;

/**
 * User Access
 */
class AccessServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$UserAccess = new UserAccess();
		//$Access->setLogin(1);
		return $UserAccess;
	}
}
