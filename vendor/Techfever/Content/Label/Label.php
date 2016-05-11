<?php

namespace Techfever\Content\Label;

use Techfever\Exception;
use Techfever\Parameter\Parameter;

class Label extends Detail {
	
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
	 * @var Content Label
	 *     
	 */
	private $content_label = null;
	
	/**
	 * Initial Content Label
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
	 * Get Content Label
	 *
	 * @return array()
	 */
	public function getLabel() {
		if (! is_array ( $this->content_label ) || count ( $this->content_label ) < 1) {
			
			$PublishParameter = new Parameter ( array (
					'key' => 'content_label_publish_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$LoginParameter = new Parameter ( array (
					'key' => 'content_label_login_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$label_id = $this->getContentLabelID ();
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			$rawdata = array ();
			$QLabel = $this->getDatabase ();
			$QLabel->select ();
			$QLabel->columns ( array (
					'content_label_id',
					'user_access_id',
					'content_type_id',
					'content_label_ref_id',
					'content_label_delete_status',
					'content_label_publish_status',
					'content_label_publish_date',
					'content_label_login_status',
					'content_label_created_date',
					'content_label_created_by',
					'content_label_modified_date',
					'content_label_modified_by' 
			) );
			$QLabel->from ( array (
					'cd' => 'content_label' 
			) );
			$where = array (
					'cd.content_label_id' => $label_id,
					'cd.content_type_id' => $type_id,
					'cd.content_label_delete_status' => '0' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$QLabel->where ( $where );
			$QLabel->limit ( 1 );
			$QLabel->execute ();
			if ($QLabel->hasResult ()) {
				while ( $QLabel->valid () ) {
					$rawdata = $QLabel->current ();
					$rawdata ['content_label_code'] = $rawdata ['content_label_ref_id'];
					$rawdata ['content_label_publish_date_format'] = "";
					if ($rawdata ['content_label_publish_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_label_publish_date'] );
						$rawdata ['content_label_publish_date_format'] = $datetime->format ( 'd-F-Y' );
					}
					
					$rawdata ['content_label_created_date_format'] = "";
					if ($rawdata ['content_label_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_label_created_date'] );
						$rawdata ['content_label_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_label_modified_date_format'] = "";
					if ($rawdata ['content_label_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_label_modified_date'] );
						$rawdata ['content_label_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_label_publish_status_text'] = "";
					if ($PublishParameter->hasResult ()) {
						$rawdata ['content_label_publish_status_text'] = $PublishParameter->getMessageByValue ( $rawdata ['content_label_publish_status'] );
					}
					
					$rawdata ['content_label_login_status_text'] = "";
					if ($LoginParameter->hasResult ()) {
						$rawdata ['content_label_login_status_text'] = $LoginParameter->getMessageByValue ( $rawdata ['content_label_login_status'] );
					}
					
					$rawdata ['content_label_login_status'] = ($rawdata ['content_label_login_status'] == "1" ? True : False);
					
					$cryptID = $this->Encrypt ( $rawdata ['content_label_id'] );
					$rawdata ['modify_value'] = $cryptID;
					
					$this->content_label = $rawdata;
					$QLabel->next ();
				}
			}
		}
		return $this->content_label;
	}
	
	/**
	 * Reset Content Label
	 */
	public function resetLabel() {
		$this->content_label = null;
	}
	
	/**
	 * Create Content Label
	 */
	public function createLabel($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$user_id = $this->getContentUserID ();
			$type_id = $this->getContentTypeID ();
			$code = $this->Encrypt ( ( int ) $user_id . '-' . ( int ) $type_id );
			$ILabel = $this->getDatabase ();
			$ILabel->insert ();
			$ILabel->into ( 'content_label' );
			$ILabel->values ( array (
					'user_access_id' => $user_id,
					'content_type_id' => $type_id,
					'content_label_ref_id' => $code . '-' . $data ['timestamp'],
					'content_label_publish_status' => $data ['content_label_publish_status'],
					'content_label_publish_date' => $data ['content_label_publish_date'],
					'content_label_login_status' => $data ['content_label_login_status'],
					'content_label_created_date' => $data ['log_created_date'],
					'content_label_modified_date' => $data ['log_modified_date'],
					'content_label_created_by' => $data ['log_created_by'],
					'content_label_modified_by' => $data ['log_modified_by'] 
			) );
			$ILabel->execute ();
			if ($ILabel->affectedRows ()) {
				$id = $ILabel->getLastGeneratedValue ();
				return $id;
			}
		}
		return $status;
	}
	
	/**
	 * Update Content Label
	 */
	public function updateLabel($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$id = $this->getContentLabelID ();
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			
			$ULabel = $this->getDatabase ();
			$ULabel->update ();
			$ULabel->table ( 'content_label' );
			$ULabel->set ( array (
					'content_label_publish_status' => $data ['content_label_publish_status'],
					'content_label_publish_date' => $data ['content_label_publish_date'],
					'content_label_login_status' => $data ['content_label_login_status'],
					'content_label_modified_date' => $data ['log_modified_date'],
					'content_label_modified_by' => $data ['log_modified_by'] 
			) );
			$ULabel->where ( array (
					'content_label_id' => $id,
					'content_type_id' => $type_id,
					'user_access_id' => $user_id 
			) );
			$ULabel->execute ();
			if ($ULabel->affectedRows ()) {
				$status = true;
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Label
	 *
	 * @return Boolean
	 *
	 */
	public function verifyLabelID() {
		$id = $this->getContentLabelID ();
		$type_id = $this->getContentTypeID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getContentUserID ();
		}
		if (! empty ( $id )) {
			$VLabel = $this->getDatabase ();
			$VLabel->select ();
			$VLabel->columns ( array (
					'id' => 'content_label_id' 
			) );
			$VLabel->from ( array (
					'cd' => 'content_label' 
			) );
			$where = array (
					'cd.content_label_id = ' . $id,
					'cd.content_type_id = ' . $type_id,
					'cd.content_label_delete_status = 0' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$VLabel->where ( $where );
			$VLabel->limit ( 1 );
			$VLabel->execute ();
			if ($VLabel->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Label
	 *
	 * @return Boolean
	 *
	 */
	public function deleteLabel($forever = false) {
		$id = $this->getContentLabelID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getContentUserID ();
		}
		if (! empty ( $id )) {
			if ($forever) {
				$DLabel = $this->getDatabase ();
				$DLabel->delete ();
				$DLabel->from ( 'content_label' );
				$where = array (
						'content_label_id = ' . $id 
				);
				if (! is_null ( $user_id ) && $user_id > 0) {
					$where [] = 'user_access_id = "' . $user_id . '"';
				}
				$DLabel->where ( $where );
				$DLabel->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$ULabel = $this->getDatabase ();
				$ULabel->update ();
				$ULabel->table ( 'content_label' );
				$ULabel->set ( array (
						'content_label_delete_status' => '1',
						'content_label_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_label_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$where = array (
						'content_label_id = ' . $id 
				);
				if (! is_null ( $user_id ) && $user_id > 0) {
					$where [] = 'user_access_id = "' . $user_id . '"';
				}
				$ULabel->where ( $where );
				$ULabel->execute ();
				return true;
			}
		}
		return false;
	}
}
