<?php
namespace Techfever\Template\Plugin\Controllers;

use Traversable;
use Zend\EventManager\EventInterface;
use Zend\Mvc\Exception;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Url extends AbstractPlugin {
	/**
	 * Generates a URL based on a route
	 *
	 * @param  string             $route              RouteInterface name
	 * @param  array|Traversable  $params             Parameters to use in url generation, if any
	 * @param  array|bool         $options            RouteInterface-specific options to use in url generation, if any.
	 *                                                If boolean, and no fourth argument, used as $reuseMatchedParams.
	 * @param  bool               $reuseMatchedParams Whether to reuse matched parameters
	 *
	 * @throws \Zend\Mvc\Exception\RuntimeException
	 * @throws \Zend\Mvc\Exception\InvalidArgumentException
	 * @throws \Zend\Mvc\Exception\DomainException
	 * @return string
	 */
	public function fromRoute($route = null, $params = array(), $options = array(), $reuseMatchedParams = false) {
		$controller = $this->getController();
		if (!$controller instanceof InjectApplicationEventInterface) {
			throw new Exception\DomainException('Url plugin requires a controller that implements InjectApplicationEventInterface');
		}

		if (!is_array($params)) {
			if (!$params instanceof Traversable) {
				throw new Exception\InvalidArgumentException('Params is expected to be an array or a Traversable object');
			}
			$params = iterator_to_array($params);
		}

		$event = $controller->getEvent();
		$router = null;
		$matches = null;
		if ($event instanceof MvcEvent) {
			$router = $event->getRouter();
			$matches = $event->getRouteMatch();
		} elseif ($event instanceof EventInterface) {
			$router = $event->getParam('router', false);
			$matches = $event->getParam('route-match', false);
		}
		if (!$router instanceof RouteStackInterface) {
			throw new Exception\DomainException('Url plugin requires that controller event compose a router; none found');
		}

		if (3 == func_num_args() && is_bool($options)) {
			$reuseMatchedParams = $options;
			$options = array();
		}

		if ($route === null) {
			if (!$matches) {
				throw new Exception\RuntimeException('No RouteMatch instance present');
			}

			$route = $matches->getMatchedRouteName();

			if ($route === null) {
				throw new Exception\RuntimeException('RouteMatch does not contain a matched route name');
			}
		}

		if ($reuseMatchedParams && $matches) {
			$routeMatchParams = $matches->getParams();

			if (isset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER])) {
				$routeMatchParams['controller'] = $routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER];
				unset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER]);
			}

			if (isset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE])) {
				unset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE]);
			}

			$params = array_merge($routeMatchParams, $params);
		}

		$options['name'] = $route;

		/* Technation Added */
		$url = $router->assemble($params, $options);
		if (substr($url, -1) === '/') {
			$url = substr($url, 0, (strlen($url) - 1));
		}
		return $url;
	}
}
