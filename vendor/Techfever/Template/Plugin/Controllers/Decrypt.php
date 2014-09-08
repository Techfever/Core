<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Functions\Crypt\Decode;

class Decrypt extends AbstractPlugin {
	public function __invoke($value) {
		$value = new Decode ( $value );
		$value = $value->__toString ();
		return $value;
	}
}
