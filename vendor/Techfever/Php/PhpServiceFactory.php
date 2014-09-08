<?php

namespace Techfever\Php;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Template Module service factory.
 */
class PhpServiceFactory implements FactoryInterface {
	/**
	 * Create Template Module service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return Template
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$config = $serviceLocator->get ( 'Config' );
		$phpConfig = isset ( $config ['php'] ) ? $config ['php'] : array ();
		return new Php ( $phpConfig );
	}
}
