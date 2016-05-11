<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class isLoginWallet extends AbstractPlugin {
	protected $isLoginWallet = false;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($UserAccess instanceof UserAccess) {
			$this->isLoginWallet = $UserAccess->isLoginWallet ();
		}
		return $this->isLoginWallet;
	}
}
