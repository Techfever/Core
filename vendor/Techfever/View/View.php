<?php

namespace Techfever\View;

use Traversable;
use Zend\Stdlib\PriorityQueue;
use Techfever\Exception;
use Techfever\Template\Plugin\Filters\ToUnderscore;
use Techfever\Functions\General as GeneralBase;

class View extends Element implements ViewInterface {
	/**
	 *
	 * @var Options
	 */
	private $options = array (
			'servicelocator' => null,
			'request' => null,
			'response' => null,
			'controller' => null,
			'route' => null,
			'action' => null,
			'variable' => null,
			'id' => null 
	);
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	
	/**
	 * View
	 *
	 * @var View
	 */
	protected $view;
	
	/**
	 * View Data being validated
	 *
	 * @var null array Traversable
	 */
	protected $view_data;
	
	/**
	 * Data being validated
	 *
	 * @var null array Traversable
	 */
	protected $data;
	
	/**
	 * Is the View prepared ?
	 *
	 * @var bool
	 */
	protected $isPrepared = false;
	
	/**
	 *
	 * @var Factory
	 */
	protected $factory;
	
	/**
	 *
	 * @var array
	 */
	protected $byName = array ();
	
	/**
	 *
	 * @var array
	 */
	protected $elements = array ();
	
	/**
	 *
	 * @var array
	 */
	protected $elements_structure = array ();
	
	/**
	 *
	 * @var PriorityQueue
	 */
	protected $iterator;
	
