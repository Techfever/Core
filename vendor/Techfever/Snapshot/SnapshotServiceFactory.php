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
		$session = $serviceLocator->get('session');
		$Snapshot = new Snapshot($session);
		$Snapshot->set();
		return $Snapshot;
	}
}
