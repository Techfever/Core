<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Bank\Bank;

class getUserBank extends AbstractPlugin {
	protected $userBank = null;
	public function __invoke() {
		if (! isset ( $this->userBank )) {
			$options = array (
					'servicelocator' => $this->getController ()->getServiceLocator () 
			);
			$this->userBank = new Bank ( $options );
		}
		return $this->userBank;
	}
}
