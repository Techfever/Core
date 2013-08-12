<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\InjectApplicationEventInterface;
use Techfever\Exception\RuntimeException;
use Zend\Cache\PatternFactory;

class getPageCache extends AbstractPlugin {
	/**
	 * Grabs a param from route match by default.
	 *
	 * @param string $param
	 * @param mixed $default
	 * @return mixed
	 */
	public function __invoke() {
		$controller = $this->getController();
		$serviceLocator = $this->getController()->getServiceLocator();

		if (!$controller instanceof InjectApplicationEventInterface) {
			throw new RuntimeException('Controllers must implement Zend\Mvc\InjectApplicationEventInterface to use this plugin.');
		}

		$action = $controller->getEvent()->getRouteMatch()->getParam('action');

		$config = $serviceLocator->get('Config');
		$config = (isset($config['cachepattern']) ? $config['cachepattern'] : array());
		$config = (isset($config['output']) ? $config['output'] : array());
		$option = (isset($config['options']) ? $config['options'] : array());
		$cache = PatternFactory::factory('output', $option);

		return $cache;
	}
}
