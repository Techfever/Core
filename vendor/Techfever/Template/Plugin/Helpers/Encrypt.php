<?php

namespace Techfever\Template\Plugin\Helpers;

use Zend\View\Helper\AbstractHelper;
use Techfever\Functions\Crypt\Encode;

class Encrypt extends AbstractHelper {
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
