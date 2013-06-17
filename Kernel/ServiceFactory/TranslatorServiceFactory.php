<?php
namespace Kernel\ServiceFactory;

use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Locale;

/**
 * Translator.
 */
class TranslatorServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		// Configure the translator
		$config = $serviceLocator->get('Config');
		$trConfig = isset($config['translator']) ? $config['translator'] : array();
		$translator = Translator::factory($trConfig);

		$cache = $serviceLocator->get('cache\filesystem');
		$cacheoption = $config['cachestorage']['filesystem']['options'];
		$cacheoption['namespace'] = 'translator';
		$cache->setOptions($cacheoption);

		$translator->setCache($cache);
		$translator->setLocale(Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']))->setFallbackLocale('en_US');

		return $translator;
	}
}
