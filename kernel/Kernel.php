<?php
require_once __DIR__ . 'Superglobal/AdapterInterface.php';
class Kernel {
	/**
	 * Constructor.
	 */
	public function __construct() {
	}
	public function __call($name, $arguments) {
		$varible = $this->_ {$name};
		return $varible [$arguments [0]];
	}
	public function initialize() {
	}
}