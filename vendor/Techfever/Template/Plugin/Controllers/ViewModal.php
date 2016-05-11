<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Model\ViewModel;
use Zend\Mvc\InjectApplicationEventInterface;
use Techfever\Exception\RuntimeException;
use Techfever\Template\Plugin\Filters\ToForwardSlash;

class ViewModal extends AbstractPlugin {
	public function __invoke($variables = array(), $path = null) {
		$ToForwardSlash = new ToForwardSlash ( '\\' );
		
		$controller = $this->getController ();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new RuntimeException ( 'Controllers must implement Zend\Mvc\InjectApplicationEventInterface to use this plugin.' );
		}
		
		if (isset ( $path ) && ! empty ( $path )) {
			$templatePath = $path;
		} else {
			$templatePath = $controller->getEvent ()->getRouteMatch ()->getParam ( 'controller' );
			$templatePath .= '\\' . $controller->getEvent ()->getRouteMatch ()->getParam ( 'action' );
			$templatePath = $ToForwardSlash->filter ( $templatePath );
		}
		
		$templatePath = strtolower ( $templatePath );
		
		$ViewModel = new ViewModel ();
		$ViewModel->setTemplate ( $templatePath );
		if (is_array ( $variables ) && count ( $variables ) > 0) {
			$ViewModel->setVariables ( $variables );
		}
		
		$ViewRenderer = $controller->getServiceLocator ()->get ( 'ViewRenderer' );
		if ($ViewRenderer instanceof RendererInterface) {
			return $ViewRenderer->render ( $ViewModel );
		}
		return null;
	}
}
