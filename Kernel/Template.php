<?php
namespace Kernel;

use Zend\ServiceManager\ServiceManager;

class Template {

	/**
	 * @var View Manager - Template Path
	 */
	private static $_templatePath = 'Techfever/Theme/';

	/**
	 * @var View Manager - Template Default
	 */
	private static $_templatedefault = 'Default';

	/**
	 * @var View Manager - Template Path
	 */
	private static $_templatemap = array(
		'layout/layout' => 'Default/main.phtml', 'error/404' => 'Default/Error/404.phtml', 'error/index' => 'Default/Error/index.phtml'
	);

	/**
	 * @var View Manager - Template Path Stack
	 */
	private static $_templatepathstack = array(
		'../View',
	);

	/**
	 * @var View Manager - Default Templates Suffix
	 */
	private static $_defaulttemplatesuffix = 'phtml';

	/**
	 * @var View Manager - Layout
	 */
	private static $_layout = 'Techfever/Theme';

	/**
	 * @var View Manager - Display Exceptions
	 */
	private static $_displayexceptions = True;

	/**
	 * @var View Manager - Exception Template
	 */
	private static $_exceptiontemplate = 'error/index';

	/**
	 * @var View Manager - Display Not Found Reason
	 */
	private static $_displaynotfoundreason = True;

	/**
	 * @var View Manager - Not Found template
	 */
	private static $_notfoundtemplate = 'error/404';

	/**
	 * @var View Manager - Doctype
	 */
	private static $_doctype = 'HTML5';

	/**
	 * @var View Manager
	 */
	private static $_viewmanager = array(
		'view_manager' => array(
			'display_not_found_reason', 'display_exceptions', 'doctype', 'not_found_template', 'exception_template', 'template_map', 'template_path_stack', 'default_template_suffix'
		)
	);

	/**
	 * @var Controller - Class
	 */
	private static $_invokables = array(
		'Module\Controller\Action' => 'Module\Controller\ActionController'
	);

	/**
	 * @var Controller
	 */
	private static $_controllers = array(
		'controllers' => array(
			'invokables' => array()
		)
	);

	/**
	 * @var View Manager
	 */
	private static $_router = array(
		'router' => array(
			'routes' => array()
		)
	);

	/**
	 * @var View Manager
	 */
	private static $_modulemanager = array(
		'controllers' => array(), 'router' => array(), 'view_manager' => array()
	);

	public static function prepare($config = null) {

		self::$_modulemanager = $config;
		self::$_templatePath = CORE_PATH . '/Vendor/' . self::$_templatePath;
		self::$_templatePath = self::$_templatePath . self::$_templatedefault;

		self::$_templatemap = array(
			'layout/layout' => self::$_templatePath . '/layout.phtml', 'error/404' => self::$_templatePath . '/Error/404.phtml', 'error/index' => self::$_templatePath . '/Error/index.phtml'
		);

		self::$_templatepathstack = array(
			CORE_PATH . '/Kernel/Module/View'
		);
	}

	public static function getConfig() {
		return self::getModuleManager();
	}

	public static function getModuleManager() {
		$viewmanager = self::getViewManager();
		self::$_modulemanager = array_merge(self::$_modulemanager, $viewmanager);

		$controllers = self::getControllers();
		self::$_modulemanager = array_merge(self::$_modulemanager, $controllers);

		//$router = self::getRouter();
		//self::$_modulemanager = array_merge(self::$_modulemanager, $router);

		return self::$_modulemanager;
	}

	public static function getRouter() {
		self::$_router = array(
			'router' => array(
				'routes' => array()
			)
		);
		return self::$_router;
	}

	public static function getControllers() {
		self::$_controllers = array(
			'controllers' => array(
				'invokables' => self::$_invokables
			)
		);
		return self::$_controllers;
	}

	public static function getViewManager() {
		self::$_viewmanager = array(
				'view_manager' => array(
						'display_not_found_reason' => self::$_displaynotfoundreason,
						'display_exceptions' => self::$_displayexceptions,
						'doctype' => self::$_doctype,
						'not_found_template' => self::$_notfoundtemplate,
						'exception_template' => self::$_exceptiontemplate,
						'template_map' => self::$_templatemap,
						'template_path_stack' => self::$_templatepathstack
				)
		);
		return self::$_viewmanager;
	}

	public static function getDoctype() {
		if (!self::checkDoctype(self::$_doctype)) {
			self::$_doctype = 'HTML5';
		}
		return self::$_doctype;
	}

	public static function setDoctype($value) {
		if (!self::checkDoctype($value)) {
			self::$_doctype = 'HTML5';
		} else {
			self::$_doctype = strtoupper($value);
		}
	}

	public static function checkDoctype($value) {
		$doctype_array = array(
			'XHTML1_STRICT', 'XHTML1_TRANSITIONAL', 'XHTML1_FRAMESET', 'XHTML1_RDFA', 'XHTML1_RDFA11', 'XHTML_BASIC1', 'XHTML5', 'HTML4_STRICT', 'HTML4_LOOSE', 'HTML4_FRAMESET', 'HTML5', 'CUSTOM_XHTML', 'CUSTOM'
		);
		if (in_array(strtoupper($value), $doctype_array)) {
			return true;
		}
		return false;
	}
}
