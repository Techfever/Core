<?php
namespace Kernel;

use Zend\Session\Container as SessionContainer;
use Kernel\ServiceLocator;
use Kernel\Database;
use DateTime;

class Snapshot {

	/**
	 * @var Location
	 **/
	private $_location = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->_location = null;
		$this->container = new SessionContainer('Snapshot');
	}

	public function getProtocol() {
		$protocol = 'http://';
		if (isset($_SERVER['HTTPS'])) {
			$protocol = 'https://';
		}
		return $protocol;
	}

	public function getHost() {
		$host = null;
		if (isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		}
		return $host;
	}

	public function getUri() {
		$uri = null;
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['REQUEST_URI'];
		}
		return $uri;
	}

	public function getContainer() {
		return $this->container;
	}

	public function set() {
		$this->_location = null;

		$protocol = $this->getProtocol();
		$host = $this->getHost();
		$uri = $this->getUri();
		$uricheck = explode('/', $uri);
		$uristatus = true;
		if (is_array($uricheck) && count($uricheck) > 1 && $uricheck[2] === 'Theme') {
			$uristatus = false;
		}
		if ($uristatus) {
			$this->_location = $protocol . $host . $uri;
		}

		$location = array();
		if ($this->container->offsetExists('location')) {
			$location = $this->container->offsetGet('location');
		}
		if (!empty($this->_location)) {
			$location[] = $this->_location;
		}
		if (is_array($location) && count($location) > 5) {
			$location = array_slice($location, 1);
		}
		$this->container->offsetSet('location', $location);

		if (!empty($this->_location)) {
			return true;
		}
		return false;
	}

	public function has() {
		if ($this->container->offsetExists('location')) {
			return true;
		}
		return false;
	}

	public function get() {
		if ($this->container->offsetExists('location')) {
			$location = $this->container->offsetGet('location');
			return $location[(count($location) - 1)];
		}
		return false;
	}

	public function reset() {
		if ($this->container->offsetExists('location')) {
			$this->container->offsetUnset('location');
		}
		return false;
	}

	public function redirect() {
		if ($this->container->offsetExists('location')) {
			$location = $this->container->offsetGet('location');
			$count = (count($location) > 0 ? (count($location) - 1) : 0);
			if (is_array($location) && array_key_exists($count, $location)) {
				header('Location: ' . $location[$count]);
			}
		}
	}
}
