<?php

namespace Techfever\Template\Plugin\Validators;

use Techfever\Exception;
use Zend\Stdlib\StringUtils;
use Zend\Stdlib\StringWrapper\StringWrapperInterface as StringWrapper;
use Zend\Validator\AbstractValidator;
use Zend\I18n\Filter\Alnum as AlnumFilter;
use Techfever\Parameter\Parameter;
use Techfever\Functions\General as GeneralBase;

class Placement extends AbstractValidator {
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	const INVALID = 'textInvalid';
	const ALPHA_CHAR_MIN = 'textAlphaCharMin';
	const ALPHA_CHAR_MAX = 'textAlphaCharMax';
	const ALPHA_CHAR_ONLY = 'textAlphaCharOnly';
	const POSITION_INVALID = 'textPositionInvalid';
	const USERNAME_INVALID = 'textUsernameMatchInvalid';
	const POSITION_SELECT = 'textPositionSelect';
	
	/**
	 *
	 * @var array
	 */
	protected $messageTemplates = array (
			self::INVALID => "text_error_invalid_value_type",
			self::ALPHA_CHAR_MIN => "text_error_alphabetic_characters_min",
			self::ALPHA_CHAR_MAX => "text_error_alphabetic_characters_max",
			self::ALPHA_CHAR_ONLY => "text_error_alphabetic_characters_only",
			self::POSITION_INVALID => "text_error_position_exist",
			self::USERNAME_INVALID => "text_error_username_not_valid",
			self::POSITION_SELECT => "text_error_position_select" 
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
			),
			'match' => array (
					'options' => 'match' 
			),
			'fieldmatch' => array (
					'options' => 'fieldmatch' 
			) 
	);
	protected $options = array (
			'type' => 'Char',
			'min' => 0,
			'max' => null,
			'chain' => null,
			'fieldmatch' => null,
			'match' => null,
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
		if (! is_string ( $value ) && ! is_int ( $value ) && ! is_float ( $value )) {
			$this->error ( self::ALPHA_CHAR_ONLY );
			return false;
		}
		
		$this->setValue ( strtoupper ( $value ) );
		
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
		
		$rankcheck = constant ( "USER_REGISTER_PLACEMENT_" . $this->options ['rank'] );
		$rankcheck = explode ( ':', $rankcheck );
		$DBRank = $this->getDatabase ();
		$DBRank->select ();
		$DBRank->columns ( array (
				'id' => 'user_rank_id',
				'iso' => 'user_rank_key',
				'group' => 'user_rank_group_id' 
		) );
		$DBRank->from ( array (
				'ur' => 'user_rank' 
		) );
		$DBRank->where ( array (
				'ur.user_rank_group_id in (' . implode ( ', ', $rankcheck ) . ')',
				'ur.user_rank_status = 1' 
		) );
		$DBRank->order ( array (
				'user_rank_key ASC' 
		) );
		$DBRank->setCacheName ( 'user_rank_' . implode ( '_', $rankcheck ) );
		$DBRank->execute ();
		$rank = array ();
		if ($DBRank->hasResult ()) {
			$data = array ();
			while ( $DBRank->valid () ) {
				$data = $DBRank->current ();
				$rank [] = $data ['id'];
				$DBRank->next ();
			}
		} else {
			$this->error ( self::USERNAME_INVALID );
			return false;
		}
		
		$DBVerify = $this->getDatabase ();
		$DBVerify->select ();
		$DBVerify->columns ( array (
				'id' => 'user_access_id' 
		) );
		$DBVerify->from ( array (
				'ua' => 'user_access' 
		) );
		$DBVerify->join ( array (
				'uh' => 'user_hierarchy' 
		), 'uh.user_access_id = ua.user_access_id', array (
				'sponsor' => 'user_hierarchy_sponsor',
				'placement' => 'user_hierarchy_placement' 
		) );
		$DBVerify->where ( array (
				'ua.user_rank_id  in (' . implode ( ', ', $rank ) . ')',
				'uh.user_access_username = "' . strtoupper ( $value ) . '"',
				'ua.user_access_status = 1',
				'ua.user_access_delete_status = 0' 
		) );
		$DBVerify->limit ( 1 );
		$DBVerify->execute ();
		$placement = null;
		if ($DBVerify->hasResult ()) {
			$data = $DBVerify->current ();
			$placement = $data ['placement'];
		} else {
			$this->error ( self::USERNAME_INVALID );
			return false;
		}
		
		$key = $this->options ['match'];
		if (empty ( $key )) {
			$this->error ( self::POSITION_SELECT );
			return false;
		} else {
			$translator = $this->getTranslator ();
			$match = $translator->translate ( 'text_' . $key );
			$this->options ['match'] = $match;
		}
		
		$PlacementParameter = new Parameter ( array (
				'key' => 'user_hierarchy_placement_position',
				'servicelocator' => $this->getServiceLocator () 
		) );
		$valuePlacement = $PlacementParameter->getValueByKey ( $key );
		if (strlen ( $valuePlacement ) > 0) {
			$placement .= '|L:' . $valuePlacement;
		} else {
			$this->error ( self::POSITION_SELECT );
			return false;
		}
		
		$DBVerify = $this->getDatabase ();
		$DBVerify->select ();
		$DBVerify->columns ( array (
				'id' => 'user_access_id' 
		) );
		$DBVerify->from ( array (
				'uh' => 'user_hierarchy' 
		) );
		$DBVerify->where ( array (
				'uh.user_hierarchy_placement = "' . $placement . '"' 
		) );
		$DBVerify->execute ();
		if ($DBVerify->hasResult () || empty ( $placement )) {
			$this->error ( self::POSITION_INVALID );
			return false;
		}
		
		return true;
	}
}
