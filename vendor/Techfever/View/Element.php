<?php

namespace Techfever\View;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\InitializableInterface;
use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Element extends GeneralBase implements ElementAttributeRemovalInterface, ElementInterface, InitializableInterface {
	/**
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var array
	 */
	protected $labelAttributes;

	/**
	 * @var array custom options
	 */
	protected $options = array();

	/**
	 * @var mixed
	 */
	protected $content;

	/**
	 * @param  null|int|string  $name    Optional name for the element
	 * @param  array            $options Optional options for the element
	 * @throws Exception\InvalidArgumentException
	 */
	public function __construct($name = null, $options = array()) {
		if (!is_array($options)) {
			throw new Exception\RuntimeException('Options has not been set or configured.');
		}
		$options = array_merge($this->options, $options);
		$this->setServiceLocator($options['servicelocator']);
		parent::__construct($options);
		unset($options['servicelocator']);
		$this->setOptions($options);

		if (null !== $name) {
			$this->setName($name);
		}

		if (!empty($options)) {
			$this->setOptions($options);
		}
	}

	/**
	 * This function is automatically called when creating element with factory. It
	 * allows to perform various operations (add elements...)
	 *
	 * @return void
	 */
	public function init() {
	}

	/**
	 * Set value for name
	 *
	 * @param  string $name
	 * @return Element|ElementInterface
	 */
	public function setName($name) {
		$this->setAttribute('name', $name);
		return $this;
	}

	/**
	 * Get value for name
	 *
	 * @return string|int
	 */
	public function getName() {
		return $this->getAttribute('name');
	}

	/**
	 * Set options for an element. Accepted options are:
	 *
	 * @param  array|Traversable $options
	 * @return Element|ElementInterface
	 * @throws Exception\InvalidArgumentException
	 */
	public function setOptions($options) {
		if ($options instanceof Traversable) {
			$options = ArrayUtils::iteratorToArray($options);
		} elseif (!is_array($options)) {
			throw new Exception\InvalidArgumentException('The options parameter must be an array or a Traversable');
		}
		if (isset($options['attributes'])) {
			$this->setAttributes($options['attributes']);
		}

		if (isset($options['label'])) {
			$this->setLabel($options['label']);
		}

		if (isset($options['label_attributes'])) {
			$this->setLabelAttributes($options['label_attributes']);
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
	 * @return NULL|mixed
	 */
	public function getOption($option) {
		if (!isset($this->options[$option])) {
			return null;
		}

		return $this->options[$option];
	}

	/**
	 * Retrieve the type used for this element
	 *
	 * @return string
	 */
	public function getType() {
		if (!isset($this->options['type'])) {
			return null;
		}
		return $this->options['type'];
	}

	/**
	 * Set the element content
	 *
	 * @param  mixed $content
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
	 * Set the label used for this element
	 *
	 * @param $label
	 * @return Element|ElementInterface
	 */
	public function setLabel($label) {
		if (is_string($label)) {
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
	 * @return Element|ElementInterface
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
	 * Retrieve the title used for this element
	 *
	 * @return string
	 */
	public function getTitle() {
		if (!isset($this->options['title'])) {
			return null;
		}
		return $this->options['title'];
	}

	/**
	 * Return the is password
	 *
	 * @return boolean
	 */
	public function isPassword() {
		$options = $this->getOption('options');
		if (isset($options['isPassword'])) {
			if ($options['isPassword'] == 'True') {
				$options['isPassword'] = True;
			}
			return $options['isPassword'];
		}
		return false;
	}

	/**
	 * Set a single element attribute
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 * @return Element|ElementInterface
	 */
	public function setAttribute($key, $value) {
		// Do not include the value in the list of attributes
		if ($key === 'content') {
			$this->setContent($value);
			return $this;
		}
		$this->attributes[$key] = $value;
		return $this;
	}

	/**
	 * Retrieve a single element attribute
	 *
	 * @param  $key
	 * @return mixed|null
	 */
	public function getAttribute($key) {
		if (!array_key_exists($key, $this->attributes)) {
			return null;
		}
		return $this->attributes[$key];
	}

	/**
	 * Remove a single attribute
	 *
	 * @param string $key
	 * @return ElementInterface
	 */
	public function removeAttribute($key) {
		unset($this->attributes[$key]);
		return $this;
	}

	/**
	 * Does the element has a specific attribute ?
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function hasAttribute($key) {
		return array_key_exists($key, $this->attributes);
	}

	/**
	 * Set many attributes at once
	 *
	 * Implementation will decide if this will overwrite or merge.
	 *
	 * @param  array|Traversable $arrayOrTraversable
	 * @return Element|ElementInterface
	 * @throws Exception\InvalidArgumentException
	 */
	public function setAttributes($arrayOrTraversable) {
		if (!is_array($arrayOrTraversable) && !$arrayOrTraversable instanceof Traversable) {
			throw new Exception\InvalidArgumentException(sprintf('%s expects an array or Traversable argument; received "%s"', __METHOD__, (is_object($arrayOrTraversable) ? get_class($arrayOrTraversable) : gettype($arrayOrTraversable))));
		}
		foreach ($arrayOrTraversable as $key => $value) {
			$this->setAttribute($key, $value);
		}
		return $this;
	}

	/**
	 * Retrieve all attributes at once
	 *
	 * @return array|Traversable
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
		foreach ($keys as $key) {
			unset($this->attributes[$key]);
		}

		return $this;
	}

	/**
	 * Clear all attributes
	 *
	 * @return Element|ElementInterface
	 */
	public function clearAttributes() {
		$this->attributes = array();
		return $this;
	}
}
