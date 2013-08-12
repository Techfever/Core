<?php
namespace Techfever\Session;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Session service factory.
 */
class SessionServiceFactory implements FactoryInterface {
	/**
	 * Create Session service
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return Template
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$config = $serviceLocator->get('Config');
		$sessionConfig = isset($config['session']) ? $config['session'] : array();
		return new Session($sessionConfig);
	}
}
