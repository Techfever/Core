<?php
namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class LogoutActionController extends AbstractActionController {

	protected $type = 'account';

	protected $module = 'logout';

	public function IndexAction() {
		$this->getUserAccess()->setLogout();
		return $this->redirect()->toRoute('Account/Login', array(
						'action' => 'Index'
				));
	}
}
