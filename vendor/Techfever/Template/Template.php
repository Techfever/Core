<?php
namespace Techfever\Template;

use Techfever\Exception;
use Techfever\Database\Database;
use Techfever\Session\Session;
use Techfever\Template\Module\Router;
use Techfever\Template\Module\Controller;
use Techfever\Template\Module\ViewManager;

class Template {
	/**
	 * @var Database\Database
	 */
	private $database = null;
	/**
	 * @var Session\Session
	 */
	private $session = null;

	/**
	 * @var Module Manager
	 **/
	private $modulemanager = array(
			'router' => array(),
			'view_manager' => array(),
			'controllers' => array(),
	);

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
	 * @var Config
	 **/
	private $config = null;

	public function __construct(Database $database, Session $session) {
		$this->database = $database;
		$this->session = $session;
		$this->getConfig();
	}

	/**
	 * getDatabase()
	 *
	 * @throws Exception\RuntimeException
	 * @return Database\Database
	 */
	public function getDatabase() {
		if ($this->database == null) {
			throw new Exception\RuntimeException('Database has not been set or configured.');
		}
		return clone $this->database;
	}

	/**
	 * getSession()
	 *
	 * @throws Exception\RuntimeException
	 * @return Session\Session
	 */
	public function getSession() {
		if ($this->session == null) {
			throw new Exception\RuntimeException('Session has not been set or configured.');
		}
		return $this->session;
	}

	/**
	 * Get Template Config
	 * 
	 * @return void
	 **/
	public function getConfig() {
		if (!is_array($this->config) || count($this->config) < 1) {
			$Session = $this->getSession();
			$Container = $Session->getContainer('Template');
			$dbconfig = array();
			if ($Container->offsetExists('Config')) {
				$dbconfig = $Container->offsetGet('Config');
				$this->_config = $dbconfig;
			} else {
				$QSystem = $this->getDatabase();
				$QSystem->select();
				$QSystem->columns(array(
								'key' => 'system_configuration_key',
								'value' => 'system_configuration_value',
						));
				$QSystem->from(array(
								'ss' => 'system_configuration'
						));
				$QSystem->setCacheName('system_configuration');
				$QSystem->execute();
				if ($QSystem->hasResult()) {
					while ($QSystem->valid()) {
						$rawdata = $QSystem->current();
						$dbconfig[strtoupper($rawdata['key'])] = (strlen($rawdata['value']) > 0 ? $rawdata['value'] : null);
						$QSystem->next();
					}
				}
				if (is_array($dbconfig) && array_key_exists('SYSTEM_THEME', $dbconfig)) {
					$QTheme = $this->getDatabase();
					$QTheme->select();
					$QTheme->columns(array(
									'*',
							));
					$QTheme->from(array(
									't' => 'theme'
							));
					$QTheme->where(array(
									't.theme_key = "' . $dbconfig['SYSTEM_THEME'] . '"',
							));
					$QTheme->limit(1);
					$QTheme->setCacheName('theme');
					$QTheme->execute();
					if ($QTheme->hasResult()) {
						$result = $QTheme->current();
						foreach ($result as $key => $value) {
							$dbconfig[strtoupper($key)] = $value;
						}
					}
				}
				$this->_config = $dbconfig;
				$Container->offsetSet('Config', $this->_config);
			}
		}
		foreach ($this->_config as $key => $value) {
			if (!defined(strtoupper($key))) {
				define(strtoupper($key), $value);
			}
		}
		return $this->_config;
	}

	/**
	 * Get Router
	 * 
	 * @return $router
	 **/
	public function getRouter() {
		if (!isset($this->router)) {
			$this->router = new Router($this->getDatabase());
		}
		return $this->router;
	}

	/**
	 * Get Controllers
	 * 
	 * @return $controllers
	 **/
	public function getControllers() {
		if (!isset($this->controllers)) {
			$this->controllers = new Controller($this->getDatabase());
		}
		return $this->controllers;
	}

	/**
	 * Get View Manager
	 * 
	 * @return $viewmanager
	 **/
	public function getViewManager() {
		if (!isset($this->viewmanager)) {
			$this->viewmanager = new ViewManager($this->getConfig(), $this->getControllers()->getController());
		}
		return $this->viewmanager;
	}

	/**
	 * Get Module Manager
	 * 
	 * @return array module.config
	 **/
	public function getModuleManager() {
		$router = array(
				'router' => $this->getRouter()->getStructure()
		);
		$this->modulemanager = array_merge($this->modulemanager, $router);

		$controllers = array(
				'controllers' => $this->getControllers()->getStructure()
		);
		$this->modulemanager = array_merge($this->modulemanager, $controllers);

		$viewmanager = array(
				'view_manager' => $this->getViewManager()->getStructure()
		);
		$this->modulemanager = array_merge($this->modulemanager, $viewmanager);
		//print_r($this->modulemanager);
		//die();
		return $this->modulemanager;
	}

	/**
	 * Get Classmap Autoloader
	 *
	 * @param  $classmapautoloader
	 **/
	public function getControllerConfig() {
		$controllers = array(
				'controllers' => $this->getControllers()->getStructure()
		);
		return $controllers;
	}

	/**
	 * Get Classmap Autoloader
	 *
	 * @param  $classmapautoloader
	 **/
	public function getClassMapAutoloader() {
		return $this->getControllers()->getClassMapAutoloader();
	}

	/**
	 * Reset CSS
	 * 
	 * @void
	 **/
	public function resetCSS() {
		$Session = $this->getSession();
		$Container = $Session->getContainer('Template');
		if ($Container->offsetExists('CSS')) {
			$Container->offsetUnset('CSS');
		}
	}

	/**
	 * Add CSS
	 * 
	 * @void
	 **/
	public function addCSS($data, $for = null) {
		$Session = $this->getSession();
		$Container = $Session->getContainer('Template');

		$css = array();
		if ($Container->offsetExists('CSS')) {
			$css = $Container->offsetGet('CSS');
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
							$css['vendor/Techfever/Javascript/jquery/themes/' . $path] = True;
							break;
					}
				} else if (!empty($path)) {
					$css[$path] = True;
				}
			}
		}
		$Container->offsetSet('CSS', (is_array($css) && count($css) > 0 ? $css : null));
	}

	/**
	 * Reset Javascript
	 * 
	 * @void
	 **/
	public function resetJavascript() {
		$Session = $this->getSession();
		$Container = $Session->getContainer('Template');
		if ($Container->offsetExists('Javascript')) {
			$Container->offsetUnset('Javascript');
		}
	}

	/**
	 * Add Javascript
	 * 
	 * @void
	 **/
	public function addJavascript($data, $parameter = null) {
		$Session = $this->getSession();
		$Container = $Session->getContainer('Template');

		$javascript = array();
		if ($Container->offsetExists('Javascript')) {
			$javascript = $Container->offsetGet('Javascript');
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
					if (!empty($parameter) && is_array($parameter)) {
						$javascript[$path] = array(
								'parameter' => $parameter,
								'status' => True
						);
					}
				}
			}
		}
		$Container->offsetSet('Javascript', (is_array($javascript) && count($javascript) > 0 ? $javascript : null));
	}
}
