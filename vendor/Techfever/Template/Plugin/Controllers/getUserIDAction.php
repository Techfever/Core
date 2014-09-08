<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class getUserIDAction extends AbstractPlugin {
	protected $userIDAction = 0;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($this->userIDAction == 0 && $UserAccess instanceof UserAccess) {
			if ($UserAccess->isAdminUser ()) {
				$this->userIDAction = 1;
			} else {
				$this->userIDAction = $UserAccess->getID ();
			}
		}
		return $this->userIDAction;
	}
}
