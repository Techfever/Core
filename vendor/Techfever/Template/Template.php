<?php

namespace Techfever\Template;

use Techfever\Exception;
use Techfever\Template\Module\Router;
use Techfever\Template\Module\Controller;
use Techfever\Template\Module\ViewManager;
use Techfever\Functions\General as GeneralBase;

class Template {
	
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
	 * @var Module Manager
	 *     
	 */
	private $modulemanager = array (
			'router' => array (),
			'view_manager' => array (),
			'controllers' => array () 
	);
	
	/**
	 *
	 * @var Router Configuration
	 *     
	 */
	private $router = null;
	
	/**
	 *
	 * @var View Manager Configuration
	 *     
	 */
	private $viewmanager = null;
	
	/**
	 *
	 * @var Controllers Configuration
	 *     
	 */
	private $controllers = null;
	
	/**
	 *
	 * @var Config
	 *
	 */
	private $config = null;
	
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
		
		$config = $this->getConfig ();
		$options ['config'] = $config;
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
	 * Get Template Config
	 *
	 * @return void
	 *
	 */
	public function getConfig() {
		if (! is_array ( $this->config ) || count ( $this->config ) < 1) {
			$Session = $this->getSession ();
			$Container = $Session->getContainer ( 'Template' );
			$dbconfig = array ();
			if ($Container->offsetExists ( 'Config' )) {
				$dbconfig = $Container->offsetGet ( 'Config' );
				$this->_config = $dbconfig;
			} else {
				$QSystem = $this->getDatabase ();
				$QSystem->select ();
				$QSystem->columns ( array (
						'key' => 'system_configuration_key',
						'value' => 'system_configuration_value' 
				) );
				$QSystem->from ( array (
						'ss' => 'system_configuration' 
				) );
				$QSystem->execute ();
				if ($QSystem->hasResult ()) {
					while ( $QSystem->valid () ) {
						$rawdata = $QSystem->current ();
						$dbconfig [strtoupper ( $rawdata ['key'] )] = (strlen ( $rawdata ['value'] ) > 0 ? $rawdata ['value'] : null);
						$QSystem->next ();
					}
				}
				if (is_array ( $dbconfig ) && array_key_exists ( 'SYSTEM_THEME', $dbconfig )) {
					$QTheme = $this->getDatabase ();
					$QTheme->select ();
					$QTheme->columns ( array (
							'*' 
					) );
					$QTheme->from ( array (
							't' => 'theme' 
					) );
					$QTheme->where ( array (
							't.theme_key = "' . $dbconfig ['SYSTEM_THEME'] . '"' 
					) );
					$QTheme->limit ( 1 );
					$QTheme->execute ();
					if ($QTheme->hasResult ()) {
						$result = $QTheme->current ();
						foreach ( $result as $key => $value ) {
							$dbconfig [strtoupper ( $key )] = $value;
						}
					}
					if (array_key_exists ( 'THEME_ID', $dbconfig )) {
						$QTSystem = $this->getDatabase ();
						$QTSystem->select ();
						$QTSystem->columns ( array (
								'key' => 'theme_system_configuration_key',
								'value' => 'theme_system_configuration_value' 
						) );
						$QTSystem->from ( array (
								'ss' => 'theme_system_configuration' 
						) );
						$QTSystem->where ( array (
								'ss.theme_id = ' . $dbconfig ['THEME_ID'] . '' 
						) );
						$QTSystem->execute ();
						if ($QTSystem->hasResult ()) {
							while ( $QTSystem->valid () ) {
								$rawdata2 = $QTSystem->current ();
								if (array_key_exists ( strtoupper ( $rawdata2 ['key'] ), $dbconfig )) {
									$dbconfig [strtoupper ( $rawdata2 ['key'] )] = (strlen ( $rawdata2 ['value'] ) > 0 ? $rawdata2 ['value'] : null);
								}
								$QTSystem->next ();
							}
						}
					}
				}
				$this->_config = $dbconfig;
				$Container->offsetSet ( 'Config', $this->_config );
			}
		}
		foreach ( $this->_config as $key => $value ) {
			if (! defined ( strtoupper ( $key ) )) {
				define ( strtoupper ( $key ), $value );
			}
		}
		return $this->_config;
	}
	
	/**
	 * Get Router
	 *
	 * @return $router
	 *
	 */
	public function getRouter() {
		if (! isset ( $this->router )) {
			$options = $this->getOptions ();
			$options ['servicelocator'] = $this->getServiceLocator ();
			$this->router = new Router ( $options );
		}
		return $this->router;
	}
	
