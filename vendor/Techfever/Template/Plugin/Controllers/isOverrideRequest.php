<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class isOverrideRequest extends AbstractPlugin {
	protected $request = null;
	
	/**
	 * Is the request a Javascript OverrideRequest?
	 *
	 * Should work with Prototype/Script.aculo.us, possibly others.
	 *
	 * @return bool
	 */
	public function __invoke() {
		if (! isset ( $this->request )) {
			$this->request = $this->getController ()->getServiceLocator ()->get ( 'request' );
		}
		$isOverrideRequest = $this->request->getPost ( 'isOverrideRequest' );
		return ($isOverrideRequest == 1 ? true : false);
	}
}
