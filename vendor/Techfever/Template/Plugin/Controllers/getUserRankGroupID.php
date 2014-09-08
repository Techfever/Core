<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class getUserRankGroupID extends AbstractPlugin {
	protected $userRankGroupID = 0;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($this->userRankGroupID == 0 && $UserAccess instanceof UserAccess) {
			$this->userRankGroupID = $UserAccess->getRankGroupID ();
		}
		return $this->userRankGroupID;
	}
}
