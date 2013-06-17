<?php
namespace Kernel\ServiceFactory\Cache;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Cache\PatternFactory;

class OutputCacheServiceFactory implements FactoryInterface {
	/**
	 * Create db adapter service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return Adapter
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$config = $serviceLocator->get ( 'Config' );
		$options = $config ['cachepattern'] ['output'];
		
		$pattern = PatternFactory::factory ( 'output', $options ['options'] );
		
		return $pattern;
	}
}
