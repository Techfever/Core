<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class addCSS extends AbstractPlugin {
	protected $template = null;
	public function __invoke($data, $for = null) {
		if (! isset ( $this->template )) {
			$this->template = $this->getController ()->getServiceLocator ()->get ( 'template' );
		}
		return $this->template->addCSS ( $data, $for );
	}
}
