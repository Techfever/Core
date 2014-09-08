<?php

namespace Techfever\Form;

use Zend\InputFilter\InputFilter as BaseInputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Techfever\Functions\General as GeneralBase;
use Techfever\Exception;
use Techfever\Form\Element;

class InputFilter implements InputFilterAwareInterface {
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	
	/**
	 *
	 * @var Options
	 */
	protected $options = array ();
	
	/**
	 * Element object
	 *
	 * @var Element
	 */
	protected $element;
	
	/**
	 *
	 * @var Data
	 */
	public $data;
	protected $inputFilter;
	public function __construct($options = null, $element) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		if (! isset ( $options ['servicelocator'] )) {
			throw new Exception\RuntimeException ( 'ServiceLocator has not been set or configured.' );
		}
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		
		$options = array_merge ( $this->options, $options );
		
		$this->setOptions ( $options );
		
		$this->element = $element;
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
	private function getElement() {
		if (empty ( $this->element )) {
			$this->element = new Element ( $this->options );
		}
		return $this->element;
	}
	
	// Add content to these methods:
	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new Exception\InvalidArgumentException ( 'Not used' );
	}
	public function getInputFilter() {
		if (! $this->inputFilter) {
			$inputFilter = new BaseInputFilter ();
			$Element = $this->getElement ();
			$elements = $Element->getElementData ();
			if (is_array ( $elements ) && count ( $elements ) > 0) {
				foreach ( $elements as $element_key => $element_value ) {
					if ($Element->validElementByKey ( $element_key )) {
						$inputFilter->add ( $Element->getFilterStuctureByKey ( $element_key ) );
					}
				}
			}
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
}
