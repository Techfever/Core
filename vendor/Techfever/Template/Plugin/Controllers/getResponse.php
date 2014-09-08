<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class getResponse extends AbstractPlugin {
	protected $response = null;
	public function __invoke($key = null, $default = null) {
		if (! isset ( $this->response )) {
			$this->response = $this->getController ()->getServiceLocator ()->get ( 'response' );
		}
		return $this->response;
	}
}
