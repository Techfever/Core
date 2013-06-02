<?php
interface AdapterInterface {
	/**
	 * Get Superglobal variable
	 *
	 * @param string $key        	
	 * @return arrry/string Superglobal variable
	 */
	public function getVariable($key = null);
	
	/**
	 * Set Superglobal variable
	 *
	 * @param string $key        	
	 * @return void
	 */
	public function setVariable($key, $value = null);
}
