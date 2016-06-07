<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class isFullyJSON extends AbstractPlugin {
	protected $isFullyJSON = false;
	public function __invoke() {
		if (strtolower ( SYSTEM_FULLY_JSON ) == "true") {
			$this->isFullyJSON = True;
		}
		return $this->isFullyJSON;
	}
}
