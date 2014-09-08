<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class getUserAddressID extends AbstractPlugin {
	protected $userAddressID = 0;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($this->userAddressID == 0 && $UserAccess instanceof UserAccess) {
			$this->userAddressID = $UserAccess->getAddressID ();
		}
		return $this->userAddressID;
	}
}
