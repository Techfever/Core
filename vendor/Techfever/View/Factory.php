<?php

namespace Techfever\View;

use ArrayAccess;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Techfever\Exception;

class Factory {
	
	/**
	 *
	 * @var ViewElementManager
	 */
	protected $viewElementManager;
	
	/**
	 *
	 * @param ViewElementManager $viewElementManager        	
	 */
	public function __construct(ViewElementManager $viewElementManager = null) {
		if ($viewElementManager) {
			$this->setViewElementManager ( $viewElementManager );
		}
	}
	
	/**
	 * Set the view element manager
	 *
	 * @param ViewElementManager $viewElementManager        	
	 * @return Factory
	 */
	public function setViewElementManager(ViewElementManager $viewElementManager) {
		$this->viewElementManager = $viewElementManager;
		return $this;
	}
	
	/**
	 * Get view element manager
	 *
	 * @return ViewElementManager
	 */
	public function getViewElementManager() {
		if ($this->viewElementManager === null) {
			$this->setViewElementManager ( new ViewElementManager () );
		}
		
		return $this->viewElementManager;
	}
	
	/**
	 * Create an element, or view
	 *
	 * Introspects the 'type' key of the provided $spec, and determines what
	 * type is being requested; if none is provided, assumes the spec
	 * represents simply an element.
	 *
	 * @param array|Traversable $spec        	
	 * @return ElementInterface
	 * @throws Exception\DomainException
	 */
	public function create($spec) {
		$spec = $this->validateSpecification ( $spec, __METHOD__ );
		$type = isset ( $spec ['type'] ) ? $spec ['type'] : 'Techfever\View\Element';
		
		$element = $this->getViewElementManager ()->get ( $type );
		
		if ($element instanceof ViewInterface) {
			return $this->configureView ( $element, $spec );
		}
		
		if ($element instanceof ElementInterface) {
			return $this->configureElement ( $element, $spec );
		}
		
		throw new Exception\DomainException ( sprintf ( '%s expects the $spec["type"] to implement one of %s, %s, or %s; received %s', __METHOD__, 'Techfever\View\ElementInterface', 'Techfever\View\ViewInterface', $type ) );
	}
	
	/**
	 * Create an element
	 *
	 * @param array $spec        	
	 * @return ElementInterface
	 */
	public function createElement($spec) {
		if (! isset ( $spec ['type'] )) {
			$spec ['type'] = 'Techfever\View\Element';
		}
		
		return $this->create ( $spec );
	}
	
	/**
	 * Create a view
	 *
	 * @param array $spec        	
	 * @return ElementInterface
	 */
	public function createView($spec) {
		if (! isset ( $spec ['type'] )) {
			$spec ['type'] = 'Techfever\View\View';
		}
		
		return $this->create ( $spec );
	}
	
	/**
	 * Configure an element based on the provided specification
	 *
	 * Specification can contain any of the following:
	 * - type: the Element class to use; defaults to \Techfever\View\Element
	 * - name: what name to provide the element, if any
	 * - options: an array, Traversable, or ArrayAccess object of element options
	 * - attributes: an array, Traversable, or ArrayAccess object of element
	 * attributes to assign
	 *
	 * @param ElementInterface $element        	
	 * @param array|Traversable|ArrayAccess $spec        	
	 * @throws Exception\DomainException
	 * @return ElementInterface
	 */
	public function configureElement(ElementInterface $element, $spec) {
		$spec = $this->validateSpecification ( $spec, __METHOD__ );
		
		$name = isset ( $spec ['name'] ) ? $spec ['name'] : null;
		$options = isset ( $spec ['options'] ) ? $spec ['options'] : null;
		$attributes = isset ( $spec ['attributes'] ) ? $spec ['attributes'] : null;
		
		if ($name !== null && $name !== '') {
			$element->setName ( $name );
		}
		
		if (is_array ( $options ) || $options instanceof Traversable || $options instanceof ArrayAccess) {
			$element->setOptions ( $options );
		}
		
		if (is_array ( $attributes ) || $attributes instanceof Traversable || $attributes instanceof ArrayAccess) {
			$element->setAttributes ( $attributes );
		}
		
		return $element;
	}
	
