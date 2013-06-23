<?php
namespace Kernel\Service;

use Zend\ServiceManager\ServiceManager;
use Kernel\ServiceLocator;
use Kernel\Database;

class Manager {

	private static $serviceFactories = null;

	private static $hasFactories = False;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->serviceFactories = $this->getService();
		if ($this->hasService()) {
			foreach ($this->serviceFactories as $factoryClass => $name) {
				ServiceLocator::setFactory($name, $factoryClass);
			}
		}
	}

	public function initialize() {
		if ($this->hasService()) {
			foreach ($this->serviceFactories as $factoryClass => $name) {
				ServiceLocator::getServiceManager($name);
			}
		}
	}

	public function hasService() {
		return $this->hasFactories;
	}

	public function getService() {
		$configuration = array();
		$Database = new Database('select');
		$Database->columns(array(
					'class' => 'system_service_class', 'alias' => 'system_service_alias'
				));
		$Database->from(array(
					'ss' => 'system_service'
				));
		$Database->setCacheName('system_service');
		$Database->execute();
		if ($Database->hasResult()) {
			while ($Database->valid()) {
				$configuration[$Database->get('class')] = $Database->get('alias');
				$Database->next();
			}
		}

		if (is_array($configuration)) {
			$this->hasFactories = True;
			return $configuration;
		}
		return null;
	}
}
