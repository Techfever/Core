<?php

namespace Techfever\Content;

use Techfever\Exception;

class Management {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	private $options = array (
			'user_id' => 0,
			'data_id' => 0,
			'label_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content
	 *
	 */
	private $content = null;
	
	/**
	 *
	 * @var Content Data Type
	 *     
	 */
	private $typeobject = null;
	
	/**
	 *
	 * @var Content Data Object
	 *     
	 */
	private $dataobject = null;
	
	/**
	 *
	 * @var Content Label Object
	 *     
	 */
	private $labelobject = null;
	
	/**
	 *
	 * @var Content Tag Object
	 *     
	 */
	private $tagobject = null;
	
	/**
	 * Initial Content
	 */
	public function __construct($options = null) {
		if (! is_array ( $options )) {
			throw new Exception\RuntimeException ( 'Options has not been set or configured.' );
		}
		$options = array_merge ( $this->options, $options );
		$options ['user_id'] = ($options ['user_id'] > 0 ? $options ['user_id'] : 0);
		
		$this->dataobject = new Data ( $options );
		
		$this->typeobject = new Type ( $options );
		
		$this->labelobject = new Label ( $options );
		
		$this->tagobject = new Tag ( $options );
		
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
		if (is_object ( $this->dataobject )) {
			$rawobj = $this->dataobject;
			if (method_exists ( $rawobj, $name )) {
				$obj = $rawobj;
			}
		}
		if (is_object ( $this->typeobject )) {
			$rawobj = $this->typeobject;
			if (method_exists ( $rawobj, $name )) {
				$obj = $rawobj;
			}
		}
		if (is_object ( $this->labelobject )) {
			$rawobj = $this->labelobject;
			if (method_exists ( $rawobj, $name )) {
				$obj = $rawobj;
			}
		}
		if (is_object ( $this->tagobject )) {
			$rawobj = $this->tagobject;
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
	
	/**
	 * Create Content
	 */
	public function createContent($data) {
		$content_id = $this->createDataFactory ( $data );
		if ($content_id != false) {
			return $content_id;
		} else {
			return false;
		}
	}
}
