<?php
namespace Kernel\Service;

use Techfever\Database\Driver;

class Database implements ServiceInterface {

	/**
	 *
	 * @var Database Data
	 */
	private static $_data = array();

	/**
	 *
	 * @var Database Option
	 */
	private static $_option = array();

	/**
	 *
	 * @var Database Start Status
	 */
	private static $_isStarted = False;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct($option = null) {
		self::$_option = $option;
		print_r($option);
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
	 * Check Database start status.
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
	 * Check Database stop status.
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
