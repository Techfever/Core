<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Template\Plugin\Filters\ToForwardSlash;

class convertToForwardSlash extends AbstractPlugin {
	/**
	 * Grabs Template.
	 *
	 * @return mixed
	 */
	public function __invoke($value, $searchSeparator) {
		$ToForwardSlash = new ToForwardSlash ( $searchSeparator );
		return $ToForwardSlash->filter ( $value );
	}
}
