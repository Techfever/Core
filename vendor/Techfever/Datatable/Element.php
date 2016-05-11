<?php

namespace Techfever\Datatable;

use Techfever\Exception;
use Techfever\Parameter\Parameter;
use Techfever\Functions\General as GeneralBase;

class Element extends GeneralBase {
	/**
	 *
	 * @var Options
	 */
	protected $options = array ();
	
	/**
	 * Variable
	 *
	 * @var Variable
	 */
	protected $variable;
	
	/**
	 *
	 * @var Element Data
	 *     
	 */
	private $element_data = null;
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->variable = $options ['variable'];
		$this->setServiceLocator ( $options ['servicelocator'] );
		$this->setOptions ( $options );
		$options ['element_id'] = $this->getElementID ();
		parent::__construct ( $options );
		unset ( $this->options ['servicelocator'] );
	}
	
	/**
	 * Get Element Data
	 *
	 * @return array data
	 *        
	 */
	public function getElementData() {
		if (! is_array ( $this->element_data ) || count ( $this->element_data ) < 1) {
			$this->element_data = array ();
			$QElement = $this->getDatabase ();
			$QElement->select ();
			$QElement->columns ( array (
					'mid' => 'module_controllers_id' 
			) );
			$QElement->from ( array (
					'mc' => 'module_controllers' 
			) );
			$QElement->join ( array (
					'fc' => 'datatable_element_controller' 
			), 'fc.module_controllers_id = mc.module_controllers_id', array (
					'fid' => 'datatable_element_controller_id' 
			) );
			$QElement->join ( array (
					'fec' => 'datatable_element_to_controller' 
			), 'fec.datatable_element_controller_id = fc.datatable_element_controller_id', array (
					'id' => 'datatable_element_id',
					'link_id' => 'datatable_element_to_controller_id',
					'parent' => 'datatable_element_to_controller_parent',
					'islink' => 'datatable_element_to_controller_is_link',
					'link' => 'datatable_element_to_controller_link',
					'sort_order' => 'datatable_element_to_controller_sort_order',
					'column_status' => 'datatable_element_to_controller_column_status',
					'column_required' => 'datatable_element_to_controller_column_required',
					'column_default' => 'datatable_element_to_controller_column_default' 
			) );
			$QElement->join ( array (
					'fe' => 'datatable_element' 
			), 'fe.datatable_element_id = fec.datatable_element_id', array (
					'type' => 'datatable_element_type',
					'check_locale' => 'datatable_element_check_locale',
					'locale' => 'datatable_element_locale',
					'table' => 'datatable_element_table',
					'field' => 'datatable_element_field',
					'value' => 'datatable_element_value',
					'pattern' => 'datatable_element_pattern',
					'accordion_inactive' => 'datatable_element_accordion_inactive',
					'accordion_total_column' => 'datatable_element_accordion_total_column' 
			) );
			$QElement->where ( array (
					'fc.module_controllers_action = "' . $this->getRouteAction () . '"',
					'mc.module_controllers_alias = "' . str_replace ( '\\', '\\\\', $this->getController () ) . '"',
					'fec.datatable_element_to_controller_status = 1' 
			) );
			$QElement->order ( array (
					'fec.datatable_element_to_controller_parent ASC',
					'fec.datatable_element_to_controller_sort_order ASC' 
			) );
			$QElement->execute ();
			if ($QElement->hasResult ()) {
				while ( $QElement->valid () ) {
					$rawdata = $QElement->current ();
					$rawdata ['key'] = strtolower ( $rawdata ['value'] );
					$type = explode ( '\\', $rawdata ['type'] );
					$type = array_slice ( $type, - 1 );
					$rawdata ['class'] = $type [0];
					
					$check_locale = (strtolower ( $rawdata ['check_locale'] ) == "true" ? True : False);
					$add_status = true;
					if ($check_locale) {
						$get_locale = $rawdata ['locale'];
						$add_status = false;
						if (! empty ( $get_locale )) {
							if ($this->getTranslator ()->checkLocale ( $get_locale )) {
								$add_status = true;
							}
						}
					}
					if ($add_status) {
						$this->element_data [$rawdata ['key']] = $rawdata;
					}
					$QElement->next ();
				}
			}
		}
		return $this->element_data;
	}
	
	/**
	 * Get Element ID
	 *
	 * @return array id
	 *        
	 */
	public function getElementID() {
		$data = $this->getElementData ();
		$id = null;
		if (is_array ( $data ) && count ( $data ) > 0) {
			$id = array ();
			foreach ( $data as $value ) {
				$id [] = $value ['id'];
			}
		}
		return $id;
	}
	
	/**
	 * Valid Element by Key
	 *
	 * @return boolean
	 *
	 */
	public function validElementByKey($element) {
		$data = $this->getElementData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $key => $value ) {
				if (strtolower ( $element ) == $key) {
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Get Stucture By Key
	 *
	 * @return array
	 *
	 */
	public function getStuctureByKey($element) {
		$data = $this->getElementData ();
		
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (array_key_exists ( $element, $data )) {
				$key = $element;
				$value = $data [$key];
				$elementOrFieldset = array (
						'name' => strtolower ( $key ),
						'type' => $value ['type'],
						'options' => array (
								'label' => 'text_' . strtolower ( $key ),
								'node' => $value ['link_id'],
								'parent' => $value ['parent'],
								'column_status' => (strtolower ( $value ['column_status'] ) == "1" ? True : False),
								'column_required' => (strtolower ( $value ['column_required'] ) == "1" ? True : False),
								'column_default' => (strtolower ( $value ['column_default'] ) == "1" ? True : False) 
						),
						'attributes' => array (
								'value' => null,
								'accordion_inactive' => (strtolower ( $value ['accordion_inactive'] ) == "true" ? True : False),
								'accordion_total_column' => $value ['accordion_total_column'],
								'islink' => (strtolower ( $value ['islink'] ) == "true" ? True : False),
								'link' => (strtolower ( $value ['islink'] ) == "true" ? $value ['link'] : null),
								'class' => strtolower ( $value ['class'] ),
								'id' => strtolower ( $key ),
								'show_preview_tab' => False,
								'show_finish_button' => False 
						) 
				);
				$ElementParameter = null;
				$type = strtolower ( $value ['class'] );
				switch ($type) {
					case 'selectdate' :
						$elementOrFieldset ['options'] ['create_empty_option'] = True;
						break;
					case 'selection' :
					case 'select' :
					case 'radio' :
						$ElementParameter = new Parameter ( array (
								'key' => strtolower ( $value ['field'] ),
								'servicelocator' => $this->getServiceLocator () 
						) );
						$valueParameter = $ElementParameter->toForm ();
						if (in_array ( $type, array (
								'selection',
								'select' 
						) )) {
							if (is_array ( $valueParameter )) {
								$valueParameter = array_merge ( array (
										'' => '' 
								), $valueParameter );
							} else {
								$variables = $this->getVariable ( strtolower ( $value ['field'] ) );
								if (is_array ( $variables ) && count ( $variables ) > 0) {
									$valueParameter = array_merge ( array (
											'' => '' 
									), $variables );
								} else {
									$valueParameter = array (
											'' => '' 
									);
								}
							}
						}
						$elementOrFieldset ['options'] ['value_options'] = $valueParameter;
						break;
				}
				return $elementOrFieldset;
			}
		}
		return array ();
	}
	
	/**
	 * Get Form Stucture By Key
	 *
	 * @return array
	 *
	 */
	public function getFormStuctureByKey($element, $default = null) {
		$structure = $this->getStuctureByKey ( $element, $default );
		return $structure;
	}
	
	/**
	 * Get
	 */
	public function getVariable($key = null) {
		$data = $this->variable;
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (array_key_exists ( $key, $data )) {
				return $data [$key];
			}
		}
		return false;
	}
}
