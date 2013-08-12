<?php
namespace Techfever\Template\Plugin\Controllers;

use Traversable;
use Zend\EventManager\EventInterface;
use Zend\Mvc\Exception;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Escaper;

class HrefLink extends AbstractPlugin {

	/**
	 * The tag closing bracket
	 *
	 * @var string
	 */
	private $closingBracket = null;

	/**
	 * The tag opening bracket
	 *
	 * @var string
	 */
	private $openingBracket = null;

	/**
	 * The tag opening bracket
	 *
	 * @var string
	 */
	private $attribs = array();

	/**
	 * @var string Encoding
	 */
	private $encoding = 'UTF-8';
	/**
	 * Host (including port)
	 *
	 * @var string
	 */
	private $host;

	/**
	 * Port
	 *
	 * @var int
	 */
	private $port;

	/**
	 * Scheme
	 *
	 * @var string
	 */
	private $scheme;

	/**
	 * Whether or not to query proxy servers for address
	 *
	 * @var bool
	 */
	private $useProxy = false;

	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	private $options = array(
			'value' => '',
			'route' => '',
			'params' => array(),
			'options' => array(),
			'reuseMatchedParams' => false,
	);

	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	private $attributes = array(
			'href' => '',
			'title' => '',
			'id' => '',
			'class' => '',
			'style' => '',
	);

	/**
	 * Generates a URL based on a route
	 *
	 * @param  string             $route              RouteInterface name
	 * @param  array|Traversable  $params             Parameters to use in url generation, if any
	 * @param  array|bool         $options            RouteInterface-specific options to use in url generation, if any.
	 *                                                If boolean, and no fourth argument, used as $reuseMatchedParams.
	 * @param  bool               $reuseMatchedParams Whether to reuse matched parameters
	 *
	 * @throws \Zend\Mvc\Exception\RuntimeException
	 * @throws \Zend\Mvc\Exception\InvalidArgumentException
	 * @throws \Zend\Mvc\Exception\DomainException
	 * @return string
	 */
	public function __invoke($options = array()) {
		$this->attributes = array(
				'href' => '',
				'title' => '',
				'id' => '',
				'class' => '',
				'style' => '',
		);
		if (is_array($options)) {
			$attributes = array();
			if (isset($options['attributes'])) {
				$attributes = array_merge($this->attributes, $options['attributes']);
				$this->attributes = $attributes;
				unset($options['attributes']);
			}
			$options = array_merge($this->options, $options);
		}
		$this->options = $options;

		$href = $this->getAttribute('href');
		if (empty($href)) {
			$this->setAttribute('href', urldecode($this->getScheme() . '://' . $this->getHost() . $this->url($this->getOption('route'), $this->getOption('params'), $this->getOption('options'), $this->getOption('reuseMatchedParams'))));
		}
		return $this->getOpeningBracket() . $this->getOption('value') . $this->getClosingBracket();
	}

	/**
	 * Returns an option
	 *
	 * @param string $option Option to be returned
	 * @return mixed Returned option
	 * @throws Exception\InvalidArgumentException
	 */
	public function getOption($option) {
		if (isset($this->options) && array_key_exists($option, $this->options)) {
			return $this->options[$option];
		}

		throw new Exception\InvalidArgumentException("Invalid option '$option'");
	}

	/**
	 * Returns all available options
	 *
	 * @return array Array with all available options
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Sets one or multiple options
	 *
	 * @param  array|Traversable $options Options to set
	 * @throws Exception\InvalidArgumentException If $options is not an array or Traversable
	 * @return AbstractValidator Provides fluid interface
	 */
	public function setOptions($options = array()) {
		if (!is_array($options) && !$options instanceof Traversable) {
			throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
		}

		if ($this->options !== $options) {
			$this->options = $options;
		}
		return $this;
	}

	/**
	 * Set a single option
	 *
	 * @param  string $name
	 * @param  mixed $value
	 * @return Object
	 */
	public function setOption($name, $value) {
		$this->options[(string) $name] = $value;
		return $this;
	}

	/**
	 * Returns an attribute
	 *
	 * @param string $attribute Attribute to be returned
	 * @return mixed Returned attribute
	 * @throws Exception\InvalidArgumentException
	 */
	public function getAttribute($attribute) {
		if (isset($this->attributes) && array_key_exists($attribute, $this->attributes)) {
			return $this->attributes[$attribute];
		}

		throw new Exception\InvalidArgumentException("Invalid attribute '$attribute'");
	}

	/**
	 * Returns all available attributes
	 *
	 * @return array Array with all available attributes
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * Sets one or multiple attributes
	 *
	 * @param  array|Traversable $attributes Attributes to set
	 * @throws Exception\InvalidArgumentException If $attributes is not an array or Traversable
	 * @return AbstractValidator Provides fluid interface
	 */
	public function setAttributes($attributes = array()) {
		if (!is_array($attributes) && !$attributes instanceof Traversable) {
			throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
		}

		if ($this->attributes !== $attributes) {
			$this->attributes = $attributes;
		}
		return $this;
	}

