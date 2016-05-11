<?php

namespace Techfever\Form;

use Techfever\Functions\BaseHref;
use Techfever\Exception;
use Techfever\Form\Element;
use Techfever\Form\InputFilter as FormInputFilter;
use Techfever\Template\Plugin\Filters\ToUnderscore;
use Techfever\Functions\General as GeneralBase;
use Zend\Form\Form as BaseZForm;
use Zend\Form\FormInterface;
use Zend\Captcha\Image as CaptchaImage;

class Form extends BaseZForm {
	/**
	 *
	 * @var ServiceLocator
	 */
	private $serviceLocator = null;
	
	/**
	 *
	 * @var Database
	 */
	private $database = null;
	
	/**
	 *
	 * @var Translator
	 */
	private $translator = null;
	
	/**
	 *
	 * @var Session
	 */
	private $session = null;
	
	/**
	 *
	 * @var Log
	 */
	private $log = null;
	
	/**
	 *
	 * @var Options
	 */
	protected $options = array (
			'request' => null,
			'response' => null,
			'controller' => null,
			'route' => null,
			'action' => null,
			'id' => null,
			'variable' => null,
			'data' => null,
			'rank' => null 
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
	
	/**
	 * Captcha object
	 *
	 * @var Captcha
	 */
	protected $captcha;
	
	/**
	 * Error Messages
	 *
	 * @var Messages
	 */
	protected $error_message;
	
	/**
	 * Error Messages Total
	 *
	 * @var Messages
	 */
	protected $error_message_total;
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		if (! isset ( $options ['servicelocator'] )) {
			throw new Exception\RuntimeException ( 'ServiceLocator has not been set or configured.' );
		}
		
		$options = array_merge ( $this->options, $options );
		$this->setOptions ( $options );
		
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $this->options ['servicelocator'] );
		
		if (! isset ( $options ['variable'] )) {
			$options ['variable'] = $this->getVariables ();
		}
		$this->setOptions ( $options );
		$id = $this->getFormID ();
		
		parent::__construct ( $id );
		$ToUnderscore = new ToUnderscore ( '/' );
		$this->setAttribute ( 'id', $ToUnderscore->filter ( $id ) );
		
		$this->elementFactory ();
		
