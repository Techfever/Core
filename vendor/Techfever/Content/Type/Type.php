<?php

namespace Techfever\Content\Type;

use Techfever\Exception;
use Techfever\Content\General\General as GeneralBase;

class Type extends GeneralBase {
	
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
	 * @var Type Data
	 *     
	 */
	private $content_type = null;
	
	/**
	 * Initial Type Data
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
	 * Get Type Data
	 *
	 * @return array()
	 */
	public function getContentType() {
		if (! is_array ( $this->content_type ) || count ( $this->content_type ) < 1) {
			$type_id = $this->getContentTypeID ();
			$rawdata = array ();
			$QData = $this->getDatabase ();
			$QData->select ();
			$QData->columns ( array (
					'id' => 'content_type_id',
					'status' => 'content_type_status',
					'code' => 'content_type_code',
					'multi_language_status' => 'content_type_multi_language_status' 
			) );
			$QData->from ( array (
					'ct' => 'content_type' 
			) );
			$QData->where ( array (
					'ct.content_type_id' => $type_id 
			) );
			$QData->limit ( 1 );
			$QData->execute ();
			if ($QData->hasResult ()) {
				while ( $QData->valid () ) {
					$rawdata = $QData->current ();
					$this->content_type = $rawdata;
					$QData->next ();
				}
			}
		}
		return $this->content_type;
	}
	
	/**
	 * Reset Content Type
	 */
	public function resetContentType() {
		$this->content_type = null;
	}
	
	/**
	 * Verify Content Type
	 */
	public function verifyContentTypeID() {
		$type_id = $this->getContentTypeID ();
		if (! empty ( $type_id )) {
			$DBVerify = $this->getDatabase ();
			$DBVerify->select ();
			$DBVerify->columns ( array (
					'id' => 'content_type_id' 
			) );
			$DBVerify->from ( array (
					'ct' => 'content_type' 
			) );
			$where = array (
					'ct.content_type_id = ' . $type_id,
					'ct.content_type_status = 1' 
			);
			$DBVerify->where ( $where );
			$DBVerify->limit ( 1 );
			$DBVerify->execute ();
			if ($DBVerify->hasResult ()) {
				return true;
			}
		}
		return false;
	}
}
