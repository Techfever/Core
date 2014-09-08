<?php

namespace Techfever\Template\Plugin\Validators;

use Techfever\Exception;
use Zend\Validator\AbstractValidator;
use Techfever\Functions\General as GeneralBase;

class Captcha extends AbstractValidator {
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	const IS_REQUIRED = 'isEmpty';
	const MATCH_INVALID = 'matchInvalid';
	const CAPTCHA_INVALID = 'captchaInvalid';
	const CAPTCHA_LENGTH = 'captchaLength';
	
	/**
	 *
	 * @var array
	 */
	protected $messageTemplates = array (
			self::IS_REQUIRED => "text_error_required",
			self::MATCH_INVALID => "text_error_captcha_match",
			self::CAPTCHA_INVALID => "text_error_captcha_invalid",
			self::CAPTCHA_LENGTH => "text_error_captcha_length" 
	);
	protected $options = array (
			'length' => 6 
	);
	
	/**
	 *
	 * @var array
	 */
	protected $messageVariables = array (
			'length' => array (
					'options' => 'length' 
			) 
	);
	
	/**
	 * Random session ID
	 *
	 * @var string
	 */
	protected $id;
	
	/**
	 * Generated word
	 *
	 * @var string
	 */
	protected $word;
	
	/**
	 * Sets validator options
	 *
	 * @param int|array|\Traversable $options        	
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			$options = func_get_args ();
			$temp ['length'] = array_shift ( $options );
			
			$options = $temp;
		}
		if (! isset ( $options ['servicelocator'] )) {
			throw new Exception\RuntimeException ( 'ServiceLocator has not been set or configured.' );
		}
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		
		$options = array_merge ( $this->options, $options );
		
		parent::__construct ( $options );
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
	 * Get captcha word
	 *
	 * @return string
	 */
	public function getWord() {
		if (empty ( $this->word )) {
			$id = $this->getID ();
			$Session = $this->getSession ();
			$Container = $Session->getContainer ( 'Zend_Form_Captcha_' . $id );
			$Container->setExpirationHops ( 1, null );
			$this->word = $Container->word;
		}
		return $this->word;
	}
	
	/**
	 * Retrieve captcha ID
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Set captcha identifier
	 *
	 * @param string $id        	
	 * @return AbstractWord
	 */
	protected function setID($id) {
		$this->id = $id;
		return $this;
	}
	
	/**
	 * Returns the length option
	 *
	 * @return int null
	 */
	public function getLength() {
		return $this->options ['length'];
	}
	
	/**
	 * Sets the length option
	 *
	 * @param int|null $length        	
	 * @throws Exception\InvalidArgumentException
	 * @return text Provides a fluent interface
	 */
	public function setLength($length) {
		if (null === $length) {
			$this->options ['length'] = null;
		} else {
			$this->options ['length'] = ( int ) $length;
		}
		
		return $this;
	}
	
	/**
	 * Validate the word
	 *
	 * @see Zend\Validator\ValidatorInterface::isValid()
	 * @param mixed $value        	
	 * @return bool
	 */
	public function isValid($value) {
		if (! is_array ( $value )) {
			$this->error ( self::CAPTCHA_INVALID );
			return false;
		}
		$id = $value ['id'];
		$input = strtolower ( $value ['input'] );
		if (empty ( $input )) {
			$this->error ( self::IS_REQUIRED );
			return false;
		}
		if (strlen ( $input ) !== $this->getLength ()) {
			$this->error ( self::CAPTCHA_LENGTH );
			return false;
		}
		$this->setID ( $id );
		$this->setValue ( $input );
		$key = $this->getWord ();
		if ($input !== $key) {
			$this->error ( self::MATCH_INVALID );
			return false;
		}
		if (empty ( $key )) {
			$this->error ( self::CAPTCHA_INVALID );
			return false;
		}
		
		return true;
	}
}
