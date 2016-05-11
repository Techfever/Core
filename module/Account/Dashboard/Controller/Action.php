<?php

namespace Account\Dashboard\Controller;

use Techfever\Template\Plugin\AbstractActionController;

class ActionController extends AbstractActionController {
	protected $type = 'account';
	protected $module = 'dashboard';
	protected $inputform = null;
	
	/**
	 * Index Action
	 *
	 * @return ViewModel
	 */
	public function IndexAction() {
		if (! $this->getUserAccess ()->isLogin ()) {
			return $this->redirect ()->toRoute ( 'Account/Login', array (
					'action' => 'Index' 
			) );
		}
	}
}
