<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class getUsername extends AbstractPlugin {
	protected $username = "Unknow";
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($this->username == 0 && $UserAccess instanceof UserAccess) {
			$this->username = $UserAccess->getUsername ();
		}
		return $this->username;
	}
}
