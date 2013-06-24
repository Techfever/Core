<?php
namespace Kernel\Template\Module;

use Kernel\Exception;

class ViewManager implements ModuleInterface {

	/**
	 * @var Template Path
	 **/
	private $_templatePath = 'Techfever/Theme/';

	/**
	 * @var Template Default
	 **/
	private $_templatedefault = 'Default';

	/**
	 * @var Template Path
	 **/
	private $_templatemap = array();

	/**
	 * @var Template Path Stack
	 **/
	private $_templatepathstack = array();

	/**
	 * @var Default Templates Suffix
	 **/
	private $_defaulttemplatesuffix = 'phtml';

	/**
	 * @var Layout
	 **/
	private $_layout = 'frontend/layout';

	/**
	 * @var Display Exceptions
	 **/
	private $_displayexceptions = True;

	/**
	 * @var Exception Template
	 **/
	private $_exceptiontemplate = 'error/index';

	/**
	 * @var Display Not Found Reason
	 **/
	private $_displaynotfoundreason = True;

	/**
	 * @var Not Found template
	 **/
	private $_notfoundtemplate = 'error/404';

	/**
	 * @var Doctype
	 **/
	private $_doctype = 'HTML5';

	/**
	 * @var Structure
	 **/
	private $_structure = array(
		'display_not_found_reason', 'display_exceptions', 'doctype', 'not_found_template', 'exception_template', 'template_map' => array(), 'template_path_stack' => array(), 'default_template_suffix'
	);

	/**
	 * @var Config
	 **/
	private $_config = array();

	/**
	 * @var Default Config
	 **/
	private $_defaultconfig = array();

	/**
	 * @var Controllers Config
	 **/
	private $_controllers = array();

	/**
	 * Constructor
	 *
	 * @param  null|array $config
	 **/
	public function __construct($config = null, $controllers = null) {
		$this->_defaultconfig = $config;
		$this->_controllers = $controllers;
		$themeconfig = $this->_defaultconfig['theme'];
		if (is_array($themeconfig) && array_key_exists('key', $themeconfig)) {
			$this->_templatedefault = $themeconfig['key'];
		}
		$this->_developer = $themeconfig['developer'];
		$this->_theme = $themeconfig['name'];
		$this->setDoctype($themeconfig['doctype']);

		$this->_templatepathstack = array(
			'Index' => CORE_PATH . '/Module/Index/View'
		);

		$this->generateTemplateMap();
	}

	/**
	 * Get Default Config
	 *
	 * @return array
	 */
	public function getDefaultConfig() {
		return $this->_defaultconfig;
	}

	/**
	 * Get Config
	 *
	 * @return array
	 */
	public function getConfig() {
		$this->_config = array(
				'developer' => $this->_developer,
				'theme' => $this->_theme,
				'not_found_template' => $this->_notfoundtemplate,
				'display_not_found_reason' => $this->_displaynotfoundreason,
				'exception_template' => $this->_exceptiontemplate,
				'display_exceptions' => $this->_displayexceptions,
				'layout' => $this->_layout,
				'doctype' => $this->_doctype,
				'template_map' => $this->_templatemap,
				'template_path_stack' => $this->_templatepathstack
		);
		return $this->_config;
	}

	/**
	 * Get Structure
	 *
	 * @return array
	 */
	public function getStructure() {
		return $this->_structure;
	}

	/**
	 * Set Doctype
	 * 
	 * @return string doctype
	 **/
	public function getDoctype() {
		if (!$this->checkDoctype($this->_doctype)) {
			$this->_doctype = 'HTML5';
		}
		return $this->_doctype;
	}

	/**
	 * Set Doctype
	 * 
	 * @param  string $value one of 'XHTML1_STRICT', 'XHTML1_TRANSITIONAL', 'XHTML1_FRAMESET', 'XHTML1_RDFA', 'XHTML1_RDFA11', 'XHTML_BASIC1', 'XHTML5', 'HTML4_STRICT', 'HTML4_LOOSE', 'HTML4_FRAMESET', 'HTML5', 'CUSTOM_XHTML', 'CUSTOM'
	 * @return string doctype
	 **/
	public function setDoctype($value) {
		if (!$this->checkDoctype($value)) {
			$this->_doctype = 'HTML5';
		} else {
			$this->_doctype = strtoupper($value);
		}
	}