	/**
	 * Set a single attribute
	 *
	 * @param  string $name
	 * @param  mixed $value
	 * @return Object
	 */
	public function setAttribute($name, $value) {
		$this->attributes[(string) $name] = $value;
		return $this;
	}

	/**
	 * Get the tag closing bracket
	 *
	 * @return string
	 */
	public function getClosingBracket() {
		$this->closingBracket = '</a>';

		return $this->closingBracket;
	}

	/**
	 * Get the tag opening bracket
	 *
	 * @return string
	 */
	public function getOpeningBracket() {
		$this->openingBracket = '<a' . $this->htmlAttribs($this->getAttributes()) . '>';

		return $this->openingBracket;
	}

	/**
	 * Get the encoding to use for escape operations
	 *
	 * @return string
	 */
	public function getEncoding() {
		return $this->encoding;
	}

	/**
	 * Converts an associative array to a string of tag attributes.
	 *
	 * @access public
	 *
	 * @param array $attribs From this array, each key-value pair is
	 * converted to an attribute name and value.
	 *
	 * @return string The XHTML for the attributes.
	 */
	private function htmlAttribs($attribs) {
		$xhtml = '';
		$escaper = new Escaper\Escaper($this->getEncoding());

		foreach ((array) $attribs as $key => $val) {
			$key = $escaper->escapeHtml($key);
			if (is_array($val)) {
				$val = implode(' ', $val);
			}
			$val = $escaper->escapeHtml($val);

			if ('id' == $key) {
				$val = $this->normalizeId($val);
			}

			if (strpos($val, '"') !== false) {
				$xhtml .= " $key='$val'";
			} else {
				$xhtml .= " $key=\"$val\"";
			}
		}

		return $xhtml;
	}

	/**
	 * Normalize an ID
	 *
	 * @param  string $value
	 * @return string
	 */
	private function normalizeId($value) {
		if (strstr($value, '[')) {
			if ('[]' == substr($value, -2)) {
				$value = substr($value, 0, strlen($value) - 2);
			}
			$value = trim($value, ']');
			$value = str_replace('][', '-', $value);
			$value = str_replace('[', '-', $value);
		}

		return $value;
	}

