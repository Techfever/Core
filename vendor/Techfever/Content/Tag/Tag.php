<?php

namespace Techfever\Content\Tag;

use Techfever\Exception;
use Techfever\Parameter\Parameter;

class Tag extends Detail {
	
	/**
	 * Option
	 *
	 * @var option
	 *
	 */
	private $options = array (
			'user_id' => 0,
			'data_id' => 0,
			'tag_id' => 0,
			'tag_id' => 0,
			'type_id' => 0,
			'language_id' => 0 
	);
	
	/**
	 *
	 * @var Content Tag
	 *     
	 */
	private $content_tag = null;
	
	/**
	 * Initial Content Tag
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
	 * Get Content Tag
	 *
	 * @return array()
	 */
	public function getTag() {
		if (! is_array ( $this->content_tag ) || count ( $this->content_tag ) < 1) {
			
			$PublishParameter = new Parameter ( array (
					'key' => 'content_tag_publish_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$LoginParameter = new Parameter ( array (
					'key' => 'content_tag_login_status',
					'servicelocator' => $this->getServiceLocator () 
			) );
			
			$tag_id = $this->getContentTagID ();
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			$rawdata = array ();
			$QTag = $this->getDatabase ();
			$QTag->select ();
			$QTag->columns ( array (
					'content_tag_id',
					'user_access_id',
					'content_type_id',
					'content_tag_ref_id',
					'content_tag_delete_status',
					'content_tag_publish_status',
					'content_tag_publish_date',
					'content_tag_login_status',
					'content_tag_created_date',
					'content_tag_created_by',
					'content_tag_modified_date',
					'content_tag_modified_by' 
			) );
			$QTag->from ( array (
					'cd' => 'content_tag' 
			) );
			$where = array (
					'cd.content_tag_id' => $tag_id,
					'cd.content_type_id' => $type_id,
					'cd.content_tag_delete_status' => '0' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$QTag->where ( $where );
			$QTag->limit ( 1 );
			$QTag->execute ();
			if ($QTag->hasResult ()) {
				while ( $QTag->valid () ) {
					$rawdata = $QTag->current ();
					$rawdata ['content_tag_code'] = $rawdata ['content_tag_ref_id'];
					$rawdata ['content_tag_publish_date_format'] = "";
					if ($rawdata ['content_tag_publish_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_tag_publish_date'] );
						$rawdata ['content_tag_publish_date_format'] = $datetime->format ( 'd-F-Y' );
					}
					
					$rawdata ['content_tag_created_date_format'] = "";
					if ($rawdata ['content_tag_created_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_tag_created_date'] );
						$rawdata ['content_tag_created_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_tag_modified_date_format'] = "";
					if ($rawdata ['content_tag_modified_date'] !== "0000-00-00 00:00:00") {
						$datetime = new \DateTime ( $rawdata ['content_tag_modified_date'] );
						$rawdata ['content_tag_modified_date_format'] = $datetime->format ( 'H:i:s d-F-Y' );
					}
					
					$rawdata ['content_tag_publish_status_text'] = "";
					if ($PublishParameter->hasResult ()) {
						$rawdata ['content_tag_publish_status_text'] = $PublishParameter->getMessageByValue ( $rawdata ['content_tag_publish_status'] );
					}
					
					$rawdata ['content_tag_login_status_text'] = "";
					if ($LoginParameter->hasResult ()) {
						$rawdata ['content_tag_login_status_text'] = $LoginParameter->getMessageByValue ( $rawdata ['content_tag_login_status'] );
					}
					
					$rawdata ['content_tag_login_status'] = ($rawdata ['content_tag_login_status'] == "1" ? True : False);
					
					$cryptID = $this->Encrypt ( $rawdata ['content_tag_id'] );
					$rawdata ['modify_value'] = $cryptID;
					
					$this->content_tag = $rawdata;
					$QTag->next ();
				}
			}
		}
		return $this->content_tag;
	}
	
	/**
	 * Reset Content Tag
	 */
	public function resetTag() {
		$this->content_tag = null;
	}
	
