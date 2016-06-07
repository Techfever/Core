<?php

namespace Techfever\Service;

use Techfever\Exception;

class System extends Group {
	
	/**
	 * Options
	 *
	 * @var array
	 */
	protected $options = array ();
	
	/**
	 * Construct an instance of this class.
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$this->setServiceLocator ( $options ['servicelocator'] );
		parent::__construct ( $options );
		unset ( $this->options ['servicelocator'] );
		$this->setOptions ( $options );
	}
}