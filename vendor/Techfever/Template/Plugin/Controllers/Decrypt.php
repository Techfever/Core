<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Functions\Crypt\Decode;

class Decrypt extends AbstractPlugin {
	public function __invoke($value, $add_key = true) {
		if (strlen ( $value ) > 0) {
			$value = new Decode ( $value, $add_key );
			$value = $value->__toString ();
		} else {
			$value = null;
		}
		return $value;
	}
}
