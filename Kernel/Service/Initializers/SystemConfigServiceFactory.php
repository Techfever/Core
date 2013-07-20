<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Kernel\Database;
use Kernel\ServiceLocator;

/**
 * Phpsetting.
 */
class SystemConfigServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$configuration = array();
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
				$configuration[$Database->get('key')] = $Database->get('value');
				define(strtoupper($Database->get('key')), $Database->get('value'));
				$Database->next();
			}
		}

		if (is_array($configuration)) {
			$configuration = array(
				'system' => $configuration,
				'theme' => array('default'=>$configuration['system_theme'])
			);
			ServiceLocator::setServiceConfig($configuration);
		}
		return true;
	}
}
