<?php

namespace Wallet\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class LogoutActionController extends AbstractActionController {
	protected $type = 'wallet';
	protected $module = 'logout';
	public function IndexAction() {
		$this->getUserAccess ()->setLogoutWallet ();
		return $this->redirect ()->toRoute ( 'Wallet/Login', array (
				'action' => 'Index' 
		) );
	}
}
