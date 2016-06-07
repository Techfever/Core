<?php

namespace Techfever\Template\Module;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Router extends GeneralBase {
	
	/**
	 * options
	 *
	 * @var mixed
	 */
	private $options = array ();
	
	/**
	 *
	 * @var Variables
	 */
	private $variables = array ();
	
	/**
	 * General object
	 *
	 * @var General
	 */
	protected $generalobject = null;
	
	/**
	 *
	 * @var Structure
	 *
	 */
	private $structure = array ();
	
	/**
	 *
	 * @var Routers
	 *
	 */
	private $routes = array ();
	
	/**
	 *
	 * @var Constraints
	 *
	 */
	private $constraints = array ();
	
	/**
	 *
	 * @var Constraints
	 *
	 */
	private $defaults = array ();
	
	/**
	 * Constructor
	 *
	 * @param null|array $options        	
	 *
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		if (! isset ( $options ['servicelocator'] )) {
			throw new Exception\RuntimeException ( 'ServiceLocator has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		parent::__construct ( $options );
		unset ( $this->options ['servicelocator'] );
		$this->setOptions ( $options );
	}
	
	/**
	 * Get Routes
	 *
	 * @param
	 *        	$routes
	 *        	
	 */
	public function getRoutes() {
		if (! is_array ( $this->routes ) || count ( $this->routes ) < 1) {
			$QRoutes = $this->getDatabase ();
			$QRoutes->select ();
			$QRoutes->columns ( array (
					'root' => 'system_routes_id',
					'uri' => 'system_routes_uri',
					'route' => 'system_routes_route',
					'type' => 'system_routes_type',
					'controller' => 'system_routes_controller',
					'action' => 'system_routes_action',
					'parent' => 'system_routes_parent' 
			) );
			$QRoutes->from ( array (
					'sr' => 'system_routes' 
			) );
			$QRoutes->where ( array (
					'sr.system_routes_status = 1' 
			) );
			$QRoutes->order ( array (
					'system_routes_parent ASC',
					'system_routes_order ASC',
					'system_routes_uri ASC' 
			) );
			$QRoutes->execute ();
			if ($QRoutes->hasResult ()) {
				$this->routes = $QRoutes->toArray ();
			}
		}
		return $this->routes;
	}
	
	/**
	 * Get Constraints
	 *
	 * @param
	 *        	$constraints
	 *        	
	 */
	public function getConstraints() {
		if (! is_array ( $this->constraints ) || count ( $this->constraints ) < 1) {
			$QConstraints = $this->getDatabase ();
			$QConstraints->select ();
			$QConstraints->columns ( array (
					'id' => 'system_routes_id',
					'name' => 'system_routes_constraints_name',
					'regex' => 'system_routes_constraints_regex' 
			) );
			$QConstraints->from ( array (
					'src' => 'system_routes_constraints' 
			) );
			$QConstraints->order ( array (
					'system_routes_constraints_order ASC' 
			) );
			$QConstraints->execute ();
			if ($QConstraints->hasResult ()) {
				$this->constraints = $QConstraints->toArray ();
			}
		}
		return $this->constraints;
	}
	
	/**
	 * Get Defaults
	 *
	 * @param
	 *        	$constraints
	 *        	
	 */
	public function getDefaults() {
		if (! is_array ( $this->defaults ) || count ( $this->defaults ) < 1) {
			$QDefaults = $this->getDatabase ();
			$QDefaults->select ();
			$QDefaults->columns ( array (
					'id' => 'system_routes_id',
					'name' => 'system_routes_defaults_name',
					'value' => 'system_routes_defaults_value' 
			) );
			$QDefaults->from ( array (
					'src' => 'system_routes_defaults' 
			) );
			$QDefaults->order ( array (
					'system_routes_defaults_order ASC' 
			) );
			$QDefaults->execute ();
			if ($QDefaults->hasResult ()) {
				$this->defaults = $QDefaults->toArray ();
			}
		}
		return $this->defaults;
	}
	
