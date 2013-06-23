<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Module;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Kernel\ServiceLocator;
use Kernel\Template;

class Module {
	public function onBootstrap(MvcEvent $e) {
		$application = $e->getApplication();
		$eventManager = $application->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
	}
	public function getConfig() {
		return include __DIR__ . '/Config/module.config.php';
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
