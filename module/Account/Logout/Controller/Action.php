<?php

namespace Account\Logout\Controller;

use Techfever\Template\Plugin\AbstractActionController;

class ActionController extends AbstractActionController {
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		$this->getUserAccess ()->setLogout ();
		$this->getUserAccess ()->setLogoutWallet ();
		
		return $this->redirect ()->toRoute ( 'Index', array (
				'action' => 'Index' 
		) );
	}
}