	/**
	 * Get Tree
	 */
	public function getTree($parent = 0) {
		$data = null;
		$routes = $this->getRoutes ();
		$constraints = $this->getConstraints ();
		$defaults = $this->getDefaults ();
		if (is_array ( $routes ) && count ( $routes ) > 0) {
			foreach ( $routes as $dbdata ) {
				if ($dbdata ['parent'] == $parent) {
					$rawdata = $this->getTree ( $dbdata ['root'] );
					if (is_array ( $rawdata ) && count ( $rawdata ) > 0) {
						$dbdata ['may_terminate'] = true;
						$dbdata ['child_routes'] = $rawdata;
					}
					
					$structure = array ();
					if (! empty ( $dbdata ['route'] )) {
						$structure ['route'] = $dbdata ['route'];
					}
					if (! empty ( $dbdata ['type'] )) {
						$structure ['type'] = $dbdata ['type'];
					}
					if (! empty ( $dbdata ['controller'] )) {
						$structure ['controller'] = $dbdata ['controller'];
					}
					if (! empty ( $dbdata ['action'] )) {
						$structure ['action'] = $dbdata ['action'];
					}
					if (is_array ( $constraints ) && count ( $constraints ) > 0) {
						foreach ( $constraints as $dbconstraints ) {
							if ($dbconstraints ['id'] == $dbdata ['root']) {
								$dbdata ['constraints'] [$dbconstraints ['name']] = $dbconstraints ['regex'];
								$structure ['route'] .= '[/:' . $dbconstraints ['name'] . ']';
							}
						}
					}
					if (! empty ( $dbdata ['constraints'] ) && is_array ( $dbdata ['constraints'] )) {
						$structure ['constraints'] = $dbdata ['constraints'];
					}
					if (! empty ( $dbdata ['child_routes'] ) && is_array ( $dbdata ['child_routes'] )) {
						$structure ['child_routes'] = $dbdata ['child_routes'];
					}
					$data [$dbdata ['uri']] = array (
							'type' => $structure ['type'],
							'options' => array () 
					);
					$data [$dbdata ['uri']] ['options'] = array (
							'route' => $structure ['route'] 
					);
					if (array_key_exists ( 'constraints', $structure ) && is_array ( $structure ['constraints'] )) {
						$data [$dbdata ['uri']] ['options'] ['constraints'] = $structure ['constraints'];
					}
					$cdefaults = array ();
					if (isset ( $structure ['controller'] )) {
						$cdefaults ['controller'] = $structure ['controller'];
					}
					if (isset ( $structure ['action'] )) {
						$cdefaults ['action'] = $structure ['action'];
					}
					if (is_array ( $defaults ) && count ( $defaults ) > 0) {
						foreach ( $defaults as $dbdefaults ) {
							if ($dbdefaults ['id'] == $dbdata ['root']) {
								$cdefaults [$dbdefaults ['name']] = $dbdefaults ['value'];
							}
						}
					}
					$data [$dbdata ['uri']] ['options'] ['defaults'] = $cdefaults;
					if (array_key_exists ( 'child_routes', $structure ) && is_array ( $structure ['child_routes'] )) {
						$data [$dbdata ['uri']] ['may_terminate'] = true;
						$data [$dbdata ['uri']] ['child_routes'] = $structure ['child_routes'];
					}
				}
			}
		}
		return $data;
	}
	
	/**
	 * Get Structure
	 *
	 * @return array
	 */
	public function getStructure() {
		if (! is_array ( $this->structure ) || count ( $this->structure ) < 1) {
			$this->structure = $this->getTree ();
		}
		if ($this->getUrlRewrite ()->validateBlog () && $this->getUrlRewrite ()->isUri ()) {
			$rawblog = $this->getUrlRewrite ()->detectBlog ();
			if (strlen ( $rawblog ) > 0) {
				return array (
						'routes' => array (
								$rawblog => array (
										'type' => 'Zend\Mvc\Router\Http\Literal',
										'options' => array (
												'route' => '/' . $rawblog,
												'defaults' => array (
														'controller' => 'Index\Controller\Action',
														'action' => 'Index' 
												) 
										),
										'may_terminate' => true,
										'child_routes' => $this->structure 
								) 
						) 
				);
			}
		} else {
			return array (
					'routes' => $this->structure 
			);
		}
	}
}
