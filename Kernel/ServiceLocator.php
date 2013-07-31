<?php
namespace Kernel;

use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;
use Kernel\Exception;

class ServiceLocator {

	private static $serviceManager = null;

	/**
	 * @var array
	 */
	protected $config = array(
			'factories' => array(),
			'abstract_factories' => array(),
			'invokables' => array(),
			'services' => array(),
			'aliases' => array(),
			'initializers' => array(),
			'shared' => array()
	);

	/**
	 * @var boolean
	 */
	protected static $hasServiceManager = false;

	public function setServiceManager(ServiceManager $serviceManager) {
		if (!$serviceManager instanceof ServiceManager) {
			throw new Exception\RuntimeException('Zend\ServiceManager object not found');
		}
		self::$serviceManager = $serviceManager;
		self::setServiceClass('factories', 'Kernel', 'Kernel\Service\Factories\KernelServiceFactory');
		self::getServiceManager('Kernel')->initialize();

		self::$hasServiceManager = true;
	}

	public static function hasServiceManager() {
		return self::$hasServiceManager = true;
	}

	public static function getServiceManager($service = null) {
		if (self::$serviceManager instanceof ServiceManager) {
			if (!empty($service)) {
				return self::$serviceManager->get($service);
			} else {
				return self::$serviceManager;
			}
		}
		return False;
	}

	public static function setService($name = null, $service = null) {
		if (self::$serviceManager instanceof ServiceManager) {
			if (is_string($name) && (is_array($service) || is_object($service))) {
				self::$serviceManager->setAllowOverride(True);
				self::$serviceManager->setService($name, $service);
				self::$serviceManager->setAllowOverride(False);
			}
		}
		return False;
	}

	public static function getServiceConfig($config = null, $key = null) {
		if (self::$serviceManager instanceof ServiceManager) {
			$configuration = self::getServiceManager('Config');
			if (!empty($config) && array_key_exists($config, $configuration)) {
				if (!empty($key) && array_key_exists($key, $configuration[$config])) {
					return $configuration[$config][$key];
				} else {
					return $configuration[$config];
				}
			} else {
				return $configuration;
			}
		}
		return False;
	}

	public static function setServiceConfig($config = null) {
		if (self::$serviceManager instanceof ServiceManager) {
			if (is_array($config)) {
				self::$serviceManager->setAllowOverride(True);
				$configuration = self::getServiceConfig();
				if (!is_array($configuration)) {
					$configuration = array();
				}
				$configuration = array_merge($configuration, $config);
				self::setService('Config', $configuration);
			}
		}
		return False;
	}

	public static function setServiceClass($key = null, $name = null, $class) {
		if (!empty($key) && !empty($name) && !empty($class) && $key == 'invokables') {
			self::getServiceManager()->setInvokableClass($name, $class);
		}

		if (!empty($key) && !empty($name) && !empty($class) && $key == 'factories') {
			self::getServiceManager()->setFactory($name, $class);
		}

		if (!empty($key) && !empty($class) && $key == 'abstract_factories') {
			self::getServiceManager()->addAbstractFactory($class);
		}

		if (!empty($key) && !empty($name) && !empty($class) && $key == 'aliases') {
			self::getServiceManager()->setAlias($name, $class);
		}

		if (!empty($key) && !empty($name) && !empty($class) && $key == 'shared') {
			self::getServiceManager()->setShared($name, $class);
		}
	}

	public static function setConfigClass($key = null, $name = null, $class) {
		if ($key == 'factories') {
			$this->config['factories'][$name] = $class;
		} elseif ($key == 'abstract_factories') {
			$this->config['abstract_factories'][$name] = $class;
		} elseif ($key == 'invokables') {
			$this->config['invokables'][] = $class;
		} elseif ($key == 'services') {
			$this->config['services'][$name] = $class;
		} elseif ($key == 'aliases') {
			$this->config['aliases'][$name] = $class;
		} elseif ($key == 'initializers') {
			$this->config['initializers'][] = $class;
		} elseif ($key == 'shared') {
			$this->config['shared'][$name] = $class;
		}
	}

	/**
	 * Configure service manager
	 *
	 * @param ServiceManager $serviceManager
	 * @return void
	 */
	public function configureServiceManager(ServiceManager $serviceManager) {
		foreach ($this->getFactories() as $name => $factory) {
			$serviceManager->setFactory($name, $factory);
		}

		foreach ($this->getAbstractFactories() as $factory) {
			$serviceManager->addAbstractFactory($factory);
		}

		foreach ($this->getInvokables() as $name => $invokable) {
			$serviceManager->setInvokableClass($name, $invokable);
		}

		foreach ($this->getServices() as $name => $service) {
			$serviceManager->setService($name, $service);
		}

		foreach ($this->getAliases() as $alias => $nameOrAlias) {
			$serviceManager->setAlias($alias, $nameOrAlias);
		}

		foreach ($this->getInitializers() as $initializer) {
			$serviceManager->addInitializer($initializer);
		}

		foreach ($this->getShared() as $name => $isShared) {
			$serviceManager->setShared($name, $isShared);
		}
	}
}
