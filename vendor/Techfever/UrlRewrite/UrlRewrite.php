<?php

namespace Techfever\UrlRewrite;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class UrlRewrite extends GeneralBase {
	/**
	 * Host (including port)
	 *
	 * @var string
	 */
	protected $host;
	
	/**
	 * Port
	 *
	 * @var int
	 */
	protected $port;
	
	/**
	 * Use Uri
	 *
	 * @var boolean
	 */
	protected $useuri = false;
	
	/**
	 * Options
	 *
	 * @var array
	 */
	protected $options = array ();
	
	/**
	 * Construct an instance of this class.
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		parent::__construct ( $options );
		unset ( $this->options ['servicelocator'] );
		$this->setOptions ( $options );
	}
	
	/**
	 * Validate the blog
	 *
	 * @return void
	 */
	public function validateBlog() {
		$rawblog = $this->detectBlog ();
		if (strlen ( $rawblog ) > 0) {
			$DBVerify = $this->getDatabase ();
			$DBVerify->select ();
			$DBVerify->columns ( array (
					'key' => 'blog_key' 
			) );
			$DBVerify->from ( array (
					'b' => 'blog' 
			) );
			$DBVerify->where ( array (
					'b.blog_key = "' . $rawblog . '"' 
			) );
			$DBVerify->limit ( 1 );
			$DBVerify->execute ();
			if ($DBVerify->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Validate the blog
	 *
	 * @return void
	 */
	public function isUri() {
		if (! $this->validateBlog ()) {
			$this->useuri = false;
		}
		return $this->useuri;
	}
	
	/**
	 * Detect the blog
	 *
	 * @return void
	 */
	public function detectBlog() {
		$uristatus = false;
		$rawblog = "";
		if (strtolower ( $this->getHost () ) === "localhost") {
			$uristatus = true;
		} else {
			$default = $this->getOption ( 'default' );
			$subdomain = $default;
			$matches = explode ( '.', $this->getHost () );
			if (is_array ( $matches ) && count ( $matches ) > 1) {
				$subdomain = $matches [0];
			}
			if (strtolower ( $subdomain ) === $default) {
				$uristatus = true;
			} else {
				$rawblog = $subdomain;
			}
		}
		if ($uristatus) {
			$uri = (array_key_exists ( 'REQUEST_URI', $_SERVER ) ? $_SERVER ['REQUEST_URI'] : null);
			if (substr ( strtolower ( $uri ), 0, 1 ) === "/") {
				$uri = substr ( $uri, 1, strlen ( $uri ) );
			}
			$rawuri = explode ( '/', $uri );
			if (is_array ( $rawuri ) && count ( $rawuri ) > 1) {
				$rawblog = $rawuri [0];
				$this->useuri = true;
			} else if (strlen ( $uri ) > 0) {
				$rawblog = $uri;
				$this->useuri = true;
			}
		}
		return $rawblog;
	}
	
	/**
	 * Detect the host based on headers
	 *
	 * @return void
	 */
	protected function detectHost() {
		if (isset ( $_SERVER ['HTTP_HOST'] ) && ! empty ( $_SERVER ['HTTP_HOST'] )) {
			// Detect if the port is set in SERVER_PORT and included in HTTP_HOST
			if (isset ( $_SERVER ['SERVER_PORT'] ) && preg_match ( '/^(?P<host>.*?):(?P<port>\d+)$/', $_SERVER ['HTTP_HOST'], $matches )) {
				// If they are the same, set the host to just the hostname
				// portion of the Host header.
				if (( int ) $matches ['port'] === ( int ) $_SERVER ['SERVER_PORT']) {
					$this->setHost ( $matches ['host'] );
					return;
				}
				
				// At this point, we have a SERVER_PORT that differs from the
				// Host header, indicating we likely have a port-forwarding
				// situation. As such, we'll set the host and port from the
				// matched values.
				$this->setPort ( ( int ) $matches ['port'] );
				$this->setHost ( $matches ['host'] );
				return;
			}
			
			$this->setHost ( $_SERVER ['HTTP_HOST'] );
			
			return;
		}
		
		if (! isset ( $_SERVER ['SERVER_NAME'] ) || ! isset ( $_SERVER ['SERVER_PORT'] )) {
			return;
		}
		
		$name = $_SERVER ['SERVER_NAME'];
		$this->setHost ( $name );
	}
	
	/**
	 * Detect the port
	 *
	 * @return null
	 */
	protected function detectPort() {
		if (isset ( $_SERVER ['SERVER_PORT'] ) && $_SERVER ['SERVER_PORT']) {
			$this->setPort ( $_SERVER ['SERVER_PORT'] );
			return;
		}
	}
	
	/**
	 * Sets host
	 *
	 * @param string $host        	
	 * @return ServerUrl
	 */
	public function setHost($host) {
		$this->host = $host;
		
		return $this;
	}
	
	/**
	 * Returns host
	 *
	 * @return string
	 */
	public function getHost() {
		if (null === $this->host) {
			$this->detectHost ();
		}
		
		return $this->host;
	}
	
	/**
	 * Set server port
	 *
	 * @param int $port        	
	 * @return ServerUrl
	 */
	public function setPort($port) {
		$this->port = ( int ) $port;
		
		return $this;
	}
	
	/**
	 * Retrieve the server port
	 *
	 * @return int null
	 */
	public function getPort() {
		if (null === $this->port) {
			$this->detectPort ();
		}
		
		return $this->port;
	}
}