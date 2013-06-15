<?php
namespace Kernel\Service;

use Kernel\Startup;

class ServiceClass extends Startup {

	/**
	 * Start.
	 *
	 * @return void
	 */
	public function start() {
	}

	/**
	 * Stop.
	 *
	 * @return void
	 */
	public function stop() {
	}

	/**
	 * Reset.
	 *
	 * @return void
	 */
	public function restart() {
	}

	/**
	 * Check start status.
	 *
	 * @return void
	 */
	public function isStarted() {
	}

	/**
	 * Check stop status.
	 *
	 * @return void
	 */
	public function isStopped() {
	}

	/**
	 * Set the variable data
	 *
	 * @return void
	 */
	public function setVariable($name, $key, $value) {
	}

	/**
	 * Get the variable data
	 *
	 * @return array $_data
	 */
	public function getVariable($name = null, $key = null) {
	}

	/**
	 * Get the variable data
	 *
	 * @return array $_data
	 */
	public function getConfig($name = null, $key = null) {
		return parent::$Config->getConfig($name, $key);
	}
}
