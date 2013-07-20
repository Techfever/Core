<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;

/**
 * Phpsetting.
 */
class SessionServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {

		$config = $serviceLocator->get('Config');

		$sessionConfig = null;
		$sessionStorage = null;
		$session = null;

		if (array_key_exists('session', $config) && isset($config['session'])) {
			$session = $config['session'];
		}

		if (array_key_exists('config', $session) && isset($session['config'])) {
			$class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
			$options = isset($session['config']['options']) ? $session['config']['options'] : array();
			$sessionConfig = new $class();
			$sessionConfig->setOptions($options);
		}

		if (array_key_exists('storage', $session) && isset($session['storage'])) {
			$class = $session['storage'];
			$sessionStorage = new $class();
		}

		if (array_key_exists('save_handler', $session) && isset($session['save_handler']['name'])) {
			if (array_key_exists('name', $session['save_handler']) && $session['save_handler']['name'] == 'db') {
				$sessionAdapter = $serviceLocator->get($session['save_handler']['adapter']);
				$tableGateway = new TableGateway('session', $sessionAdapter);
				$sessionSaveHandler = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
			}
		}
		$sessionManager = new SessionManager($sessionConfig, $sessionStorage);
		$sessionManager->setSaveHandler($sessionSaveHandler);
		if (array_key_exists('validator', $session) && ($session['validator'])) {
			$chain = $sessionManager->getValidatorChain();
			foreach ($session['validator'] as $validator) {
				$validator = new $validator();
				$chain->attach('session.validate', array(
								$validator,
								'isValid'
						));
			}
		}
		Container::setDefaultManager($sessionManager);

		$sessionManager->start();
		$container = new Container('initialized');
		if (!isset($container->init)) {
			$sessionManager->regenerateId(true);
			$container->init = 1;
		}
		return $sessionManager;
	}
}
