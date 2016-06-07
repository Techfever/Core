<?php

namespace Techfever\UrlRewrite;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Mobile Detect Module container abstract service factory.
 */
class UrlRewriteServiceFactory implements FactoryInterface {
	/**
	 * Create Template Module service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return Template
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$config = $serviceLocator->get ( 'Config' );
		$options = isset ( $config ['urlrewrite'] ) ? $config ['urlrewrite'] : array ();
		$options ['servicelocator'] = $serviceLocator;
		return new UrlRewrite ( $options );
	}
}