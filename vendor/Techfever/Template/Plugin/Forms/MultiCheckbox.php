<?php

namespace Techfever\Template\Plugin\Forms;

use Zend\Form\Exception\InvalidArgumentException;
use Zend\Validator\Explode as ExplodeValidator;
use Zend\Validator\InArray as InArrayValidator;
use Zend\Validator\ValidatorInterface;
use Zend\Form\Element\Checkbox;

class MultiCheckbox extends Checkbox {
	/**
	 * Seed attributes
	 *
	 * @var array
	 */
	protected $attributes = array (
			'type' => 'multi_checkbox' 
	);
	
	/**
	 *
	 * @var bool
	 */
	protected $useHiddenElement = false;
	
	/**
	 *
	 * @var string
	 */
	protected $uncheckedValue = '';
	
	/**
	 *
	 * @var array
	 */
	protected $valueOptions = array ();
	
	/**
	 *
	 * @var bool
	 */
	protected $disableInArrayValidator = false;
	
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
	 * @return MultiCheckbox
	 */
	public function setValueOptions(array $options) {
		$this->valueOptions = $options;
		
		// Update InArrayValidator validator haystack
		if (null !== $this->validator) {
			// Update Explode validator haystack
			if ($this->validator instanceof ExplodeValidator) {
				$validator = $this->validator->getValidator ();
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
	 *
	 * @param array|\Traversable $options        	
	 * @return MultiCheckbox ElementInterface
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
		
		if (isset ( $this->options ['disable_inarray_validator'] )) {
			$this->setDisableInArrayValidator ( $this->options ['disable_inarray_validator'] );
		}
		
		return $this;
	}
	
	/**
	 * Set a single element attribute
	 *
	 * @param string $key        	
	 * @param mixed $value        	
	 * @return MultiCheckbox ElementInterface
	 */
	public function setAttribute($key, $value) {
		if ($key === 'options') {
			$this->setValueOptions ( $value );
			return $this;
		}
		return parent::setAttribute ( $key, $value );
	}
	
	/**
	 * Get validator
	 *
	 * @return ValidatorInterface
	 */
	protected function getValidator() {
		if (null === $this->validator && ! $this->disableInArrayValidator ()) {
			$inArrayValidator = new InArrayValidator ( array (
					'haystack' => $this->getValueOptionsValues (),
					'strict' => false 
			) );
			$this->validator = new ExplodeValidator ( array (
					'validator' => $inArrayValidator,
					'valueDelimiter' => null 
			) );
		}
		return $this->validator;
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
	 * Get only the values from the options attribute
	 *
	 * @return array
	 */
	protected function getValueOptionsValues() {
		$values = array ();
		$options = $this->getValueOptions ();
		foreach ( $options as $key => $optionSpec ) {
			$value = (is_array ( $optionSpec )) ? $optionSpec ['value'] : $key;
			$values [] = $value;
		}
		if ($this->useHiddenElement ()) {
			$values [] = $this->getUncheckedValue ();
		}
		return $values;
	}
	
	/**
	 * Sets the value that should be selected.
	 *
	 * @param mixed $value
	 *        	The value to set.
	 * @return MultiCheckbox
	 */
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}
}
