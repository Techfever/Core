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
use Zend\Cache\StorageFactory;

class XCacheServiceFactory implements FactoryInterface {
	/**
	 * Create db adapter service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return Adapter
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$config = $serviceLocator->get ( 'Config' );
		$options = $config ['cachestorage'] ['xcache'];
		
		$cache = StorageFactory::adapterFactory ( 'xcache' );
		$cache->setOptions ( $options ['options'] );
		
		$pluginConfig = $options ['plugins'];
		$plugin = false;
		$pluginName = null;
		$pluginOption = null;
		if (isset ( $pluginConfig ['clearexpiredbyfactor'] )) {
			$pluginName = 'clearexpiredbyfactor';
			$pluginOption = $pluginConfig ['clearexpiredbyfactor'];
			$plugin = true;
		} elseif (isset ( $pluginConfig ['exceptionhandler'] )) {
			$pluginName = 'exceptionhandler';
			$pluginOption = $pluginConfig ['exceptionhandler'];
			$plugin = true;
		} elseif (isset ( $pluginConfig ['ignoreuserabort'] )) {
			$pluginName = 'ignoreuserabort';
			$pluginOption = $pluginConfig ['ignoreuserabort'];
			$plugin = true;
		} elseif (isset ( $pluginConfig ['optimizebyfactor'] )) {
			$pluginName = 'optimizebyfactor';
			$pluginOption = $pluginConfig ['optimizebyfactor'];
			$plugin = true;
		} elseif (isset ( $pluginConfig ['serializer'] )) {
			$pluginName = 'serializer';
			$pluginOption = $pluginConfig ['serializer'];
			$plugin = true;
		}
		if ($plugin) {
			$plugin = StorageFactory::pluginFactory ( $pluginName, $pluginOption );
			$cache->addPlugin ( $plugin );
		}
		
		return $cache;
	}
}