	/**
	 * Check Doctype
	 * 
	 * @param  string $value one of 'XHTML1_STRICT', 'XHTML1_TRANSITIONAL', 'XHTML1_FRAMESET', 'XHTML1_RDFA', 'XHTML1_RDFA11', 'XHTML_BASIC1', 'XHTML5', 'HTML4_STRICT', 'HTML4_LOOSE', 'HTML4_FRAMESET', 'HTML5', 'CUSTOM_XHTML', 'CUSTOM'
	 * @return boolean
	 **/
	public function checkDoctype($value) {
		$doctype_array = array(
			'XHTML1_STRICT', 'XHTML1_TRANSITIONAL', 'XHTML1_FRAMESET', 'XHTML1_RDFA', 'XHTML1_RDFA11', 'XHTML_BASIC1', 'XHTML5', 'HTML4_STRICT', 'HTML4_LOOSE', 'HTML4_FRAMESET', 'HTML5', 'CUSTOM_XHTML', 'CUSTOM'
		);
		if (in_array(strtoupper($value), $doctype_array)) {
			return true;
		}
		return false;
	}

	/**
	 * Get Layout
	 * 
	 * @param  string $key
	 * @return string/boolean template map
	 **/
	public function getLayout($key = null) {
		if (is_array($this->_templatemap) && count($this->_templatemap) > 0) {
			if (!empty($key)) {
				return $this->_templatemap[$key];
			}
			return $this->_templatemap;
		}
		return False;
	}

	/**
	 * Set Layout
	 * 
	 * @param  string $layout
	 * @param  string $path		Path to layout
	 * @throws Exception
	 **/
	public function setLayout($layout, $path) {
		if (!file_exists($path)) {
			throw new Exception\RuntimeException('$path must be exist');
		}
		if (!is_string($layout)) {
			throw new Exception\UnexpectedValueException('$layout must be a string');
		}
		$this->_templatemap[$layout] = $path;
	}

	/**
	 * Set Default Layout
	 * 
	 * @param  string $layout
	 * @throws Exception
	 **/
	public function setDefaultLayout($layout) {
		if (!is_string($layout)) {
			throw new Exception\UnexpectedValueException('$layout must be a string');
		}
		if (array_key_exists($layout, $this->_templatemap)) {
			$this->_layout = $layout;
		} else {
			throw new Exception\UnexpectedValueException('$layout must be exist in templatemap by setLayout function');
		}
	}

	/**
	 * Generate Template Map
	 *
	 * @return string
	 **/
	public function generateTemplateMap() {
		$class = $this->_controllers;
		$map = array();
		$themelocation = CORE_PATH . '/Vendor/';
		if (file_exists($themelocation . $this->_templatePath . $this->_templatedefault . '/layout.phtml')) {
			$map[$this->_layout] = $themelocation . $this->_templatePath . $this->_templatedefault . '/layout.phtml';
			if (file_exists($themelocation . $this->_templatePath . $this->_templatedefault . '/Error/404.phtml')) {
				$map[$this->_notfoundtemplate] = $themelocation . $this->_templatePath . $this->_templatedefault . '/Error/404.phtml';
			}
			if (file_exists($themelocation . $this->_templatePath . $this->_templatedefault . '/Error/index.phtml')) {
				$map[$this->_exceptiontemplate] = $themelocation . $this->_templatePath . $this->_templatedefault . '/Error/index.phtml';
			}
			if (file_exists($themelocation . $this->_templatePath . $this->_templatedefault . '/topnavigator.phtml')) {
				$map['navigator/layout'] = $themelocation . $this->_templatePath . $this->_templatedefault . '/topnavigator.phtml';
			}
			if (file_exists($themelocation . $this->_templatePath . $this->_templatedefault . '/breadcrumb.phtml')) {
				$map['breadcrumb/layout'] = $themelocation . $this->_templatePath . $this->_templatedefault . '/breadcrumb.phtml';
			}
		}
		$map['blank/layout'] = CORE_PATH . '/Vendor/Techfever/Theme/Blank.phtml';
		foreach ($class as $classkey => $classvalue) {
			$alias = explode('\\', $classkey);
			$module = $alias['0'];
			$class = $alias['2'];
			preg_match_all('/[A-Z]/', substr($class, 1, strlen($class)), $classmatch, PREG_OFFSET_CAPTURE);
			if (is_array($classmatch[0]) && count($classmatch[0]) > 0) {
				$startlen = 0;
				$endlen = 0;
				foreach ($classmatch[0] as $classmatchpos) {
					$class = strtolower(str_replace($classmatchpos[0], '-' . $classmatchpos[0], $class));
				}
			}
			$class = strtolower($class);
			$dir = CORE_PATH . '/' . $classvalue['path'] . '/View/';
			$dh = opendir($dir);
			while (false !== ($filename = readdir($dh))) {
				$fileinfo = pathinfo($dir . $filename);
				if ($fileinfo['extension'] == 'phtml') {
					$method = $fileinfo['filename'];
					$map[strtolower($module . '/' . $class . '/' . $method)] = CORE_PATH . '/' . $classvalue['path'] . '/View/' . $fileinfo['basename'];
				}
			}
		}
		$this->_templatemap = $map;
	}
}
