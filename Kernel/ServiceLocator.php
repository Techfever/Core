<?php
namespace Kernel;

use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;

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

		$serviceManager->get('phpsetting');

		$session = $serviceManager->get('session');
		$session->start();

		$container = new Container('initialized');
		if (!isset($container->init)) {
			$session->regenerateId(true);
			$container->init = 1;
		}

		$eventManager = $serviceManager->get('EventManager');
		$sharedManager = $eventManager->getSharedManager ();
		$sharedManager->attach ( 'Zend\Mvc\Application', 'dispatch.error', function ($e) use($serviceManager) {
			if ($e->getParam ( 'exception' )) {
				$log = $serviceManager->get ( 'log' );
				$log->crit ( $e->getParam ( 'exception' ) );
			}
		} );
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
			if (!empty($service) && array_key_exists($service, $configuration)) {
				return $configuration[$config];
			} else {
				return $configuration;
			}
		}
		return false;
	}
}
