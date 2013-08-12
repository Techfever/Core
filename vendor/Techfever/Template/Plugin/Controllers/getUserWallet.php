<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Wallet\Wallet as UserWallet;

class getUserWallet extends AbstractPlugin {

	protected $userWallet = null;
	
	public function __invoke() {
		if (!isset($this->userManagement)) {
			$options = array(
					'servicelocator' => $this->getController()->getServiceLocator(),
					'user_id' => $this->getUserAccess()->getID(),
			);
			$this->userWallet = new UserWallet($options);
		}
		return $this->userWallet;
	}
}
