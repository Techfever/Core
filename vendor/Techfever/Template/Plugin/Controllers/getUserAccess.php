<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class getUserAccess extends AbstractPlugin {
	public function __invoke() {
		return $this->getController ()->getServiceLocator ()->get ( 'UserAccess' );
	}
}
