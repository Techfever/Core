<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Template\Plugin\Filters\ToDash;

class convertToDash extends AbstractPlugin {
	/**
	 * Grabs Template.
	 *
	 * @return mixed
	 */
	public function __invoke($value, $searchSeparator) {
		$ToDash = new ToDash ( $searchSeparator );
		return $ToDash->filter ( $value );
	}
}
