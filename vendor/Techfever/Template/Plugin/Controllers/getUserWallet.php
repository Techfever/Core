<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;
use Techfever\Wallet\Wallet as UserWallet;

class getUserWallet extends AbstractPlugin {
	protected $userWallet = null;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if (! isset ( $this->userWallet ) && $UserAccess instanceof UserAccess) {
			$options = array (
					'servicelocator' => $this->getController ()->getServiceLocator (),
					'from_user' => $UserAccess->getID () 
			);
			$this->userWallet = new UserWallet ( $options );
		}
		return $this->userWallet;
	}
}
