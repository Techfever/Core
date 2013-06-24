<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Kernel\Snapshot;

/**
 * Snapshot
 */
class SnapshotServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$Snapshot = new Snapshot();
		$Snapshot->set();
		return $Snapshot;
	}
}
