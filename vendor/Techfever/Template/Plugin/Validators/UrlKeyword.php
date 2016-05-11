<?php

namespace Techfever\Template\Plugin\Validators;

use Techfever\Exception;
use Zend\Stdlib\StringUtils;
use Zend\Stdlib\StringWrapper\StringWrapperInterface as StringWrapper;
use Zend\Validator\AbstractValidator;
use Techfever\Functions\General as GeneralBase;
use Techfever\Template\Plugin\Filters\ToUnderscore;

class UrlKeyword extends AbstractValidator {
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	const INVALID = 'textInvalid';
	const ALPHA_CHAR_MIN = 'textAlphaCharMin';
	const ALPHA_CHAR_MAX = 'textAlphaCharMax';
	const URL_KEYWORD_INVALID = 'textURLKeywordExistInvalid';
	
	/**
	 *
	 * @var array
	 */
	protected $messageTemplates = array (
			self::INVALID => "text_error_invalid_value_type",
			self::ALPHA_CHAR_MIN => "text_error_alphabetic_characters_min",
			self::ALPHA_CHAR_MAX => "text_error_alphabetic_characters_max",
			self::URL_KEYWORD_INVALID => "text_error_url_keyword_exist" 
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
			'max' => null,
			'content' => "",
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
		$ToUnderscore = new ToUnderscore ( ' ' );
		$value = $ToUnderscore->filter ( $value );
		
		$this->setValue ( strtoupper ( $value ) );
		
		$length = $this->getStringWrapper ()->strlen ( $value );
		if ($length < $this->getMin ()) {
			$this->error ( self::ALPHA_CHAR_MIN );
			return false;
		}
		
		if (null !== $this->getMax () && $this->getMax () < $length) {
			$this->error ( self::ALPHA_CHAR_MAX );
			return false;
		}
		
		$content_type = $this->options ['content'];
		
		if (! empty ( $content_type )) {
			
			$user_access_id = (empty ( $this->options ['user_access_id'] ) ? 0 : $this->options ['user_access_id']);
			$content_id = (array_key_exists ( 'content_' . $content_type . '_id', $this->options ) ? $this->options ['content_' . $content_type . '_id'] : 0);
			$content_type_id = $this->options ['content_type_id'];
			
			$DBVerify = $this->getDatabase ();
			$DBVerify->select ();
			$DBVerify->columns ( array (
					'id' => 'content_' . $content_type . '_id' 
			) );
			$DBVerify->from ( array (
					'cd' => 'content_' . $content_type 
			) );
			$DBVerify->join ( array (
					'cdd' => 'content_' . $content_type . '_url' 
			), 'cdd.content_' . $content_type . '_id  = cd.content_' . $content_type . '_id', array (
					'content_' . $content_type . '_url_id' 
			) );
			$where = array (
					'cdd.content_' . $content_type . '_url_keyword = "' . strtoupper ( $value ) . '"',
					'cdd.content_' . $content_type . '_url_delete_status = 0',
					'cd.content_' . $content_type . '_delete_status = 0',
					'cd.content_type_id = "' . $content_type_id . '"',
					'cd.user_access_id = "' . $user_access_id . '"' 
			);
			if (is_numeric ( $content_id ) && $content_id > 0) {
				$where [] = 'cd.content_' . $content_type . '_id != ' . $content_id;
			}
			$DBVerify->where ( $where );
			$DBVerify->limit ( 1 );
			$DBVerify->execute ();
			if ($DBVerify->hasResult () === true) {
				$this->error ( self::URL_KEYWORD_INVALID );
				return false;
			}
		}
		return true;
	}
}