		$getInputFilter = $this->getFilter ()->getInputFilter ();
		$this->setInputFilter ( $getInputFilter );
	}
	
	/**
	 * function call handler
	 *
	 * @param string $function
	 *        	Function name to call
	 * @param array $args
	 *        	Function arguments
	 * @return mixed
	 * @throws Exception\RuntimeException
	 * @throws \Exception
	 */
	public function __call($name, $arguments) {
		if (is_object ( $this->generalobject )) {
			$obj = $this->generalobject;
			if (method_exists ( $obj, $name )) {
				if (is_array ( $arguments ) && count ( $arguments ) > 0) {
					return call_user_func_array ( array (
							$obj,
							$name 
					), $arguments );
				} else {
					return call_user_func ( array (
							$obj,
							$name 
					) );
				}
			}
		}
		return null;
	}
	
	/**
	 * getUserAccess()
	 *
	 * @return UserAccess
	 */
	public function getUserAccess() {
		return $this->getServiceLocator ()->get ( 'useraccess' );
	}
	
	/**
	 * Get Form ID
	 *
	 * @return string
	 *
	 */
	public function getFormID() {
		$id = $this->getOption ( 'id' );
		if (! empty ( $id )) {
			return $id;
		}
		return $this->getRoute () . '/' . $this->getRouteAction ();
	}
	
	/**
	 * Retrieve the validated data
	 *
	 * By default, retrieves normalized values; pass one of the
	 * FormInterface::VALUES_* constants to shape the behavior.
	 *
	 * @param int $flag        	
	 * @return array object
	 * @throws Exception\DomainException
	 */
	public function getData($flag = FormInterface::VALUES_NORMALIZED) {
		if (! $this->hasValidated) {
			throw new Exception\DomainException ( sprintf ( '%s cannot return data as validation has not yet occurred', __METHOD__ ) );
		}
		
		if (($flag !== FormInterface::VALUES_AS_ARRAY) && is_object ( $this->object )) {
			return $this->object;
		}
		
		$filter = $this->getInputFilter ();
		
		if ($flag === FormInterface::VALUES_RAW) {
			return $filter->getRawValues ();
		}
		
		$this->data = $filter->getValues ();
		
		$datetime = new \DateTime ();
		// $User = $this->getUserAccess()->getUsername();
		$this->data ['timestamp'] = $datetime->getTimestamp ();
		$this->data ['log_created_by'] = ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown');
		$this->data ['log_created_date'] = $datetime->format ( 'Y-m-d H:i:s' );
		$this->data ['log_modified_by'] = ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown');
		$this->data ['log_modified_date'] = $datetime->format ( 'Y-m-d H:i:s' );
		return $this->data;
	}
	
	/**
	 * Is this a POST method request?
	 *
	 * @return bool
	 */
	public function isPost() {
		return $this->getRequest ()->isPost ();
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
		$getPost = array_merge_recursive ( $this->getRequest ()->getPost ()->toArray (), $this->getRequest ()->getFiles ()->toArray () );
		// $this->getLog ()->info ( $getPost );
		$this->setData ( $getPost );
		
		return parent::isValid ();
	}
	
	/**
	 * Is this a POST method request?
	 *
	 * @return bool
	 */
	public function isSubmit() {
		$status = false;
		if ($this->isPost ()) {
			if ($this->isValid ()) {
				if ($this->getPost ( 'action', null ) == "submit") {
					$status = true;
				}
			}
		}
		return $status;
	}
	
	/**
	 * Return the parameter container responsible for post parameters or a single post parameter.
	 *
	 * @param string|null $name
	 *        	Parameter name to retrieve, or null to get the whole container.
	 * @param mixed|null $default
	 *        	Default value to use when the parameter is missing.
	 * @return \Zend\Stdlib\ParametersInterface mixed
	 */
	public function getPost($name = null, $default = null) {
		return $this->getRequest ()->getPost ( $name, $default );
	}
	
	/**
	 * Is the request a Javascript XMLHttpRequest?
	 *
	 * Should work with Prototype/Script.aculo.us, possibly others.
	 *
	 * @return bool
	 */
	public function isXmlHttpRequest() {
		return $this->getRequest ()->isXmlHttpRequest ();
	}
	
	/**
	 * Get validation error messages, if any
	 *
	 * Returns a hash of element names/messages for all elements failing
	 * validation, or, if $elementName is provided, messages for that element
	 * only.
	 *
	 * @param null|string $elementName        	
	 * @return array Traversable
	 * @throws Exception\InvalidArgumentException
	 */
	public function getMessages($elementName = null) {
		$status = false;
		if (! is_array ( $this->error_message ) && sizeof ( $this->error_message ) < 1) {
			$status = true;
		} elseif (! empty ( $elementName ) && ! array_key_exists ( $elementName, $this->error_message )) {
			$status = true;
		}
		if ($status) {
			$messages = parent::getMessages ( $elementName );
			$messagereturn = array ();
			if (is_array ( $messages ) && count ( $messages ) > 0) {
				foreach ( $messages as $messages_key => $messages_value ) {
					$messagereturn [$messages_key] = array ();
					$label = $this->getTranslate ( strtolower ( 'text_' . $messages_key ) );
					foreach ( $messages_value as $message_key => $message_value ) {
						$message_value = str_replace ( "%field%", ( string ) $label, $message_value );
						$messagereturn [$messages_key] = $message_value;
					}
				}
			}
			$this->error_message = $messagereturn;
		}
		return $this->error_message;
	}
	
	/**
	 * Get validation error messages, if any
	 *
	 * Returns a hash of element names/messages for all elements failing
	 * validation, or, if $elementName is provided, messages for that element
	 * only.
	 *
	 * @param null|string $elementName        	
	 * @return array Int
	 * @throws Exception\InvalidArgumentException
	 */
	public function getMessagesTotal($elementName = null) {
		if (empty ( $this->error_message_total )) {
			$this->error_message_total = count ( $this->getMessages ( $elementName ) );
		}
		return ( integer ) $this->error_message_total;
	}
	
	/**
	 * getMessageTemplates
	 *
	 * @return void
	 *
	 */
	public function getMessageTemplates($elementName = null, $messageKey = null) {
		$messageTemplates = null;
		if ($this->getFilter ()->has ( $elementName )) {
			$validatorChain = $this->getFilter ()->get ( $elementName )->getValidatorChain ()->getValidators ();
			$label = $this->getTranslate ( strtolower ( 'text_' . $elementName ) );
			if (count ( $validatorChain ) > 0) {
				$messageTemplates = array ();
				foreach ( $validatorChain as $validators ) {
					$validatorsMessage = $validators ['instance']->getMessageTemplates ();
					$validatorsVariable = $validators ['instance']->getMessageVariables ();
					foreach ( $validatorsMessage as $validatorsMessageKey => $validatorsMessageValue ) {
						if (substr ( $validatorsMessageKey, - 7 ) !== 'Invalid' && ! empty ( $validatorsMessageValue )) {
							$validatorsMessageValue = $this->getTranslate ( strtolower ( $validatorsMessageValue ) );
							$searchMatch = array ();
							$valid = false;
							foreach ( $validatorsVariable as $ident ) {
								$searchMatch [] = "%$ident%";
								try {
									$value = ( string ) $validators ['instance']->getOption ( $ident );
								} catch ( \Exception $e ) {
									$value = '';
								}
								if (strlen ( $value ) > 0) {
									$valid = true;
									$validatorsMessageValue = str_replace ( "%$ident%", $value, $validatorsMessageValue );
								}
							}
							$value = $label;
							$validatorsMessageValue = str_replace ( "%field%", ( string ) $value, $validatorsMessageValue );
							
							$pattern = '/' . implode ( '|', $searchMatch ) . '/i';
							if ($valid && ! preg_match ( $pattern, $validatorsMessageValue )) {
								$messageTemplates [$validatorsMessageKey] = $validatorsMessageValue;
							} else if (substr ( $validatorsMessageKey, - 5 ) == 'Empty') {
								$messageTemplates [$validatorsMessageKey] = $validatorsMessageValue;
							}
						}
					}
				}
			}
			if (! empty ( $messageKey ) && array_key_exists ( $messageKey, $messageTemplates )) {
				return $messageTemplates [$messageKey];
			}
		}
		return $messageTemplates;
	}
	
	/**
	 * getValidatorRelation
	 *
	 * @return void
	 *
	 */
	public function getValidatorRelation($elementName = null) {
		$chain = array ();
		if ($this->getFilter ()->has ( $elementName )) {
			$validatorChain = $this->getFilter ()->get ( $elementName )->getValidatorChain ()->getValidators ();
			if (count ( $validatorChain ) > 0) {
				foreach ( $validatorChain as $validators ) {
					$options = $validators ['instance']->getOptions ();
					foreach ( $options as $option_key => $option_value ) {
						if ($option_key == 'chain' && !empty($option_value)) {
							$chain [] = $option_value;
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
		if (empty ( $this->filter ) || $this->filter instanceof FormInputFilter) {
			$options = $this->getOptions ();
			$options ['servicelocator'] = $this->getServiceLocator ();
			$this->filter = new FormInputFilter ( $options, $this->getElement () );
		}
		return $this->filter;
	}
	private function getElement() {
		if (empty ( $this->element )) {
			$options = $this->getOptions ();
			$options ['servicelocator'] = $this->getServiceLocator ();
			$this->element = new Element ( $options );
		}
		return $this->element;
	}
	public function getElementData() {
		return $this->element->getElementData ();
	}
	private function elementFactory() {
		if (! $this->inputForm) {
			$Element = $this->getElement ();
			$elements = $Element->getElementData ();
			if (is_array ( $elements ) && count ( $elements ) > 0) {
				foreach ( $elements as $element_key => $element_value ) {
					if ($Element->validElementByKey ( $element_key )) {
						$element_config = $Element->getFormStuctureByKey ( $element_key );
						if (strtolower ( $element_value ['class'] ) === "captcha") {
							$element_config ['attributes'] ['type'] = 'captcha';
							if (! array_key_exists ( "captcha", $element_config ['options'] )) {
								$element_config ['options'] ['captcha'] = $this->generateCaptcha ();
							}
						}
						$this->add ( $element_config );
					}
				}
			}
		}
		return $this->inputForm;
	}
	public function getCaptchaRefresh($elementName) {
		$captcha_data = array ();
		if ($this->has ( $elementName )) {
			$captcha = parent::get ( $elementName )->getCaptcha ();
			$captcha_data ['element'] = $elementName;
			$captcha_data ['id'] = $captcha->generate ();
			$captcha_data ['src'] = $captcha->getImgUrl () . $captcha->getId () . $captcha->getSuffix ();
		}
		return $captcha_data;
	}
	
	/**
	 * function generate captcha
	 */
	private function generateCaptcha() {
		$BaseHref = new BaseHref ();
		$captchaimg = $BaseHref->getURL ();
		$captchaimg = $captchaimg . '/Image/Captcha';
		$setting = array (
				'expiration' => CAPTCHA_SIZE_EXPIRATION,
				'width' => CAPTCHA_SIZE_WIDTH,
				'height' => CAPTCHA_SIZE_HEIGHT,
				'dotNoiseLevel' => CAPTCHA_DOT_NOISE,
				'lineNoiseLevel' => CAPTCHA_LINE_NOISE,
				'wordlen' => CAPTCHA_LENGTH,
				'font' => CAPTCHA_FONT,
				'fontSize' => CAPTCHA_FONT_SIZE,
				'imgDir' => CAPTCHA_SAVE_PATH,
				'imgUrl' => $captchaimg 
		);
		$this->captcha = new CaptchaImage ( $setting );
		return $this->captcha;
	}
	public function getVariables() {
		$data = $this->getOption ( 'variable' );
		if (is_array ( $data ) && count ( $data ) > 0) {
			return $data;
		}
		return array ();
	}
}
