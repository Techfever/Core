<?php

namespace Wallet\Logout\Controller;

use Techfever\Template\Plugin\AbstractActionController;

class ActionController extends AbstractActionController {
	protected $type = 'wallet';
	protected $module = 'logout';
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		$this->getUserAccess ()->setLogoutWallet ();
		return $this->redirect ()->toRoute ( 'Wallet/Login', array (
				'action' => 'Index' 
		) );
	}
}