	/**
	 * Configure a view based on the provided specification
	 *
	 * Specification follows that, and adds the
	 * following keys:
	 *
	 *
	 * @param ViewInterface $view        	
	 * @param array|Traversable|ArrayAccess $spec        	
	 * @return ViewInterface
	 */
	public function configureView(ViewInterface $view, $spec) {
		$view = $this->configureElement ( $view, $spec );
		
		if (isset ( $spec ['elements'] )) {
			$this->prepareAndInjectElements ( $spec ['elements'], $view, __METHOD__ );
		}
		
		$factory = (isset ( $spec ['factory'] ) ? $spec ['factory'] : $this);
		$this->prepareAndInjectFactory ( $factory, $view, __METHOD__ );
		
		return $view;
	}
	
	/**
	 * Validate a provided specification
	 *
	 * Ensures we have an array, Traversable, or ArrayAccess object, and returns it.
	 *
	 * @param array|Traversable|ArrayAccess $spec        	
	 * @param string $method
	 *        	Method invoking the validator
	 * @return array ArrayAccess
	 * @throws Exception\InvalidArgumentException for invalid $spec
	 */
	protected function validateSpecification($spec, $method) {
		if (is_array ( $spec )) {
			return $spec;
		}
		
		if ($spec instanceof Traversable) {
			$spec = ArrayUtils::iteratorToArray ( $spec );
			return $spec;
		}
		
		if (! $spec instanceof ArrayAccess) {
			throw new Exception\InvalidArgumentException ( sprintf ( '%s expects an array, or object implementing Traversable or ArrayAccess; received "%s"', $method, (is_object ( $spec ) ? get_class ( $spec ) : gettype ( $spec )) ) );
		}
		
		return $spec;
	}
	
	/**
	 * Takes a list of element specifications, creates the elements, and injects them into the provided view
	 *
	 * @param array|Traversable|ArrayAccess $elements        	
	 * @param ViewInterface $view        	
	 * @param string $method
	 *        	Method invoking this one (for exception)
	 * @return void
	 */
	protected function prepareAndInjectElements($elements, ViewInterface $view, $method) {
		$elements = $this->validateSpecification ( $elements, $method );
		
		foreach ( $elements as $elementSpecification ) {
			$flags = isset ( $elementSpecification ['flags'] ) ? $elementSpecification ['flags'] : array ();
			$spec = isset ( $elementSpecification ['spec'] ) ? $elementSpecification ['spec'] : array ();
			
			if (! isset ( $spec ['type'] )) {
				$spec ['type'] = 'Techfever\View\Element';
			}
			
			$element = $this->create ( $spec );
			$view->add ( $element, $flags );
		}
	}
	
	/**
	 * Prepare and inject a named factory
	 *
	 * Takes a string indicating a factory class name (or a concrete instance), try first to instantiates the class
	 * by pulling it from service manager, and injects the factory instance into the view.
	 *
	 * @param string|array|Factory $factoryOrName        	
	 * @param ViewInterface $view        	
	 * @param string $method        	
	 * @return void
	 * @throws Exception\DomainException If $factoryOrName is not a string, does not resolve to a known class, or
	 *         the class does not extend View\Factory
	 */
	protected function prepareAndInjectFactory($factoryOrName, ViewInterface $view, $method) {
		if (is_array ( $factoryOrName )) {
			if (! isset ( $factoryOrName ['type'] )) {
				throw new Exception\DomainException ( sprintf ( '%s expects array specification to have a type value', $method ) );
			}
			$factoryOrName = $factoryOrName ['type'];
		}
		
		if (is_string ( $factoryOrName )) {
			$factoryOrName = $this->getFactoryFromName ( $factoryOrName );
		}
		
		if (! $factoryOrName instanceof Factory) {
			throw new Exception\DomainException ( sprintf ( '%s expects a valid extention of Techfever\View\Factory; received "%s"', $method, $factoryOrName ) );
		}
		
		$view->setViewFactory ( $factoryOrName );
	}
	
	/**
	 * Try to pull factory from service manager, or instantiates it from its name
	 *
	 * @param string $factoryName        	
	 * @return mixed
	 * @throws Exception\DomainException
	 */
	protected function getFactoryFromName($factoryName) {
		$services = $this->getViewElementManager ()->getServiceLocator ();
		
		if ($services && $services->has ( $factoryName )) {
			return $services->get ( $factoryName );
		}
		
		if (! class_exists ( $factoryName )) {
			throw new Exception\DomainException ( sprintf ( 'Expects string factory name to be a valid class name; received "%s"', $factoryName ) );
		}
		
		$factory = new $factoryName ();
		return $factory;
	}
}
