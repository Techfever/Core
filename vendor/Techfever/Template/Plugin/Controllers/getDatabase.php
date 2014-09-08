<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class getDatabase extends AbstractPlugin {
	/**
	 * Grabs Template.
	 *
	 * @return mixed
	 */
	public function __invoke() {
		$Database = $this->getController ()->getServiceLocator ()->get ( 'db' );
		
		return $Database;
	}
}
