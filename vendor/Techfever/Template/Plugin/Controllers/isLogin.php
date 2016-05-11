<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class isLogin extends AbstractPlugin {
	protected $isLogin = false;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($UserAccess instanceof UserAccess) {
			$this->isLogin = $UserAccess->isLogin ();
		}
		return $this->isLogin;
	}
}
