<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class isXmlHttpRequest extends AbstractPlugin {
	protected $request = null;
	public function __invoke() {
		if (! isset ( $this->request )) {
			$this->request = $this->getController ()->getServiceLocator ()->get ( 'request' );
		}
		return $this->request->isXmlHttpRequest ();
	}
}