	/**
	 *
	 * @param null|int|string $name
	 *        	Optional name for the element
	 * @param array $options
	 *        	Optional options for the element
	 */
	public function __construct($options = array()) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		if (! isset ( $options ['servicelocator'] )) {
			throw new Exception\RuntimeException ( 'ServiceLocator has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		
		$this->setOptions ( $options );
		
		$name = $this->getViewNameID ();
		parent::__construct ( $name, $options );
		
		$this->iterator = new PriorityQueue ();
		
		$this->viewFactory ();
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
	 * Get View ID
	 *
	 * @return string
	 *
	 */
	public function getElementID() {
		$id = $this->getOption ( 'id' );
		if (! empty ( $id )) {
			return $id;
		}
		return $this->getRoute () . '/' . $this->getRouteAction ();
	}
	
	/**
	 * Compose a View factory to use when calling add() with a non-element
	 *
	 * @param Factory $factory        	
	 * @return View
	 */
	public function setViewFactory(Factory $factory) {
		$this->factory = $factory;
		return $this;
	}
	
	/**
	 * Retrieve composed View factory
	 *
	 * Lazy-loads one if none present.
	 *
	 * @return Factory
	 */
	public function getViewFactory() {
		if (null === $this->factory) {
			$this->setViewFactory ( new Factory () );
		}
		
		return $this->factory;
	}
	
	/**
	 * Add an element
	 *
	 * If $element is an array or Traversable, passes the argument on
	 * to the composed factory to create the object before attaching it.
	 *
	 * $flags could contain metadata such as the alias under which to register
	 * the element, order in which to prioritize it, etc.
	 *
	 * @param array|Traversable|ElementInterface $element        	
	 * @param array $flags        	
	 * @return \Techfever\View\ViewInterface
	 */
	public function add($element, array $flags = array()) {
		$options = $element;
		if (is_array ( $element ) || ($element instanceof Traversable && ! $element instanceof ElementInterface)) {
			$factory = $this->getViewFactory ();
			$element = $factory->create ( $element );
		}
		
		if (! $element instanceof ElementInterface) {
			throw new Exception\InvalidArgumentException ( sprintf ( '%s requires that $element be an object implementing %s; received "%s"', __METHOD__, __NAMESPACE__ . '\ElementInterface', (is_object ( $element ) ? get_class ( $element ) : gettype ( $element )) ) );
		}
		
		$name = $element->getName ();
		if ((null === $name || '' === $name) && (! array_key_exists ( 'name', $flags ) || $flags ['name'] === '')) {
			throw new Exception\InvalidArgumentException ( sprintf ( '%s: element or provided is not named, and no name provided in flags', __METHOD__ ) );
		}
		
		if (array_key_exists ( 'name', $flags ) && $flags ['name'] !== '') {
			$name = $flags ['name'];
			
			// Rename the element to the specified alias
			$element->setName ( $name );
		}
		$element->setOptions ( $options );
		
		$order = 0;
		if (array_key_exists ( 'priority', $flags )) {
			$order = $flags ['priority'];
		}
		
		$this->iterator->insert ( $element, $order );
		$this->byName [$name] = $element;
		$this->elements [$name] = $element;
		
		return $this;
	}
	
	/**
	 * Does have an element by the given name?
	 *
	 * @param string $element        	
	 * @return bool
	 */
	public function has($element) {
		return array_key_exists ( $element, $this->byName );
	}
	
	/**
	 * Retrieve a named element
	 *
	 * @param string $element        	
	 * @return ElementInterface
	 */
	public function get($element) {
		if (! $this->has ( $element )) {
			throw new Exception\InvalidElementException ( sprintf ( "No element by the name of [%s] found in View", $element ) );
		}
		return $this->byName [$element];
	}
	
	/**
	 * Remove a named element
	 *
	 * @param string $element        	
	 * @return ViewInterface
	 */
	public function remove($element) {
		if (! $this->has ( $element )) {
			return $this;
		}
		
		$entry = $this->byName [$element];
		unset ( $this->byName [$element] );
		
		$this->iterator->remove ( $entry );
		
		unset ( $this->elements [$element] );
		return $this;
	}
	
	/**
	 * Set/change the priority of an element
	 *
	 * @param string $element        	
	 * @param int $priority        	
	 * @return ViewInterface
	 */
	public function setPriority($element, $priority) {
		$element = $this->get ( $element );
		$this->remove ( $element );
		$this->add ( $element, array (
				'priority' => $priority 
		) );
		return $this;
	}
	
	/**
	 * Retrieve all attached elements
	 *
	 * Storage is an implementation detail of the concrete class.
	 *
	 * @return array Traversable
	 */
	public function getElements() {
		return $this->elements;
	}
	
	/**
	 * Ensures state is ready for use
	 *
	 * Marshalls the input, to ensure are
	 * available, and prepares any elements that require
	 * preparation.
	 *
	 * @return View
	 */
	public function prepare() {
		if ($this->isPrepared) {
			return $this;
		}
		foreach ( $this->getIterator () as $element ) {
			if ($element instanceof ViewInterface) {
				$element->prepare ();
			} elseif ($element instanceof ElementPrepareAwareInterface) {
				$element->prepareElement ( $this );
			}
		}
		
		$this->isPrepared = true;
		return $this;
	}
	
	/**
	 * Ensures state is ready for use.
	 * Here, we append the name of to every elements in order to avoid
	 * name clashes if the same is used multiple times
	 *
	 * @param ViewInterface $View        	
	 * @return mixed void
	 */
	public function prepareElement(ViewInterface $view) {
		$name = $this->getName ();
		
		foreach ( $this->byName as $element ) {
			// Recursively prepare elements
			if ($element instanceof ElementPrepareAwareInterface) {
				$element->prepareElement ( $view );
			}
		}
	}
	
	/**
	 * Countable: return count of attached elements
	 *
	 * @return int
	 */
	public function count() {
		return $this->iterator->count ();
	}
	
	/**
	 * IteratorAggregate: return internal iterator
	 *
	 * @return PriorityQueue
	 */
	public function getIterator() {
		return $this->iterator;
	}
	
	/**
	 * Make a deep clone
	 *
	 * @return void
	 */
	public function __clone() {
		$items = $this->iterator->toArray ( PriorityQueue::EXTR_BOTH );
		
		$this->byName = array ();
		$this->elements = array ();
		$this->iterator = new PriorityQueue ();
		
		foreach ( $items as $item ) {
			$element = clone $item ['data'];
			$name = $element->getName ();
			
			$this->iterator->insert ( $element, $item ['priority'] );
			$this->byName [$name] = $element;
			
			if ($element instanceof ElementInterface) {
				$this->elements [$name] = $element;
			}
		}
	}
	
	/**
	 * Get View ID
	 *
	 * @return string
	 *
	 */
	public function getViewNameID() {
		$id = $this->getOption ( 'id' );
		if (! empty ( $id )) {
			return $id;
		}
		return $this->getRoute () . '/' . $this->getRouteAction ();
	}
	
	/**
	 * Get View Data
	 *
	 * @return array data
	 *        
	 */
	public function getViewData() {
		if (! is_array ( $this->view_data ) || count ( $this->view_data ) < 1) {
			$this->view_data = array ();
			$ToUnderscore = new ToUnderscore ( '\\' );
			$QView = $this->getDatabase ();
			$QView->select ();
			$QView->columns ( array (
					'mid' => 'module_controllers_id' 
			) );
			$QView->from ( array (
					'mc' => 'module_controllers' 
			) );
			$QView->join ( array (
					'fc' => 'view_element_controller' 
			), 'fc.module_controllers_id = mc.module_controllers_id', array (
					'fid' => 'view_element_controller_id' 
			) );
			$QView->join ( array (
					'fec' => 'view_element_to_controller' 
			), 'fec.view_element_controller_id = fc.view_element_controller_id', array (
					'link_id' => 'view_element_to_controller_id',
					'parent' => 'view_element_to_controller_parent',
					'sort_order' => 'view_element_to_controller_sort_order' 
			) );
			$QView->join ( array (
					'fe' => 'view_element' 
			), 'fe.view_element_id = fec.view_element_id', array (
					'id' => 'view_element_id',
					'type' => 'view_element_type',
					'key' => 'view_element_key',
					'field' => 'view_element_field',
					'check_locale' => 'view_element_check_locale',
					'locale' => 'view_element_locale' 
			) );
			$QView->where ( array (
					'fc.module_controllers_action = "' . $this->getRouteAction () . '"',
					'mc.module_controllers_alias = "' . str_replace ( '\\', '\\\\', $this->getController () ) . '"',
					'fec.view_element_to_controller_status = 1' 
			) );
			$QView->order ( array (
					'fec.view_element_to_controller_parent ASC',
					'fec.view_element_to_controller_sort_order ASC' 
			) );
			$QView->execute ();
			if ($QView->hasResult ()) {
				while ( $QView->valid () ) {
					$rawdata = $QView->current ();
					$class = explode ( '\\', $rawdata ['type'] );
					$class = array_slice ( $class, - 1 );
					$rawdata ['class'] = $class [0];
					$rawdata ['key'] = strtolower ( $rawdata ['key'] );
					
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
						$this->view_data [$rawdata ['key']] = $rawdata;
					}
					$QView->next ();
				}
			}
		}
		return $this->view_data;
	}
	
	/**
	 * Get View ID
	 *
	 * @return array id
	 *        
	 */
	public function getViewID() {
		$data = $this->getViewData ();
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
	 * Gey View ID by Key
	 *
	 * @return int
	 *
	 */
	public function getViewIDByKey($element) {
		$data = $this->getViewData ();
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $key => $value ) {
				if (strtolower ( $element ) == $key) {
					return ( int ) $value ['id'];
				}
			}
		}
		return 0;
	}
	private function viewFactory() {
		if (! $this->view) {
			$elements = $this->getViewData ();
			if (is_array ( $elements ) && count ( $elements ) > 0) {
				foreach ( $elements as $key => $value ) {
					$elementOrFieldset = array (
							'name' => strtolower ( $key ),
							'type' => $value ['type'],
							'options' => array (
									'node' => $value ['link_id'],
									'parent' => $value ['parent'],
									'label' => 'text_' . strtolower ( $key ) 
							),
							'attributes' => array (
									'value' => "",
									'class' => strtolower ( $value ['class'] ),
									'id' => strtolower ( $key ) 
							) 
					);
					$elementOrFieldset ['attributes'] ['content'] = $this->getVariable ( strtolower ( $value ['field'] ) );
					if ($value ['type'] == 'selectdate') {
						$elementOrFieldset ['options'] ['create_empty_option'] = True;
						$elementOrFieldset ['attributes'] ['value'] = "";
					}
					$this->add ( $elementOrFieldset );
				}
			}
		}
		return $this->view;
	}
}
