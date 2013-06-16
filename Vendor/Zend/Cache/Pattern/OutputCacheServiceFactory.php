<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Cache\Storage\Adapter;

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
