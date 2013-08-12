<?php
namespace Techfever\Form;

use Zend\InputFilter\InputFilter as BaseInputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Techfever\Exception;
use Techfever\Form\Element;
use Traversable;

class InputFilter implements InputFilterAwareInterface {
	/**
	 * @var ServiceLocator
	 */
	private $serviceLocator = null;

	/**
	 * @var Options
	 */
	protected $options = array();

	/**
	 * Element object
	 *
	 * @var Element
	 */
	protected $element;

	/**
	 * @var Data
	 */
	public $data;

	protected $inputFilter;

	public function __construct($options = null, $element) {
		if (!is_array($options)) {
			throw new Exception\RuntimeException('Options has not been set or configured.');
		}
		if (!isset($options['servicelocator'])) {
			throw new Exception\RuntimeException('ServiceLocator has not been set or configured.');
		}
		$this->setServiceLocator($options['servicelocator']);
		unset($options['servicelocator']);

		$options = array_merge($this->options, $options);

		$this->setOptions($options);

		$this->element = $element;
	}

	/**
	 * Returns an option
	 *
	 * @param string $option
	 *        	Option to be returned
	 * @return mixed Returned option
	 * @throws Exception\InvalidArgumentException
	 */
	public function getOption($option) {
		if (isset($this->options) && array_key_exists($option, $this->options)) {
			return $this->options[$option];
		}

		throw new Exception\InvalidArgumentException("Invalid option '$option'");
	}

	/**
	 * Returns all available options
	 *
	 * @return array Array with all available options
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Sets one or multiple options
	 *
	 * @param array|Traversable $options
	 *        	Options to set
	 * @throws Exception\InvalidArgumentException If $options is not an array or Traversable
	 * @return AbstractValidator Provides fluid interface
	 */
	public function setOptions($options = array()) {
		if (!is_array($options) && !$options instanceof Traversable) {
			throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
		}

		if ($this->options !== $options) {
			$this->options = $options;
		}
		return $this;
	}

	/**
	 * Set a single option
	 *
	 * @param string $name        	
	 * @param mixed $value        	
	 * @return Object
	 */
	public function setOption($name, $value) {
		$this->options[(string) $name] = $value;
		return $this;
	}

	/**
	 * Set serviceManager instance
	 *
	 * @param  ServiceLocatorInterface $serviceLocator
	 * @return void
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * Retrieve serviceManager instance
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	private function getElement() {
		if (empty($this->element)) {
			$this->element = new Element($this->options);
		}
		return $this->element;
	}

	// Add content to these methods:
	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new Exception\InvalidArgumentException('Not used');
	}

	public function getInputFilter() {
		if (!$this->inputFilter) {
			$inputFilter = new BaseInputFilter();
			$Element = $this->getElement();
			$elements = $Element->getElementData();
			if (is_array($elements) && count($elements) > 0) {
				foreach ($elements as $element_key => $element_value) {
					if ($Element->validElementByKey($element_key)) {
						$inputFilter->add($Element->getFilterStuctureByKey($element_key));
					}
				}
			}
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
}
