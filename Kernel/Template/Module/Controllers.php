<?php
namespace Kernel\Template\Module;

use Kernel\Database;
use Kernel\Exception;

class Controllers implements ModuleInterface {

	/**
	 * @var Config
	 **/
	private $_controllers = array();

	/**
	 * @var Config
	 **/
	private $_method = array();

	/**
	 * @var Structure
	 **/
	private $_structure = array(
			'services' => array(),
			'invokables' => array(),
			'factories' => array(),
			'abstract_factories' => array(),
			'initializers' => array(),
	);

	/**
	 * @var Config
	 **/
	private $_config = array();

	/**
	 * @var Config
	 **/
	private $_dbconfig = array();

	/**
	 * @var Default Config
	 **/
	private $_defaultconfig = array();

	/**
	 * Constructor
	 *
	 * @param  null|array $config
	 **/
	public function __construct($config = null) {
		$this->_defaultconfig = $config;
		$this->_controllers = $this->_structure;

		foreach ($this->getDbConfig() as $controllers) {
			$this->setController($controllers['config'], $controllers['alias'], $controllers['class'], $controllers['path'], $controllers['file']);
		}
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
		$this->_config = $this->_controllers;
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
	 * Get Controller
	 * 
	 * @param  string $config one of services, invokables, factories, abstract_factories, initializers
	 * @param  string $key
	 **/
	public function getController($config = null, $key = null) {
		if (is_array($this->_controllers) && count($this->_controllers) > 0) {
			if (!empty($config)) {
				if (!empty($key)) {
					return $this->_controllers[$config][$key];
				}
				return $this->_controllers[$config];
			}
			return $this->_controllers;
		}
		return False;
	}

	/**
	 * Get Controller
	 * 
	 * @param  class $class
	 * @param  string $key
	 **/
	public function getMethod($class = null) {
		if (is_array($this->_method) && array_key_exists($class, $this->_method)) {
			return $this->_method[$class];
		}
		return $this->_method;
	}

	/**
	 * Set Controller
	 * 
	 * @param  string $config one of services, invokables, factories, abstract_factories, initializers
	 * @param  string $alias
	 * @param  class $class
	 * @throws Exception
	 **/
	public function setController($config, $alias, $class, $path, $file) {
		if (!is_string($alias)) {
			throw new Exception\UnexpectedValueException('$alias must be a string');
		}
		if (!class_exists($class)) {
			throw new Exception\RuntimeException(sprintf('Unable to locate "%s"; class does not exist', $class));
		}
		if (!array_key_exists($config, $this->_controllers)) {
			throw new Exception\RuntimeException(sprintf('"%s" config key does not exist', $config));
		}
		$this->_controllers[$config][$alias] = $class;
		$this->_method[$alias] = get_class_methods($class);
		$this->_method[$alias]['file'] = $file;
		$this->_method[$alias]['path'] = $path;
	}

	/**
	 * Get Db Controller
	 * 
	 * @return dbconfig
	 **/
	public function getDbConfig() {
		if (!is_array($this->_dbconfig) || count($this->_dbconfig) < 1) {
			/* Get Db Controller */
			$DbControllers = new Database('select');
			$DbControllers->columns(array(
							'config' => 'module_controllers_config',
							'class' => 'module_controllers_class',
							'alias' => 'module_controllers_alias',
							'path' => 'module_controllers_path',
							'file' => 'module_controllers_file'
					));
			$DbControllers->from(array(
							'm' => 'module_controllers'
					));
			$DbControllers->order(array(
							'module_controllers_config ASC',
							'module_controllers_priority ASC',
							'module_controllers_alias ASC'
					));
			$DbControllers->setCacheName('module_controllers');
			$DbControllers->execute();
			if ($DbControllers->hasResult()) {
				$this->_dbconfig = $DbControllers->toArray();
			}
		}
		return $this->_dbconfig;
	}
}
