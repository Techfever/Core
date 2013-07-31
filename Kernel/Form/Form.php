<?php
namespace Kernel\Form;

use Zend\Validator\AbstractValidator;
use Zend\Http\Request as Request;
use Zend\Http\Response as Response;
use Kernel\Database\Database;
use Kernel\Exception;
use Kernel\ServiceLocator;
use Kernel\Form\Input;
use Kernel\Form\Verify;

class Form {

	/**
	 * @var Form Field
	 **/
	private $_field = array();

	/**
	 * @var Form Field
	 **/
	private $_route = null;

	/**
	 * @var Form Url Action
	 **/
	private $_route_action = null;

	/**
	 * @var Form Input
	 **/
	private $_input = null;

	/**
	 * @var Form Verify
	 **/
	private $_verify = null;

	/**
	 * @var Form Method
	 **/
	private $_method = 'post';

	/**
	 * @var ViewHelper
	 **/
	private $_viewhelper = null;

	/**
	 * Reference to the HTTP Request object
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Reference to the HTTP Response object
	 *
	 * @var Response
	 */
	protected $response;

	/**
	 * Constructor
	 *
	 * @return	void
	 **/
	public function __construct($options) {
		$this->request = null;
		$this->response = null;

		if (!is_array($options) || count($options) < 1) {
			throw new Exception\InvalidArgumentException('$options must be an array');
		}

		if (!isset($options['field']) || !is_array($options['field']) || count($options['field']) < 1) {
			throw new Exception\InvalidArgumentException('$options field must be an array');
		} else {
			$this->_field = $options['field'];
		}

		if (isset($options['route'])) {
			if (!is_string($options['route'])) {
				throw new Exception\InvalidArgumentException('$options route must be a string');
			} else {
				$this->_route = $options['route'];
			}
		}
		$options['route'] = $this->getRoute();

		if (isset($options['action'])) {
			if (!is_string($options['action'])) {
				throw new Exception\InvalidArgumentException('$options action must be a string');
			} else {
				$this->_route_action = $options['action'];
			}
		}
		$options['action'] = $this->getAction();

		if (isset($options['method'])) {
			if (!is_string($options['method'])) {
				throw new Exception\InvalidArgumentException('$options method must be a string');
			} else {
				$this->_method = $options['method'];
			}
		}
		$options['method'] = $this->getMethod();
		if (isset($options['request'])) {
			$this->setRequest($options['request']);
		}

		if (isset($options['response'])) {
			$this->setResponse($options['response']);
		}

		$translator = ServiceLocator::getServiceManager('Translator');
		AbstractValidator::setDefaultTranslator($translator);

		$this->_input = new Input($options);

		$this->_verify = new Verify($options);

		$this->_input->setInputFilter($this->_verify->getInputFilter());
	}

	/**
	 * Setter for the Request object
	 *
	 * @param  Request $request
	 * @return Http Provides a fluent interface
	 */
	public function setRequest(Request $request) {

		if (isset($request) && !$request instanceof Request) {
			throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an Zend\Http\Request');
		}
		$this->request = $request;

		return $this;
	}

	/**
	 * Getter for the Request object
	 *
	 * @return Request
	 */
	public function getRequest() {
		if (null === $this->request) {
			$this->setRequest(new Request());
		}
		return $this->request;
	}

	/**
	 * Setter for the Response object
	 *
	 * @param  Response $response
	 * @return Http Provides a fluent interface
	 */
	public function setResponse(Response $response) {

		if (isset($response) && !$response instanceof Response) {
			throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an Zend\Http\Response');
		}
		$this->response = $response;

		return $this;
	}

	/**
	 * Getter for the Response object
	 *
	 * @return Response
	 */
	public function getResponse() {
		if (null === $this->response) {
			$this->setResponse(new Response());
		}
		return $this->response;
	}

	/**
	 * Return the parameter container responsible for post parameters or a single post parameter.
	 *
	 * @param string|null           $name            Parameter name to retrieve, or null to get the whole container.
	 * @param mixed|null            $default         Default value to use when the parameter is missing.
	 * @return \Zend\Stdlib\ParametersInterface|mixed
	 */
	public function getPost($name = null, $default = null) {
		return $this->getRequest()->getPost($name, $default);
	}

	/**
	 * Is this a POST method request?
	 *
	 * @return bool
	 */
	public function isPost() {
		$status = $this->getRequest()->isPost();
		if ($status) {
			$this->setData($this->getPost());
		}
		return $status;
	}

	/**
	 * Set Post Parameter
	 *
	 * @param  array $values
	 * @return void
	 */
	public function setPostParameter($values) {
		return $this->getRequest()->getPost()->fromArray($values);
	}

	/**
	 * Get Input
	 * 
	 * @return Kernel\Input
	 **/
	public function getInput() {
		return $this->_input;
	}

	/**
	 * Get Verify
	 * 
	 * @return Kernel\Verify
	 **/
	public function getVerify() {
		return $this->_verify;
	}

	/**
	 * Get Method
	 * 
	 * @return string
	 **/
	public function getMethod() {
		if (!isset($this->_method)) {
			$this->_method = 'post';
		}
		return $this->_method;
	}

	/**
	 * Get Method
	 * 
	 * @return string
	 **/
	public function getAction() {
		if (!isset($this->_route_action)) {
			$this->_route_action = 'Index';
		}
		return array(
				'action' => $this->_route_action
		);
	}

