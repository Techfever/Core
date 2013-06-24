<?php
namespace Kernel\Service\Initializers;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Kernel\Language;

/**
 * Language.
 */
class LanguageServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$locale = $serviceLocator->get('translator')->getLocale();
		$Language = new Language($locale);
		return $Language;
	}
}
