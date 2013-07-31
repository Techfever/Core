<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Kernel\User\Log as UserLog;

/**
 * User Log
 */
class AccessLogServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$UserLog = new UserLog();
		$UserLog->insert();
		return $UserLog;
	}
}
