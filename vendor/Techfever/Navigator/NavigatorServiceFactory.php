<?php

namespace Techfever\Navigator;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Techfever\Exception;

/**
 * Navigator
 */
class NavigatorServiceFactory extends DefaultNavigationFactory {
	/**
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return array
	 * @throws \Zend\Navigation\Exception\InvalidArgumentException
	 */
	protected function getPages(ServiceLocatorInterface $serviceLocator) {
		if (null === $this->pages) {
			$Navigator = new Navigator ( array (
					'servicelocator' => $serviceLocator 
			) );
			$configuration ['navigation'] [$this->getName ()] = $Navigator->getStructure ();
			
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
}
