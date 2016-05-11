<?php

namespace Techfever\Template\Plugin\Validators;

use Techfever\Exception;
use Zend\Stdlib\StringUtils;
use Zend\Stdlib\StringWrapper\StringWrapperInterface as StringWrapper;
use Zend\Validator\AbstractValidator;
use Techfever\Functions\General as GeneralBase;

class Select extends AbstractValidator {
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	const INVALID = 'selectInvalid';
	const isEmpty = 'isEmpty';
	
	/**
	 *
	 * @var array
	 */
	protected $messageTemplates = array (
			self::INVALID => "text_error_invalid_value_type",
			self::isEmpty => "text_error_required" 
	);
	
	/**
	 *
	 * @var array
	 */
	protected $messageVariables = array (
			'fieldmatch' => array (
					'options' => 'fieldmatch' 
			) 
	);
	protected $options = array (
			'type' => 'Integer',
			// Minimum length,
			'encoding' => 'UTF-8',
			// Encoding to use
			'chain' => null,
			'fieldmatch' => null 
	);
	protected $stringWrapper;
	
	/**
	 * Sets validator options
	 *
	 * @param integer|array|\Traversable $options        	
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		if (! isset ( $options ['servicelocator'] )) {
			throw new Exception\RuntimeException ( 'ServiceLocator has not been set or configured.' );
		}
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		
		if (isset ( $options ['chain'] )) {
			$translator = $this->getTranslator ();
			$options ['fieldmatch'] = $translator->translate ( 'text_' . $options ['chain'] );
		}
		
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
	 * @param
	 *        	StringWrapper
	 * @return StringLength
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
		$this->setValue ( $value );
		
		if (isset ( $this->options ['match'] )) {
			if (empty ( $this->options ['match'] )) {
				$this->error ( self::isEmpty );
				return false;
			} else {
				$translator = $this->getTranslator ();
				$match = $translator->translate ( 'text_' . $this->options ['match'] );
				$this->options ['match'] = $match;
			}
		}
		if ($this->getType () == 'Integer' && ! is_numeric ( $value )) {
			$this->error ( self::INVALID );
			return false;
		} elseif ($this->getType () == 'String' && is_string ( $value )) {
			$length = $this->getStringWrapper ()->strlen ( $value );
			if ($length < 1) {
				$this->error ( self::INVALID );
				return false;
			}
		}
		return true;
	}
}
