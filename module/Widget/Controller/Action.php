<?php

namespace Widget\Controller;

use Techfever\Template\Plugin\AbstractActionController;
use Zend\Json\Json;
use Techfever\Widget\Widget;

class ActionController extends AbstractActionController {
	/**
	 *
	 * @var $widget_container
	 */
	private $widget_container = null;
	
	/**
	 *
	 * @var $widget_object
	 */
	private $widget_object = null;
	public function InitialAction() {
		$this->layout ( 'blank/layout' );
		$request = $this->getRequest ();
		$response = $this->getResponse ();
		
		if (! $request->isXmlHttpRequest ()) {
			// return $this->redirect ()->toRoute ( 'Index' );
		}
		
		$success = True;
		$content = array ();
		$redirect = null;
		$module = ( string ) $this->params ()->fromRoute ( 'module', null );
		$location = ( string ) $this->params ()->fromRoute ( 'location', null );
		if (substr ( $module, - 1 ) === '/') {
			$module = substr ( $module, 0, (strlen ( $module ) - 1) );
		}
		if (substr ( $location, - 1 ) === '/') {
			$location = substr ( $location, 0, (strlen ( $location ) - 1) );
		}
		$Widget = $this->getWidgetObject ();
		if (empty ( $Widget )) {
			$success = False;
		}

		if ($success) {
			if ($Widget->verifyLocation ( $location )) {
				$Widget->setLocation ( $location );

				$widgetData = array ();
				if (! $this->validWidgetStatus () || $module == 'Initial') {
					$widgetData = $Widget->getLocationValidWidget ();
					$this->setWidgetStatus ( True );
				} else {
					if (! $Widget->verifyWidget ( $module, 1 )) {
						$success = False;
					}
					$Widget->setWidget ( $module );
					$widgetData [] = $Widget->prepareWidget ();
				}
				$widgetData = $Widget->getLocationValidWidget ();
				
				if (is_array ( $widgetData ) && count ( $widgetData ) > 0) {
					$controllerManager = $this->getServiceLocator ()->get ( 'ControllerLoader' );
					foreach ( $widgetData as $widgetValue ) {
						if ($this->getTemplate ()->getControllers ()->verifyController ( $widgetValue ['alias'] )) {
							$controller = $controllerManager->get ( $widgetValue ['alias'] );
							$results = $controller->InitialAction ();
							$content [$widgetValue ['sort']] = $results->getContent ();
						} else {
							$success = False;
						}
					}
				}
			} else {
				$success = False;
			}
		}
		$response->setContent ( Json::encode ( array (
				'success' => $success,
				'module' => $Widget->getWidget (),
				'location' => $Widget->getLocation (),
				'redirect' => $redirect,
				'content' => $content,
				'width' => $Widget->getLocationWidth () 
		) ) );
		return $response;
	}
	public function getWidgetObject() {
		if (empty ( $this->widget_object )) {
			
			$controllerName = null;
			$controllerId = null;
			$controllerAction = null;
			$referer_uri = $_SERVER ['HTTP_REFERER'];
			
			$request = new \Zend\Http\Request ();
			$request->setUri ( $referer_uri );
			$router = $this->getServiceLocator ()->get ( 'Router' );
			$routeMatch = $router->match ( $request );
			if ($routeMatch) {
				$controllerName = $routeMatch->getParam ( 'controller' );
				$controllerAction = $routeMatch->getParam ( 'action' );
				$controllerId = $this->getTemplate ()->getControllers ()->getControllerID ( $controllerName );
			}
			
			$options = array (
					'servicelocator' => $this->getServiceLocator (),
					'controllerid' => $controllerId,
					'controllername' => $controllerName,
					'controlleraction' => $controllerAction 
			);
			$this->widget_object = new Widget ( $options );

			if (! $this->widget_object->verifyPermission ( $controllerName, $controllerAction )) {
				$this->widget_object = null;
			}
		}
		return $this->widget_object;
	}
	public function validWidgetStatus() {
		$WidgetContainer = $this->getWidgetContainer ();
		$ContainerController = strtolower ( $WidgetContainer->offsetGet ( 'Controller' ) );
		$WidgetController = strtolower ( $this->getWidgetObject ()->getControllerName () . '\\' . $this->getWidgetObject ()->getLocation () );
		if ($ContainerController != $WidgetController) {
			return false;
		}
		if (! $WidgetContainer->offsetGet ( 'Initialized' )) {
			return false;
		}
		return true;
	}
	public function setWidgetStatus($status = True) {
		$WidgetContainer = $this->getWidgetContainer ();
		$WidgetContainer->offsetSet ( 'Initialized', $status );
		$WidgetContainer->offsetSet ( 'Controller', $this->getWidgetObject ()->getControllerName () . '\\' . $this->getWidgetObject ()->getLocation () );
	}
	public function getWidgetContainer() {
		if (empty ( $this->widget_container )) {
			$Session = $this->getSession ();
			$this->widget_container = $Session->getContainer ( 'Widget' );
		}
		return $this->widget_container;
	}
}
