<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class getUserSponsorUsername extends AbstractPlugin {
	protected $sponsor = 0;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($this->sponsor == 0 && $UserAccess instanceof UserAccess) {
			$this->sponsor = $UserAccess->getSponsorUsername ();
		}
		return $this->sponsor;
	}
}
