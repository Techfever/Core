<?php

namespace Techfever\Datatable;

use Techfever\Exception;
use Techfever\Datatable\Element;
use Techfever\Template\Plugin\Filters\ToUnderscore;
use Techfever\Functions\General as GeneralBase;
use Zend\Form\Form as BaseZForm;
use Zend\Form\FormInterface;

class Datatable extends BaseZForm {
	
	/**
	 *
	 * @var Options
	 */
	protected $options = array (
			'request' => null,
			'response' => null,
			'controller' => null,
			'route' => null,
			'action' => null,
			'id' => null,
			'variable' => null,
			'data' => null,
			'rank' => null 
	);
	
	/**
	 * InputForm
	 *
	 * @var InputForm
	 */
	protected $inputForm;
	
	/**
	 * Element object
	 *
	 * @var Element
	 */
	protected $element;
	
	/**
	 * Search Data
	 *
	 * @var Array
	 */
	protected $search_data;
	
	/**
	 * Column Data
	 *
	 * @var Array
	 */
	protected $column_data;
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		if (! isset ( $options ['servicelocator'] )) {
			throw new Exception\RuntimeException ( 'ServiceLocator has not been set or configured.' );
		}
		
		$options = array_merge ( $this->options, $options );
		$this->setOptions ( $options );
		
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $this->options ['servicelocator'] );
		$this->setOptions ( $options );
		$id = $this->getFormID ();
		
		parent::__construct ( $id );
		$ToUnderscore = new ToUnderscore ( '/' );
		$this->setAttribute ( 'id', $ToUnderscore->filter ( $id ) );
		
		$this->elementFactory ();
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
	 * getUserAccess()
	 *
	 * @return UserAccess
	 */
	public function getUserAccess() {
		return $this->getServiceLocator ()->get ( 'useraccess' );
	}
	
	/**
	 * Get Form ID
	 *
	 * @return string
	 *
	 */
	public function getFormID() {
		$id = $this->getOption ( 'id' );
		if (! empty ( $id )) {
			return $id;
		}
		return $this->getRoute () . '/' . $this->getRouteAction ();
	}
	
	/**
	 * Retrieve the data
	 *
	 * By default, retrieves normalized values; pass one of the
	 * FormInterface::VALUES_* constants to shape the behavior.
	 *
	 * @param int $flag        	
	 * @return array object
	 * @throws Exception\DomainException
	 */
	public function getData($flag = FormInterface::VALUES_NORMALIZED) {
		if (($flag !== FormInterface::VALUES_AS_ARRAY) && is_object ( $this->object )) {
			return $this->object;
		}
		return $this->data;
	}
	
	/**
	 * Is this a POST method request?
	 *
	 * @return bool
	 */
	public function isPost() {
		return $this->getRequest ()->isPost ();
	}
	
	/**
	 * Validate the form
	 *
	 * Typically, will proxy to the composed input filter.
	 *
	 * @return bool
	 * @throws Exception\DomainException
	 */
	public function isValid() {
		$getPost = $this->getRequest ()->getPost ();
		$this->setData ( $getPost );
		
		return parent::isValid ();
	}
	
	/**
	 * Return the parameter container responsible for post parameters or a single post parameter.
	 *
	 * @param string|null $name
	 *        	Parameter name to retrieve, or null to get the whole container.
	 * @param mixed|null $default
	 *        	Default value to use when the parameter is missing.
	 * @return \Zend\Stdlib\ParametersInterface mixed
	 */
	public function getPost($name = null, $default = null) {
		return $this->getRequest ()->getPost ( $name, $default );
	}
	
	/**
	 * Is the request a Javascript XMLHttpRequest?
	 *
	 * Should work with Prototype/Script.aculo.us, possibly others.
	 *
	 * @return bool
	 */
	public function isXmlHttpRequest() {
		return $this->getRequest ()->isXmlHttpRequest ();
	}
	private function getElement() {
		if (empty ( $this->element )) {
			$options = $this->getOptions ();
			$options ['servicelocator'] = $this->getServiceLocator ();
			$options ['variable'] = $this->getVariables ();
			$this->element = new Element ( $options );
		}
		return $this->element;
	}
	public function getElementData() {
		return $this->element->getElementData ();
	}
	private function elementFactory() {
		if (! $this->inputForm) {
			$Element = $this->getElement ();
			$elements = $Element->getElementData ();
			if (is_array ( $elements ) && count ( $elements ) > 0) {
				foreach ( $elements as $element_key => $element_value ) {
					if ($Element->validElementByKey ( $element_key )) {
						$element_config = $Element->getFormStuctureByKey ( $element_key );
						$type = strtolower ( $element_config ['attributes'] ['class'] );
						$add_status = True;
						switch ($type) {
							case 'checkbox' :
								$element_config ['options'] ['use_hidden_element'] = False;
								if ($element_config ['options'] ['column_status']) {
									if ($element_config ['options'] ['column_required']) {
										$add_status = False;
									} else {
										if ($element_config ['options'] ['column_default']) {
											$element_config ['attributes'] ['checked'] = 'checked';
										}
									}
								} else {
									$add_status = False;
								}
								break;
						}
						$element_config ['options'] ['disable_help'] = True;
						if ($add_status) {
							$this->add ( $element_config );
						}
					}
				}
			}
		}
		return $this->inputForm;
	}
	public function getColumnData() {
		if (! is_array ( $this->column_data ) && empty ( $this->column_data )) {
			$Element = $this->getElement ();
			$elements = $Element->getElementData ();
			if (is_array ( $elements ) && count ( $elements ) > 0) {
				foreach ( $elements as $element_key => $element_value ) {
					$rawdata = array ();
					if ($Element->validElementByKey ( $element_key ) && $element_value ['column_status'] == "1") {
						switch (strtolower ( $element_value ['class'] )) {
							case "checkbox" :
								$key = substr ( $element_value ['value'], 0, strlen ( $element_value ['value'] ) - 9 );
								$rawdata ['column'] = $key;
								$rawdata ['key'] = $key;
								$rawdata ['field'] = $element_value ['field'];
								$rawdata ['table'] = $element_value ['table'];
								$rawdata ['primary'] = $element_value ['column_required'];
								$rawdata ['default'] = $element_value ['column_default'];
								$this->column_data [$element_value ['sort_order']] = $rawdata;
								break;
						}
					}
				}
			}
		}
		ksort ( $this->column_data );
		return $this->column_data;
	}
	
	/**
	 * Get
	 */
	public function getColumnFieldByColumn($key) {
		$data = $this->getColumnData ();
		// $this->getLog ()->info ( 'column-field:' . $key );
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $key )) {
				foreach ( $data as $value ) {
					if ($value ['column'] == $key) {
						// $this->getLog ()->info ( 'column-field-result:' . $value ['field'] );
						// $this->getLog ()->info ( '' );
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
	public function getColumnTableByColumn($key) {
		$data = $this->getColumnData ();
		// $this->getLog ()->info ( 'column-table:' . $key );
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $key )) {
				foreach ( $data as $value ) {
					if ($value ['column'] == $key) {
						// $this->getLog ()->info ( 'column-table-result:' . $value ['table'] );
						// $this->getLog ()->info ( '' );
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
	public function getSearchData() {
		if (! is_array ( $this->search_data ) && empty ( $this->search_data )) {
			$Element = $this->getElement ();
			$elements = $Element->getElementData ();
			if (is_array ( $elements ) && count ( $elements ) > 0) {
				foreach ( $elements as $element_key => $element_value ) {
					if ($Element->validElementByKey ( $element_key ) && $element_value ['column_status'] == "0") {
						switch (strtolower ( $element_value ['class'] )) {
							case "button" :
							case "group" :
							case "checkboxgroup" :
							case "tabgroup" :
							case "tab" :
							case "stepgroup" :
							case "step" :
							case "reportfiltergroup" :
							case "reportfilter" :
							case "accordiongroup" :
							case "accordion" :
								break;
							default :
								$key = $element_value ['value'];
								$rawdata ['flag'] = "1";
								$rawdata ['column'] = $key;
								$rawdata ['field'] = $element_value ['field'];
								$rawdata ['table'] = $element_value ['table'];
								$rawdata ['pattern'] = $element_value ['pattern'];
								$this->search_data [$element_value ['sort_order']] = $rawdata;
								break;
						}
					}
				}
			}
		}
		ksort ( $this->search_data );
		return $this->search_data;
	}
	
	/**
	 * Get
	 */
	public function getSearchFieldByColumn($key) {
		$data = $this->getSearchData ();
		// $this->getLog ()->info ( 'search-field:' . $key );
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $key )) {
				foreach ( $data as $value ) {
					if ($value ['column'] == $key) {
						// $this->getLog ()->info ( 'search-field-result:' . $value ['field'] );
						// $this->getLog ()->info ( '' );
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
		// $this->getLog ()->info ( 'search-table:' . $key );
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $key )) {
				foreach ( $data as $value ) {
					if ($value ['column'] == $key) {
						// $this->getLog ()->info ( 'search-table-result:' . $value ['table'] );
						// $this->getLog ()->info ( '' );
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
		// $this->getLog ()->info ( 'search-pattern:' . $key );
		if (is_array ( $data ) && count ( $data ) > 0) {
			if (! empty ( $key )) {
				foreach ( $data as $value ) {
					if ($value ['column'] == $key) {
						// $this->getLog ()->info ( 'search-pattern-result:' . $value ['pattern'] );
						// $this->getLog ()->info ( '' );
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
	public function getVariables() {
		$data = $this->getOption ( 'variable' );
		if (is_array ( $data ) && count ( $data ) > 0) {
			return $data;
		}
		return array ();
	}
}
