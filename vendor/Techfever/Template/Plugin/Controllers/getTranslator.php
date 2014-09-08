<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class getTranslator extends AbstractPlugin {
	/**
	 * Grabs Translator.
	 *
	 * @return mixed
	 */
	public function __invoke() {
		$Translator = $this->getController ()->getServiceLocator ()->get ( 'translator' );
		
		return $Translator;
	}
}
