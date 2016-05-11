<?php

namespace Techfever\Template\Plugin\Forms;

use Zend\Form\Element;

class Accordion extends Element {
	/**
	 * Seed attributes
	 *
	 * @var array
	 */
	protected $attributes = array (
			'type' => 'accordion' 
	);
}
