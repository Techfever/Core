<?php

namespace Techfever\Document\Type;

use Techfever\Exception;
use Techfever\Document\General\General as GeneralBase;

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
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Type Data
	 *     
	 */
	private $document_type = null;
	
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
	public function getDocumentType() {
		if (! is_array ( $this->document_type ) || count ( $this->document_type ) < 1) {
			$type_id = $this->getDocumentTypeID ();
			$rawdata = array ();
			$QData = $this->getDatabase ();
			$QData->select ();
			$QData->columns ( array (
					'id' => 'document_type_id',
					'status' => 'document_type_status',
					'code' => 'document_type_code',
					'multi_language_status' => 'document_type_multi_language_status' 
			) );
			$QData->from ( array (
					'ct' => 'document_type' 
			) );
			$QData->where ( array (
					'ct.document_type_id' => $type_id 
			) );
			$QData->limit ( 1 );
			$QData->execute ();
			if ($QData->hasResult ()) {
				while ( $QData->valid () ) {
					$rawdata = $QData->current ();
					$this->document_type = $rawdata;
					$QData->next ();
				}
			}
		}
		return $this->document_type;
	}
	
	/**
	 * Reset Document Type
	 */
	public function resetDocumentType() {
		$this->document_type = null;
	}
	
	/**
	 * Verify Document Type
	 */
	public function verifyDocumentTypeID() {
		$type_id = $this->getDocumentTypeID ();
		if (! empty ( $type_id )) {
			$DBVerify = $this->getDatabase ();
			$DBVerify->select ();
			$DBVerify->columns ( array (
					'id' => 'document_type_id' 
			) );
			$DBVerify->from ( array (
					'ct' => 'document_type' 
			) );
			$where = array (
					'ct.document_type_id = ' . $type_id,
					'ct.document_type_status = 1' 
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
