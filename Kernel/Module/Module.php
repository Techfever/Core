<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Module;

use Kernel\Template;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module {
	public function onBootstrap(MvcEvent $e) {
		$application = $e->getApplication();
		$eventManager = $application->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		$serviceManager = $e->getApplication()->getServiceManager();
		//print_r($serviceManager->get('Config'));
	}
	public function getConfig() {
		Template::prepare(include __DIR__ . '/Config/module.config.php');
		return Template::getConfig();
	}
	public function getAutoloaderConfig() {
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/autoload_classmap.php'
			), 'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/'
				)
			)
		);
	}
}
