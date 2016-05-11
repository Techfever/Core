<?php

namespace Techfever\Autoresponder\SMS;

use Techfever\Exception;
use Techfever\Autoresponder\General\General as GeneralBase;

class SMS extends GeneralBase {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	protected $options = array (
			'request' => null,
			'response' => null,
			'controller' => null,
			'route' => null,
			'action' => null,
			'variable' => null,
			'data' => null 
	);
	
	/**
	 *
	 * @var Autoresponder SMS Data
	 *     
	 */
	private $sms_data = null;
	
	/**
	 * Initial Autoresponder SMS
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
