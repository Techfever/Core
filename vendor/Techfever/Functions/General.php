<?php

namespace Techfever\Functions;

use Zend\ServiceManager\ServiceLocatorInterface;
use Techfever\Exception;
use Traversable;
use NumberFormatter;

class General {
	/**
	 * @var ServiceLocator
	 */
	private $serviceLocator = null;

	/**
	 * @var Database
	 */
	private $database = null;

	/**
	 * @var Translator
	 */
	private $translator = null;

	/**
	 * @var Log
	 */
	private $log = null;

	/**
	 * @var Session
	 */
	private $session = null;

	/**
	 * @var Number Format
	 */
	private $numberformatter = null;

	/**
	 * options
	 *
	 * @var mixed
	 */
	private $options = array();

	/**
	 * @var Variables
	 */
	private $variables = array();

	public function __construct($options = null) {
		if (!isset($options['servicelocator'])) {
			throw new Exception\RuntimeException('ServiceLocator has not been set or configured.');
		}
		$this->setServiceLocator($options['servicelocator']);
		unset($options['servicelocator']);

		if (isset($options['variable'])) {
			$this->setVariables($options['variable']);
			unset($options['variable']);
		}
		$this->setOptions($options);
	}

	/**
	 * Returns an option
	 *
	 * @param string $option
	 *        	Option to be returned
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
	 * @param array|Traversable $options
	 *        	Options to set
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
	 * @param string $name        	
	 * @param mixed $value        	
	 * @return Object
	 */
	public function setOption($name, $value) {
		$this->options[(string) $name] = $value;
		return $this;
	}

	/**
	 * Returns an variable
	 *
	 * @param string $variable Variable to be returned
	 * @return mixed Returned variable
	 * @throws Exception\InvalidArgumentException
	 */
	public function getVariable($variable) {
		if (isset($this->variables) && array_key_exists($variable, $this->variables)) {
			return $this->variables[$variable];
		}
	}

	/**
	 * Returns all available variables
	 *
	 * @return array Array with all available variables
	 */
	public function getVariables() {
		return $this->variables;
	}

	/**
	 * Sets one or multiple variables
	 *
	 * @param  array|Traversable $variables Variables to set
	 * @throws Exception\InvalidArgumentException If $variables is not an array or Traversable
	 * @return AbstractValidator Provides fluid interface
	 */
	public function setVariables($variables = array()) {
		if (!is_array($variables) && !$variables instanceof Traversable) {
			throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
		}

		if ($this->variables !== $variables) {
			$this->variables = $variables;
		}
		return $this;
	}

	/**
	 * Set a single variable
	 *
	 * @param  string $name
	 * @param  mixed $value
	 * @return Object
	 */
	public function setVariable($name, $value) {
		$this->variables[(string) $name] = $value;
		return $this;
	}

	/**
	 * Set serviceManager instance
	 *
	 * @param  ServiceLocatorInterface $serviceLocator
	 * @return void
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * Retrieve serviceManager instance
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	/**
	 * Get Request
	 *
	 * @return Request
	 */
	public function getRequest() {
		$request = $this->getOption('request');
		if (!empty($request)) {
			return $request;
		}
		return $this->getServiceLocator()->get('request');
	}

	/**
	 * Get Response
	 *
	 * @return Response
	 */
	public function getResponse() {
		$response = $this->getOption('response');
		if (!empty($response)) {
			return $response;
		}
		return $this->getServiceLocator()->get('response');
	}

	/**
	 * Get Router
	 *
	 * @return Router
	 */
	public function getRouter() {
		return $this->getServiceLocator()->get('Router');
	}

	/**
	 * Get Route Action Method
	 * 
	 * @return string
	 **/
	public function getRouteAction() {
		$action = $this->getOption('action');
		if (!empty($action)) {
			return $action;
		}
		$router = $this->getRouter();
		$request = $this->getRequest();

		$routeMatch = $router->match($request);
		if (!is_null($routeMatch)) {
			return $routeMatch->getParam('action');
		}
		return null;
	}

	/**
	 * Get Route Method
	 * 
	 * @return string
	 **/
	public function getRoute() {
		$route = $this->getOption('route');
		if (!empty($route)) {
			return $route;
		}
		$router = $this->getRouter();
		$request = $this->getRequest();

		$routeMatch = $router->match($request);
		if (!is_null($routeMatch)) {
			return $routeMatch->getMatchedRouteName();
		}
		return null;
	}

