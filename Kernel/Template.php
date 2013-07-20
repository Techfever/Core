<?php
namespace Kernel;

use Kernel\ServiceLocator;
use Kernel\Template\TemplateInterface;
use Kernel\Template\Module\Router;
use Kernel\Template\Module\Controllers;
use Kernel\Template\Module\ViewManager;
use Zend\Session\Container as SessionContainer;

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
	 * @var Container
	 **/
	private $_container = null;

	/**
	 * Constructor
	 *
	 * @param  null|array $config
	 **/
	public function __construct() {
		$this->_container = new SessionContainer('Template');
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

	/**
	 * Add CSS
	 * 
	 * @void
	 **/
	public function addCSS($data, $for = null) {
		$this->_container = new SessionContainer('Template');
		$css = array();
		if ($this->_container->offsetExists('CSS')) {
			$css = $this->_container->offsetGet('CSS');
		}
		if (is_string($data)) {
			$data = array(
				$data
			);
		}
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $path) {
				if (!empty($for)) {
					switch ($for) {
						case 'jquery':
							$css['Vendor/Techfever/Javascript/jquery/themes/' . $path] = True;
							break;
					}
				} else if (!empty($path)) {
					$css[$path] = True;
				}
			}
		}
		$this->_container->offsetSet('CSS', (is_array($css) && count($css) > 0 ? $css : null));
	}

	/**
	 * Add Javascript
	 * 
	 * @void
	 **/
	public function addJavascript($data) {
		$this->_container = new SessionContainer('Template');
		$javascript = array();
		if ($this->_container->offsetExists('Javascript')) {
			$javascript = $this->_container->offsetGet('Javascript');
		}
		if (is_string($data)) {
			$data = array(
				$data
			);
		}
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $path) {
				if (!empty($path)) {
					$javascript[$path] = True;
				}
			}
		}
		$this->_container->offsetSet('Javascript', (is_array($javascript) && count($javascript) > 0 ? $javascript : null));
	}
}
