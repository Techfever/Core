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
		$UserAccess = new UserAccess ( array (
				'servicelocator' => $serviceLocator 
		) );
		// $UserAccess->setLogin(1);
		// $UserAccess->setLoginWallet(1);
		return $UserAccess;
	}
}
