<?php

namespace Techfever\Template\Plugin\Forms;

use Zend\Form\Element;

class Permission extends Element {
	/**
	 * Seed attributes
	 *
	 * @var array
	 */
	protected $attributes = array (
			'type' => 'permission' 
	);
	
	/**
	 *
	 * @var array
	 */
	protected $valueOptions = array ();
	
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
			$this->setOptions ( $this->options ['options'] );
		}
		
		return $this;
	}
	
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
		return $this;
	}
}
