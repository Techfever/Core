<?php
namespace Kernel\Service;
class Superglobal implements  ServiceInterface {

	/**
	 *
	 * @var Superglobal Data
	 */
	private static $_data = array(
		'Server', 'Request', 'Post', 'Get', 'Cookie', 'Session'
	);

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
	}

	/**
	 * Start.
	 *
	 * @return void
	 */
	public function start() {
		self::$_data['Server'] = $_SERVER;
		self::$_data['Request'] = $_REQUEST;
		self::$_data['Post'] = (isset($_POST) ? $_POST : null);
		self::$_data['Get'] = (isset($_GET) ? $_GET : null);
		self::$_data['Cookie'] = (isset($_COOKIE) ? $_COOKIE : null);
		self::$_data['Session'] = (isset($_SESSION) ? $_SESSION : null);
		self::$_data['Files'] = (isset($_FILES) ? $_FILES : null);
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
		switch ($name) {
			case 'Server':
				$_SERVER[$key] = $value;
				break;
			case 'Request':
				$_REQUEST[$key] = $value;
				break;
			case 'Post':
				$_POST[$key] = $value;
				break;
			case 'Get':
				$_GET[$key] = $value;
				break;
			case 'Cookie':
				$_COOKIE[$key] = $value;
				break;
			case 'Files':
				$_FILES[$key] = $value;
				break;
			case 'Session':
				$_SESSION[$key] = $value;
				break;
		}
		self::restart();
		return False;
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
