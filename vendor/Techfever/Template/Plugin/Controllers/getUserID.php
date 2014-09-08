<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class getUserID extends AbstractPlugin {
	protected $userID = 0;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($this->userID == 0 && $UserAccess instanceof UserAccess) {
			$this->userID = $UserAccess->getID ();
		}
		return $this->userID;
	}
}
