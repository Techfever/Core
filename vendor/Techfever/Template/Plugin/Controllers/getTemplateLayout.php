<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Exception;

class getTemplateLayout extends AbstractPlugin {
	public function __invoke() {
		$controller = $this->getController ();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new Exception\DomainException ( 'MatchedRouteName plugin requires a controller that implements InjectApplicationEventInterface' );
		}
		$layout = null;
		$event = $controller->getEvent ();
		if ($event instanceof MvcEvent) {
			$layout = $event->getViewModel ()->getTemplate ();
		}
		return $layout;
	}
}
