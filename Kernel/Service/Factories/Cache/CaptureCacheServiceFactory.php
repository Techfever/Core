<?php
namespace Kernel\Service\Factories\Cache;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Cache\PatternFactory;

class CaptureCacheServiceFactory implements FactoryInterface {
	/**
	 * Create db adapter service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return Adapter
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$config = $serviceLocator->get('Config');
		$options = $config['cachepattern']['capture'];

		$pattern = PatternFactory::factory('capture', $options['options']);

		return $pattern;
	}
}
