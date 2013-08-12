<?php
namespace Techfever\Template;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Template Module container abstract service factory.
 */
class TemplateServiceFactory implements FactoryInterface {
	/**
	 * Create Template Module service
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return Template
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {

		$database = $serviceLocator->get('db');
		$session = $serviceLocator->get('session');
		return new Template($database, $session);
	}
}
