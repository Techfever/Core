<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Access as UserAccess;

class getUserPlacementUsername extends AbstractPlugin {
	protected $placement = 0;
	public function __invoke() {
		$UserAccess = $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
		if ($this->placement == 0 && $UserAccess instanceof UserAccess) {
			$this->placement = $UserAccess->getPlacementUsername ();
		}
		return $this->placement;
	}
}
