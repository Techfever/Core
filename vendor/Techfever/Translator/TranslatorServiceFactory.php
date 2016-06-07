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
		$database = $serviceLocator->get ( 'db' );
		$config = $serviceLocator->get ( 'Config' );
		$trConfig = isset ( $config ['translator'] ) ? $config ['translator'] : array ();
		$trConfig ['servicelocator'] = $serviceLocator;
		$translator = new Translator ( $trConfig );
		
		$cache = $serviceLocator->get ( 'cachestorage' );
		$cacheoption = $config ['cachestorage'] ['filesystem'] ['options'];
		$cache->setOptions ( $cacheoption );
		
		$locale = $config ['system'] ['SYSTEM_LANGUAGE'];

		$httpacceptlanguage = Locale::acceptFromHttp ( (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : $locale) );
		$Session = $serviceLocator->get ( 'session' );
		$Container = $Session->getContainer ( 'Translator' );
		if ($Container->offsetExists ( 'locale' )) {
			$httpacceptlanguage = $Container->offsetGet ( 'locale' );
		} else {
			$Container->offsetSet ( 'locale', $httpacceptlanguage );
		}
		$verifyLocale = $translator->checkLocale ( $locale );
		if (! $verifyLocale) {
			$locale = SYSTEM_DEFAULT_LOCALE;
		}
		$translator->setCache ( $cache );
		$translator->setLocale ( $httpacceptlanguage )->setFallbackLocale ( $locale );
		
		return $translator;
	}
}
