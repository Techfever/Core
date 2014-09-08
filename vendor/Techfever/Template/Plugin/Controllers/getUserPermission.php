<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class getUserPermission extends AbstractPlugin {
	/**
	 * Grabs Template.
	 *
	 * @return mixed
	 */
	public function __invoke() {
		$UserPermission = $this->getController ()->getServiceLocator ()->get ( 'UserPermission' );
		
		return $UserPermission;
	}
}
