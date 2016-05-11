<?php

namespace Techfever\Template\Plugin\Validators;

use Techfever\Exception;
use Zend\Stdlib\StringUtils;
use Zend\Stdlib\StringWrapper\StringWrapperInterface as StringWrapper;
use Zend\Validator\AbstractValidator;
use Zend\Filter\Digits as DigitsFilter;
use Zend\I18n\Filter\Alnum as AlnumFilter;
use Zend\I18n\Filter\Alpha as AlphaFilter;
use Techfever\Functions\General as GeneralBase;

class NricPassport extends AbstractValidator {
	const INVALID = 'textInvalid';
	const ALPHA_CHAR_MIN = 'textAlphaCharMin';
	const ALPHA_CHAR_MAX = 'textAlphaCharMax';
	const ALPHA_CHAR_ONLY = 'textAlphaCharOnly';
	const NUMERIC_MIN = 'textNumericMin';
	const NUMERIC_MAX = 'textNumericMax';
	const NUMERIC_ONLY = 'textNumericOnly';
	const CHAR_MIN = 'textCharMin';
	const CHAR_MAX = 'textCharMax';
	const CHAR_ONLY = 'textCharOnly';
	const NRICPASSPORTEXIST = 'textNricPassportExist';
	
	/**
	 *
	 * @var array
	 */
	protected $messageTemplates = array (
			self::INVALID => "text_error_invalid_value_type",
			self::NUMERIC_MIN => "",
			self::NUMERIC_MAX => "",
			self::NUMERIC_ONLY => "",
			self::CHAR_MIN => "",
			self::CHAR_MAX => "",
			self::CHAR_ONLY => "",
			self::ALPHA_CHAR_MIN => "",
			self::ALPHA_CHAR_MAX => "",
			self::ALPHA_CHAR_ONLY => "",
			self::NRICPASSPORTEXIST => "text_error_nricpassport_exist" 
	);
	
	/**
	 *
	 * @var array
	 */
	protected $messageVariables = array (
			'min' => array (
					'options' => 'min' 
			),
			'max' => array (
					'options' => 'max' 
			) 
	);
	protected $options = array (
			'type' => 'AlphaChar',
			'unique' => 'False',
			'min' => 0,
			'max' => null,
			'encoding' => 'UTF-8' 
	);
	protected $stringWrapper;
	
	/**
	 * Alphabetic filter used for validation
	 *
	 * @var AlphaFilter
	 */
	protected static $filter = null;
	
	/**
	 * Sets validator options
	 *
	 * @param int|array|\Traversable $options        	
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			$options = func_get_args ();
			$temp ['min'] = array_shift ( $options );
			if (! empty ( $options )) {
				$temp ['max'] = array_shift ( $options );
			}
			
			if (! empty ( $options )) {
				$temp ['encoding'] = array_shift ( $options );
			}
			
			$options = $temp;
		}
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		
		parent::__construct ( $options );
		$messageTemplates = array ();
		if ($this->getType () == 'Numeric') {
			$messageTemplates = array (
					self::NUMERIC_MIN => "text_error_numeric_min",
					self::NUMERIC_MAX => "text_error_numeric_max",
					self::NUMERIC_ONLY => "text_error_numeric_only" 
			);
		} elseif ($this->getType () == 'Char') {
			$messageTemplates = array (
					self::CHAR_MIN => "text_error_characters_min",
					self::CHAR_MAX => "text_error_characters_max",
					self::CHAR_ONLY => "text_error_characters_only" 
			);
		} elseif ($this->getType () == 'AlphaChar') {
			$messageTemplates = array (
					self::ALPHA_CHAR_MIN => "text_error_alphabetic_characters_min",
					self::ALPHA_CHAR_MAX => "text_error_alphabetic_characters_max",
					self::ALPHA_CHAR_ONLY => "text_error_alphabetic_characters_only" 
			);
		}
		$this->setMessages ( $messageTemplates );
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
	 * Returns the unique option
	 *
	 * @return string null
	 */
	public function getUnique() {
		return $this->options ['unique'];
	}
	
	/**
	 * Sets the unique option
	 *
	 * @param string|null $unique        	
	 * @return Unique Provides a fluent interface
	 */
	public function setUnique($unique) {
		if (null === $unique) {
			$this->options ['unique'] = null;
		} else {
			$this->options ['unique'] = ( string ) $unique;
		}
		
		return $this;
	}
	
	/**
	 * Returns the type option
	 *
	 * @return string null
	 */
	public function getType() {
		return $this->options ['type'];
	}
	
	/**
	 * Sets the type option
	 *
	 * @param string|null $type        	
	 * @return Type Provides a fluent interface
	 */
	public function setType($type) {
		if (null === $type) {
			$this->options ['type'] = null;
		} else {
			$this->options ['type'] = ( string ) $type;
		}
		
		return $this;
	}
	
	/**
	 * Returns the min option
	 *
	 * @return int
	 */
	public function getMin() {
		return $this->options ['min'];
	}
	
	/**
	 * Sets the min option
	 *
	 * @param int $min        	
	 * @throws Exception\InvalidArgumentException
	 * @return text Provides a fluent interface
	 */
	public function setMin($min) {
		if (null !== $this->getMax () && $min > $this->getMax ()) {
			throw new Exception\InvalidArgumentException ( "The minimum must be less than or equal to the maximum length, but $min >" . " " . $this->getMax () );
		}
		
		$this->options ['min'] = max ( 0, ( int ) $min );
		return $this;
	}
	
