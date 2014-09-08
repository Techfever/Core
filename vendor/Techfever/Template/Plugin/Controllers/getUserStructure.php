<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Structure as UserStructure;

class getUserStructure extends AbstractPlugin {
	protected $userStructure = null;
	public function __invoke() {
		if (! isset ( $this->userStructure )) {
			$options = array (
					'servicelocator' => $this->getController ()->getServiceLocator () 
			);
			$this->userStructure = new UserStructure ( $options );
		}
		return $this->userStructure;
	}
}
