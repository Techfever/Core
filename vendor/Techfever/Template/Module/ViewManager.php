<?php

namespace Techfever\Template\Module;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;
use Techfever\Functions\DirConvert;
use Techfever\Template\Plugin\Filters\ToForwardSlash;

class ViewManager {
	
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
	 * @var Config
	 *
	 */
	private $config = array ();
	
	/**
	 *
	 * @var Controller
	 *
	 */
	private $controller = array ();
	
	/**
	 *
	 * @var Developer
	 *
	 */
	private $developer = 'Techfever';
	
	/**
	 *
	 * @var Theme
	 *
	 */
	private $theme = 'Default';
	
	/**
	 *
	 * @var Not Found template
	 *     
	 */
	private $notfoundtemplate = 'error/404';
	
	/**
	 *
	 * @var Display Not Found Reason
	 *     
	 */
	private $displaynotfoundreason = True;
	
	/**
	 *
	 * @var Display Exceptions
	 *     
	 */
	private $displayexceptions = True;
	
	/**
	 *
	 * @var Exception Template
	 *     
	 */
	private $exceptiontemplate = 'error/index';
	
	/**
	 *
	 * @var Layout
	 *
	 */
	private $layout = 'layout/layout';
	
	/**
	 *
	 * @var Doctype
	 *
	 */
	private $doctype = 'HTML5';
	
	/**
	 *
	 * @var Template Path Stack
	 *     
	 */
	private $templatepathstack = array ();
	
