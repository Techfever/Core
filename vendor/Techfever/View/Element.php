<?php

namespace Techfever\View;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\InitializableInterface;
use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Element implements ElementAttributeRemovalInterface, ElementInterface, InitializableInterface {
	/**
	 *
	 * @var array
	 */
	protected $attributes = array ();
	
	/**
	 *
	 * @var string
	 */
	protected $label;
	
	/**
	 *
	 * @var string
	 */
	protected $title;
	
	/**
	 *
	 * @var integer
	 */
	protected $node;
	
	/**
	 *
	 * @var integer
	 */
	protected $parent;
	
	/**
	 *
	 * @var boolean
	 */
	protected $ispassword;
	
	/**
	 *
	 * @var array
	 */
	protected $labelAttributes;
	
	/**
	 *
	 * @var array custom options
	 */
	private $options = array (
			'servicelocator' => null 
	);
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	
	/**
	 *
	 * @var mixed
	 */
	protected $content;
	
	/**
	 *
	 * @var mixed
	 */
	protected $tab;
	
	/**
	 *
	 * @param null|int|string $name
	 *        	Optional name for the element
	 * @param array $options
	 *        	Optional options for the element
	 * @throws Exception\InvalidArgumentException
	 */
	public function __construct($name = null, $options = array()) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		if (isset ( $options ['servicelocator'] )) {
			$this->generalobject = new GeneralBase ( $options );
			$this->setServiceLocator ( $options ['servicelocator'] );
			unset ( $options ['servicelocator'] );
		}
		$this->setOptions ( $options );
		
		if (null !== $name) {
			$this->setName ( $name );
		}
		
		if (! empty ( $options )) {
			$this->setOptions ( $options );
		}
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
	 * This function is automatically called when creating element with factory.
	 * It
	 * allows to perform various operations (add elements...)
	 *
	 * @return void
	 */
	public function init() {
	}
	
	/**
	 * Set value for name
	 *
	 * @param string $name        	
	 * @return Element ElementInterface
	 */
	public function setName($name) {
		$this->setAttribute ( 'name', $name );
		return $this;
	}
	
	/**
	 * Get value for name
	 *
	 * @return string int
	 */
	public function getName() {
		return $this->getAttribute ( 'name' );
	}
	
	/**
	 * Set options for an element.
	 * Accepted options are:
	 *
	 * @param array|Traversable $options        	
	 * @return Element ElementInterface
	 * @throws Exception\InvalidArgumentException
	 */
	public function setOptions($options) {
		if ($options instanceof Traversable) {
			$options = ArrayUtils::iteratorToArray ( $options );
		} elseif (! is_array ( $options )) {
			throw new Exception\InvalidArgumentException ( 'The options parameter must be an array or a Traversable' );
		}
		if (isset ( $options ['attributes'] )) {
			$this->setAttributes ( $options ['attributes'] );
		}
		
		if (isset ( $options ['label'] )) {
			$this->setLabel ( $options ['label'] );
		}
		
		if (isset ( $options ['title'] )) {
			$this->setTitle ( $options ['label'] );
		}
		
		if (isset ( $options ['node'] )) {
			if (! array_key_exists ( 'node', $options )) {
				$options ['node'] = 0;
			} else {
				if (is_int ( $options ['node'] )) {
					$options ['node'] = ( int ) $options ['node'];
				}
			}
			$this->setNode ( $options ['node'] );
		}
		
		if (isset ( $options ['parent'] )) {
			if (! array_key_exists ( 'parent', $options )) {
				$options ['parent'] = 0;
			} else {
				if (is_int ( $options ['parent'] )) {
					$options ['parent'] = ( int ) $options ['parent'];
				}
			}
			$this->setParent ( $options ['parent'] );
		}
		
		if (isset ( $options ['ispassword'] )) {
			if (! array_key_exists ( 'ispassword', $options )) {
				$options ['ispassword'] = "False";
			} else {
				if (is_bool ( $options ['ispassword'] )) {
					if ($options ['ispassword']) {
						$options ['ispassword'] = True;
					} else {
						$options ['ispassword'] = False;
					}
				}
			}
			$options ['ispassword'] = ($options ['ispassword'] == "True" ? True : False);
			$this->setisPassword ( $options ['ispassword'] );
		}
		
		if (isset ( $options ['label_attributes'] )) {
			$this->setLabelAttributes ( $options ['label_attributes'] );
		}
		$this->options = $options;
		
		return $this;
	}
	
	/**
	 * Get defined options
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}
	
	/**
	 * Return the specified option
	 *
	 * @param string $option        	
	 * @return NULL mixed
	 */
	public function getOption($option) {
		if (! isset ( $this->options [$option] )) {
			return null;
		}
		
		return $this->options [$option];
	}
	
	/**
	 * Retrieve the type used for this element
	 *
	 * @return string
	 */
	public function getType() {
		if (! isset ( $this->options ['type'] )) {
			return null;
		}
		return $this->options ['type'];
	}
	
	/**
	 * Set the element content
	 *
	 * @param mixed $content        	
	 * @return Element
	 */
	public function setContent($value) {
		$this->content = $value;
		return $this;
	}
	
	/**
	 * Retrieve the element content
	 *
	 * @return mixed
	 */
	public function getContent() {
		return $this->content;
	}
	
	/**
	 * Set the element tab
	 *
	 * @param mixed $tab        	
	 * @return Element
	 */
	public function setTab($value) {
		$this->tab = $value;
		return $this;
	}
	
	/**
	 * Retrieve the element tab
	 *
	 * @return mixed
	 */
	public function getTab() {
		return $this->tab;
	}
	
	/**
	 * Set the label used for this element
	 *
	 * @param
	 *        	$label
	 * @return Element ElementInterface
	 */
	public function setLabel($label) {
		if (is_string ( $label )) {
			$this->label = $label;
		}
		
		return $this;
	}
	
	/**
	 * Retrieve the label used for this element
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * Set the attributes to use with the label
	 *
	 * @param array $labelAttributes        	
	 * @return Element ElementInterface
	 */
	public function setLabelAttributes(array $labelAttributes) {
		$this->labelAttributes = $labelAttributes;
		return $this;
	}
	
	/**
	 * Get the attributes to use with the label
	 *
	 * @return array
	 */
	public function getLabelAttributes() {
		return $this->labelAttributes;
	}
	
	/**
	 * Set the Title used for this element
	 *
	 * @param
	 *        	$title
	 * @return Element ElementInterface
	 */
	public function setTitle($title) {
		if (is_string ( $title )) {
			$this->title = $title;
		}
		
		return $this;
	}
	
	/**
	 * Retrieve the title used for this element
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Set the Node used for this element
	 *
	 * @param
	 *        	$node
	 * @return Element ElementInterface
	 */
	public function setNode($node) {
		if (is_int ( $node )) {
			$this->node = $node;
		}
		
		return $this;
	}
	
	/**
	 * Retrieve the node used for this element
	 *
	 * @return interger
	 */
	public function getNode() {
		return $this->node;
	}
	
	/**
	 * Set the Parent used for this element
	 *
	 * @param
	 *        	$parent
	 * @return Element ElementInterface
	 */
	public function setParent($parent) {
		if (is_int ( $parent )) {
			$this->parent = $parent;
		}
		
		return $this;
	}
	
	/**
	 * Retrieve the parent used for this element
	 *
	 * @return interger
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * Set the is Password used for this element
	 *
	 * @param
	 *        	$ispassword
	 * @return Element ElementInterface
	 */
	public function setisPassword($ispassword) {
		if (is_bool ( $ispassword )) {
			$this->ispassword = $ispassword;
		}
		
		return $this;
	}
	
	/**
	 * Return the is password
	 *
	 * @return boolean
	 */
	public function isPassword() {
		return $this->ispassword;
	}
	
	/**
	 * Set a single element attribute
	 *
	 * @param string $key        	
	 * @param mixed $value        	
	 * @return Element ElementInterface
	 */
	public function setAttribute($key, $value) {
		// Do not include the value in the list of attributes
		if ($key === 'content') {
			$this->setContent ( $value );
			return $this;
		}
		$this->attributes [$key] = $value;
		return $this;
	}
	
	/**
	 * Retrieve a single element attribute
	 *
	 * @param
	 *        	$key
	 * @return mixed null
	 */
	public function getAttribute($key) {
		if (! array_key_exists ( $key, $this->attributes )) {
			return null;
		}
		return $this->attributes [$key];
	}
	
	/**
	 * Remove a single attribute
	 *
	 * @param string $key        	
	 * @return ElementInterface
	 */
	public function removeAttribute($key) {
		unset ( $this->attributes [$key] );
		return $this;
	}
	
	/**
	 * Does the element has a specific attribute ?
	 *
	 * @param string $key        	
	 * @return bool
	 */
	public function hasAttribute($key) {
		return array_key_exists ( $key, $this->attributes );
	}
	
	/**
	 * Set many attributes at once
	 *
	 * Implementation will decide if this will overwrite or merge.
	 *
	 * @param array|Traversable $arrayOrTraversable        	
	 * @return Element ElementInterface
	 * @throws Exception\InvalidArgumentException
	 */
	public function setAttributes($arrayOrTraversable) {
		if (! is_array ( $arrayOrTraversable ) && ! $arrayOrTraversable instanceof Traversable) {
			throw new Exception\InvalidArgumentException ( sprintf ( '%s expects an array or Traversable argument; received "%s"', __METHOD__, (is_object ( $arrayOrTraversable ) ? get_class ( $arrayOrTraversable ) : gettype ( $arrayOrTraversable )) ) );
		}
		foreach ( $arrayOrTraversable as $key => $value ) {
			$this->setAttribute ( $key, $value );
		}
		return $this;
	}
	
	/**
	 * Retrieve all attributes at once
	 *
	 * @return array Traversable
	 */
	public function getAttributes() {
		return $this->attributes;
	}
	
	/**
	 * Remove many attributes at once
	 *
	 * @param array $keys        	
	 * @return ElementInterface
	 */
	public function removeAttributes(array $keys) {
		foreach ( $keys as $key ) {
			unset ( $this->attributes [$key] );
		}
		
		return $this;
	}
	
	/**
	 * Clear all attributes
	 *
	 * @return Element ElementInterface
	 */
	public function clearAttributes() {
		$this->attributes = array ();
		return $this;
	}
}
