<?php

namespace Techfever\Autoresponder\General;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class General extends GeneralBase {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array (
			'request' => null,
			'response' => null,
			'controller' => null,
			'route' => null,
			'action' => null,
			'variable' => null,
			'data' => null 
	);
	
	/**
	 * Initial Autoresponder General
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		parent::__construct ( $options );
		unset ( $options ['servicelocator'] );
		$this->setOptions ( $options );
	}
	
	/**
	 * Get Request
	 */
	public function getRequest() {
		return $this->getOption ( 'request' );
	}
	
	/**
	 * Get Response
	 */
	public function getResponse() {
		return $this->getOption ( 'response' );
	}
	
	/**
	 * Get Controller
	 */
	public function getController() {
		return $this->getOption ( 'controller' );
	}
	
	/**
	 * Get Route
	 */
	public function getRoute() {
		return $this->getOption ( 'route' );
	}
	
	/**
	 * Get Action
	 */
	public function getAction() {
		return $this->getOption ( 'action' );
	}
	
	/**
	 * Get Variable
	 */
	public function getVariable() {
		return $this->getOption ( 'variable' );
	}
	
	/**
	 * Get Data
	 */
	public function getData() {
		return $this->getOption ( 'data' );
	}
}
?>