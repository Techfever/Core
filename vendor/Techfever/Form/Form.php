<?php
namespace Techfever\Form;

use Techfever\Exception;
use Techfever\Form\Element;
use Techfever\Form\InputFilter as FormInputFilter;
use Zend\Form\Form as BaseZForm;
use Zend\Form\FormInterface;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Validator\AbstractValidator;
use Traversable;

class Form extends BaseZForm {
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
	 * @var Session
	 */
	private $session = null;

	/**
	 * @var Options
	 */
	protected $options = array(
			'request' => null,
			'response' => null,
			'controller' => null,
			'route' => null,
			'action' => null,
			'id' => null,
			'variable' => null,
			'value' => null,
			'rank' => null,
	);

	/**
	 * InputFilter object
	 *
	 * @var InputFilter
	 */
	protected $filter;

	/**
	 * InputForm
	 *
	 * @var InputForm
	 */
	protected $inputForm;

	/**
	 * Element object
	 *
	 * @var Element
	 */
	protected $element;

	public function __construct($options = null) {
		if (!is_array($options)) {
			throw new Exception\RuntimeException('Options has not been set or configured.');
		}
		if (!isset($options['servicelocator'])) {
			throw new Exception\RuntimeException('ServiceLocator has not been set or configured.');
		}
		$this->setServiceLocator($options['servicelocator']);
		unset($options['servicelocator']);

		$options = array_merge($this->options, $options);
		$this->setOptions($options);

		if (!isset($options['variable'])) {
			$options['variable'] = $this->getVariable();
		}
		$this->setOptions($options);
		$id = $this->getFormID();

		parent::__construct($id);
		$this->setAttribute('id', str_replace('/', '_', $id));
		//$this->setAttribute('class', 'steps');

		$this->elementFactory();

		$getInputFilter = $this->getFilter()->getInputFilter();
		$this->setInputFilter($getInputFilter);
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
		return $this->getServiceLocator()->get('router');
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
	 * getSession()
	 *
	 * @throws Exception\RuntimeException
	 * @return getSession\getSession
	 */
	public function getSession($key) {
		if (!is_object($this->session)) {
			$this->session = $this->getServiceLocator()->get('session');
		}
		return $this->session;
	}

	/**
	 * getUserAccess()
	 *
	 * @return UserAccess
	 */
	public function getUserAccess() {
		return $this->getServiceLocator()->get('useraccess');
	}

	/**
	 * Get Form ID
	 * 
	 * @return string
	 **/
	public function getFormID() {
		$id = $this->getOption('id');
		if (!empty($id)) {
			return $id;
		}
		return $this->getRoute() . '/' . $this->getRouteAction();
	}

	/**
	 * Retrieve the validated data
	 *
	 * By default, retrieves normalized values; pass one of the
	 * FormInterface::VALUES_* constants to shape the behavior.
	 *
	 * @param  int $flag
	 * @return array|object
	 * @throws Exception\DomainException
	 */
	public function getData($flag = FormInterface::VALUES_NORMALIZED) {
		if (!$this->hasValidated) {
			throw new Exception\DomainException(sprintf('%s cannot return data as validation has not yet occurred', __METHOD__));
		}

		if (($flag !== FormInterface::VALUES_AS_ARRAY) && is_object($this->object)) {
			return $this->object;
		}

		$filter = $this->getInputFilter();

		if ($flag === FormInterface::VALUES_RAW) {
			return $filter->getRawValues();
		}

		$this->data = $filter->getValues();

		$datetime = new \DateTime();
		//$User = $this->getUserAccess()->getUsername();
		$User = '';
		$this->data['log_created_by'] = (!empty($User) ? $User : 'Unknown');
		$this->data['log_created_date'] = $datetime->format('Y-m-d H:i:s');
		$this->data['log_modified_by'] = (!empty($User) ? $User : 'Unknown');
		$this->data['log_modified_date'] = $datetime->format('Y-m-d H:i:s');
		return $this->data;
	}

	/**
	 * Is this a POST method request?
	 *
	 * @return bool
	 */
	public function isPost() {
		return $this->getRequest()->isPost();
	}

	/**
	 * Validate the form
	 *
	 * Typically, will proxy to the composed input filter.
	 *
	 * @return bool
	 * @throws Exception\DomainException
	 */
	public function isValid() {

		$getPost = $this->getRequest()->getPost();
		$this->setData($getPost);

		return parent::isValid();
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
	 * Is the request a Javascript XMLHttpRequest?
	 *
	 * Should work with Prototype/Script.aculo.us, possibly others.
	 *
	 * @return bool
	 */
	public function isXmlHttpRequest() {
		return $this->getRequest()->isXmlHttpRequest();
	}

	/**
	 * Get validation error messages, if any
	 *
	 * Returns a hash of element names/messages for all elements failing
	 * validation, or, if $elementName is provided, messages for that element
	 * only.
	 *
	 * @param  null|string $elementName
	 * @return array|Traversable
	 * @throws Exception\InvalidArgumentException
	 */
	public function getMessages($elementName = null) {
		$messages = parent::getMessages($elementName);
		$messagereturn = array();
		if (is_array($messages) && count($messages) > 0) {
			foreach ($messages as $messages_key => $messages_value) {
				$messagereturn[$messages_key] = array();
				$label = $this->getTranslate(strtolower('text_' . $messages_key));
				foreach ($messages_value as $message_key => $message_value) {
					$message_value = str_replace("%field%", (string) $label, $message_value);
					$messagereturn[$messages_key][$message_key] = $message_value;
				}
			}
		}
		return $messagereturn;
	}

	/**
	 * getMessageTemplates
	 *
	 * @return void
	 **/
	public function getMessageTemplates($elementName = null, $messageKey = null) {
		$messageTemplates = null;
		if ($this->getFilter()->has($elementName)) {
			$validatorChain = $this->getFilter()->get($elementName)->getValidatorChain()->getValidators();
			$label = $this->getTranslate(strtolower('text_' . $elementName));
			if (count($validatorChain) > 0) {
				$messageTemplates = array();
				foreach ($validatorChain as $validators) {
					$validatorsMessage = $validators['instance']->getMessageTemplates();
					$validatorsVariable = $validators['instance']->getMessageVariables();
					foreach ($validatorsMessage as $validatorsMessageKey => $validatorsMessageValue) {
						if (substr($validatorsMessageKey, -7) !== 'Invalid' && !empty($validatorsMessageValue)) {
							$validatorsMessageValue = $this->getTranslate(strtolower($validatorsMessageValue));
							$searchMatch = array();
							$valid = false;
							foreach ($validatorsVariable as $ident) {
								$searchMatch[] = "%$ident%";
								try {
									$value = (string) $validators['instance']->getOption($ident);
								} catch (\Exception $e) {
									$value = '';
								}
								if (strlen($value) > 0) {
									$valid = true;
									$validatorsMessageValue = str_replace("%$ident%", $value, $validatorsMessageValue);
								}
							}
							$value = $label;
							$validatorsMessageValue = str_replace("%field%", (string) $value, $validatorsMessageValue);

							$pattern = '/' . implode('|', $searchMatch) . '/i';
							if ($valid && !preg_match($pattern, $validatorsMessageValue)) {
								$messageTemplates[$validatorsMessageKey] = $validatorsMessageValue;
							} else if (substr($validatorsMessageKey, -5) == 'Empty') {
								$messageTemplates[$validatorsMessageKey] = $validatorsMessageValue;
							}
						}
					}
				}
			}
			if (!empty($messageKey) && array_key_exists($messageKey, $messageTemplates)) {
				return $messageTemplates[$messageKey];
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
		$chain = array();
		if ($this->getFilter()->has($elementName)) {
			$validatorChain = $this->getFilter()->get($elementName)->getValidatorChain()->getValidators();
			if (count($validatorChain) > 0) {
				foreach ($validatorChain as $validators) {
					$options = $validators['instance']->getOptions();
					foreach ($options as $option_key => $option_value) {
						if ($option_key == 'chain') {
							$chain[] = $option_value;
						}
					}
				}
			}
		}
		return $chain;
	}

	/**
	 * Get Filter()
	 *
	 * @return Zend\InputFilter
	 */
	public function getFilter() {
		if (empty($this->filter) || $this->filter instanceof FormInputFilter) {
			$options = $this->getOptions();
			$options['servicelocator'] = $this->getServiceLocator();
			$this->filter = new FormInputFilter($options, $this->getElement());
		}
		return $this->filter;
	}

	private function getElement() {
		if (empty($this->element)) {
			$options = $this->getOptions();
			$options['servicelocator'] = $this->getServiceLocator();
			$this->element = new Element($options);
		}
		return $this->element;
	}

	private function elementFactory() {
		if (!$this->inputForm) {
			$Element = $this->getElement();
			$elements = $Element->getElementData();
			if (is_array($elements) && count($elements) > 0) {
				foreach ($elements as $element_key => $element_value) {
					if ($Element->validElementByKey($element_key)) {
						$this->add($Element->getFormStuctureByKey($element_key));
					}
				}
			}
		}
		return $this->inputForm;
	}

	public function getVariable() {
		$data = $this->getOption('variable');
		if (is_array($data) && count($data) > 0) {
			return $data;
		}
		return array();
	}
}
