<?php
namespace Kernel\Service\Factories\Cache;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Cache\StorageFactory;

class FilesystemServiceFactory implements FactoryInterface {
	/**
	 * Create db adapter service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return Adapter
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$config = $serviceLocator->get ( 'Config' );
		$options = $config ['cachestorage'] ['filesystem'];
		
		$cache = StorageFactory::adapterFactory ( 'filesystem' );
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
