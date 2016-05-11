<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\InjectApplicationEventInterface;
use Techfever\Exception\RuntimeException;
use Zend\Mvc\MvcEvent;

class renderViewModal extends AbstractPlugin {
	public function __invoke($variables = array()) {
		$controller = $this->getController ();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new RuntimeException ( 'Controllers must implement Zend\Mvc\InjectApplicationEventInterface to use this plugin.' );
		}
		
		$event = $controller->getEvent ();
		if ($event instanceof MvcEvent) {
			$event->getViewModel ()->setTemplate ( 'blank/layout' );
		}
		return $variables;
	}
}
