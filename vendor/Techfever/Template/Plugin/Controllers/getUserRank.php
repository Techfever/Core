<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\User\Rank as UserRank;

class getUserRank extends AbstractPlugin {
	protected $userRank = null;
	public function __invoke() {
		if (! isset ( $this->userRank )) {
			$this->userRank = new UserRank ( array (
					'servicelocator' => $this->getController ()->getServiceLocator () 
			) );
		}
		return $this->userRank;
	}
}
