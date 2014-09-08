<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Address\Address;

class getUserAddress extends AbstractPlugin {
	protected $userAddress = null;
	public function __invoke() {
		if (! isset ( $this->userAddress )) {
			$options = array (
					'servicelocator' => $this->getController ()->getServiceLocator () 
			);
			$this->userAddress = new Address ( $options );
		}
		return $this->userAddress;
	}
}
