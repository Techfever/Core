<?php
namespace Techfever\Template\Module;

use Techfever\Exception;
use Techfever\Database\Database;
use Techfever\Functions\DirConvert;

class Controller {
	/**
	 * @var Database\Database
	 */
	private $database = null;

	/**
	 * @var Structure
	 **/
	private $structure = array();

	/**
	 * @var Controller
	 **/
	private $controller = array();

	/**
	 * @var Classmap Autoloader
	 **/
	private $classmapautoloader = array();

	/**
	 * Constructor
	 *
	 * @param  null|array $config
	 **/
	public function __construct(Database $database) {
		$this->database = $database;
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
	 * Get Controller
	 *
	 * @param  $controller
	 **/
	public function getController() {
		if (!is_array($this->controller) || count($this->controller) < 1) {
			$QControllers = $this->getDatabase();
			$QControllers->select();
			$QControllers->columns(array(
							'config' => 'module_controllers_config',
							'class' => 'module_controllers_class',
							'alias' => 'module_controllers_alias',
							'path' => 'module_controllers_path',
							'file' => 'module_controllers_file'
					));
			$QControllers->from(array(
							'm' => 'module_controllers'
					));
			$QControllers->order(array(
							'module_controllers_config ASC',
							'module_controllers_priority ASC',
							'module_controllers_alias ASC'
					));
			$QControllers->setCacheName('module_controllers');
			$QControllers->execute();
			if ($QControllers->hasResult()) {
				$this->controller = $QControllers->toArray();
			}
		}
		return $this->controller;
	}

	/**
	 * Get Classmap Autoloader
	 *
	 * @param  $classmapautoloader
	 **/
	public function getClassMapAutoloader() {
		if (!is_array($this->classmapautoloader) || count($this->classmapautoloader) < 1) {
			$controller = $this->getController();
			if (is_array($controller) && count($controller) > 0) {
				foreach ($controller as $controllervalue) {
					$DirConvert = new DirConvert(CORE_PATH . '/' . $controllervalue['path'] . '/' . $controllervalue['file']);
					$configfile = $DirConvert->__toString();
					$this->classmapautoloader[$controllervalue['class']] = $configfile;
				}
			}
		}
		return $this->classmapautoloader;
	}

	/**
	 * Get Structure
	 *
	 * @return array
	 */
	public function getStructure() {
		if (!is_array($this->structure) || count($this->structure) < 1) {
			$structure = null;
			$controller = $this->getController();
			if (is_array($controller) && count($controller) > 0) {
				foreach ($controller as $value) {
					if (!is_string($value['alias'])) {
						throw new Exception\UnexpectedValueException('$alias must be a string');
					}
					if (!class_exists($value['class'])) {
						throw new Exception\RuntimeException(sprintf('Unable to locate "%s"; class does not exist', $value['class']));
					}
					if (!isset($value['config'])) {
						throw new Exception\RuntimeException(sprintf('"%s" config key does not exist', $value['config']));
					}
					$structure[$value['config']][$value['alias']] = $value['class'];
				}
			}
			$this->structure = $structure;
		}
		return $this->structure;
	}
}
