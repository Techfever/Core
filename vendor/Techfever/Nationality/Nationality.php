<?php

namespace Techfever\Nationality;

use Techfever\Exception;

class Nationality extends Country {
	/**
	 * options
	 *
	 * @var mixed
	 */
	protected $options = array ();
	
	/**
	 * Constructor
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		parent::__construct ( $options );
		unset ( $options ['servicelocator'] );
		$this->setOptions ( $options );
	}
}
