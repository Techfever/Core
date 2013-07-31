<?php
namespace Kernel;

use Zend\Loader\ClassMapAutoloader;
use Kernel\ServiceLocator;
use Kernel\Database\Database;

class Module {

	/**
	 * @var Controllers List
	 **/
	private $_controllers = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$controller = $this->getController();
		$AutoloadClassmap = new ClassMapAutoloader();
		$AutoloadClassmap->registerAutoloadMap($controller);
		$AutoloadClassmap->register();
	}

	public function getController() {
		if (!is_array($this->_controllers) || count($this->_controllers) < 1) {
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
				while ($DbControllers->valid()) {
					$this->_controllers[$DbControllers->get('class')] = CORE_PATH . '/' . $DbControllers->get('path') . '/' . $DbControllers->get('file');
					$DbControllers->next();
				}
			}
		}
		return $this->_controllers;
	}
}
