<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Management as UserManagement;

class getUserManagement extends AbstractPlugin {
	protected $userManagement = null;
	public function __invoke() {
		if (! isset ( $this->userManagement )) {
			$options = array (
					'servicelocator' => $this->getController ()->getServiceLocator () 
			);
			$this->userManagement = new UserManagement ( $options );
		}
		return $this->userManagement;
	}
}
