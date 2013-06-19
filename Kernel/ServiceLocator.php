<?php
namespace Kernel;

use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;
use Zend\Db\Sql\Sql;

class ServiceLocator {
	protected static $servicefactories = array(
			'phpsetting' => 'Kernel\ServiceFactory\PhpsettingServiceFactory',
			'log' => 'Kernel\ServiceFactory\LoggerServiceFactory',
			'cache\filesystem' => 'Kernel\ServiceFactory\Cache\FilesystemServiceFactory',
			'cache\output' => 'Kernel\ServiceFactory\Cache\OutputCacheServiceFactory',
			'cache\capture' => 'Kernel\ServiceFactory\Cache\CaptureCacheServiceFactory',
			'db' => 'Kernel\ServiceFactory\DbServiceFactory',
			'session' => 'Kernel\ServiceFactory\SessionManagerServiceFactory',
			'translator' => 'Kernel\ServiceFactory\TranslatorServiceFactory'
	);

	private static $serviceManager = null;

	public function configureServiceManager(ServiceManager $serviceManager) {
		if ($serviceManager instanceof ServiceManager) {
			foreach (self::$servicefactories as $name => $factoryClass) {
				$serviceManager->setFactory($name, $factoryClass);
			}
			self::$serviceManager = $serviceManager;
		}

		$Database = new Database('select');
		$Database->columns(array(
					'key' => 'system_configuration_key', 'value' => 'system_configuration_value'
				));
		$Database->from(array(
					'ss' => 'system_configuration'
				));
		$Database->setCacheName('system_configuration');
		$Database->execute();
		if ($Database->hasResult()) {
			while ($Database->valid()) {
				echo $Database->get('key') . '-' . $Database->get('value') . "\n";
				$Database->next();
			}
		}

		$serviceManager->get('phpsetting');

		$session = $serviceManager->get('session');
		$session->start();
		$container = new Container('initialized');
		if (!isset($container->init)) {
			$session->regenerateId(true);
			$container->init = 1;
		}

		$eventManager = $serviceManager->get('EventManager');
		$sharedManager = $eventManager->getSharedManager();
		$sharedManager->attach('Zend\Mvc\Application', 'dispatch.error', function ($e) use ($serviceManager) {
					if ($e->getParam('exception')) {
						$log = $serviceManager->get('log');
						$log->crit($e->getParam('exception'));
					}
				});
	}

	public static function getServiceManager($service = null) {
		if (self::$serviceManager instanceof ServiceManager) {
			if (!empty($service)) {
				return self::$serviceManager->get($service);
			} else {
				return self::$serviceManager;
			}
		}
		return false;
	}

	public static function getServiceConfig($config = null) {
		if (self::$serviceManager instanceof ServiceManager) {
			$configuration = self::getServiceManager('Config');
			if (!empty($config) && array_key_exists($config, $configuration)) {
				return $configuration[$config];
			} else {
				return $configuration;
			}
		}
		return false;
	}

	public static function setServiceConfig($config = null) {
		if (self::$serviceManager instanceof ServiceManager) {
			if (is_array($config)) {
				self::$serviceManager['Config'] = array_merge(self::$serviceManager['Config'], $config);
			}
		}
		return false;
	}
}
