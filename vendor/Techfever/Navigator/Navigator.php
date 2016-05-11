<?php

namespace Techfever\Navigator;

use Techfever\Functions\General as GeneralBase;
use Techfever\Exception;

class Navigator {
	
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
	 * @var Data
	 *
	 */
	private $structure = array ();
	
	/**
	 *
	 * @var Query
	 *
	 */
	private $navigator = array ();
	
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
		
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		
		$this->setOptions ( $options );
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
	 * Get Data
	 *
	 * @return void
	 *
	 */
	public function getUserPermission() {
		return $this->getServiceLocator ()->get ( 'UserPermission' )->getUserPermission ();
	}
	
	/**
	 * Get Data
	 *
	 * @return void
	 *
	 */
	public function getNavigator() {
		if (! is_array ( $this->navigator ) || count ( $this->navigator ) < 1) {
			$permission = $this->getUserPermission ();
			$permission_array = array ();
			if (is_array ( $permission ) && count ( $permission ) > 0) {
				foreach ( $permission as $permissionvalue ) {
					$permission_array [] = str_replace ( '\\', '\\\\', $permissionvalue ['controller'] );
				}
			}
			$QNavigator = $this->getDatabase ();
			$QNavigator->select ();
			$QNavigator->columns ( array (
					'root' => 'theme_navigator_id',
					'label' => 'theme_navigator_label',
					'route' => 'theme_navigator_route',
					'action' => 'theme_navigator_action',
					'controller' => 'theme_navigator_controller',
					'uri' => 'theme_navigator_uri',
					'class' => 'theme_navigator_css_class',
					'order' => 'theme_navigator_order',
					'visible' => 'theme_navigator_visible',
					'parent' => 'theme_navigator_parent',
					'backend' => 'theme_navigator_backend' 
			) );
			$QNavigator->from ( array (
					'sn' => 'theme_navigator' 
			) );
			$where = array (
					'theme_navigator_visible = 1' 
			);
			$where [] = 'theme_id = ' . THEME_ID;
			$where [] = '(theme_navigator_controller in ("' . implode ( '", "', $permission_array ) . '") or theme_navigator_controller is null or theme_navigator_controller = "")';
			$QNavigator->where ( $where );
			$QNavigator->order ( array (
					'theme_navigator_parent ASC',
					'theme_navigator_order ASC',
					'theme_navigator_label ASC' 
			) );
			$QNavigator->execute ();
			if ($QNavigator->hasResult ()) {
				$this->navigator = $QNavigator->toArray ();
			}
		}
		return $this->navigator;
	}
	
	/**
	 * Prepare
	 */
	public function getTree($parent = 0, $backend = false) {
		$data = null;
		$navigator = $this->getNavigator ();
		if (is_array ( $navigator ) && count ( $navigator ) > 0) {
			foreach ( $navigator as $dbdata ) {
				if ($dbdata ['parent'] == $parent) {
					$rawdata = $this->getTree ( $dbdata ['root'], $backend );
					if (is_array ( $rawdata ) && count ( $rawdata ) > 0) {
						$dbdata ['pages'] = $rawdata;
					}
					$structure = array ();
					$structure ['class'] = "";
					if (! empty ( $dbdata ['label'] )) {
						$structure ['label'] = "<span></span>" . $this->getTranslate ( 'text_navigator_' . strtolower ( $dbdata ['label'] ) );
						$structure ['id'] = strtolower ( $dbdata ['label'] );
						$structure ['class'] .= $dbdata ['label'];
					}
					if (! empty ( $dbdata ['route'] )) {
						$structure ['route'] = $dbdata ['route'];
					}
					if (! empty ( $dbdata ['action'] )) {
						$structure ['action'] = $dbdata ['action'];
					}
					if (! empty ( $dbdata ['controller'] )) {
						$structure ['controller'] = $dbdata ['controller'];
					}
					if (! empty ( $dbdata ['uri'] )) {
						$structure ['uri'] = $dbdata ['uri'];
					}
					if (! empty ( $dbdata ['class'] )) {
						$structure ['class'] .= ' ' . $dbdata ['class'];
					}
					if (! empty ( $dbdata ['order'] )) {
						$structure ['order'] = $dbdata ['order'];
					}
					if (! empty ( $dbdata ['visible'] )) {
						$structure ['visible'] = ($dbdata ['visible'] == 1 ? True : False);
					}
					$structure ['backend'] = false;
					if (! empty ( $dbdata ['backend'] )) {
						$structure ['backend'] = ($dbdata ['backend'] == 1 ? True : False);
					}
					if (! empty ( $dbdata ['pages'] )) {
						unset ( $structure ['route'] );
						unset ( $structure ['action'] );
						unset ( $structure ['controller'] );
						$structure ['uri'] = "#";
						$structure ['pages'] = $dbdata ['pages'];
					}
					$status = false;
					if ($backend && $structure ['backend']) {
						$status = true;
					} else if (! $backend && ! $structure ['backend']) {
						$status = true;
					}
					if ($status) {
						$data [] = $structure;
					}
				}
			}
		}
		
		return $data;
	}
	
	/**
	 * getStructure
	 */
	public function getStructure($backend = false) {
		if (! is_array ( $this->structure ) || count ( $this->structure ) < 1) {
			$this->structure = $this->getTree ( 0, $backend );
		}
		return $this->structure;
	}
}
