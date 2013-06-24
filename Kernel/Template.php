<?php
namespace Kernel;

use Kernel\ServiceLocator;
use Kernel\Template\TemplateInterface;
use Kernel\Template\Module\Router;
use Kernel\Template\Module\Controllers;
use Kernel\Template\Module\ViewManager;

class Template extends TemplateInterface {

	/**
	 * @var Theme Developer
	 **/
	private $_developer = 'Techfever';

	/**
	 * @var Theme Developer
	 **/
	private $_theme = 'Default';

	/**
	 * @var Module Manager
	 **/
	private $_modulemanager = array(
		'router' => array(), 'view_manager' => array(), 'controllers' => array(),
	);

	/**
	 * @var Template Configuration
	 **/
	private $_configuration = array();

	/**
	 * @var Router Configuration
	 **/
	private $router = null;

	/**
	 * @var View Manager Configuration
	 **/
	private $viewmanager = null;

	/**
	 * @var Controllers Configuration
	 **/
	private $controllers = null;

	/**
	 * Constructor
	 *
	 * @param  null|array $config
	 **/
	public function __construct() {
	}

	/**
	 * Prepare Template Config
	 * 
	 * @return void
	 **/
	public function prepare() {
		$dbconfig = array(
			'theme' => array()
		);

		/* Get Db theme */
		$DbTheme = new Database('select');
		$DbTheme->columns(array(
					'name' => 'theme_name', 'developer' => 'theme_developer', 'key' => 'theme_key', 'doctype' => 'theme_doctype'
				));
		$DbTheme->from(array(
					't' => 'theme'
				));
		$DbTheme->join(array(
					'ss' => 'system_configuration'
				), 't.theme_key = ss.system_configuration_value', array(
					'system_key' => 'system_configuration_key'
				));
		$DbTheme->where(array(
					'ss.system_configuration_key' => 'system_theme',
				));
		$DbTheme->limit(1);
		$DbTheme->setCacheName('theme_configuration');
		$DbTheme->execute();
		if ($DbTheme->hasResult()) {
			$result = $DbTheme->toArray();
			$dbconfig['theme'] = $result[0];
		}
		if (is_array($dbconfig)) {
			$this->_configuration = $dbconfig;
			$this->_templatedefault = $dbconfig['theme']['key'];
			$this->_theme = $dbconfig['theme']['name'];
			$this->_developer = $dbconfig['theme']['developer'];
		}

		$configuration = array_merge($this->_modulemanager, $this->_configuration);
		$this->router = new Router($configuration);
		$this->controllers = new Controllers($configuration);
		$this->viewmanager = new ViewManager($configuration, $this->controllers->getMethod());
	}

	/**
	 * Get Theme Default
	 * 
	 * @return string
	 **/
	public function getTheme() {
		return $this->_templatedefault;
	}

	/**
	 * Get Theme Name
	 * 
	 * @return string
	 **/
	public function getThemeName() {
		return $this->_theme;
	}

	/**
	 * Get Theme Developer
	 * 
	 * @return string
	 **/
	public function getDeveloper() {
		return $this->_developer;
	}

	/**
	 * Get Module Manager Config
	 * 
	 * @return array module.config
	 **/
	public function getConfig() {

		$router = array(
			'router' => $this->router->getConfig()
		);
		$this->_modulemanager = array_merge($this->_modulemanager, $router);

		$controllers = array(
			'controllers' => $this->controllers->getConfig()
		);
		$this->_modulemanager = array_merge($this->_modulemanager, $controllers);

		$viewmanager = array(
			'view_manager' => $this->viewmanager->getConfig()
		);
		$this->_modulemanager = array_merge($this->_modulemanager, $viewmanager);
		//print_r($this->_modulemanager);
		return $this->_modulemanager;
	}

	/**
	 * Reseet
	 * 
	 * @void
	 **/
	public function reset() {
		ServiceLocator::setServiceConfig($this->getConfig());
	}
}