	/**
	 * Create Content Tag
	 */
	public function createTag($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$user_id = $this->getContentUserID ();
			$type_id = $this->getContentTypeID ();
			$code = $this->Encrypt ( ( int ) $user_id . '-' . ( int ) $type_id );
			$ITag = $this->getDatabase ();
			$ITag->insert ();
			$ITag->into ( 'content_tag' );
			$ITag->values ( array (
					'user_access_id' => $user_id,
					'content_type_id' => $type_id,
					'content_tag_ref_id' => $code . '-' . $data ['timestamp'],
					'content_tag_publish_status' => $data ['content_tag_publish_status'],
					'content_tag_publish_date' => $data ['content_tag_publish_date'],
					'content_tag_login_status' => $data ['content_tag_login_status'],
					'content_tag_created_date' => $data ['log_created_date'],
					'content_tag_modified_date' => $data ['log_modified_date'],
					'content_tag_created_by' => $data ['log_created_by'],
					'content_tag_modified_by' => $data ['log_modified_by'] 
			) );
			$ITag->execute ();
			if ($ITag->affectedRows ()) {
				$id = $ITag->getLastGeneratedValue ();
				return $id;
			}
		}
		return $status;
	}
	
	/**
	 * Update Content Tag
	 */
	public function updateTag($data) {
		$status = false;
		if (count ( $data ) > 0) {
			$id = $this->getContentTagID ();
			$type_id = $this->getContentTypeID ();
			$user_id = null;
			if ($this->isAdminUser ()) {
				$user_id = $this->getContentUserID ();
			}
			
			$UTag = $this->getDatabase ();
			$UTag->update ();
			$UTag->table ( 'content_tag' );
			$UTag->set ( array (
					'content_tag_publish_status' => $data ['content_tag_publish_status'],
					'content_tag_publish_date' => $data ['content_tag_publish_date'],
					'content_tag_modified_date' => $data ['log_modified_date'],
					'content_tag_modified_by' => $data ['log_modified_by'] 
			) );
			$UTag->where ( array (
					'content_tag_id' => $id,
					'content_type_id' => $type_id,
					'user_access_id' => $user_id 
			) );
			$UTag->execute ();
			if ($UTag->affectedRows ()) {
				$status = true;
			}
		}
		return $status;
	}
	
	/**
	 * Verify Content Tag
	 *
	 * @return Boolean
	 *
	 */
	public function verifyTagID() {
		$id = $this->getContentTagID ();
		$type_id = $this->getContentTypeID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getContentUserID ();
		}
		if (! empty ( $id )) {
			$VTag = $this->getDatabase ();
			$VTag->select ();
			$VTag->columns ( array (
					'id' => 'content_tag_id' 
			) );
			$VTag->from ( array (
					'cd' => 'content_tag' 
			) );
			$where = array (
					'cd.content_tag_id = ' . $id,
					'cd.content_type_id = ' . $type_id,
					'cd.content_tag_delete_status = 0' 
			);
			if (! is_null ( $user_id ) && $user_id > 0) {
				$where [] = 'cd.user_access_id = "' . $user_id . '"';
			}
			$VTag->where ( $where );
			$VTag->limit ( 1 );
			$VTag->execute ();
			if ($VTag->hasResult ()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete Content Tag
	 *
	 * @return Boolean
	 *
	 */
	public function deleteTag($forever = false) {
		$id = $this->getContentTagID ();
		$user_id = null;
		if ($this->isAdminUser ()) {
			$user_id = $this->getContentUserID ();
		}
		if (! empty ( $id )) {
			if ($forever) {
				$DTag = $this->getDatabase ();
				$DTag->delete ();
				$DTag->from ( 'content_tag' );
				$where = array (
						'content_tag_id = ' . $id 
				);
				if (! is_null ( $user_id ) && $user_id > 0) {
					$where [] = 'user_access_id = "' . $user_id . '"';
				}
				$DTag->where ( $where );
				$DTag->execute ();
				return true;
			} else {
				$datetime = new \DateTime ();
				$UTag = $this->getDatabase ();
				$UTag->update ();
				$UTag->table ( 'content_tag' );
				$UTag->set ( array (
						'content_tag_delete_status' => '1',
						'content_tag_modified_date' => $datetime->format ( 'Y-m-d H:i:s' ),
						'content_tag_modified_by' => ($this->getUserAccess ()->isLogin () ? $this->getUserAccess ()->getUsername () : 'Unknown') 
				) );
				$where = array (
						'content_tag_id = ' . $id 
				);
				if (! is_null ( $user_id ) && $user_id > 0) {
					$where [] = 'user_access_id = "' . $user_id . '"';
				}
				$UTag->where ( $where );
				$UTag->execute ();
				return true;
			}
		}
		return false;
	}
}
