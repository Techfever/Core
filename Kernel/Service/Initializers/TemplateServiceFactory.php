<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Kernel\Template;
use Kernel\ServiceLocator;

/**
 * Phpsetting.
 */
class TemplateServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		Template::prepare(include CORE_PATH . '/Kernel/Module/Config/module.config.php');
		ServiceLocator::setServiceConfig(Template::getConfig());
		return true;
	}
}