	/**
	 * Get Route Uri
	 * 
	 * @return string
	 **/
	public function getRoute() {
		if (!isset($this->_route)) {
			$router = ServiceLocator::getServiceManager('router');
			$request = ServiceLocator::getServiceManager('request');

			$routeMatch = $router->match($request);
			if (!is_null($routeMatch)) {
				$this->_route = $routeMatch->getMatchedRouteName();
			}
		}
		return $this->_route;
	}

	/**
	 * Get Field
	 * 
	 * @return array
	 **/
	public function getField() {
		if (!isset($this->_field)) {
			$this->_field = array();
		}
		return $this->_field;
	}

	/**
	 * Set Data
	 * 
	 * @return void
	 **/
	public function bind($data) {
		$this->_input->bind($data);
	}

	/**
	 * Set Data
	 * 
	 * @return void
	 **/
	public function setData($data) {
		$this->_input->setData($data);
	}

	/**
	 * Get Data
	 * 
	 * @return void
	 **/
	public function getData() {
		$data = $this->_input->getData();
		$this->_verify->exchangeArray($data);
		return $this->_verify->getData();
	}

	/**
	 * Is Valid
	 * 
	 * @return void
	 **/
	public function isValid() {
		return $this->_input->isValid();
	}

	/**
	 * Prepare
	 * 
	 * @return void
	 **/
	public function prepare() {
		return $this->_input->prepare();
	}

	/**
	 * Input getElements
	 * 
	 * @return void
	 **/
	public function getElements() {
		return $this->_input->getElements();
	}

	/**
	 * Input Get
	 * 
	 * @return void
	 **/
	public function get($element) {
		return $this->_input->get($element);
	}

	/**
	 * Open Tag
	 * 
	 * @return void
	 **/
	public function openTag() {
		$this->prepare();
		return $this->_input->openTag();
	}

	/**
	 * End Tag
	 * 
	 * @return void
	 **/
	public function closeTag() {
		return $this->_input->closeTag();
	}

	/**
	 * Get Messages
	 * 
	 * @return void
	 **/
	public function getMessages($elementName = null) {
		return $this->_input->getMessages($elementName = null);
	}

	/**
	 * getInputFilter
	 * 
	 * @return void
	 **/
	public function getInputFilter($validator = null) {
		return $this->_verify->getInputFilter()->get($validator);
	}

	/**
	 * getMessageTemplates
	 *
	 * @return void
	 **/
	public function getMessageTemplates($elementName = null) {
		$Translator = ServiceLocator::getServiceManager('Translator');
		$messageTemplates = null;
		$validatorChain = $this->getInputFilter($elementName)->getValidatorChain()->getValidators();
		if (count($validatorChain) > 0) {
			$messageTemplates = array();
			foreach ($validatorChain as $validators) {
				$validatorsMessage = $validators['instance']->getMessageTemplates();
				$validatorsVariable = $validators['instance']->getMessageVariables();
				foreach ($validatorsMessage as $validatorsMessageKey => $validatorsMessageValue) {
					if (substr($validatorsMessageKey, -7) !== 'Invalid' && !empty($validatorsMessageValue)) {
						$validatorsMessageValue = $Translator->translate(strtolower($validatorsMessageValue));
						$searchMatch = array();
						$valid = false;
						foreach ($validatorsVariable as $ident) {
							$searchMatch[] = "%$ident%";
							try {
								$value = (string) $validators['instance']->getOption($ident);
							} catch (Exception $e) {
								$value = '';
							}
							if (strlen($value) > 0) {
								$valid = true;
								$validatorsMessageValue = str_replace("%$ident%", $value, $validatorsMessageValue);
							}
						}
						if (array_key_exists('field', $validators['instance']->getOptions())) {
							$value = $validators['instance']->getOption('field');
							$validatorsMessageValue = str_replace("%field%", (string) $value, $validatorsMessageValue);
						}
						if (array_key_exists('fieldmatch', $validators['instance']->getOptions())) {
							$value = $validators['instance']->getOption('fieldmatch');
							$validatorsMessageValue = str_replace("%fieldmatch%", (string) $value, $validatorsMessageValue);
						}
						$pattern = '/' . implode('|', $searchMatch) . '/i';
						if ($valid && !preg_match($pattern, $validatorsMessageValue)) {
							$messageTemplates[$validatorsMessageValue] = $validatorsMessageValue;
						} else if (substr($validatorsMessageKey, -5) == 'Empty') {
							$messageTemplates[$validatorsMessageValue] = $validatorsMessageValue;
						}
					}
				}
			}
		}
		return $messageTemplates;
	}

	/**
	 * getValidatorRelation
	 *
	 * @return void
	 **/
	public function getValidatorRelation($elementName = null) {
		$validatorChain = $this->getInputFilter($elementName)->getValidatorChain()->getValidators();
		$fieldmatch = array();
		if (count($validatorChain) > 0) {
			foreach ($validatorChain as $validators) {
				$options = $validators['instance']->getOptions();
				foreach ($options as $option_key => $option_value) {
					if ($option_key == 'chain') {
						$fieldmatch[] = $option_value;
					}
				}
			}
		}
		return $fieldmatch;
	}
}
