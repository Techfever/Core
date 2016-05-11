<?php

namespace Techfever\User;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Techfever\User\Log as UserLog;

/**
 * User Log
 */
class LogServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$UserLog = new UserLog ( array (
				'servicelocator' => $serviceLocator 
		) );
		$controller = null;
		$router = $serviceLocator->get ( 'Router' );
		$request = $serviceLocator->get ( 'Request' );
		$routeMatch = $router->match ( $request );
		if (! is_null ( $routeMatch )) {
			$controller = $routeMatch->getParam ( 'controller' );
		}
		$uristatus = true;
		if (preg_match ( '/Widget/', $controller )) {
			$uristatus = false;
		} else if (preg_match ( '/Theme/', $controller )) {
			$uristatus = false;
		}
		if ($uristatus) {
			$UserLog->prepare ();
			$UserLog->insert ();
		}
		return $UserLog;
	}
}
