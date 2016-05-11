<?php

namespace Techfever\Content\General;

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
			'label_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 * Initial Content General
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
	 * Set Content User ID
	 */
	public function setContentUserID($user_id = null) {
		$this->setOption ( 'user_id', $user_id );
		return $this;
	}
	
	/**
	 * Set Content Data ID
	 */
	public function setContentDataID($data_id = null) {
		$this->setOption ( 'data_id', $data_id );
		return $this;
	}
	
	/**
	 * Set Content Label ID
	 */
	public function setContentLabelID($label_id = null) {
		$this->setOption ( 'label_id', $label_id );
		return $this;
	}
	
	/**
	 * Set Content Tag ID
	 */
	public function setContentTagID($tag_id = null) {
		$this->setOption ( 'tag_id', $tag_id );
		return $this;
	}
	
	/**
	 * Set Content Type ID
	 */
	public function setContentTypeID($type_id = null) {
		$this->setOption ( 'type_id', $type_id );
		return $this;
	}
	
	/**
	 * Set Content Language ID
	 */
	public function setContentLanguageID($language_id = null) {
		$this->setOption ( 'language_id', $language_id );
		return $this;
	}
	
	/**
	 * Get Content User ID
	 */
	public function getContentUserID() {
		return ( int ) $this->getOption ( 'user_id' );
	}
	
	/**
	 * Get Content Data ID
	 */
	public function getContentDataID() {
		return ( int ) $this->getOption ( 'data_id' );
	}
	
	/**
	 * Get Content Label ID
	 */
	public function getContentLabelID() {
		return ( int ) $this->getOption ( 'label_id' );
	}
	
	/**
	 * Get Content Tag ID
	 */
	public function getContentTagID() {
		return ( int ) $this->getOption ( 'tag_id' );
	}
	
	/**
	 * Get Content Type ID
	 */
	public function getContentTypeID() {
		return ( int ) $this->getOption ( 'type_id' );
	}
	
	/**
	 * Get Content Language ID
	 */
	public function getContentLanguageID() {
		return ( int ) $this->getOption ( 'language_id' );
	}
}
