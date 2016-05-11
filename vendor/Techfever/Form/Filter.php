<?php

namespace Techfever\Form;

use Techfever\Exception;

class Filter extends Validator {
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
	 * @var Filter Data
	 *     
	 */
	private $filter_data = null;
	
	/**
	 *
	 * @var Filter Data
	 *     
	 */
	private $filter_option_data = null;
	
	/**
	 *
	 * @var Filter Data
	 *     
	 */
	private $filter_type_data = null;
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
	 * Get Filter Data
	 *
	 * @return array data
	 *        
	 */
	public function getFiltersData() {
		if (! is_array ( $this->filter_data ) || count ( $this->filter_data ) < 1) {
			$element = $this->getElementID ();
			if (is_array ( $element ) && count ( $element ) > 0) {
				$config = array ();
				$QFilter = $this->getDatabase ();
				$QFilter->select ();
				$QFilter->columns ( array (
						'id' => 'form_element_filters_id',
						'element' => 'form_element_id',
						'key' => 'form_element_filters_key' 
				) );
				$QFilter->from ( array (
						'fe' => 'form_element_filters' 
				) );
				$QFilter->where ( array (
						'fe.form_element_id in (' . implode ( ', ', $element ) . ')' 
				) );
				$QFilter->order ( array (
						'fe.form_element_filters_key ASC' 
				) );
				$QFilter->execute ();
				if ($QFilter->hasResult ()) {
					while ( $QFilter->valid () ) {
						$config [] = $QFilter->current ();
						$QFilter->next ();
					}
				}
				$this->filter_data = $config;
			}
		}
		return $this->filter_data;
	}
	
	/**
	 * Get Filter Option Data
	 *
	 * @return array data
	 *        
	 */
	public function getFiltersOptionsData() {
		if (! is_array ( $this->filter_option_data ) || count ( $this->filter_option_data ) < 1) {
			$filter = $this->getFiltersData ();
			if (is_array ( $filter ) && count ( $filter ) > 0) {
				$filter_id = array ();
				foreach ( $filter as $filter_value ) {
					$filter_id [] = $filter_value ['id'];
				}
				$config = array ();
				$QFilter = $this->getDatabase ();
				$QFilter->select ();
				$QFilter->columns ( array (
						'id' => 'form_element_filters_options_id',
						'filter' => 'form_element_filters_id',
						'key' => 'form_element_filters_options_key',
						'value' => 'form_element_filters_options_value' 
				) );
				$QFilter->from ( array (
						'fe' => 'form_element_filters_options' 
				) );
				$QFilter->where ( array (
						'fe.form_element_filters_id in (' . implode ( ', ', $filter_id ) . ')' 
				) );
				$QFilter->order ( array (
						'fe.form_element_filters_options_key ASC' 
				) );
				$QFilter->execute ();
				if ($QFilter->hasResult ()) {
					while ( $QFilter->valid () ) {
						$rawdata = $QFilter->current ();
						if (preg_match ( '/val\{(.*)\}$/', $rawdata ['value'] )) {
							$variable = $rawdata ['value'];
							$variable = str_replace ( 'val{', '', $variable );
							$variable = str_replace ( '}', '', $variable );
							$rawdata ['value'] = $this->getVariable ( $variable );
						}
						$config [$rawdata ['filter']] [$rawdata ['key']] = $rawdata ['value'];
						$QFilter->next ();
					}
				}
				$this->filter_option_data = $config;
			}
		}
		return $this->filter_option_data;
	}
	
	/**
	 * Get Filter Type Data
	 *
	 * @return array data
	 *        
	 */
	public function getFiltersTypeData() {
		if (! is_array ( $this->filter_type_data ) || count ( $this->filter_type_data ) < 1) {
			$element = $this->getElementID ();
			if (is_array ( $element ) && count ( $element ) > 0) {
				$config = array ();
				$QFilter = $this->getDatabase ();
				$QFilter->select ();
				$QFilter->columns ( array (
						'id' => 'form_element_filters_type_id',
						'element' => 'form_element_id',
						'key' => 'form_element_filters_type_key' 
				) );
				$QFilter->from ( array (
						'fe' => 'form_element_filters_type' 
				) );
				$QFilter->where ( array (
						'fe.form_element_id in (' . implode ( ', ', $element ) . ')' 
				) );
				$QFilter->order ( array (
						'fe.form_element_filters_type_key ASC' 
				) );
				$QFilter->execute ();
				if ($QFilter->hasResult ()) {
					while ( $QFilter->valid () ) {
						$rawdata = $QFilter->current ();
						$config [$rawdata ['element']] = $rawdata ['key'];
						$QFilter->next ();
					}
				}
				$this->filter_type_data = $config;
			}
		}
		return $this->filter_type_data;
	}
	
	/**
	 * Get Filter Option
	 *
	 * @return array data
	 *        
	 */
	public function getFiltersOptions($id = null) {
		$option_data = $this->getFiltersOptionsData ();
		if (! empty ( $id ) && array_key_exists ( $id, $option_data )) {
			return $option_data [$id];
		}
		return array ();
	}
	
	/**
	 * Get Filter Option
	 *
	 * @return array data
	 *        
	 */
	public function getFiltersType($id = null) {
		$option_data = $this->getFiltersTypeData ();
		if (! empty ( $id ) && array_key_exists ( $id, $option_data )) {
			return $option_data [$id];
		}
		return null;
	}
	
	/**
	 * Get Filters by ID
	 *
	 * @return array
	 *
	 */
	public function getFiltersByID($id) {
		$data = $this->getFiltersData ();
		$filters = array ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $value ) {
				if ($id == $value ['element']) {
					$options = $this->getFiltersOptions ( $value ['id'] );
					$filters [] = array (
							'name' => $value ['key'],
							'options' => $options 
					);
				}
			}
		}
		return $filters;
	}
	
	/**
	 * Get Filters Type by Key
	 *
	 * @return array
	 *
	 */
	public function getFiltersTypeByID($id) {
		$data = $this->getFiltersData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $value ) {
				if ($id == $value ['element']) {
					return $this->getFiltersType ( $value ['element'] );
				}
			}
		}
		return null;
	}
}
