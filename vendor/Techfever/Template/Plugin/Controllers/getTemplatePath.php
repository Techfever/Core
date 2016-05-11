<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\InjectApplicationEventInterface;
use Techfever\Exception\RuntimeException;
use Techfever\Template\Plugin\Filters\ToForwardSlash;

class getTemplatePath extends AbstractPlugin {
	/**
	 * Grabs a param from route match by default.
	 *
	 * @param string $param        	
	 * @param mixed $default        	
	 * @return mixed
	 */
	public function __invoke() {
		$controller = $this->getController ();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new RuntimeException ( 'Controllers must implement Zend\Mvc\InjectApplicationEventInterface to use this plugin.' );
		}
		$fullpath = $controller->getEvent ()->getRouteMatch ()->getParam ( 'controller' );
		$fullpath .= '\\' . $controller->getEvent ()->getRouteMatch ()->getParam ( 'action' );
		
		$ToForwardSlash = new ToForwardSlash ( '\\' );
		$fullpath = $ToForwardSlash->filter ( $fullpath );
		return strtolower ( $fullpath );
	}
}
