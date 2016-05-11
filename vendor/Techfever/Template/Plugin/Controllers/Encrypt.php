<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Functions\Crypt\Encode;

class Encrypt extends AbstractPlugin {
	public function __invoke($value, $add_key = true) {
		if (strlen ( $value ) > 0) {
			$value = new Encode ( $value, $add_key );
			$value = $value->__toString ();
		} else {
			$value = null;
		}
		return $value;
	}
}
