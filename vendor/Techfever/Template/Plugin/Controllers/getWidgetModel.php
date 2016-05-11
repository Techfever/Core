<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Techfever\Exception;
use Traversable;

class getWidgetModel extends AbstractPlugin {
	
	/**
	 * options
	 *
	 * @var mixed
	 */
	private $options = array (
			'controllername' => null,
			'controlleraction' => null,
			'success' => null,
			'title' => null,
			'content' => null,
			'redirect' => null 
	);
	
	/**
	 * response
	 *
	 * @var object
	 */
	private $response = null;
	
	/**
	 * viewrenderer
	 *
	 * @var object
	 */
	private $viewrenderer = null;
	
	/**
	 * translator
	 *
	 * @var object
	 */
	private $translator = null;
	public function __invoke($options = null) {
		if (is_array ( $options )) {
			$this->setOptions ( $options );
			$response = $this->getResponse ();
			
			$ToUnderscore = new \Techfever\Template\Plugin\Filters\ToUnderscore ( '\\' );
			$ToForwardSlash = new \Techfever\Template\Plugin\Filters\ToForwardSlash ( '\\' );
			$template = $this->getOption ( 'controllername' ) . '/Action/' . $this->getOption ( 'controlleraction' );
			$template = $ToForwardSlash->filter ( $template );
			$template = strtolower ( $template );
			
			$contentVars = $this->getOption ( 'content' );
			
			$content = new ViewModel ();
			$content->setTerminal ( true )->setTemplate ( $template )->setVariables ( $contentVars );
			$content = $this->getViewRenderer ()->render ( $content );
			
			$title = $this->getOption ( 'title' );
			if (empty ( $title )) {
				$title = strtolower ( 'text_' . $this->getOption ( 'controllername' ) . '_action_' . $this->getOption ( 'controlleraction' ) . '_title' );
				$title = $ToUnderscore->filter ( $title );
			}
			$title = $this->getTranslator ( $title );
			
			$response->setContent ( Json::encode ( array (
					'success' => $this->getOption ( 'success' ),
					'title' => $title,
					'content' => $content,
					'redirect' => $this->getOption ( 'redirect' ) 
			) ) );
			return $response;
		} else {
			return null;
		}
	}
	
	/**
	 * Returns an translator
	 *
	 * @return return Translator
	 */
	public function getTranslator($key) {
		if (! isset ( $this->translator )) {
			$this->translator = $this->getController ()->getServiceLocator ()->get ( 'translator' );
		}
		return $this->translator->translate ( $key );
	}
	
	/**
	 * Returns an viewrenderer
	 *
	 * @return return View Renderer
	 */
	public function getViewRenderer() {
		if (! isset ( $this->viewrenderer )) {
			$this->viewrenderer = $this->getController ()->getServiceLocator ()->get ( 'viewrenderer' );
		}
		return $this->viewrenderer;
	}
	
	/**
	 * Returns an response
	 *
	 * @return return Response
	 */
	public function getResponse() {
		if (! isset ( $this->response )) {
			$this->response = $this->getController ()->getServiceLocator ()->get ( 'response' );
		}
		return $this->response;
	}
	
	/**
	 * Returns an option
	 *
	 * @param string $option
	 *        	Option to be returned
	 * @return mixed Returned option
	 * @throws Exception\InvalidArgumentException
	 */
	public function getOption($option) {
		if (isset ( $this->options ) && array_key_exists ( $option, $this->options )) {
			return $this->options [$option];
		}
		
		throw new Exception\InvalidArgumentException ( "Invalid option '$option'" );
	}
	
	/**
	 * Returns all available options
	 *
	 * @return array Array with all available options
	 */
	public function getOptions() {
		return $this->options;
	}
	
	/**
	 * Sets one or multiple options
	 *
	 * @param array|Traversable $options
	 *        	Options to set
	 * @throws Exception\InvalidArgumentException If $options is not an array or Traversable
	 * @return AbstractValidator Provides fluid interface
	 */
	public function setOptions($options = array()) {
		if (! is_array ( $options ) && ! $options instanceof Traversable) {
			throw new Exception\InvalidArgumentException ( __METHOD__ . ' expects an array or Traversable' );
		}
		
		if ($this->options !== $options) {
			$this->options = $options;
		}
		return $this;
	}
	
	/**
	 * Set a single option
	 *
	 * @param string $name        	
	 * @param mixed $value        	
	 * @return Object
	 */
	public function setOption($name, $value) {
		$this->options [( string ) $name] = $value;
		return $this;
	}
}
