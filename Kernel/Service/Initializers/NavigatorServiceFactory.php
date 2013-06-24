<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Kernel\Navigator;

/**
 * Navigator
 */
class NavigatorServiceFactory extends DefaultNavigationFactory {
	/**
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return array
	 * @throws \Zend\Navigation\Exception\InvalidArgumentException
	 */
	protected function getPages(ServiceLocatorInterface $serviceLocator) {
		if (null === $this->pages) {
			$Navigator = new Navigator();
			$configuration['navigation'][$this->getName()] = $Navigator->getData();

			if (!isset($configuration['navigation'])) {
				throw new Exception\InvalidArgumentException('Could not find navigation configuration key');
			}
			if (!isset($configuration['navigation'][$this->getName()])) {
				throw new Exception\InvalidArgumentException(sprintf('Failed to find a navigation container by the name "%s"', $this->getName()));
			}

			$application = $serviceLocator->get('Application');
			$routeMatch = $application->getMvcEvent()->getRouteMatch();
			$router = $application->getMvcEvent()->getRouter();
			$pages = $this->getPagesFromConfig($configuration['navigation'][$this->getName()]);

			$this->pages = $this->injectComponents($pages, $routeMatch, $router);
		}
		return $this->pages;
	}
}
