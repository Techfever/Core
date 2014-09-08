<?php

namespace Techfever\Datatable;

use Techfever\Exception;
use Zend\Form\Element;
use Zend\Form\Form;
use Techfever\Functions\General as GeneralBase;

class Search extends GeneralBase {
	
	/**
	 *
	 * @var Options
	 */
	protected $options = array (
			'request' => null,
			'controller' => null,
			'route' => null,
			'action' => null,
			'variable' => null 
	);
	
	/**
	 *
	 * @var Data
	 *
	 */
	private $_search_data = array ();
	
	/**
	 *
	 * @var Form
	 *
	 */
	private $_form = null;
	
	/**
	 * Constructor
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		parent::__construct ( $options );
		unset ( $options ['servicelocator'] );
		$this->setOptions ( $options );
	}
	
	/**
	 * Get
	 */
	public function getSearchData() {
		if (! is_array ( $this->_search_data ) || count ( $this->_search_data ) < 1) {
			$cachename = strtolower ( $this->convertToUnderscore ( ($this->getController () . '\\' . $this->getRouteAction ()), '\\' ) );
			
			$this->_form = new Form ();
			
			$DBSearch = $this->getDatabase ();
			$DBSearch->select ();
			$DBSearch->columns ( array (
					'vid' => 'datatable_search_to_datatable_id',
					'flag' => 'datatable_search_to_datatable_flag' 
			) );
			$DBSearch->from ( array (
					'dfv' => 'datatable_search_to_datatable' 
			) );
			$DBSearch->join ( array (
					'df' => 'datatable_search' 
			), 'df.datatable_search_id  = dfv.datatable_search_id', array (
					'id' => 'datatable_search_id',
					'key' => 'datatable_search_key',
					'table' => 'datatable_search_table',
					'field' => 'datatable_search_field',
					'column' => 'datatable_search_column',
					'pattern' => 'datatable_search_pattern',
					'form' => 'datatable_search_form' 
			) );
			$DBSearch->where ( array (
					'dfv.datatable_search_to_datatable_status = 1',
					'dfv.datatable_id = ' . $this->getOption ( 'datatable' ),
					'df.datatable_search_pattern != ""',
					'df.datatable_search_form != ""' 
			) );
			$DBSearch->order ( array (
					'dfv.datatable_search_to_datatable_sort_order ASC, df.datatable_search_key ASC' 
			) );
			$DBSearch->setCacheName ( 'datatable_search_' . $cachename );
			$DBSearch->execute ();
			if ($DBSearch->hasResult ()) {
				$data = array ();
				while ( $DBSearch->valid () ) {
					$rawdata = $DBSearch->current ();
					$options = array (
							'label' => 'text_' . $rawdata ['key'] 
					);
					if ($rawdata ['form'] == 'text') {
						$options ['label_attributes'] = array (
								'id' => 'search_' . $rawdata ['column'] 
						);
						
						$element = new Element\Text ( 'search_' . $rawdata ['column'], $options );
						$element->setAttributes ( array (
								'class' => 'text',
								'id' => 'search_' . $rawdata ['column'] 
						) );
						$rawdata ['element'] = $element;
					} elseif ($rawdata ['form'] == 'select') {
						$options ['label_attributes'] = array (
								'id' => 'search_' . $rawdata ['column'] 
						);
						
						$options ['empty_option'] = '';
						$options ['value_options'] = $this->getVariable ( $rawdata ['column'] );
						
						$element = new Element\Select ( 'search_' . $rawdata ['column'], $options );
						$element->setAttributes ( array (
								'class' => 'select',
								'id' => 'search_' . $rawdata ['column'] 
						) );
						$rawdata ['element'] = $element;
					} elseif ($rawdata ['form'] == 'date') {
						$options ['label_attributes'] = array (
								'id' => 'search_' . $rawdata ['column'],
								'name' => 'search_' . $rawdata ['column'] 
						);
						$options ['day_attributes'] = array (
								'class' => 'selectdate' 
						);
						$options ['month_attributes'] = array (
								'class' => 'selectmonth' 
						);
						$options ['year_attributes'] = array (
								'class' => 'selectyear' 
						);
						
						$options ['empty_option'] = '';
						$element = new \Techfever\Template\Plugin\Forms\SelectDate ( 'search_' . $rawdata ['column'], $options );
						$element->setAttributes ( array (
								'class' => 'date',
								'id' => 'search_' . $rawdata ['column'] 
						) );
						$rawdata ['element'] = $element;
					}
					$data [$rawdata ['id']] = $rawdata;
					$DBSearch->next ();
				}
				
				$this->_search_data = $data;
			}
		}
		return $this->_search_data;
	}
	
	/**
	 * Get
	 */
	public function getSearchFieldByColumn($key) {
		$data = $this->getSearchData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $key )) {
				foreach ( $data as $value ) {
					if ($value ['column'] == $key) {
						return $value ['field'];
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * Get
	 */
	public function getSearchTableByColumn($key) {
		$data = $this->getSearchData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $key )) {
				foreach ( $data as $value ) {
					if ($value ['column'] == $key) {
						return $value ['table'];
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * Get
	 */
	public function getSearchPatternByColumn($key) {
		$data = $this->getSearchData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $key )) {
				foreach ( $data as $value ) {
					if ($value ['column'] == $key) {
						return $value ['pattern'];
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * Get
	 */
	public function getSearch($id = null) {
		$data = $this->getSearchData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $id )) {
				return (array_key_exists ( $id, $data ) ? $data [$id] : null);
			}
		}
		return false;
	}
}
