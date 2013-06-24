<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Kernel\Access\Log as AccessLog;

/**
 * Access Log
 */
class AccessLogServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$AccessLog = new AccessLog();
		$AccessLog->insert();
		return $AccessLog;
	}
}
