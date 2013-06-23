<?php
namespace Kernel;

use Zend\ServiceManager\ServiceManager;
use Zend\Db\Sql\Sql;

class ServiceLocator {

	private static $serviceManager = null;

	public function configureServiceManager(ServiceManager $serviceManager) {
		if (!$serviceManager instanceof ServiceManager) {
			throw new Exception\RuntimeException('Zend\ServiceManager object not found');
		}
		self::$serviceManager = $serviceManager;
		self::setFactory('Kernel', 'Kernel\Service\Factories\KernelServiceFactory');
		self::getServiceManager('Kernel')->initialize();
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

	public static function setFactory($name, $factoryClass) {
		self::getServiceManager()->setFactory($name, $factoryClass);
	}
}
