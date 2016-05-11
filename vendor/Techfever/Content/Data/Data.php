<?php

namespace Techfever\Content\Data;

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
			'data_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content Data
	 *     
	 */
	private $content_data = null;
	
	/**
	 * Initial Content Data
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
	 * Get Content Data
	 *
	 * @return array()
	 */
	public function getData() {
		if (! is_array ( $this->content_data ) || count ( $this->content_data ) < 1) {
			
			$PublishParameter = new Parameter ( array (
					'key' => 'content_data_publish_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$LoginParameter = new Parameter ( array (
					'key' => 'content_data_login_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$data_id = $this->getContentDataID ();
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			$rawdata = array ();
			$QData = $this->getDatabase ();
			$QData->select ();
			$QData->columns ( array (
					'content_data_id',
					'user_access_id',
					'content_type_id',
					'content_data_ref_id',
					'content_data_delete_status',
					'content_data_publish_status',
					'content_data_publish_date',
					'content_data_login_status',
					'content_data_taken_date',
					'content_data_taken_by',
					'content_data_location',
					'content_data_created_date',
					'content_data_created_by',
					'content_data_modified_date',
					'content_data_modified_by' 
			) );
			$QData->from ( array (
					'cd' => 'content_data' 
			) );
			$where = array (
					'cd.content_data_id' => $data_id,
					'cd.content_type_id' => $type_id,
					'cd.content_data_delete_status' => '0' 
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
					$rawdata ['content_data_code'] = $rawdata ['content_data_ref_id'];
					$rawdata ['content_data_publish_date_format'] = "";
					if ($rawdata ['content_data_publish_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_data_publish_date'] );
						$rawdata ['content_data_publish_date_format'] = $datetime->format ( 'd-F-Y' );
					}
					
					$rawdata ['content_data_created_date_format'] = "";
					if ($rawdata ['content_data_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_data_created_date'] );
						$rawdata ['content_data_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_data_taken_date_format'] = "";
					if ($rawdata ['content_data_taken_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_data_taken_date'] );
						$rawdata ['content_data_taken_date_format'] = $datetime->format ( 'd-F-Y' );
					}
					
					$rawdata ['content_data_modified_date_format'] = "";
					if ($rawdata ['content_data_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_data_modified_date'] );
						$rawdata ['content_data_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_data_publish_status_text'] = "";
					if ($PublishParameter->hasResult ()) {
						$rawdata ['content_data_publish_status_text'] = $PublishParameter->getMessageByValue ( $rawdata ['content_data_publish_status'] );
					}
					
					$rawdata ['content_data_login_status_text'] = "";
					if ($LoginParameter->hasResult ()) {
						$rawdata ['content_data_login_status_text'] = $LoginParameter->getMessageByValue ( $rawdata ['content_data_login_status'] );
					}
					
					$rawdata ['content_data_login_status'] = ($rawdata ['content_data_login_status'] == "1" ? True : False);
					
					$cryptID = $this->Encrypt ( $rawdata ['content_data_id'] );
					$rawdata ['modify_value'] = $cryptID;
					
					$this->content_data = $rawdata;
					$QData->next ();
				}
			}
		}
		return $this->content_data;
	}
	
	/**
	 * Reset Content Data
	 */
	public function resetData() {
		$this->content_data = null;
	}
	
	/**
	 * Create Content Data
	 */
	public function createData($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$user_id = $this->getContentUserID ();
			$type_id = $this->getContentTypeID ();
			$code = $this->Encrypt ( ( int ) $user_id . '-' . ( int ) $type_id );
			$IData = $this->getDatabase ();
			$IData->insert ();
			$IData->into ( 'content_data' );
			$IData->values ( array (
					'user_access_id' => $user_id,
					'content_type_id' => $type_id,
					'content_data_ref_id' => $code . '-' . $data ['timestamp'],
					'content_data_publish_status' => $data ['content_data_publish_status'],
					'content_data_publish_date' => $data ['content_data_publish_date'],
					'content_data_login_status' => $data ['content_data_login_status'],
					'content_data_taken_date' => $data ['content_data_taken_date'],
					'content_data_taken_by' => $data ['content_data_taken_by'],
					'content_data_location' => $data ['content_data_location'],
					'content_data_created_date' => $data ['log_created_date'],
					'content_data_modified_date' => $data ['log_modified_date'],
					'content_data_created_by' => $data ['log_created_by'],
					'content_data_modified_by' => $data ['log_modified_by'] 
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
	 * Update Content Data
	 */
	public function updateData($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$id = $this->getContentDataID ();
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			
			$UData = $this->getDatabase ();
			$UData->update ();
			$UData->table ( 'content_data' );
			$UData->set ( array (
					'content_data_publish_status' => $data ['content_data_publish_status'],
					'content_data_publish_date' => $data ['content_data_publish_date'],
					'content_data_login_status' => $data ['content_data_login_status'],
					'content_data_taken_date' => $data ['content_data_taken_date'],
					'content_data_taken_by' => $data ['content_data_taken_by'],
					'content_data_location' => $data ['content_data_location'],
					'content_data_modified_date' => $data ['log_modified_date'],
					'content_data_modified_by' => $data ['log_modified_by'] 
			) );
			$UData->where ( array (
					'content_data_id' => $id,
					'content_type_id' => $type_id,
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
	 * Verify Content Data
	 *
	 * @return Boolean
	 *
	 */
	public function verifyDataID() {
		$id = $this->getContentDataID ();
		$type_id = $this->getContentTypeID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getContentUserID ();
		}
		if (! empty ( $id )) {
			$VData = $this->getDatabase ();
			$VData->select ();
			$VData->columns ( array (
					'id' => 'content_data_id' 
			) );
			$VData->from ( array (
					'cd' => 'content_data' 
			) );
			$where = array (
					'cd.content_data_id = ' . $id,
					'cd.content_type_id = ' . $type_id,
					'cd.content_data_delete_status = 0' 
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
	 * Delete Content Data
	 *
	 * @return Boolean
	 *
	 */
	public function deleteData($forever = false) {
		$id = $this->getContentDataID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getContentUserID ();
		}
		if (! empty ( $id )) {
			if ($forever) {
				$DData = $this->getDatabase ();
				$DData->delete ();
				$DData->from ( 'content_data' );
				$where = array (
						'content_data_id = ' . $id 
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
				$UData->table ( 'content_data' );
				$UData->set ( array (
						'content_data_delete_status' => '1',
						'content_data_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_data_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$where = array (
						'content_data_id = ' . $id 
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
