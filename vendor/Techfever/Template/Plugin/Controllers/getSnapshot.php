<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class getSnapshot extends AbstractPlugin {
	/**
	 * Grabs Template.
	 *
	 * @return mixed
	 */
	public function __invoke() {
		$Snapshot = $this->getController ()->getServiceLocator ()->get ( 'snapshot' );
		
		return $Snapshot;
	}
}