	/**
	 * Get Controller
	 * 
	 * @return string
	 **/
	public function getController() {
		$controller = $this->getOption('controller');
		if (!empty($controller)) {
			return $controller;
		}
		$router = $this->getRouter();
		$request = $this->getRequest();

		$routeMatch = $router->match($request);
		if (!is_null($routeMatch)) {
			return $routeMatch->getParam('controller');
		}
		return null;
	}

	/**
	 * getDatabase()
	 *
	 * @throws Exception\RuntimeException
	 * @return Database\Database
	 */
	public function getDatabase() {
		if (!is_object($this->database)) {
			$this->database = $this->getServiceLocator()->get('db');
		}
		return clone $this->database;
	}

	/**
	 * getTranslate()
	 *
	 * @throws Exception\RuntimeException
	 * @return Translator\Translator
	 */
	public function getTranslate($key) {
		if (!is_object($this->translator)) {
			$this->translator = $this->getServiceLocator()->get('translator');
		}
		$message = $this->translator->translate($key);
		if (!empty($message) && strlen($message) > 0) {
			return $message;
		}
		return null;
	}

	/**
	 * getTranslator()
	 *
	 * @throws Exception\RuntimeException
	 * @return Translator\Translator
	 */
	public function getTranslator() {
		if (!is_object($this->translator)) {
			$this->translator = $this->getServiceLocator()->get('translator');
		}
		return $this->translator;
	}

	/**
	 * getLog()
	 *
	 * @throws Exception\RuntimeException
	 * @return Log\Log
	 */
	public function getLog() {
		if (!is_object($this->log)) {
			$this->log = $this->getServiceLocator()->get('log');
		}
		return $this->log;
	}

	/**
	 * getSession()
	 *
	 * @throws Exception\RuntimeException
	 * @return getSession\getSession
	 */
	public function getSession() {
		if (!is_object($this->session)) {
			$this->session = $this->getServiceLocator()->get('session');
		}
		return $this->session;
	}

	/**
	 * getNumberFormat()
	 */
	public function getNumberFormat($locale = null, $style = NumberFormatter::CURRENCY) {
		if (empty($locale)) {
			$locale = $this->getTranslator()->getLocale();
		}
		$numberformatter = new NumberFormatter($locale, $style);
		return $numberformatter;
	}

	/**
	 * formatCurrency()
	 */
	public function formatCurrency($amount = 0, $code = '', $decimals = 2, $locale = null) {
		$numberFormatter = $this->getNumberFormat($locale, NumberFormatter::CURRENCY);
		$numberFormatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);
		$value = $numberFormatter->formatCurrency($amount, $code);
		return $value;
	}

	/**
	 * formatCurrency()
	 */
	public function formatNumber($amount = 0, $decimals = 2, $locale = null) {
		$numberFormatter = $this->getNumberFormat($locale, NumberFormatter::DECIMAL);
		$numberFormatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
		$numberFormatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
		$value = $numberFormatter->format($amount, NumberFormatter::TYPE_DEFAULT);
		return $value;
	}

	/**
	 * Adds the origin of the log() call to the event extras
	 *
	 * @param array $event event data
	 * @return array event data
	 */
	private function getBacktrace() {
		if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
			return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $this->traceLimit);
		}

		if (version_compare(PHP_VERSION, '5.3.6') >= 0) {
			return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		}

		$trace = debug_backtrace();

		array_shift($trace); // ignore $this->getBacktrace();
		array_shift($trace); // ignore $this->process()

		$i = 0;
		while (isset($trace[$i]['class']) && false !== strpos($trace[$i]['class'], $this->ignoredNamespace)) {
			$i++;
		}

		$origin = array(
				'file' => isset($trace[$i - 1]['file']) ? $trace[$i - 1]['file'] : null,
				'line' => isset($trace[$i - 1]['line']) ? $trace[$i - 1]['line'] : null,
				'class' => isset($trace[$i]['class']) ? $trace[$i]['class'] : null,
				'function' => isset($trace[$i]['function']) ? $trace[$i]['function'] : null,
		);

		return $origin;
	}

	/**
	 * Log Backtrace
	 */
	public function logBacktrace() {
		$backtrace = $this->getBacktrace();
		$this->getLog()->info($backtrace);
	}
}