	/**
	 * Generates a URL based on a route
	 *
	 * @param  string             $route              RouteInterface name
	 * @param  array|Traversable  $params             Parameters to use in url generation, if any
	 * @param  array|bool         $options            RouteInterface-specific options to use in url generation, if any.
	 *                                                If boolean, and no fourth argument, used as $reuseMatchedParams.
	 * @param  bool               $reuseMatchedParams Whether to reuse matched parameters
	 *
	 * @throws \Zend\Mvc\Exception\RuntimeException
	 * @throws \Zend\Mvc\Exception\InvalidArgumentException
	 * @throws \Zend\Mvc\Exception\DomainException
	 * @return string
	 */
	public function url($route = null, $params = array(), $options = array(), $reuseMatchedParams = false) {
		$controller = $this->getController();
		if (!$controller instanceof InjectApplicationEventInterface) {
			throw new Exception\DomainException('Url plugin requires a controller that implements InjectApplicationEventInterface');
		}

		if (!is_array($params)) {
			if (!$params instanceof Traversable) {
				throw new Exception\InvalidArgumentException('Params is expected to be an array or a Traversable object');
			}
			$params = iterator_to_array($params);
		}

		$event = $controller->getEvent();
		$router = null;
		$matches = null;
		if ($event instanceof MvcEvent) {
			$router = $event->getRouter();
			$matches = $event->getRouteMatch();
		} elseif ($event instanceof EventInterface) {
			$router = $event->getParam('router', false);
			$matches = $event->getParam('route-match', false);
		}
		if (!$router instanceof RouteStackInterface) {
			throw new Exception\DomainException('Url plugin requires that controller event compose a router; none found');
		}

		if (3 == func_num_args() && is_bool($options)) {
			$reuseMatchedParams = $options;
			$options = array();
		}

		if ($route === null) {
			if (!$matches) {
				throw new Exception\RuntimeException('No RouteMatch instance present');
			}

			$route = $matches->getMatchedRouteName();

			if ($route === null) {
				throw new Exception\RuntimeException('RouteMatch does not contain a matched route name');
			}
		}

		if ($reuseMatchedParams && $matches) {
			$routeMatchParams = $matches->getParams();

			if (isset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER])) {
				$routeMatchParams['controller'] = $routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER];
				unset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER]);
			}

			if (isset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE])) {
				unset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE]);
			}

			$params = array_merge($routeMatchParams, $params);
		}

		$options['name'] = $route;

		/* Technation Added */
		$url = $router->assemble($params, $options);
		if (substr($url, -1) === '/') {
			$url = substr($url, 0, (strlen($url) - 1));
		}
		return $url;
	}

	/**
	 * Detect the host based on headers
	 *
	 * @return void
	 */
	private function detectHost() {
		if ($this->setHostFromProxy()) {
			return;
		}

		if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
			// Detect if the port is set in SERVER_PORT and included in HTTP_HOST
			if (isset($_SERVER['SERVER_PORT'])) {
				$portStr = ':' . $_SERVER['SERVER_PORT'];
				if (substr($_SERVER['HTTP_HOST'], 0 - strlen($portStr), strlen($portStr)) == $portStr) {
					$this->setHost(substr($_SERVER['HTTP_HOST'], 0, 0 - strlen($portStr)));
					return;
				}
			}

			$this->setHost($_SERVER['HTTP_HOST']);

			return;
		}

		if (!isset($_SERVER['SERVER_NAME']) || !isset($_SERVER['SERVER_PORT'])) {
			return;
		}

		$name = $_SERVER['SERVER_NAME'];
		$this->setHost($name);
	}

	/**
	 * Detect the port
	 *
	 * @return null
	 */
	private function detectPort() {
		if ($this->setPortFromProxy()) {
			return;
		}

		if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']) {
			$this->setPort($_SERVER['SERVER_PORT']);
			return;
		}
	}

	/**
	 * Detect the scheme
	 *
	 * @return null
	 */
	private function detectScheme() {
		if ($this->setSchemeFromProxy()) {
			return;
		}

		switch (true) {
			case (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true)):
			case (isset($_SERVER['HTTP_SCHEME']) && ($_SERVER['HTTP_SCHEME'] == 'https')):
			case (443 === $this->getPort()):
				$scheme = 'https';
				break;
			default:
				$scheme = 'http';
				break;
		}

		$this->setScheme($scheme);
	}

	/**
	 * Detect if a proxy is in use, and, if so, set the host based on it
	 *
	 * @return bool
	 */
	private function setHostFromProxy() {
		if (!$this->useProxy) {
			return false;
		}

		if (!isset($_SERVER['HTTP_X_FORWARDED_HOST']) || empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			return false;
		}

		$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
		if (strpos($host, ',') !== false) {
			$hosts = explode(',', $host);
			$host = trim(array_pop($hosts));
		}
		if (empty($host)) {
			return false;
		}
		$this->setHost($host);

		return true;
	}

	/**
	 * Set port based on detected proxy headers
	 *
	 * @return bool
	 */
	private function setPortFromProxy() {
		if (!$this->useProxy) {
			return false;
		}

		if (!isset($_SERVER['HTTP_X_FORWARDED_PORT']) || empty($_SERVER['HTTP_X_FORWARDED_PORT'])) {
			return false;
		}

		$port = $_SERVER['HTTP_X_FORWARDED_PORT'];
		$this->setPort($port);

		return true;
	}

	/**
	 * Set the current scheme based on detected proxy headers
	 *
	 * @return bool
	 */
	private function setSchemeFromProxy() {
		if (!$this->useProxy) {
			return false;
		}

		if (isset($_SERVER['SSL_HTTPS'])) {
			$sslHttps = strtolower($_SERVER['SSL_HTTPS']);
			if (in_array($sslHttps, array(
					'on',
					1
			))) {
				$this->setScheme('https');
				return true;
			}
		}

		if (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
			return false;
		}

		$scheme = trim(strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']));
		if (empty($scheme)) {
			return false;
		}

		$this->setScheme($scheme);

		return true;
	}

	/**
	 * Sets host
	 *
	 * @param  string $host
	 * @return ServerUrl
	 */
	public function setHost($host) {
		$port = $this->getPort();
		$scheme = $this->getScheme();

		if (($scheme == 'http' && (null === $port || $port == 80)) || ($scheme == 'https' && (null === $port || $port == 443))) {
			$this->host = $host;
			return $this;
		}

		$this->host = $host . ':' . $port;

		return $this;
	}

	/**
	 * Returns host
	 *
	 * @return string
	 */
	public function getHost() {
		if (null === $this->host) {
			$this->detectHost();
		}

		return $this->host;
	}

	/**
	 * Set server port
	 *
	 * @param  int $port
	 * @return ServerUrl
	 */
	public function setPort($port) {
		$this->port = (int) $port;

		return $this;
	}

	/**
	 * Retrieve the server port
	 *
	 * @return int|null
	 */
	public function getPort() {
		if (null === $this->port) {
			$this->detectPort();
		}

		return $this->port;
	}

	/**
	 * Sets scheme (typically http or https)
	 *
	 * @param  string $scheme
	 * @return ServerUrl
	 */
	public function setScheme($scheme) {
		$this->scheme = $scheme;

		return $this;
	}

	/**
	 * Returns scheme (typically http or https)
	 *
	 * @return string
	 */
	public function getScheme() {
		if (null === $this->scheme) {
			$this->detectScheme();
		}

		return $this->scheme;
	}

	/**
	 * Set flag indicating whether or not to query proxy servers
	 *
	 * @param  bool $useProxy
	 * @return ServerUrl
	 */
	public function setUseProxy($useProxy = false) {
		$this->useProxy = (bool) $useProxy;

		return $this;
	}
}
