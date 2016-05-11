<?php

namespace Techfever\Template\Plugin\Filters;

use Zend\Filter\Word\SeparatorToSeparator;

class ToDash extends SeparatorToSeparator {
	/**
	 * Constructor
	 */
	public function __construct($searchSeparator) {
		parent::__construct ( $searchSeparator, '-' );
	}
}
