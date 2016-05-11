<?php

namespace Techfever\Widget;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Permission extends GeneralBase {
	
	/**
	 * options
	 *
	 * @var mixed
	 */
	private $options = array (
			'controllerid' => '',
			'controllername' => '',
			'controlleraction' => 'Initial' 
	);
	
	/**
	 * Constructor
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		if (! isset ( $options ['controllerid'] )) {
			throw new Exception\RuntimeException ( 'Controller ID has not been set or configured.' );
		}
		if (! isset ( $options ['controllername'] )) {
			throw new Exception\RuntimeException ( 'Controller Name has not been set or configured.' );
		}
		if (! isset ( $options ['controlleraction'] )) {
			throw new Exception\RuntimeException ( 'Controller Action has not been set or configured.' );
		}
		if (! isset ( $options ['servicelocator'] )) {
			throw new Exception\RuntimeException ( 'ServiceLocator has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		parent::__construct ( $options );
		unset ( $options ['servicelocator'] );
		$this->setOptions ( $options );
	}
	
	/**
	 * Verify if permission valid
	 *
	 * @param string $controllername        	
	 * @param string $controlleraction        	
	 * @return bool
	 */
	public function verifyPermission($controllername, $controlleraction) {
		return $this->getUserPermission ()->isAllow ( $controllername, $controlleraction );
	}
}
?>