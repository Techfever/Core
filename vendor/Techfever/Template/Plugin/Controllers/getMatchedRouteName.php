<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\EventManager\EventInterface;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Exception;

class getMatchedRouteName extends AbstractPlugin {
	
	/**
	 *
	 * @param
	 *        	MatchedRouteName
	 */
	public function __invoke() {
		$controller = $this->getController ();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new Exception\DomainException ( 'MatchedRouteName plugin requires a controller that implements InjectApplicationEventInterface' );
		}
		
		$event = $controller->getEvent ();
		$matches = null;
		if ($event instanceof MvcEvent) {
			$matches = $event->getRouteMatch ();
		} elseif ($event instanceof EventInterface) {
			$matches = $event->getParam ( 'route-match', false );
		}
		if (! $matches) {
			throw new Exception\RuntimeException ( 'No RouteMatch instance present' );
		}
		
		return $matches->getMatchedRouteName ();
	}
}
