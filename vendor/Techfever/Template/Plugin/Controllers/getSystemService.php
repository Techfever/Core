<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class getSystemService extends AbstractPlugin {
	/**
	 * Grabs Template.
	 *
	 * @return mixed
	 */
	public function __invoke() {
		$SystemService = $this->getController ()->getServiceLocator ()->get ( 'SystemService' );
		
		return $SystemService;
	}
}
