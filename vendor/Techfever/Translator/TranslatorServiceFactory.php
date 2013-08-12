<?php
namespace Techfever\Translator;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Locale;

/**
 * Translator.
 */
class TranslatorServiceFactory implements FactoryInterface {
	/**
	 * Create Translator service
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return Template
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		// Configure the translator
		$database = $serviceLocator->get('db');
		$config = $serviceLocator->get('Config');
		$trConfig = isset($config['translator']) ? $config['translator'] : array();
		$translator = Translator::factory($trConfig, $database);

		$cache = $serviceLocator->get('cachestorage');
		$cacheoption = $config['cachestorage']['filesystem']['options'];
		$cacheoption['namespace'] = 'translator';
		$cache->setOptions($cacheoption);

		$locale = $config['system']['SYSTEM_LANGUAGE'];
		$httpacceptlanguage = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);

		$translator->setCache($cache);
		$translator->setLocale($httpacceptlanguage)->setFallbackLocale($locale);

		return $translator;
	}
}
