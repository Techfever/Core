<?php

namespace Techfever\Document\Data;

use Techfever\Exception;
use Techfever\Parameter\Parameter;

class Data extends Detail {
	
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
	 * @var Document Data
	 *     
	 */
	private $document_data = null;
	
	/**
	 * Initial Document Data
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
	 * Get Document Data
	 *
	 * @return array()
	 */
	public function getData() {
		if (! is_array ( $this->document_data ) || count ( $this->document_data ) < 1) {
			
			$PublishParameter = new Parameter ( array (
					'key' => 'document_data_publish_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$LoginParameter = new Parameter ( array (
					'key' => 'document_data_login_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$data_id = $this->getDocumentDataID ();
			$type_id = $this->getDocumentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getDocumentUserID ();
			}
			$rawdata = array ();
			$QData = $this->getDatabase ();
			$QData->select ();
			$QData->columns ( array (
					'document_data_id',
					'user_access_id',
					'document_type_id',
					'document_data_ref_id',
					'document_data_delete_status',
					'document_data_publish_status',
					'document_data_publish_date',
					'document_data_login_status',
					'document_data_taken_date',
					'document_data_taken_by',
					'document_data_location',
					'document_data_created_date',
					'document_data_created_by',
					'document_data_modified_date',
					'document_data_modified_by' 
			) );
			$QData->from ( array (
					'cd' => 'document_data' 
			) );
			$where = array (
					'cd.document_data_id' => $data_id,
					'cd.document_type_id' => $type_id,
					'cd.document_data_delete_status' => '0' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$QData->where ( $where );
			$QData->limit ( 1 );
			$QData->execute ();
			if ($QData->hasResult ()) {
				while ( $QData->valid () ) {
					$rawdata = $QData->current ();
					$rawdata ['document_data_code'] = $rawdata ['document_data_ref_id'];
					$rawdata ['document_data_publish_date_format'] = "";
					if ($rawdata ['document_data_publish_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['document_data_publish_date'] );
						$rawdata ['document_data_publish_date_format'] = $datetime->format ( 'd-F-Y' );
					}
					
					$rawdata ['document_data_created_date_format'] = "";
					if ($rawdata ['document_data_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['document_data_created_date'] );
						$rawdata ['document_data_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['document_data_taken_date_format'] = "";
					if ($rawdata ['document_data_taken_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['document_data_taken_date'] );
						$rawdata ['document_data_taken_date_format'] = $datetime->format ( 'd-F-Y' );
					}
					
					$rawdata ['document_data_modified_date_format'] = "";
					if ($rawdata ['document_data_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['document_data_modified_date'] );
						$rawdata ['document_data_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['document_data_publish_status_text'] = "";
					if ($PublishParameter->hasResult ()) {
						$rawdata ['document_data_publish_status_text'] = $PublishParameter->getMessageByValue ( $rawdata ['document_data_publish_status'] );
					}
					
					$rawdata ['document_data_login_status_text'] = "";
					if ($LoginParameter->hasResult ()) {
						$rawdata ['document_data_login_status_text'] = $LoginParameter->getMessageByValue ( $rawdata ['document_data_login_status'] );
					}
					
					$rawdata ['document_data_login_status'] = ($rawdata ['document_data_login_status'] == "1" ? True : False);
					
					$cryptID = $this->Encrypt ( $rawdata ['document_data_id'] );
					$rawdata ['document_data_modify'] = $cryptID;
					
					$this->document_data = $rawdata;
					$QData->next ();
				}
			}
		}
		return $this->document_data;
	}
	
	/**
	 * Reset Document Data
	 */
	public function resetData() {
		$this->document_data = null;
	}
	
	/**
	 * Create Document Data
	 */
	public function createData($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$user_id = $this->getDocumentUserID ();
			$type_id = $this->getDocumentTypeID ();
			$code = $this->Encrypt ( ( int ) $user_id . '-' . ( int ) $type_id );
			$IData = $this->getDatabase ();
			$IData->insert ();
			$IData->into ( 'document_data' );
			$IData->values ( array (
					'user_access_id' => $user_id,
					'document_type_id' => $type_id,
					'document_data_ref_id' => $code . '-' . $data ['timestamp'],
					'document_data_publish_status' => $data ['document_data_publish_status'],
					'document_data_publish_date' => $data ['document_data_publish_date'],
					'document_data_login_status' => $data ['document_data_login_status'],
					'document_data_taken_date' => $data ['document_data_taken_date'],
					'document_data_taken_by' => $data ['document_data_taken_by'],
					'document_data_location' => $data ['document_data_location'],
					'document_data_created_date' => $data ['log_created_date'],
					'document_data_modified_date' => $data ['log_modified_date'],
					'document_data_created_by' => $data ['log_created_by'],
					'document_data_modified_by' => $data ['log_modified_by'] 
			) );
			$IData->execute ();
			if ($IData->affectedRows ()) {
				$id = $IData->getLastGeneratedValue ();
				return $id;
			}
		}
		return $status;
	}
	
	/**
	 * Update Document Data
	 */
	public function updateData($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$id = $this->getDocumentDataID ();
			$type_id = $this->getDocumentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getDocumentUserID ();
			}
			
			$UData = $this->getDatabase ();
			$UData->update ();
			$UData->table ( 'document_data' );
			$UData->set ( array (
					'document_data_publish_status' => $data ['document_data_publish_status'],
					'document_data_publish_date' => $data ['document_data_publish_date'],
					'document_data_login_status' => $data ['document_data_login_status'],
					'document_data_taken_date' => $data ['document_data_taken_date'],
					'document_data_taken_by' => $data ['document_data_taken_by'],
					'document_data_location' => $data ['document_data_location'],
					'document_data_modified_date' => $data ['log_modified_date'],
					'document_data_modified_by' => $data ['log_modified_by'] 
			) );
			$UData->where ( array (
					'document_data_id' => $id,
					'document_type_id' => $type_id,
					'user_access_id' => $user_id 
			) );
			$UData->execute ();
			if ($UData->affectedRows ()) {
				$status = true;
			}
		}
		return $status;
	}
	
	/**
	 * Verify Document Data
	 *
	 * @return Boolean
	 *
	 */
	public function verifyDataID() {
		$id = $this->getDocumentDataID ();
		$type_id = $this->getDocumentTypeID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getDocumentUserID ();
		}
		if (! empty ( $id )) {
			$VData = $this->getDatabase ();
			$VData->select ();
			$VData->columns ( array (
					'id' => 'document_data_id' 
			) );
			$VData->from ( array (
					'cd' => 'document_data' 
			) );
			$where = array (
					'cd.document_data_id = ' . $id,
					'cd.document_type_id = ' . $type_id,
					'cd.document_data_delete_status = 0' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$VData->where ( $where );
			$VData->limit ( 1 );
			$VData->execute ();
			if ($VData->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Document Data
	 *
	 * @return Boolean
	 *
	 */
	public function deleteData($forever = false) {
		$id = $this->getDocumentDataID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getDocumentUserID ();
		}
		if (! empty ( $id )) {
			if ($forever) {
				$DData = $this->getDatabase ();
				$DData->delete ();
				$DData->from ( 'document_data' );
				$where = array (
						'document_data_id = ' . $id 
				);
				if (! is_null ( $user_id ) && $user_id > 0) {
					$where [] = 'user_access_id = "' . $user_id . '"';
				}
				$DData->where ( $where );
				$DData->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$UData = $this->getDatabase ();
				$UData->update ();
				$UData->table ( 'document_data' );
				$UData->set ( array (
						'document_data_delete_status' => '1',
						'document_data_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'document_data_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$where = array (
						'document_data_id = ' . $id 
				);
				if (! is_null ( $user_id ) && $user_id > 0) {
					$where [] = 'user_access_id = "' . $user_id . '"';
				}
				$UData->where ( $where );
				$UData->execute ();
				return true;
			}
		}
		return false;
	}
}
