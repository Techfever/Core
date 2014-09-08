<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Translator extends AbstractPlugin {
	/**
	 * Grabs Translator.
	 *
	 * @return mixed
	 */
	public function __invoke($key) {
		$Translator = $this->getController ()->getServiceLocator ()->get ( 'translator' );
		
		return $Translator->translate ( $key );
	}
}
