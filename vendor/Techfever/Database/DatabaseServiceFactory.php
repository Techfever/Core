<?php
namespace Techfever\Database;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Template Module container abstract service factory.
 */
class DatabaseServiceFactory implements FactoryInterface {
	/**
	 * Create Template Module service
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return Template
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {

		$adapter = $serviceLocator->get('dbadapter');

		$cache = $serviceLocator->get('cachestorage');

		$log = $serviceLocator->get('log');

		return new Database($adapter, $cache, $log);
	}
}