	/**
	 *
	 * @var Template Path Stack
	 *     
	 */
	private $templatemap = array ();
	
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
		$this->generalobject = new GeneralBase ( $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		$this->setOptions ( $options );
		
		$this->config = $this->getOption ( 'config' );
		$this->controllers = $this->getOption ( 'controllerdata' );
		$this->theme = $this->getConfig ( 'THEME_KEY' );
		$this->developer = $this->getConfig ( 'THEME_DEVELOPER' );
		if (array_key_exists ( 'notfoundtemplate', $this->config )) {
			$this->setNotFoundTemplate ( $this->config ['notfoundtemplate'] );
		}
		if (array_key_exists ( 'displaynotfoundreason', $this->config )) {
			$this->setDisplayNotFoundReason ( $this->config ['displaynotfoundreason'] );
		}
		if (array_key_exists ( 'displayexceptions', $this->config )) {
			$this->setDisplayExceptions ( $this->config ['displayexceptions'] );
		}
		if (array_key_exists ( 'exceptiontemplate', $this->config )) {
			$this->setExceptionTemplate ( $this->config ['exceptiontemplate'] );
		}
		if (array_key_exists ( 'themedoctype', $this->config )) {
			$this->setDoctype ( $this->config ['themedoctype'] );
		}
		
		$this->templatepathstack = array (
				'Index' => CORE_PATH . '/module/Index/View' 
		);
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
	 * getConfig()
	 *
	 * @throws Exception\RuntimeException
	 * @return Config
	 */
	public function getConfig($key = null) {
		if ($this->config == null) {
			throw new Exception\RuntimeException ( 'Config has not been set or configured.' );
		}
		if (! empty ( $key )) {
			if (array_key_exists ( $key, $this->config )) {
				return $this->config [$key];
			} else {
				return null;
			}
		}
		return $this->config;
	}
	
	/**
	 * getControllers()
	 *
	 * @throws Exception\RuntimeException
	 * @return Controllers
	 */
	public function getControllers() {
		if ($this->controllers == null) {
			throw new Exception\RuntimeException ( 'Controllers has not been set or configured.' );
		}
		return $this->controllers;
	}
	
	/**
	 * Get Theme
	 *
	 * @return string theme
	 *        
	 */
	public function getTheme() {
		if (! isset ( $this->theme )) {
			$this->theme = $this->getConfig ( 'theme_key' );
		}
		return $this->theme;
	}
	
	/**
	 * Get Developer
	 *
	 * @return string developer
	 *        
	 */
	public function getDeveloper() {
		if (! isset ( $this->developer )) {
			$this->developer = $this->getConfig ( 'theme_developer' );
		}
		return $this->developer;
	}
	
	/**
	 * Set Not Found Template
	 *
	 * @return void
	 *
	 */
	public function setNotFoundTemplate($template) {
		$this->notfoundtemplate = $template;
	}
	
	/**
	 * Get Not Found Template
	 *
	 * @return string notfoundtemplate
	 *        
	 */
	public function getNotFoundTemplate() {
		return $this->notfoundtemplate;
	}
	
	/**
	 * Set Not Found Reason
	 *
	 * @return void
	 *
	 */
	public function setDisplayNotFoundReason($status) {
		$this->displaynotfoundreason = $status;
	}
	
	/**
	 * Get Not Found Reason
	 *
	 * @return string displaynotfoundreason
	 *        
	 */
	public function getDisplayNotFoundReason() {
		return $this->displaynotfoundreason;
	}
	
	/**
	 * Set Display Exceptions
	 *
	 * @return void
	 *
	 */
	public function setDisplayExceptions($status) {
		$this->displayexceptions = $status;
	}
	
	/**
	 * Get Display Exceptions
	 *
	 * @return string displayexceptions
	 *        
	 */
	public function getDisplayExceptions() {
		return $this->displayexceptions;
	}
	
	/**
	 * Set Exception Template
	 *
	 * @return void
	 *
	 */
	public function setExceptionTemplate($status) {
		$this->exceptiontemplate = $status;
	}
	
	/**
	 * Get Exception Template
	 *
	 * @return string exceptiontemplate
	 *        
	 */
	public function getExceptionTemplate() {
		return $this->exceptiontemplate;
	}
	
	/**
	 * Get Doctype
	 *
	 * @return string doctype
	 *        
	 */
	public function getDoctype() {
		if (! $this->checkDoctype ( $this->doctype )) {
			$this->doctype = 'HTML5';
		}
		return $this->doctype;
	}
	
	/**
	 * Set Doctype
	 *
	 * @param string $value
	 *        	one of 'XHTML1_STRICT', 'XHTML1_TRANSITIONAL', 'XHTML1_FRAMESET', 'XHTML1_RDFA', 'XHTML1_RDFA11', 'XHTML_BASIC1', 'XHTML5', 'HTML4_STRICT', 'HTML4_LOOSE', 'HTML4_FRAMESET', 'HTML5', 'CUSTOM_XHTML', 'CUSTOM'
	 * @return string doctype
	 *        
	 */
	public function setDoctype($value) {
		if (! $this->checkDoctype ( $value )) {
			$this->doctype = 'HTML5';
		} else {
			$this->doctype = strtoupper ( $value );
		}
	}
	
	/**
	 * Check Doctype
	 *
	 * @param string $value
	 *        	one of 'XHTML1_STRICT', 'XHTML1_TRANSITIONAL', 'XHTML1_FRAMESET', 'XHTML1_RDFA', 'XHTML1_RDFA11', 'XHTML_BASIC1', 'XHTML5', 'HTML4_STRICT', 'HTML4_LOOSE', 'HTML4_FRAMESET', 'HTML5', 'CUSTOM_XHTML', 'CUSTOM'
	 * @return boolean
	 *
	 */
	public function checkDoctype($value) {
		$doctype_array = array (
				'XHTML1_STRICT',
				'XHTML1_TRANSITIONAL',
				'XHTML1_FRAMESET',
				'XHTML1_RDFA',
				'XHTML1_RDFA11',
				'XHTML_BASIC1',
				'XHTML5',
				'HTML4_STRICT',
				'HTML4_LOOSE',
				'HTML4_FRAMESET',
				'HTML5',
				'CUSTOM_XHTML',
				'CUSTOM' 
		);
		if (in_array ( strtoupper ( $value ), $doctype_array )) {
			return true;
		}
		return false;
	}
	
	/**
	 * Set Default Layout
	 *
	 * @param string $layout        	
	 * @throws Exception
	 *
	 */
	public function setDefaultLayout($layout) {
		if (! is_string ( $layout )) {
			throw new Exception\UnexpectedValueException ( '$layout must be a string' );
		}
		if (array_key_exists ( $layout, $this->templatemap )) {
			$this->layout = $layout;
		} else {
			throw new Exception\UnexpectedValueException ( '$layout must be exist in templatemap by setLayout function' );
		}
	}
	
	/**
	 * Get Default Layout
	 *
	 * @return string exceptiontemplate
	 *        
	 */
	public function getDefaultLayout() {
		return $this->layout;
	}
	
	/**
	 * Get Template Path Stack
	 *
	 * @return string templatepathstack
	 *        
	 */
	public function getTemplatePathStack() {
		return $this->templatepathstack;
	}
	
	/**
	 * Get Structure
	 *
	 * @return array
	 */
	public function getStructure() {
		if (! is_array ( $this->structure ) || count ( $this->structure ) < 1) {
			$structure = array (
					'developer' => $this->getDeveloper (),
					'theme' => $this->getDeveloper (),
					'not_found_template' => $this->getNotFoundTemplate (),
					'display_not_found_reason' => $this->getDisplayNotFoundReason (),
					'exception_template' => $this->getExceptionTemplate (),
					'display_exceptions' => $this->getDisplayExceptions (),
					'layout' => $this->getDefaultLayout (),
					'doctype' => $this->getDoctype (),
					'template_map' => $this->getTemplateMap (),
					'template_path_stack' => $this->getTemplatePathStack () 
			);
			$this->structure = $structure;
		}
		return $this->structure;
	}
	
	/**
	 * Get Template Map
	 *
	 * @return string
	 *
	 */
	public function getTemplateMap() {
		if (! is_array ( $this->templatemap ) || count ( $this->templatemap ) < 1) {
			$themedefault = $this->getTheme ();
			$themelocation = CORE_PATH . '/vendor/';
			$templatemap = array ();
			$layoutPath = new DirConvert ( $themelocation . 'Techfever/Theme/' . $themedefault . '/layout.phtml' );
			$layoutPath = $layoutPath->__toString ();
			if (file_exists ( $layoutPath )) {
				$templatemap [$this->layout] = $layoutPath;
				$error404Path = new DirConvert ( $themelocation . 'Techfever/Theme/' . $themedefault . '/Error/404.phtml' );
				$error404Path = $error404Path->__toString ();
				if (file_exists ( $error404Path )) {
					$templatemap [$this->getNotFoundTemplate ()] = $error404Path;
				}
				$errorindexPath = new DirConvert ( $themelocation . 'Techfever/Theme/' . $themedefault . '/Error/index.phtml' );
				$errorindexPath = $errorindexPath->__toString ();
				if (file_exists ( $errorindexPath )) {
					$templatemap [$this->getExceptionTemplate ()] = $errorindexPath;
				}
				$navigatorlayoutPath = new DirConvert ( $themelocation . 'Techfever/Theme/' . $themedefault . '/navigator.phtml' );
				$navigatorlayoutPath = $navigatorlayoutPath->__toString ();
				if (file_exists ( $navigatorlayoutPath )) {
					$templatemap ['navigator/layout'] = $navigatorlayoutPath;
				}
				$breadcrumblayoutPath = new DirConvert ( $themelocation . 'Techfever/Theme/' . $themedefault . '/breadcrumb.phtml' );
				$breadcrumblayoutPath = $breadcrumblayoutPath->__toString ();
				if (file_exists ( $breadcrumblayoutPath )) {
					$templatemap ['breadcrumb/layout'] = $breadcrumblayoutPath;
				}
			}
			$backendlayout = new DirConvert ( $themelocation . 'Techfever/Theme/Backend/layout.phtml' );
			$backendlayout = $backendlayout->__toString ();
			$templatemap ['backend/layout'] = $backendlayout;
			$blanklayout = new DirConvert ( $themelocation . 'Techfever/Theme/Blank.phtml' );
			$blanklayout = $blanklayout->__toString ();
			$templatemap ['blank/layout'] = $blanklayout;
			$templatemap = array_merge ( $templatemap, $this->getTemplateShareMap ( 'Share' ) );
			
			$ToForwardSlash = new ToForwardSlash ( '\\' );
			$controller = $this->getControllers ();
			if (is_array ( $controller ) && count ( $controller ) > 0) {
				foreach ( $controller as $value ) {
					$path = $value ['path'];
					$alias = $value ['alias'];
					$dir = new DirConvert ( CORE_PATH . '/' . $path . '/View/' );
					$dir = $dir->__toString ();
					if (file_exists ( $dir )) {
						$dh = opendir ( $dir );
						while ( false !== ($filename = readdir ( $dh )) ) {
							$fileinfo = pathinfo ( $dir . $filename );
							if ($fileinfo ['extension'] == 'phtml') {
								$method = $fileinfo ['filename'];
								
								$mapmodule = strtolower ( $ToForwardSlash->filter ( $alias . '\\' . $method ) );
								
								$modulepath = $path . '/View/' . $fileinfo ['basename'];
								
								$mopthememodulepath = $themelocation . 'Techfever/Theme/' . $themedefault . '/' . ucfirst ( $modulepath );
								$thememodulepath = new DirConvert ( $mopthememodulepath );
								$thememodulepath = $thememodulepath->__toString ();
								if (file_exists ( $thememodulepath )) {
									$configfile = $thememodulepath;
								} else {
									$mopmodulepath = CORE_PATH . '/' . $modulepath;
									$configfile = new DirConvert ( $mopmodulepath );
									$configfile = $configfile->__toString ();
								}
								
								$templatemap [$mapmodule] = $configfile;
							}
						}
					}
				}
			}
			ksort ( $templatemap );
			$this->templatemap = $templatemap;
		}
		return $this->templatemap;
	}
	
	/**
	 * Generate Share Map
	 *
	 * @return string
	 *
	 */
	public function getTemplateShareMap($path) {
		$DirConvert = new DirConvert ( CORE_PATH . '/module/' . $path . '/' );
		$dir = $DirConvert->__toString ();
		$data = array ();
		if (file_exists ( $dir )) {
			$dh = opendir ( $dir );
			while ( false !== ($filename = readdir ( $dh )) ) {
				$fileinfo = pathinfo ( $dir . $filename );
				if ($fileinfo ['basename'] != '.' && $fileinfo ['basename'] != '..' && is_dir ( $dir . $fileinfo ['basename'] )) {
					$datachild = $this->getTemplateShareMap ( $path . '/' . $fileinfo ['basename'] );
					$data = array_merge ( $data, $datachild );
				} elseif ($fileinfo ['extension'] == 'phtml') {
					$templateMap = $path . '/' . $fileinfo ['filename'];
					$filepath = new DirConvert ( $fileinfo ['dirname'] . '/' . $fileinfo ['basename'] );
					$data [strtolower ( $templateMap )] = $filepath->__toString ();
				}
			}
		}
		return $data;
	}
}
