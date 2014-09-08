<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class isAdminUser extends AbstractPlugin {
	protected $isAdminUser = false;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($UserAccess instanceof UserAccess) {
			$this->isAdminUser = $UserAccess->isAdminUser ();
		}
		return $this->isAdminUser;
	}
}