	/**
	 * Get Controllers
	 *
	 * @return $controllers
	 *
	 */
	public function getControllers() {
		if (! isset ( $this->controllers )) {
			$options = $this->getOptions ();
			$options ['servicelocator'] = $this->getServiceLocator ();
			$this->controllers = new Controller ( $options );
		}
		return $this->controllers;
	}
	
	/**
	 * Get View Manager
	 *
	 * @return $viewmanager
	 *
	 */
	public function getViewManager() {
		if (! isset ( $this->viewmanager )) {
			$options = $this->getOptions ();
			$options ['servicelocator'] = $this->getServiceLocator ();
			$options ['controllerdata'] = $this->getControllers ()->getController ();
			$this->viewmanager = new ViewManager ( $options );
		}
		return $this->viewmanager;
	}
	
	/**
	 * Get Module Manager
	 *
	 * @return array module.config
	 *        
	 */
	public function getModuleManager() {
		$router = array (
				'router' => $this->getRouter ()->getStructure () 
		);
		$this->modulemanager = array_merge ( $this->modulemanager, $router );
		
		$controllers = array (
				'controllers' => $this->getControllers ()->getStructure () 
		);
		$this->modulemanager = array_merge ( $this->modulemanager, $controllers );
		
		$viewmanager = array (
				'view_manager' => $this->getViewManager ()->getStructure () 
		);
		$this->modulemanager = array_merge ( $this->modulemanager, $viewmanager );
		return $this->modulemanager;
	}
	
	/**
	 * Get Classmap Autoloader
	 *
	 * @param
	 *        	$classmapautoloader
	 *        	
	 */
	public function getControllerConfig() {
		$controllers = array (
				'controllers' => $this->getControllers ()->getStructure () 
		);
		return $controllers;
	}
	
	/**
	 * Get Classmap Autoloader
	 *
	 * @param
	 *        	$classmapautoloader
	 *        	
	 */
	public function getClassMapAutoloader() {
		return $this->getControllers ()->getClassMapAutoloader ();
	}
	
	/**
	 * Reset CSS
	 *
	 * @void
	 */
	public function resetCSS() {
		$Session = $this->getSession ();
		$Container = $Session->getContainer ( 'Template' );
		if ($Container->offsetExists ( 'CSS' )) {
			$Container->offsetUnset ( 'CSS' );
		}
	}
	
	/**
	 * Add CSS
	 *
	 * @void
	 */
	public function addCSS($data, $for = null) {
		$Session = $this->getSession ();
		$Container = $Session->getContainer ( 'Template' );
		
		$css = array ();
		if ($Container->offsetExists ( 'CSS' )) {
			$css = $Container->offsetGet ( 'CSS' );
		}
		if (is_string ( $data )) {
			$data = array (
					$data 
			);
		}
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $path ) {
				if (! empty ( $for )) {
					$css ['vendor/Techfever/Javascript/' . $for . '/themes/' . $path] = True;
				} else if (! empty ( $path )) {
					$css [$path] = True;
				}
			}
		}
		$Container->offsetSet ( 'CSS', (is_array ( $css ) && count ( $css ) > 0 ? $css : null) );
	}
	
	/**
	 * Reset Javascript
	 *
	 * @void
	 */
	public function resetJavascript() {
		$Session = $this->getSession ();
		$Container = $Session->getContainer ( 'Template' );
		if ($Container->offsetExists ( 'Javascript' )) {
			$Container->offsetUnset ( 'Javascript' );
		}
	}
	
	/**
	 * Add Javascript
	 *
	 * @void
	 */
	public function addJavascript($data, $parameter = null) {
		$Session = $this->getSession ();
		$Container = $Session->getContainer ( 'Template' );
		
		$javascript = array ();
		if ($Container->offsetExists ( 'Javascript' )) {
			$javascript = $Container->offsetGet ( 'Javascript' );
		}
		if (is_string ( $data )) {
			$data = array (
					$data 
			);
		}
		if (is_array ( $data ) && count ( $data ) > 0) {
			foreach ( $data as $path ) {
				if (! empty ( $path )) {
					$javascript [$path] = True;
					if (! empty ( $parameter ) && is_array ( $parameter )) {
						$javascript [$path] = array (
								'parameter' => $parameter,
								'status' => True 
						);
					}
				}
			}
		}
		$Container->offsetSet ( 'Javascript', (is_array ( $javascript ) && count ( $javascript ) > 0 ? $javascript : null) );
	}
}
