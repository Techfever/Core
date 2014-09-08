<?php

namespace Techfever\Template\Plugin\Forms;

use Zend\Form\Element;
use Zend\Form\Exception\InvalidArgumentException;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\Explode as ExplodeValidator;
use Zend\Validator\InArray as InArrayValidator;

class Selection extends Element implements InputProviderInterface {
	/**
	 * Seed attributes
	 *
	 * @var array
	 */
	protected $attributes = array (
			'type' => 'selection' 
	);
	
	/**
	 *
	 * @var \Zend\Validator\ValidatorInterface
	 */
	protected $validator;
	
	/**
	 *
	 * @var bool
	 */
	protected $disableInArrayValidator = false;
	
	/**
	 * Create an empty option (option with label but no value).
	 * If set to null, no option is created
	 *
	 * @var bool
	 */
	protected $emptyOption = null;
	protected $singleValue = null;
	
	/**
	 *
	 * @var array
	 */
	protected $valueOptions = array ();
	
	/**
	 *
	 * @return array
	 */
	public function getValueOptions() {
		return $this->valueOptions;
	}
	
	/**
	 *
	 * @param array $options        	
	 * @return Select
	 */
	public function setValueOptions(array $options) {
		$this->valueOptions = $options;
		
		// Update InArrayValidator validator haystack
		if (null !== $this->validator) {
			if ($this->validator instanceof InArrayValidator) {
				$validator = $this->validator;
			}
			if ($this->validator instanceof ExplodeValidator && $this->validator->getValidator () instanceof InArrayValidator) {
				$validator = $this->validator->getValidator ();
			}
			if (! empty ( $validator )) {
				$validator->setHaystack ( $this->getValueOptionsValues () );
			}
		}
		
		return $this;
	}
	
	/**
	 * Set options for an element.
	 * Accepted options are:
	 * - label: label to associate with the element
	 * - label_attributes: attributes to use when the label is rendered
	 * - value_options: list of values and labels for the select options
	 * _ empty_option: should an empty option be prepended to the options ?
	 *
	 * @param array|Traversable $options        	
	 * @return Select ElementInterface
	 * @throws InvalidArgumentException
	 */
	public function setOptions($options) {
		parent::setOptions ( $options );
		
		if (isset ( $this->options ['value_options'] )) {
			$this->setValueOptions ( $this->options ['value_options'] );
		}
		// Alias for 'value_options'
		if (isset ( $this->options ['options'] )) {
			$this->setValueOptions ( $this->options ['options'] );
		}
		
		if (isset ( $this->options ['empty_option'] )) {
			$this->setEmptyOption ( $this->options ['empty_option'] );
		}
		
		if (isset ( $this->options ['disable_inarray_validator'] )) {
			$this->setDisableInArrayValidator ( $this->options ['disable_inarray_validator'] );
		}
		$total_option = $this->getTotalOption ();
		$this->setAttribute ( 'total_option', $total_option );
		
		return $this;
	}
	
	/**
	 * Set a single element attribute
	 *
	 * @param string $key        	
	 * @param mixed $value        	
	 * @return Select ElementInterface
	 */
	public function setAttribute($key, $value) {
		// Do not include the options in the list of attributes
		// TODO: Deprecate this
		if ($key === 'options') {
			$this->setValueOptions ( $value );
			return $this;
		}
		return parent::setAttribute ( $key, $value );
	}
	
	/**
	 * Set the flag to allow for disabling the automatic addition of an InArray validator.
	 *
	 * @param bool $disableOption        	
	 * @return Select
	 */
	public function setDisableInArrayValidator($disableOption) {
		$this->disableInArrayValidator = ( bool ) $disableOption;
		return $this;
	}
	
	/**
	 * Get the disable in array validator flag.
	 *
	 * @return bool
	 */
	public function disableInArrayValidator() {
		return $this->disableInArrayValidator;
	}
	
	/**
	 * Set the string for an empty option (can be empty string).
	 * If set to null, no option will be added
	 *
	 * @param string|null $emptyOption        	
	 * @return Select
	 */
	public function setEmptyOption($emptyOption) {
		$this->emptyOption = $emptyOption;
		return $this;
	}
	
	/**
	 * Return the string for the empty option (null if none)
	 *
	 * @return string null
	 */
	public function getEmptyOption() {
		return $this->emptyOption;
	}
	
	/**
	 * Return the string for the empty option (null if none)
	 *
	 * @return string null
	 */
	public function getTotalOption() {
		$options = $this->getValueOptions ();
		
		$value = '';
		$value_key = array_keys ( $options );
		if (array_key_exists ( 0, $value_key )) {
			$value = $options [$value_key [0]];
			$this->setSingleValue ( $value_key [0] );
		}
		$this->setSingleText ( $value );
		return count ( $options );
	}
	
	/**
	 * Set the string for an single value (can be empty string).
	 * If set to null, no option will be added
	 *
	 * @param string|null $singleValue        	
	 * @return Select
	 */
	public function setSingleValue($singleValue) {
		$this->singleValue = $singleValue;
		return $this;
	}
	
	/**
	 * Return the string for the single value (null if none)
	 *
	 * @return string null
	 */
	public function getSingleValue() {
		return $this->singleValue;
	}
	
	/**
	 * Set the string for an single text (can be empty string).
	 * If set to null, no option will be added
	 *
	 * @param string|null $singleText        	
	 * @return Select
	 */
	public function setSingleText($singleText) {
		$this->singleText = $singleText;
		return $this;
	}
	
	/**
	 * Return the string for the single text (null if none)
	 *
	 * @return string null
	 */
	public function getSingleText() {
		return $this->singleText;
	}
	
	/**
	 * Get validator
	 *
	 * @return \Zend\Validator\ValidatorInterface
	 */
	protected function getValidator() {
		if (null === $this->validator && ! $this->disableInArrayValidator ()) {
			$validator = new InArrayValidator ( array (
					'haystack' => $this->getValueOptionsValues (),
					'strict' => false 
			) );
			
			$multiple = (isset ( $this->attributes ['multiple'] )) ? $this->attributes ['multiple'] : null;
			
			if (true === $multiple || 'multiple' === $multiple) {
				$validator = new ExplodeValidator ( array (
						'validator' => $validator,
						'valueDelimiter' => null 
				) );
			}
			
			$this->validator = $validator;
		}
		return $this->validator;
	}
	
	/**
	 * Provide default input rules for this element
	 *
	 * Attaches the captcha as a validator.
	 *
	 * @return array
	 */
	public function getInputSpecification() {
		$spec = array (
				'name' => $this->getName (),
				'required' => true 
		);
		$validator = $this->getValidator ();
		if ($validator) {
			$spec ['validators'] = array (
					$validator 
			);
		}
		
		return $spec;
	}
	
	/**
	 * Get only the values from the options attribute
	 *
	 * @return array
	 */
	protected function getValueOptionsValues() {
		$values = array ();
		$options = $this->getValueOptions ();
		foreach ( $options as $key => $optionSpec ) {
			if (is_array ( $optionSpec ) && array_key_exists ( 'options', $optionSpec )) {
				foreach ( $optionSpec ['options'] as $nestedKey => $nestedOptionSpec ) {
					$values [] = $this->getOptionValue ( $nestedKey, $nestedOptionSpec );
				}
				continue;
			}
			
			$values [] = $this->getOptionValue ( $key, $optionSpec );
		}
		return $values;
	}
	protected function getOptionValue($key, $optionSpec) {
		return is_array ( $optionSpec ) ? $optionSpec ['value'] : $key;
	}
}
