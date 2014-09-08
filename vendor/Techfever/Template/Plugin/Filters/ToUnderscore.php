<?php

namespace Techfever\Template\Plugin\Filters;

use Zend\Filter\Word\SeparatorToSeparator;

class ToUnderscore extends SeparatorToSeparator {
	/**
	 * Constructor
	 */
	public function __construct($searchSeparator) {
		parent::__construct ( $searchSeparator, '_' );
	}
}
