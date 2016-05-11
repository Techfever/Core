<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class isAndroidRequest extends AbstractPlugin {
	protected $request = null;
	
	/**
	 * Is the request a Android Request?
	 *
	 * Should work with Prototype/Script.aculo.us, possibly others.
	 *
	 * @return bool
	 */
	public function __invoke() {
		if (! isset ( $this->request )) {
			$this->request = $this->getController ()->getServiceLocator ()->get ( 'request' );
		}
		$isXmlHttpRequest = $this->request->getPost ( 'isXmlHttpRequest', 0 );
		$isAndroidRequest = $this->request->getPost ( 'isAndroidRequest', 0 );
		return (($isAndroidRequest == 1 ? true : false) && ($isXmlHttpRequest == 1 ? true : false));
	}
}
