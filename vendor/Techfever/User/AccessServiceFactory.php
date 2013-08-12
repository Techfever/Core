<?php
namespace Techfever\User;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Techfever\User\Access as UserAccess;

/**
 * User Access
 */
class AccessServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$database = $serviceLocator->get('db');
		$session = $serviceLocator->get('session');
		$UserAccess = new UserAccess($database, $session);
		$UserAccess->setLogin(1);
		return $UserAccess;
	}
}
