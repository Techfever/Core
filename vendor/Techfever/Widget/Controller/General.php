<?php

namespace Techfever\Widget\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Techfever\Exception;
use Traversable;

class General extends AbstractActionController {
	
	/**
	 * options
	 *
	 * @var mixed
	 */
	private $options = array (
			'controllername' => null,
			'controlleraction' => 'Initial',
			'success' => null,
			'title' => null,
			'content' => null,
			'redirect' => null 
	);
	
	/**
	 * Returns an controllername
	 *
	 * @return mixed Returned controllername
	 */
	public function getControllerName() {
		$controllername = $this->getOption ( 'controllername' );
		return $controllername;
	}
	
	/**
	 * Set a single controllername
	 *
	 * @param string $value        	
	 * @return Object
	 */
	public function setControllerName($value) {
		$this->setOption ( 'controllername', $value );
		return $this;
	}
	
	/**
	 * Returns an controlleraction
	 *
	 * @return mixed Returned controlleraction
	 */
	public function getControllerAction() {
		$controlleraction = $this->getOption ( 'controlleraction' );
		return $controlleraction;
	}
	
	/**
	 * Set a single controlleraction
	 *
	 * @param string $value        	
	 * @return Object
	 */
	public function setControllerAction($value) {
		$this->setOption ( 'controlleraction', $value );
		return $this;
	}
	
	/**
	 * Returns an success
	 *
	 * @return mixed Returned success
	 */
	public function getSuccess() {
		$success = $this->getOption ( 'success' );
		return $success;
	}
	
	/**
	 * Set a single success
	 *
	 * @param boolean $value        	
	 * @return Object
	 */
	public function setSuccess($value) {
		$value = (is_bool ( $value ) ? $value : false);
		$this->setOption ( 'success', $value );
		return $this;
	}
	
	/**
	 * Returns an content
	 *
	 * @return mixed Returned content
	 */
	public function getContent() {
		$content = $this->getOption ( 'content' );
		return $content;
	}
	
	/**
	 * Set a single content
	 *
	 * @param boolean $value        	
	 * @return Object
	 */
	public function setContent($value) {
		$value = (is_array ( $value ) ? $value : array ());
		$this->setOption ( 'content', $value );
		return $this;
	}
	
	/**
	 * Returns an title
	 *
	 * @return mixed Returned title
	 */
	public function getTitle() {
		$title = $this->getOption ( 'title' );
		return $title;
	}
	
	/**
	 * Set a single title
	 *
	 * @param string $value        	
	 * @return Object
	 */
	public function setTitle($value) {
		$this->setOption ( 'title', $value );
		return $this;
	}
	
	/**
	 * Returns an redirect
	 *
	 * @return mixed Returned redirect
	 */
	public function getRedirect() {
		$redirect = $this->getOption ( 'redirect' );
		return $redirect;
	}
	
	/**
	 * Set a single redirect
	 *
	 * @param string $value        	
	 * @return Object
	 */
	public function setRedirect($value) {
		$this->setOption ( 'redirect', $value );
		return $this;
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
