<?php

namespace Techfever\Navigator;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Techfever\Exception;
use Techfever\Template\Plugin\Controllers\isBackend;

/**
 * Navigator
 */
class NavigatorServiceFactory extends DefaultNavigationFactory {
	
	/**
	 *
	 * @var object
	 */
	protected $navigator;
	
	/**
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return \Techfever\Navigator\Navigator
	 */
	public function getNavigator(ServiceLocatorInterface $serviceLocator = null) {
		if (! is_object ( $this->navigator ) && empty ( $this->navigator )) {
			$this->navigator = new Navigator ( array (
					'servicelocator' => $serviceLocator 
			) );
		}
		return $this->navigator;
	}
	
	/**
	 *
	 * @return array
	 * @throws \Zend\Navigation\Exception\InvalidArgumentException
	 */
	protected function getPages(ServiceLocatorInterface $serviceLocator) {
		if (null === $this->pages) {
			$Navigator = $this->getNavigator ( $serviceLocator );
			$isBackend = $this->isBackend ( $serviceLocator );
			$configuration ['navigation'] [$this->getName ()] = $Navigator->getStructure ( $isBackend );
			if (! isset ( $configuration ['navigation'] )) {
				throw new Exception\InvalidArgumentException ( 'Could not find navigation configuration key' );
			}
			if (! isset ( $configuration ['navigation'] [$this->getName ()] )) {
				throw new Exception\InvalidArgumentException ( sprintf ( 'Failed to find a navigation container by the name "%s"', $this->getName () ) );
			}
			
			$application = $serviceLocator->get ( 'Application' );
			$routeMatch = $application->getMvcEvent ()->getRouteMatch ();
			$router = $application->getMvcEvent ()->getRouter ();
			$pages = $this->getPagesFromConfig ( $configuration ['navigation'] [$this->getName ()] );
			
			$this->pages = $this->injectComponents ( $pages, $routeMatch, $router );
		}
		return $this->pages;
	}
	
	/**
	 *
	 * @return boolean
	 */
	protected function isBackend(ServiceLocatorInterface $serviceLocator) {
		$application = $serviceLocator->get ( 'Application' );
		$controller = $application->getMvcEvent ()->getTarget ();
		$isBackend = false;
		if (method_exists ( $controller, 'getPluginManager' )) {
			if ($controller->getPluginManager ()->has ( 'isBackend' )) {
				$isBackend = $controller->getPluginManager ()->get ( 'isBackend' );
			} else {
				$isBackend = new isBackend ();
			}
			$isBackend = $isBackend->__invoke ();
		}
		return $isBackend;
	}
}
