<?php

namespace Techfever\Navigator;

use Techfever\Functions\General as GeneralBase;
use Techfever\Exception;

class Navigator extends GeneralBase {
	
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
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		parent::__construct ( $options );
		unset ( $this->options ['servicelocator'] );
		$this->setOptions ( $options );
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
			$themesuffix = "theme";
			$themeid = THEME_ID;
			if ($this->getUrlRewrite ()->validateBlog ()) {
				$rawblog = $this->getUrlRewrite ()->detectBlog ();
				if (strlen ( $rawblog ) > 0) {
					$themesuffix = "blog";
					$themeid = SYSTEM_THEME_BLOG_ID;
				}
			}
			$QNavigator = $this->getDatabase ();
			$QNavigator->select ();
			$QNavigator->columns ( array (
					'root' => $themesuffix . '_navigator_id',
					'label' => $themesuffix . '_navigator_label',
					'route' => $themesuffix . '_navigator_route',
					'action' => $themesuffix . '_navigator_action',
					'controller' => $themesuffix . '_navigator_controller',
					'uri' => $themesuffix . '_navigator_uri',
					'class' => $themesuffix . '_navigator_css_class',
					'order' => $themesuffix . '_navigator_order',
					'visible' => $themesuffix . '_navigator_visible',
					'parent' => $themesuffix . '_navigator_parent',
					'backend' => $themesuffix . '_navigator_backend' 
			) );
			$QNavigator->from ( array (
					'sn' => $themesuffix . '_navigator' 
			) );
			$where = array (
					$themesuffix . '_navigator_visible = 1' 
			);
			$where [] = $themesuffix . '_id = ' . $themeid;
			$where [] = '(' . $themesuffix . '_navigator_controller in ("' . implode ( '", "', $permission_array ) . '") or ' . $themesuffix . '_navigator_controller is null or ' . $themesuffix . '_navigator_controller = "")';
			$QNavigator->where ( $where );
			$QNavigator->order ( array (
					$themesuffix . '_navigator_parent ASC',
					$themesuffix . '_navigator_order ASC',
					$themesuffix . '_navigator_label ASC' 
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
						$structure ['label'] = $this->getTranslate ( 'text_navigator_' . strtolower ( $dbdata ['label'] ) );
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
