<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class getUserRankID extends AbstractPlugin {
	protected $userRankID = 0;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($this->userRankID == 0 && $UserAccess instanceof UserAccess) {
			$this->userRankID = $UserAccess->getRankID ();
		}
		return $this->userRankID;
	}
}
