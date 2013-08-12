<?php
namespace Techfever\Snapshot;

use Techfever\Session\Session;
use Techfever\Exception;

class Snapshot {
	/**
	 * @var Session\Session
	 */
	private $session = null;

	/**
	 * @var Location
	 **/
	private $location = null;

	/**
	 * Constructor
	 */
	public function __construct(Session $session) {
		$this->location = null;
		$this->session = $session;
	}

	/**
	 * getSession()
	 *
	 * @throws Exception\RuntimeException
	 * @return Session\Session
	 */
	public function getSession() {
		if ($this->session == null) {
			throw new Exception\RuntimeException('Session has not been set or configured.');
		}
		return $this->session;
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

	public function set() {
		$this->location = null;

		$Session = $this->getSession();
		$Container = $Session->getContainer('Snapshot');

		$protocol = $this->getProtocol();
		$host = $this->getHost();
		$uri = $this->getUri();
		$uricheck = explode('/', $uri);
		$uristatus = true;
		if (is_array($uricheck) && count($uricheck) > 1 && $uricheck[2] === 'Theme') {
			$uristatus = false;
		}
		if ($uristatus) {
			$this->location = $protocol . $host . $uri;
		}

		$location = array();
		if ($Container->offsetExists('location')) {
			$location = $Container->offsetGet('location');
		}
		if (!empty($this->location)) {
			$location[] = $this->location;
		}
		if (is_array($location) && count($location) > 5) {
			$location = array_slice($location, 1);
		}
		$Container->offsetSet('location', $location);

		if (!empty($this->location)) {
			return true;
		}
		return false;
	}

	public function has() {
		$Session = $this->getSession();
		$Container = $Session->getContainer('Snapshot');
		if ($Container->offsetExists('location')) {
			return true;
		}
		return false;
	}

	public function get() {
		$Session = $this->getSession();
		$Container = $Session->getContainer('Snapshot');
		if ($Container->offsetExists('location')) {
			$location = $Container->offsetGet('location');
			return $location[(count($location) - 1)];
		}
		return false;
	}

	public function reset() {
		$Session = $this->getSession();
		$Container = $Session->getContainer('Snapshot');
		if ($Container->offsetExists('location')) {
			$Container->offsetUnset('location');
		}
		return false;
	}

	public function redirect() {
		$Session = $this->getSession();
		$Container = $Session->getContainer('Snapshot');
		if ($Container->offsetExists('location')) {
			$location = $Container->offsetGet('location');
			$count = (count($location) > 0 ? (count($location) - 1) : 0);
			if (is_array($location) && array_key_exists($count, $location)) {
				header('Location: ' . $location[$count]);
			}
		}
	}
}
