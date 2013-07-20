<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Kernel\Template;
use Kernel\Database;
use Kernel\ServiceLocator;

/**
 * Phpsetting.
 */
class TemplateServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$configuration = array(
				'theme' => array()
		);

		/* Get Db theme */
		$DbTheme = new Database('select');
		$DbTheme->columns(array(
						'name' => 'theme_name',
						'developer' => 'theme_developer',
						'key' => 'theme_key',
						'doctype' => 'theme_doctype'
				));
		$DbTheme->from(array(
						't' => 'theme'
				));
		$DbTheme->join(array(
						'ss' => 'system_configuration'
				), 't.theme_key = ss.system_configuration_value', array(
						'system_key' => 'system_configuration_key'
				));
		$DbTheme->where(array(
						'ss.system_configuration_key' => 'system_theme',
				));
		$DbTheme->limit(1);
		$DbTheme->setCacheName('theme_configuration');
		$DbTheme->execute();
		if ($DbTheme->hasResult()) {
			$result = $DbTheme->toArray();
			$configuration['template']['theme'] = $result[0];
		}
		//ServiceLocator::setServiceConfig($configuration);

		$Template = new Template();
		$Template->prepare($configuration['template']);
		$Template->reset();
		return $Template;
	}
}
