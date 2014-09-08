<?php

namespace Techfever\Snapshot;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Snapshot service factory.
 */
class SnapshotServiceFactory implements FactoryInterface {
	/**
	 * Create Snapshot service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return Snapshot
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$session = $serviceLocator->get ( 'session' );
		$router = $serviceLocator->get ( 'Router' );
		$request = $serviceLocator->get ( 'Request' );
		$response = $serviceLocator->get ( 'Response' );
		$controller = null;
		$routeMatch = $router->match ( $request );
		if (! is_null ( $routeMatch )) {
			$controller = $routeMatch->getParam ( 'controller' );
		}
		$Snapshot = new Snapshot ( $session, $controller, $response );
		$Snapshot->set ();
		return $Snapshot;
	}
}
