<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class getTemplate extends AbstractPlugin {
	protected $template = null;
	public function __invoke() {
		if (! isset ( $this->template )) {
			$this->template = $this->getController ()->getServiceLocator ()->get ( 'template' );
		}
		return $this->template;
	}
}
