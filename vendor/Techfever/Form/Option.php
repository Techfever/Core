<?php

namespace Techfever\Form;

use Techfever\Exception;

class Option extends Filter {
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
	 * @var Option Data
	 *     
	 */
	private $option_data = null;
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
	 * Get Option Data
	 *
	 * @return array data
	 *        
	 */
	public function getOptionsData() {
		if (! is_array ( $this->option_data ) || count ( $this->option_data ) < 1) {
			$element = $this->getElementID ();
			if (is_array ( $element ) && count ( $element ) > 0) {
				$config = array ();
				$QOption = $this->getDatabase ();
				$QOption->select ();
				$QOption->columns ( array (
						'id' => 'form_element_options_id',
						'element' => 'form_element_id',
						'key' => 'form_element_options_key',
						'value' => 'form_element_options_value' 
				) );
				$QOption->from ( array (
						'fe' => 'form_element_options' 
				) );
				$QOption->where ( array (
						'fe.form_element_id in (' . implode ( ', ', $element ) . ')' 
				) );
				$QOption->order ( array (
						'fe.form_element_options_key ASC' 
				) );
				$QOption->setCacheName ( 'form_element_options' );
				$QOption->execute ();
				if ($QOption->hasResult ()) {
					while ( $QOption->valid () ) {
						$config [] = $QOption->current ();
						$QOption->next ();
					}
				}
				$this->option_data = $config;
			}
		}
		return $this->option_data;
	}
	
	/**
	 * Get Options by ID
	 *
	 * @return array
	 *
	 */
	public function getOptionsByID($id) {
		$data = $this->getOptionsData ();
		$options = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $value ) {
				if ($id == $value ['element']) {
					if (preg_match ( '/val\{(.*)\}$/', $value ['value'] )) {
						$variable = $value ['value'];
						$variable = str_replace ( 'val{', '', $variable );
						$variable = str_replace ( '}', '', $variable );
						$value ['value'] = $this->getVariable ( $variable );
					}
					$options [$value ['key']] = $value ['value'];
				}
			}
		}
		return $options;
	}
}
