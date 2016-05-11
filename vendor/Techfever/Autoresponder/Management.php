<?php

namespace Techfever\Autoresponder;

use Techfever\Exception;
use Techfever\Autoresponder\General\General as GeneralBase;

class Management extends GeneralBase {
	
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
	 * @var Autoresponder SMS Object
	 *     
	 */
	private $smsobject = null;
	
	/**
	 *
	 * @var Autoresponder Email Object
	 *     
	 */
	private $emailobject = null;
	
	/**
	 * Initial Content
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		
		$this->emailobject = new Email ( $options );
		
		$this->smsobject = new SMS ( $options );
		
		$this->setServiceLocator ( $options ['servicelocator'] );
		unset ( $options ['servicelocator'] );
		$this->setOptions ( $options );
	}
	
	/**
	 * function call handler
	 *
	 * @param string $function
	 *        	Function name to call
	 * @param array $args
	 *        	Function arguments
	 * @return mixed
	 * @throws Exception\RuntimeException
	 * @throws \Exception
	 */
	public function __call($name, $arguments) {
		$obj = null;
		if (is_object ( $this->emailobject )) {
			$rawobj = $this->emailobject;
			if (method_exists ( $rawobj, $name )) {
				$obj = $rawobj;
			}
		}
		if (is_object ( $this->smsobject )) {
			$rawobj = $this->smsobject;
			if (method_exists ( $rawobj, $name )) {
				$obj = $rawobj;
			}
		}
		if (is_object ( $obj )) {
			if (method_exists ( $obj, $name )) {
				if (is_array ( $arguments ) && count ( $arguments ) > 0) {
					return call_user_func_array ( array (
							$obj,
							$name 
					), $arguments );
				} else {
					return call_user_func ( array (
							$obj,
							$name 
					) );
				}
			}
		}
		return null;
	}
}