	/**
	 * Returns the max option
	 *
	 * @return int null
	 */
	public function getMax() {
		return $this->options ['max'];
	}
	
	/**
	 * Sets the max option
	 *
	 * @param int|null $max        	
	 * @throws Exception\InvalidArgumentException
	 * @return text Provides a fluent interface
	 */
	public function setMax($max) {
		if (null === $max) {
			$this->options ['max'] = null;
		} elseif ($max < $this->getMin ()) {
			throw new Exception\InvalidArgumentException ( "The maximum must be greater than or equal to the minimum length, but " . "$max < " . $this->getMin () );
		} else {
			$this->options ['max'] = ( int ) $max;
		}
		
		return $this;
	}
	
	/**
	 * Get the string wrapper to detect the string length
	 *
	 * @return StringWrapper
	 */
	public function getStringWrapper() {
		if (! $this->stringWrapper) {
			$this->stringWrapper = StringUtils::getWrapper ( $this->getEncoding () );
		}
		return $this->stringWrapper;
	}
	
	/**
	 * Set the string wrapper to detect the string length
	 *
	 * @param StringWrapper $stringWrapper        	
	 * @return text
	 */
	public function setStringWrapper(StringWrapper $stringWrapper) {
		$stringWrapper->setEncoding ( $this->getEncoding () );
		$this->stringWrapper = $stringWrapper;
	}
	
	/**
	 * Returns the actual encoding
	 *
	 * @return string
	 */
	public function getEncoding() {
		return $this->options ['encoding'];
	}
	
	/**
	 * Sets a new encoding to use
	 *
	 * @param string $encoding        	
	 * @return text
	 * @throws Exception\InvalidArgumentException
	 */
	public function setEncoding($encoding) {
		$this->stringWrapper = StringUtils::getWrapper ( $encoding );
		$this->options ['encoding'] = $encoding;
		return $this;
	}
	
	/**
	 * Returns true if and only if the string length of $value is at least the min option and
	 * no greater than the max option (when the max option is not null).
	 *
	 * @param string $value        	
	 * @return bool
	 */
	public function isValid($value) {
		if ($this->getType () == 'Numeric') {
			$this->setValue ( $value );
			if (! is_string ( $value ) && ! is_int ( $value ) && ! is_float ( $value )) {
				$this->error ( self::NUMERIC_ONLY );
				return false;
			}
			
			if (null === static::$filter) {
				static::$filter = new DigitsFilter ();
			}
			
			if ($this->getValue () !== static::$filter->filter ( $this->getValue () )) {
				$this->error ( self::NUMERIC_ONLY );
				return false;
			}
			
			if (null !== $this->getMin () && $this->getValue () < $this->getMin ()) {
				$this->error ( self::NUMERIC_MIN );
				return false;
			}
			
			if (null !== $this->getMax () && $this->getValue () > $this->getMax ()) {
				$this->error ( self::NUMERIC_MAX );
				return false;
			}
		} elseif ($this->getType () == 'Char') {
			$this->setValue ( ( string ) $value );
			
			if (null === static::$filter) {
				static::$filter = new AlphaFilter ();
			}
			
			static::$filter->setAllowWhiteSpace ( true );
			
			if ($value !== static::$filter->filter ( $value )) {
				$this->error ( self::CHAR_ONLY );
				return false;
			}
			
			$length = $this->getStringWrapper ()->strlen ( $value );
			if ($length < $this->getMin ()) {
				$this->error ( self::CHAR_MIN );
				return false;
			}
			
			if (null !== $this->getMax () && $this->getMax () < $length) {
				$this->error ( self::CHAR_MAX );
				return false;
			}
		} elseif ($this->getType () == 'AlphaChar') {
			if (! is_string ( $value ) && ! is_int ( $value ) && ! is_float ( $value )) {
				$this->error ( self::ALPHA_CHAR_ONLY );
				return false;
			}
			$this->setValue ( ( string ) $value );
			
			if (null === static::$filter) {
				static::$filter = new AlnumFilter ();
			}
			
			static::$filter->setAllowWhiteSpace ( true );
			
			if ($value != static::$filter->filter ( $value )) {
				$this->error ( self::ALPHA_CHAR_ONLY );
				return false;
			}
			
			$length = $this->getStringWrapper ()->strlen ( $value );
			if ($length < $this->getMin ()) {
				$this->error ( self::ALPHA_CHAR_MIN );
				return false;
			}
			
			if (null !== $this->getMax () && $this->getMax () < $length) {
				$this->error ( self::ALPHA_CHAR_MAX );
				return false;
			}
		}
		$user_access_id = $this->options ['user_access_id'];
		$user_profile_id = $this->options ['user_profile_id'];
		$default_value = $this->options ['default_value'];
		if ($this->getUnique () == 'True') {
			$QNric = $this->getDatabase ();
			$QNric->select ();
			$QNric->columns ( array (
					'nricpassport' => 'user_profile_nric_passport' 
			) );
			$QNric->from ( array (
					'up' => 'user_profile' 
			) );
			$where = array (
					'up.user_profile_nric_passport = "' . $value . '"' 
			);
			if (is_numeric ( $user_profile_id ) && $user_profile_id > 0) {
				$where [] = 'up.user_profile_id != ' . $user_profile_id;
			}
			$QNric->where ( $where );
			$QNric->execute ();
			if ($QNric->hasResult ()) {
				$this->error ( self::NRICPASSPORTEXIST );
				return false;
			}
		}
		return true;
	}
}
