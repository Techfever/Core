<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class getUserBankID extends AbstractPlugin {
	protected $userBankID = 0;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($this->userBankID == 0 && $UserAccess instanceof UserAccess) {
			$this->userBankID = $UserAccess->getBankID ();
		}
		return $this->userBankID;
	}
}
