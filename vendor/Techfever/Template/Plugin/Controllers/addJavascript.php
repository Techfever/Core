<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class addJavascript extends AbstractPlugin {
	protected $template = null;
	public function __invoke($data, $parameter = null) {
		if (! isset ( $this->template )) {
			$this->template = $this->getController ()->getServiceLocator ()->get ( 'template' );
		}
		return $this->template->addJavascript ( $data, $parameter );
	}
}
