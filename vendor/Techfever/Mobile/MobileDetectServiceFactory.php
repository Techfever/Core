<?php

namespace Techfever\Mobile;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Mobile Detect Module container abstract service factory.
 */
class MobileDetectServiceFactory implements FactoryInterface {
	/**
	 * Create Template Module service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return Template
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		return new MobileDetect ();
	}
}