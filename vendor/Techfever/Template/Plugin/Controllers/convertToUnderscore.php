<?php

namespace Techfever\Template\Plugin\Controllers;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Techfever\Template\Plugin\Filters\ToUnderscore;

class convertToUnderscore extends AbstractPlugin {
	/**
	 * Grabs Template.
	 *
	 * @return mixed
	 */
	public function __invoke($value, $searchSeparator) {
		$ToUnderscore = new ToUnderscore ( $searchSeparator );
		return $ToUnderscore->filter ( $value );
	}
}
