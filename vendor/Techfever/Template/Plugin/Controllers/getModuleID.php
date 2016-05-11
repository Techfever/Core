<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\InjectApplicationEventInterface;
use Techfever\Exception\RuntimeException;
use Techfever\Template\Plugin\Filters\ToUnderscore;

class getModuleID extends AbstractPlugin {
	/**
	 * Grabs a param from route match by default.
	 *
	 * @param string $param        	
	 * @param mixed $default        	
	 * @return mixed
	 */
	public function __invoke() {
		$ToUnderscore = new ToUnderscore ( '\\' );
		
		$controller = $this->getController ();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new RuntimeException ( 'Controllers must implement Zend\Mvc\InjectApplicationEventInterface to use this plugin.' );
		}
		
		$module = $ToUnderscore->filter ( $controller->getEvent ()->getRouteMatch ()->getParam ( 'controller' ));
		$action = $ToUnderscore->filter ( $controller->getEvent ()->getRouteMatch ()->getParam ( 'action' ));
		
		return $module . '_' . $action;
	}
}
