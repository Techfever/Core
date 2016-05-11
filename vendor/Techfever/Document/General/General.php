<?php

namespace Techfever\Document\General;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class General extends GeneralBase {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	private $options = array (
			'user_id' => 0,
			'data_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 * Initial Document General
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
	
	/**
	 * Set Document User ID
	 */
	public function setDocumentUserID($user_id = null) {
		$this->setOption ( 'user_id', $user_id );
		return $this;
	}
	
	/**
	 * Set Document Data ID
	 */
	public function setDocumentDataID($data_id = null) {
		$this->setOption ( 'data_id', $data_id );
		return $this;
	}
	
	/**
	 * Set Document Type ID
	 */
	public function setDocumentTypeID($type_id = null) {
		$this->setOption ( 'type_id', $type_id );
		return $this;
	}
	
	/**
	 * Set Document Language ID
	 */
	public function setDocumentLanguageID($language_id = null) {
		$this->setOption ( 'language_id', $language_id );
		return $this;
	}
	
	/**
	 * Get Document User ID
	 */
	public function getDocumentUserID() {
		return ( int ) $this->getOption ( 'user_id' );
	}
	
	/**
	 * Get Document Data ID
	 */
	public function getDocumentDataID() {
		return ( int ) $this->getOption ( 'data_id' );
	}
	
	/**
	 * Get Document Type ID
	 */
	public function getDocumentTypeID() {
		return ( int ) $this->getOption ( 'type_id' );
	}
	
	/**
	 * Get Document Language ID
	 */
	public function getDocumentLanguageID() {
		return ( int ) $this->getOption ( 'language_id' );
	}
}
