<?php

namespace Techfever\Autoresponder;

use Techfever\Exception;
use Techfever\Autoresponder\Email\Email as GEmail;

class Email extends GEmail {
	
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
	 * @var Autoresponder Email Data
	 *     
	 */
	private $email_data = null;
	
	/**
	 * Initial Autoresponder Email
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
