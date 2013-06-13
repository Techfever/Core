<?php
use Techfever\Database\Driver;

class Database extends ServiceClass {

	/**
	 *
	 * @var Superglobal Data
	 */
	private static $_data = array();
	private static $_isStarted = False;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Start.
	 *
	 * @return void
	 */
	public function start() {
		self::$_isStarted = True;
	}

	/**
	 * Stop.
	 *
	 * @return void
	 */
	public function stop() {
		self::$_isStarted = False;
		unset(self::$_data);
		return True;
	}

	/**
	 * Reset.
	 *
	 * @return void
	 */
	public function restart() {
		if (self::stop()) {
			self::start();
		}
	}

	/**
	 * Check Superglobal start status.
	 *
	 * @return void
	 */
	public function isStarted() {
		if (self::$_isStarted) {
			return True;
		}
		return False;
	}

	/**
	 * Check Superglobal stop status.
	 *
	 * @return void
	 */
	public function isStopped() {
		if (!self::$_isStarted) {
			return True;
		}
		return False;
	}

	/**
	 * Set the variable data
	 *
	 * @return void
	 */
	public function setVariable($name, $key, $value) {
		self::restart();
		return True;
	}

	/**
	 * Get the variable data
	 *
	 * @return array $_data
	 */
	public function getVariable($name = null, $key = null) {
		if (array_key_exists($name, self::$_data)) {
			if (array_key_exists($key, self::$_data[$name])) {
				return self::$_data[$name][$key];
			}
			return self::$_data[$name];
		}
		return False;
	}
}
