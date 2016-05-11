<?php

namespace Techfever\Snapshot;

use Techfever\Session\Session;
use Techfever\Exception;

class Snapshot {
	/**
	 *
	 * @var Session\Session
	 */
	private $session = null;
	
	/**
	 *
	 * @var Location
	 *
	 */
	private $location = null;
	
	/**
	 *
	 * @var Controller
	 *
	 */
	private $controller = null;
	
	/**
	 *
	 * @var Response
	 *
	 */
	private $response = null;
	
	/**
	 * Constructor
	 */
	public function __construct(Session $session, $controller, $response) {
		$this->location = null;
		$this->session = $session;
		$this->controller = $controller;
		$this->response = $response;
	}
	
	/**
	 * Get the response
	 *
	 * @return Response
	 * @throws Exception\DomainException if unable to find response
	 */
	protected function getResponse() {
		if ($this->response) {
			return $this->response;
		}
	}
	
	/**
	 * getSession()
	 *
	 * @throws Exception\RuntimeException
	 * @return Session\Session
	 */
	public function getSession() {
		if ($this->session == null) {
			throw new Exception\RuntimeException ( 'Session has not been set or configured.' );
		}
		return $this->session;
	}
	
	/**
	 * getController()
	 */
	public function getController() {
		return $this->controller;
	}
	public function getIp() {
		$ip = '1.1.1.1';
		if (isset ( $_SERVER )) {
			if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
				$ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
			} elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
				$ip = $_SERVER ['HTTP_CLIENT_IP'];
			} else {
				$ip = $_SERVER ['REMOTE_ADDR'];
			}
		} else {
			if (getenv ( 'HTTP_X_FORWARDED_FOR' )) {
				$ip = getenv ( 'HTTP_X_FORWARDED_FOR' );
			} elseif (getenv ( 'HTTP_CLIENT_IP' )) {
				$ip = getenv ( 'HTTP_CLIENT_IP' );
			} else {
				$ip = getenv ( 'REMOTE_ADDR' );
			}
		}
		return $ip;
	}
	public function getReferer() {
		$referer = (array_key_exists ( 'HTTP_REFERER', $_SERVER ) ? $_SERVER ['HTTP_REFERER'] : null);
		return $referer;
	}
	public function getProtocol() {
		$protocol = 'http://';
		if (isset ( $_SERVER ['HTTPS'] )) {
			$protocol = 'https://';
		}
		return $protocol;
	}
	public function getHost() {
		$host = null;
		if (isset ( $_SERVER ['HTTP_HOST'] )) {
			$host = $_SERVER ['HTTP_HOST'];
		}
		return $host;
	}
	public function getUri() {
		$uri = null;
		if (isset ( $_SERVER ['REQUEST_URI'] )) {
			$uri = $_SERVER ['REQUEST_URI'];
		}
		return $uri;
	}
	public function set() {
		$this->location = null;
		
		$Session = $this->getSession ();
		$Container = $Session->getContainer ( 'Snapshot' );
		
		$protocol = $this->getProtocol ();
		$host = $this->getHost ();
		$uri = $this->getUri ();
		$uristatus = true;
		$controller = $this->getController ();
		if (preg_match ( '/Login/', $controller )) {
			$uristatus = false;
		} else if (preg_match ( '/Ajax/', $controller )) {
			$uristatus = false;
		} else if (preg_match ( '/Captcha/', $controller )) {
			$uristatus = false;
		} else if (preg_match ( '/Widget/', $controller )) {
			$uristatus = false;
		} else if (preg_match ( '/Language/', $controller )) {
			$uristatus = false;
		} else if (preg_match ( '/Theme/', $controller )) {
			$uristatus = false;
		} else if (preg_match ( '/Image/', $controller )) {
			$uristatus = false;
		}
		if ($uristatus) {
			$this->location = $protocol . $host . $uri;
		}
		
		$location = array ();
		if ($Container->offsetExists ( 'location' )) {
			$location = $Container->offsetGet ( 'location' );
		}
		if (! empty ( $this->location )) {
			$location [] = $this->location;
		}
		if (is_array ( $location ) && count ( $location ) > 5) {
			$location = array_slice ( $location, 1 );
		}
		$Container->offsetSet ( 'location', $location );
		
		if (! empty ( $this->location )) {
			return true;
		}
		return false;
	}
	public function has() {
		$Session = $this->getSession ();
		$Container = $Session->getContainer ( 'Snapshot' );
		if ($Container->offsetExists ( 'location' )) {
			return true;
		}
		return false;
	}
	public function get() {
		$Session = $this->getSession ();
		$Container = $Session->getContainer ( 'Snapshot' );
		if ($Container->offsetExists ( 'location' )) {
			$location = $Container->offsetGet ( 'location' );
			$count = (count ( $location ) > 0 ? (count ( $location ) - 1) : 0);
			return $location [$count];
		}
		return false;
	}
	public function reset() {
		$Session = $this->getSession ();
		$Container = $Session->getContainer ( 'Snapshot' );
		if ($Container->offsetExists ( 'location' )) {
			$Container->offsetUnset ( 'location' );
		}
		return false;
	}
	public function redirect() {
		$response = $this->getResponse ();
		$response->getHeaders ()->addHeaderLine ( 'Location', $this->get () );
		$response->setStatusCode ( 302 );
		return $response;
	}
}
