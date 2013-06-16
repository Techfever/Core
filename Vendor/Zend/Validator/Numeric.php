<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Validator;

use Traversable;

class Numeric extends AbstractValidator {
	const NOT_NUMERIC = 'notNumeric';
	const STRING_EMPTY = 'numericStringEmpty';
	const TOO_SHORT = 'stringLengthTooShort';
	const TOO_LONG = 'stringLengthTooLong';
	
	/**
	 * Numeric filter used for validation
	 *
	 * @var \Zend\Filter\Numeric
	 */
	protected static $filter = null;
	
	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $messageTemplates = array (
			self::NOT_NUMERIC => "The input must contain only numeric.",
			self::STRING_EMPTY => "The input is an empty string",
			self::TOO_SHORT => "The input is less than %min%",
			self::TOO_LONG => "The input is more than %max%" 
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
			'min' => 0,
			'max' => null 
	);
	
	/**
	 * Sets validator options
	 *
	 * @param integer|array|\Traversable $options        	
	 */
	public function __construct($options = array()) {
		if ($options instanceof Traversable) {
			$options = ArrayUtils::iteratorToArray ( $options );
		}
		if (! is_array ( $options )) {
			$options = func_get_args ();
			$temp ['min'] = array_shift ( $options );
			if (! empty ( $options )) {
				$temp ['max'] = array_shift ( $options );
			}
			
			$options = $temp;
		}
		
		if (! array_key_exists ( 'min', $options ) || ! array_key_exists ( 'max', $options )) {
			// throw new Exception\InvalidArgumentException("Missing option.
			// 'min' and 'max' has to be given");
		}
		
		parent::__construct ( $options );
	}
	
	/**
	 * Returns the min option
	 *
	 * @return mixed
	 */
	public function getMin() {
		return $this->options ['min'];
	}
	
	/**
	 * Sets the min option
	 *
	 * @param mixed $min        	
	 * @return Between Provides a fluent interface
	 */
	public function setMin($min) {
		$this->options ['min'] = $min;
		return $this;
	}
	
	/**
	 * Returns the max option
	 *
	 * @return mixed
	 */
	public function getMax() {
		return $this->options ['max'];
	}
	
	/**
	 * Sets the max option
	 *
	 * @param mixed $max        	
	 * @return Between Provides a fluent interface
	 */
	public function setMax($max) {
		$this->options ['max'] = $max;
		return $this;
	}
	
	/**
	 * Returns true if and only if $value only contains Numeric characters
	 *
	 * @param string $value        	
	 * @return bool
	 */
	public function isValid($value) {
		$this->setValue ( ( string ) $value );
		if (empty($this->getValue ())) {
			$this->error ( self::STRING_EMPTY );
			return false;
		}
		
		if (! is_numeric ( $this->getValue () )) {
			$this->error ( self::NOT_NUMERIC );
			return false;
		}
		
		if ($this->getValue () < $this->getMin ()) {
			$this->error ( self::TOO_SHORT );
			return false;
		}
		
		if (null !== $this->getMax () && $this->getMax () < $this->getValue ()) {
			$this->error ( self::TOO_LONG );
			return false;
		}
		
		return true;
	}
}
