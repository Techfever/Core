<?php

namespace Techfever\Form;

use Techfever\Exception;

class Attribute extends Option {
	/**
	 *
	 * @var Options
	 */
	protected $options = array ();
	
	/**
	 *
	 * @var Variables
	 */
	protected $variables = array ();
	
	/**
	 *
	 * @var Values
	 */
	protected $values = array ();
	
	/**
	 *
	 * @var Attribute Data
	 *     
	 */
	private $attribute_data = null;
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		$this->setOptions ( $options );
		parent::__construct ( $options );
		unset ( $this->options ['servicelocator'] );
	}
	
	/**
	 * Get Element ID
	 *
	 * @return array id
	 *        
	 */
	public function getElementID() {
		return $this->getOption ( 'element_id' );
	}
	
	/**
	 * Get Attribute Data
	 *
	 * @return array data
	 *        
	 */
	public function getAttributeData() {
		if (! is_array ( $this->attribute_data ) || count ( $this->attribute_data ) < 1) {
			$element = $this->getElementID ();
			if (is_array ( $element ) && count ( $element ) > 0) {
				$config = array ();
				$QAttribute = $this->getDatabase ();
				$QAttribute->select ();
				$QAttribute->columns ( array (
						'id' => 'form_element_attributes_id',
						'element' => 'form_element_id',
						'key' => 'form_element_attributes_key',
						'value' => 'form_element_attributes_value' 
				) );
				$QAttribute->from ( array (
						'fe' => 'form_element_attributes' 
				) );
				$QAttribute->where ( array (
						'fe.form_element_id in (' . implode ( ', ', $element ) . ')' 
				) );
				$QAttribute->order ( array (
						'fe.form_element_attributes_key ASC' 
				) );
				$QAttribute->setCacheName ( 'form_element_attributes' );
				$QAttribute->execute ();
				if ($QAttribute->hasResult ()) {
					while ( $QAttribute->valid () ) {
						$config [] = $QAttribute->current ();
						$QAttribute->next ();
					}
				}
				$this->attribute_data = $config;
			}
		}
		return $this->attribute_data;
	}
	
	/**
	 * Get Attributes by ID
	 *
	 * @return array
	 *
	 */
	public function getAttributesByID($id) {
		$data = $this->getAttributeData ();
		$attributes = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $value ) {
				if ($id == $value ['element']) {
					if (preg_match ( '/val\{(.*)\}$/', $value ['value'] )) {
						$variable = $value ['value'];
						$variable = str_replace ( 'val{', '', $variable );
						$variable = str_replace ( '}', '', $variable );
						$value ['value'] = $this->getVariable ( $variable );
					}
					$attributes [$value ['key']] = $value ['value'];
				}
			}
		}
		return $attributes;
	}
}
