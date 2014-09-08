<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Functions\Crypt\Encode;

class Encrypt extends AbstractPlugin {
	public function __invoke($value) {
		$value = new Encode ( $value );
		$value = $value->__toString ();
		return $value;
	}
}
