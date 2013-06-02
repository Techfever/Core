<?php
/**
 * Get Superglobal
 */
class Get implements AdapterInterface {
	
	/**
	 * Constructor Variables
	 */
	public function __construct() {
		$this->data = $_SERVER;
	}
	
	/**
	 * Get Superglobal Variables
	 *
	 * @param string $key        	
	 * @return mixed Get Superglobal variable
	 */
	public function getVariable($key = null) {
		if (! empty ( $key ) && array_key_exists ( $key, $_SERVER )) {
			return $_SERVER [$key];
		}
		return $_SERVER;
	}
	
	/**
	 * Set Superglobal Variables
	 *
	 * @param string $key        	
	 * @return mixed Get Superglobal variable
	 */
	public function setVariable($key, $value = null) {
		$_SERVER [$key] = $value;
	}
}